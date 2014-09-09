<?php

/**
 * Hooks for enabling authentication through GET variables.
 *
 * This hooks into the system.ready event, to try to login even before anything
 * is really loaded. The get authentication mechanism should be isolated to this
 * hook.
 */
class get_auth_hooks {
	public function __construct() {
		Event::add('system.ready', array ($this,'handle_get_auth'));
	}

	/**
	 * The handler for get auth, running as a system.ready hook.
	 */
	public function handle_get_auth() {
		$input = Input::instance();
		$auth = Auth::instance();
		if (!Auth::instance()->logged_in()) {
			$auth_method = $input->get('auth_method', false);
			$username = $input->get('username', false);
			$password = $input->get('password', false);
			if (Kohana::config('auth.use_get_auth') === true &&
				 $username !== false && $password !== false) {
				$res = ninja_auth::login_user($username, $password,
					$auth_method);
				if ($res !== true)
					die('The provided authentication is invalid');
			}
		}
	}
}

new get_auth_hooks();