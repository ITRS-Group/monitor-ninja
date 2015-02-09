<?php

/**
 * Internal exception for login method, defining some error exception in the
 * login cycle, which should interrupt the login attempt and display an error
 * message.
 */
class NinjaLogin_Exception extends Exception {}

/**
 * Handle user authentication (login and logout)
 */
class Auth_Controller extends Ninja_Controller {

	/**
	 * Handle everything with login, either display login form, or on POST, try to
	 * login. If logged in, redirect to the requested URL.
	 */
	public function login()
	{
		$auth = op5auth::instance();

		$this->template = $this->add_view('login');

		// We should always login to the current url, including ?uri=xxx
		$this->template->login_page = url::current(true);

		$this->template->js = array('application/media/js/jquery.js', $this->add_path('/js/login.js'));
		$this->template->auth_modules = $auth->get_metadata('login_screen_dropdown');

		if ($_POST) {
			$this->_verify_access('ninja.auth:login');
			try {
				if(PHP_SAPI !== 'cli' && config::get('cookie.secure') && (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS'])) {
					throw new NinjaLogin_Exception(_('Ninja is configured to only allow logins through the HTTPS protocol. Try to login via HTTPS, or change the config option cookie.secure.'));
				}

				$username    = $this->input->post('username', false);
				$password    = $this->input->post('password', false);
				$auth_method = $this->input->post('auth_method', false);

				# validate that we have both username and password
				if ( empty($username) || empty($password) ) {
					throw new NinjaLogin_Exception(_("Please supply both username and password"));
				}

				$csrf_config = Kohana::config('csrf');
				if ($csrf_config['csrf_token']!='' && $csrf_config['active'] !== false && !csrf::valid($this->input->post($csrf_config['csrf_token']))) {
					throw new NinjaLogin_Exception(_("CSRF tokens did not match.<br />This often happen when your browser opens cached windows (after restarting the browser, for example).<br />Try to login again."));
				}

				$result = $auth->login($username, $password, $auth_method);
				if (!$result) {
					throw new NinjaLogin_Exception(_("Login failed - please try again"));
				}

				# might redirect somewhere
				Event::run('ninja.logged_in');
			} catch(NinjaLogin_Exception $e) {
				$this->template->error_msg = $e->getMessage();
			}
		}

		/*
		 * If we're logged in, we should go to the requested page. This might be
		 * either because we just have logged in, or already is logged in. Either
		 * way, we shouldn't show a login page.
		 */
		if ($auth->logged_in()) {
			$requested_uri = $this->input->get('uri', Kohana::config('routes.logged_in_default'));
			return url::redirect($requested_uri);
		}

		Event::run('ninja.show_login', $this);
	}

	/**
	 * Logout user, and redirect to the login form.
	 */
	public function logout()
	{
		$this->_verify_access('ninja.auth:logout');
		op5auth::instance()->logout();
		return url::redirect(Kohana::config('routes.log_in_form'));
	}
}