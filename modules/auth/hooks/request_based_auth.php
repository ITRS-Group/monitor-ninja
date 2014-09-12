<?php
require_once('op5/auth/Auth.php');
require_once('op5/auth/User_AlwaysAuth.php');

/**
 * Hooks for enabling authentication through GET variables or CLI attributes.
 *
 * This hooks into the system.ready event, to try to login even before anything
 * is really loaded. The get authentication mechanism should be isolated to this
 * hook.
 */
class request_based_auth_hooks {
	public function __construct() {
		if (PHP_SAPI === "cli") {
			Event::add('system.ready', array ($this,'handle_cli_auth'));
		} else {
			Event::add('system.ready', array ($this,'handle_get_auth'));
		}
	}

	/**
	 * The handler for cli auth, running as a system.ready hook.
	 */
	public function handle_cli_auth() {
		$auth = op5auth::instance();
		$cli_access = Kohana::config('config.cli_access');
		if ($cli_access === true) {
			// username should be passed as argv[2]
			if (!empty($_SERVER['argc']) && isset($_SERVER['argv'][2])) {
				$auth->force_user(new op5User_AlwaysAuth(array('username' => $_SERVER['argv'][2])));
			} else {
				$auth->force_user(new op5User_AlwaysAuth(array('username' => 'cli_user')));
			}
		} else if ($cli_access !== false) {
				$auth->force_user(new op5User_AlwaysAuth(array('username' => $cli_access)));
		} else {
			/*
			 * This should be unnessecary, since we don't have authed a user anyway.
			 * But we'll keep this to make a nicer CLI you're not allowed here-message
			 */
			die("CLI access denied or not configured\n");
		}
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

new request_based_auth_hooks();