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

	public $csrf_config = false;
	public $route_config = false;

	public function __construct()
	{
		parent::__construct();
		$this->csrf_config = Kohana::config('csrf');
		$this->route_config = Kohana::config('routes');
	}

	public function index()
	{
		if ($this->is_locked_out()) {
			url::redirect('default/locked_out');
		}
		//$this->template-> = $this->add_view('menu');
		$this->template->title = $this->translate->_('Ninja');

	}

	public function show_login()
	{
		$this->template->content = $this->add_view('login');
		$this->template->content->error_msg = $this->session->get('error_msg', false);
		$this->template->content->form_title =$this->translate->_('Login');
		$this->template->content->username =$this->translate->_('Username');
		$this->template->content->password =$this->translate->_('Password');
		$this->template->content->login_btn_txt =$this->translate->_('Login');
		$this->template->title = $this->translate->_('Ninja > Login');

	}

	/**
	 * Show message (stored in session and set by do_login() below)
	 * to inform that user has been locked out due to too many failed
	 * login attempts
	 */
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
			if ($this->csrf_config['csrf_token']!='' && $this->csrf_config['active'] !== false && !csrf::valid($user->{$this->csrf_config['csrf_token']})) {
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
			url::redirect($this->route_config['_default']);
		} else {
			url::redirect($this->route_config['logged_in_default']);
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
