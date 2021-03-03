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
class Auth_Controller extends Chromeless_Controller {

	/**
	 * Handle everything with login, either display login form, or on POST, try to
	 * login. If logged in, redirect to the requested URL.
	 */
	public function login () {

		$auth = op5auth::instance();

		$this->template->content = new View('login');

		// We should always login to the current url, including ?uri=xxx
		$this->template->content->login_page = url::current(true);
		$this->template->content->auth_modules = $auth->get_metadata('login_screen_dropdown');
		$this->template->content->message = null;

		$error = $this->input->get('error', null);
		if ($error) {
			$this->template->content->message = new ErrorNotice_Model(htmlentities($error));
		}

		try {
			if(PHP_SAPI !== 'cli' && Kohana::config('cookie.secure') && (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS'])) {
				throw new NinjaLogin_Exception(_('Ninja is configured to only allow logins through the HTTPS protocol. Try to login via HTTPS, or change the config option cookie.secure.'));
			}

			if ($_POST) {
				$this->_verify_access('ninja.auth:login');

				$username    = $this->input->post('username', false);
				$password    = $this->input->post('password', false);
				$auth_method = $this->input->post('auth_method', false);

				# validate that we have both username and password
				if (empty($username) || empty($password)) {
					throw new NinjaLogin_Exception(_("Please supply both username and password"));
				}

				$result = $auth->login($username, $password, $auth_method);
				if (!$result) {
					throw new NinjaLogin_Exception(_("Login failed - please try again"));
				}

				Event::run('ninja.logged_in');
			}
		} catch(NinjaLogin_Exception $e) {
			$this->template->content->message = new ErrorNotice_Model($e->getMessage());
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
	 * No access method
	 *
	 * @param $messages array
	 * @param $action string
	 */
	function _no_access ($messages, $action) {

		$this->template->content = new View('401');

		$this->template->content->messages = $messages;
		$this->template->content->action = $action;

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
