<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Default controller.
 * Does not require login but should display default page
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
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
		/* FIXME: use some more generic method in Auth module
		if (Kohana::config('auth.driver') == 'apache') {
			if (isset($_SESSION['username'])) {
				Auth::instance()->driver->login($_SESSION['username'], false, false);
				$this->apache_login();
			} else {
				header('location: ' . Kohana::config('auth.apache_login'));
			}
			exit;
		}
		*/

		$this->session->delete('auth_user');
		$this->session->delete('nagios_access');
		$this->session->delete('contact_id');
		$this->session->delete('auth_method');
		$this->template = $this->add_view('login');
		$this->template->error_msg = $this->session->get('error_msg', false);
		$this->template->form_title =$this->translate->_('Login');
		$this->template->username =$this->translate->_('Username');
		$this->template->password =$this->translate->_('Password');
		$this->template->login_btn_txt =$this->translate->_('Login');
		$this->template->title = $this->translate->_('Ninja Login');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = array('application/media/js/jquery.min.js', $this->add_path('/js/login.js'));

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
	 * Check if the user has tried
	 * to login too many times
	 *
	 * @return bool
	 */
	public function is_locked_out()
	{
		if ($this->session->get('locked_out') && Kohana::config('auth.max_attempts')) {
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
		# check if we should allow login by GET params
		if (Kohana::config('auth.use_get_auth')
			&& array_key_exists('username', $_GET)
			&& array_key_exists('password', $_GET)) {
				$_POST['username'] = $_GET['username'];
				$_POST['password'] = $_GET['password'];
				$_POST['auth_method'] = $this->input->get('auth_method', false);
		}

		if ($_POST) {
			$post = Validation::factory($_POST);
			$post->add_rules('*', 'required');

			# validate that we have both username and password
			if (!$post->validate() ) {
				$error_msg = $this->translate->_("Please supply both username and password");
				$this->session->set_flash('error_msg', $error_msg);
				url::redirect('default/show_login');
			}

			if ($this->csrf_config['csrf_token']!='' && $this->csrf_config['active'] !== false && !csrf::valid($this->input->post($this->csrf_config['csrf_token']))) {
				$error_msg = $this->translate->_("CSRF tokens did not match.<br />This often happen when your browser opens cached windows (after restarting the browser, for example).<br />Try to login again.");
				$this->session->set_flash('error_msg', $error_msg);
				url::redirect('default/show_login');
			}

			$username    = $this->input->post('username', false);
			$password    = $this->input->post('password', false);
			$auth_method = $this->input->post('auth_method', false);

			$res = ninja_auth::login_user($username, $password, $auth_method);
			if ($res !== true) {
				url::redirect($res);
			}
			
			$requested_uri = Session::instance()->get('requested_uri', false);
			# make sure we don't end up in infinite loop
			# if user managed to request show_login
			if ($requested_uri == Kohana::config('routes.log_in_form')) {
				$requested_uri = Kohana::config('routes.logged_in_default');
			}
			if ($requested_uri !== false) {
				# remove 'requested_uri' from session
				Session::instance()->delete('requested_uri');
				url::redirect($requested_uri);
			} else {
				# we have no requested uri
				# using logged_in_default from routes config
				#die('going to default');
				url::redirect(Kohana::config('routes.logged_in_default'));
			}
		}

		# trying to login without $_POST is not allowed and shouldn't
		# even happen - redirecting to default routes
		if (!isset($auth) || !$auth->logged_in()) {
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
		if (Kohana::config('auth.driver') == 'apache') {
			# unset some session variables
			$this->session->delete('username');
			$this->session->delete('auth_user');
			$this->session->delete('nagios_access');
			$this->session->delete('contact_id');
			$this->template = $this->add_view('logged_out');
			return;
		}
		url::redirect('default/');
	}

	/**
	*	Finalize login using the apache driver
	*/
	public function apache_login()
	{
		if (empty($_SESSION['username']) || !Auth::instance()->logged_in()) {
			die('Error!');
		}

		User_Model::complete_login();
	}

	/**
	*	Display an error message about no available
	* 	objects for a valid user. This page is used when
	* 	we are using login through apache.
	*/
	public function no_objects()
	{
		# unset some session variables
		$this->session->delete('username');
		$this->session->delete('auth_user');
		$this->session->delete('nagios_access');
		$this->session->delete('contact_id');

		$this->template = $this->add_view('no_objects');
		$this->template->error_msg = $this->translate->_("You have been denied access since you aren't authorized for any objects.");
	}

	/**
	*	If called by PHP CLI this will return a username
	*	of the first user with login access. This is needed
	* 	for the install script to be able to import authorization
	* 	data from cgi.cfg
	*/
	public function get_a_user()
	{
		if (PHP_SAPI !== "cli") {
			url::redirect('default/index');
		} else {
			$this->auto_render=false;
			$user = User_Model::get_user();
			echo $user->username;
		}
	}

	/**
	*	Used from CLI calls to detect cli setting and
	* 	possibly default access from config file
	*/
	public function get_cli_status()
	{
		if (PHP_SAPI !== "cli") {
			url::redirect('default/index');
		} else {
			$this->auto_render=false;
			$cli_access =Kohana::config('config.cli_access');
			echo $cli_access;
		}
	}

	/**
	 * Accept a call from cron to look for scheduled reports to send
	 * @param string $period_str [Daily, Weekly, Monthly, downtime]
	 */
	public function cron($period_str=false)
	{
		if (PHP_SAPI !== "cli") {
			die("illegal call\n");
		}
		$this->auto_render=false;
		$cli_access = Kohana::config('config.cli_access');

		if (empty($cli_access)) {
			# CLI access is turned off in config/config.php
			echo "no cli access\n";
			return false;
		}

		# figure out path from argv
		$path = $GLOBALS['argv'][0];

		$user = false;
		if ($cli_access == 1) {
			exec('/usr/bin/php '.$path.' default/get_a_user ', $user, $retval);
			$user = $user[0];
		} else {
			# username is hard coded so let's use this
			$user = $cli_access;
		}

		if (empty($user)) {
			# we failed to detect a valid user so there's no use in continuing
			return false;
		}

		$retval = 0;
		if ($period_str === 'downtime') {
			exec('/usr/bin/php '.$path.' recurring_downtime/check_schedules/ '.$user, $return);
		} else {
			exec('/usr/bin/php '.$path.' reports/cron/'.$period_str.' '.$user, $return);
			$sent_mail = array_sum($return);
			$retval = !empty($sent_mail) ? 0:1;
		}
		exit($retval);
	}

}
