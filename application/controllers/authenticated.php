<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 *	Base authenticated controller for NINJA
 *	All controllers requiring authentication should
 * 	extend this controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
*/
class Authenticated_Controller extends Ninja_Controller {

	public $widgets = array();

	public function __construct()
	{
		parent::__construct();
		# make sure user is authenticated

		# Check if user is accessing through PHP CLI
		if (PHP_SAPI === "cli") {
			$cli_access = Kohana::config('config.cli_access');
			if ($cli_access === true) {
				# username should be passed as argv[2]
				if (!empty($_SERVER['argc']) && isset($_SERVER['argv'][2])) {
					Auth::instance()->force_login($_SERVER['argv'][2]);
				}
			} else if ($cli_access !== false) {
				Auth::instance()->force_login($cli_access);
			} else {
				echo "CLI access denied or not configured\n";
				exit(1);
			}
		} else {
			if (!Auth::instance()->logged_in()) {
				$auth_method = $this->input->get('auth_method', false);
				$username    = $this->input->get('username', false);
				$password    = $this->input->get('password', false);
				if (Kohana::config('auth.use_get_auth') === true && $username !== false && $password !== false) {
					$res = ninja_auth::login_user($username, $password, $auth_method);
					if ($res !== true)
						die('The provided authentication is invalid');
				} else {
					# store requested uri in session for later redirect
					if (!request::is_ajax() && $this->session)
						$this->session->set('requested_uri', url::current(true));

					if (Router::$controller != 'default') {
						return url::redirect(Kohana::config('routes.log_in_form'));
					}
				}
			}
		}
		
		# user might not be logged in due to CLI scripts, be quiet
		$current_skin = config::get('config.current_skin', '*', true);
		if (!file_exists(APPPATH."views/css/".$current_skin) || !$current_skin) {
			$current_skin = 'default/';
		}
		else if (substr($current_skin, -1, 1) != '/') {
			$current_skin .= '/';
		}

		if (!file_exists(APPPATH."views/css/".$current_skin)) {
			op5log::instance('ninja')->log('notice', 'Wanted to use skin "'. $current_skin.'", could not find it');
			$current_skin = 'default/';
		}
		$this->template->current_skin = $current_skin;
	}
}
