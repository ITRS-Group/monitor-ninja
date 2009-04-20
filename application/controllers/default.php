<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Default controller.
 * Does not require login but should display default page
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Default_Controller extends Ninja_Controller  {

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		# test with ORM
#		$users = ORM::factory('user')->find_all();
		#echo kohana::debug($users);
#		foreach ($users as $user) {
#			echo $user->email."<br>";
#		}
#		echo "<hr>";

		if ($this->is_locked_out()) {
			url::redirect('default/locked_out');
		}
		$this->template->content = $this->add_view('ninja_start');
		$this->template->title = $this->translate->_('NINJA::start page');
		$this->template->content->links = array
		(
			'tac' 	=> 'tac/index',
			'admin' => 'admin'
		);
		$this->template->content->info = sprintf($this->translate->_('This is the default Ninja index page.%sYou may also access this page as %s.%sThis page does not require authentication.'), '<br />', html::anchor('default/index', 'default/index'), '<br /><br />');
	}

	public function show_login()
	{
		#$this->session = Session::instance();
		$this->template->content = $this->add_view('login');
		$this->template->content->error_msg = $this->session->get('error_msg', false);
		$this->template->content->form_title =$this->translate->_('Login form');
		$this->template->content->username =$this->translate->_('Username');
		$this->template->content->password =$this->translate->_('Password');
		$this->template->content->login_btn_txt =$this->translate->_('Login!');
		$this->template->title = $this->translate->_('NINJA::login');

	}

	public function locked_out()
	{
		echo $this->session->get('error_msg');
	}

	/**
	 * Check if we should check if user has tried
	 * to login too many times
	 *
	 * @return bool
	 */
	public function is_locked_out()
	{
		if ($this->session->get('locked_out') && $this->max_attempts) {
			return true;
		}
		return false;
	}
	/**
	 * Collect user input from login form, authenticate against
	 * Auth module and redirect to controller requested by user.
	 */
	public function do_login()
	{
		if ($_POST) {
			$post = Validation::factory($_POST);
			$post->add_rules('*', 'required');

			# validate that we have both username and password
			if (!$post->validate() ) {
				$error_msg = $this->translate->_("Please supply both username and password");
				$this->session->set_flash('error_msg', $error_msg);
				url::redirect('default/show_login');
			}

			foreach ($_POST as $key => $val) {
				$user->$key = $val;
			}
			if (!csrf::valid($user->{Kohana::config('csrf.csrf_token')})) {
				$error_msg = $this->translate->_("Request forgery attack detected");
				$this->session->set_flash('error_msg', $error_msg);
				url::redirect('default/show_login');
			}
			if (!Auth::instance()->login($user->username, $user->password)) {

				# increase login attempts counter
				# this could be used to restrict access after
				#  a certain nr of failed login attempts by setting
				# 'max_attempts' in auth config
				$this->session->set('login_attempts', $this->session->get('login_attempts')+1);

				# set login error to user
				$error_msg = $this->translate->_("Login failed - please try again");
				if ($this->max_attempts) {
					$error_msg .= " (".($this->max_attempts - $this->session->get('login_attempts'))." left)";
				}

				if ($this->max_attempts && $this->session->get('login_attempts') >= $this->max_attempts) {
					$error_msg = sprintf($this->translate->_("You have been locked out due to %s failed login attempts"), $this->session->get('login_attempts'));
					$this->session->set('error_msg', $error_msg);
					$this->session->set('locked_out', true);
					url::redirect('default/locked_out');
				}

				$this->session->set_flash('error_msg', $error_msg);
				url::redirect('default/show_login');
			}
			#echo Kohana::debug($_SESSION);
			#die('lkj');
			$user_data = Auth::instance()->get_user()->last_login;
			User_Model::complete_login($user_data);
		}

		# trying to login without $_POST is not allowed and shouldn't
		# even happen - redirecting to default routes
		if (!Auth::instance()->logged_in()) {
			url::redirect(Kohana::config('routes._default'));
		} else {
			url::redirect(Kohana::config('routes.logged_in_default'));
		}
	}

	/**
	 * Logout user, remove session and redirect
	 *
	 */
	public function logout()
	{
		User_Model::logout_user();
		url::redirect('default/');
	}
}
