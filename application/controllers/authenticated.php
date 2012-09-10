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
				if (Kohana::config('auth.use_get_auth') === true && isset($_GET['username']) && isset($_GET['password'])) {
					$auth_method = $this->input->get('auth_method', false);
					if (!empty($auth_method)) {
						$_SESSION['auth_method'] = $auth_method;
						Kohana::config_set('auth.driver', $auth_method);
					}
					$res = ninja_auth::login_user($_GET['username'], $_GET['password']);
					if ($res !== true)
						die('The provided authorization is invalid');
				} else {
					# store requested uri in session for later redirect
					$this->session->set('requested_uri', url::current(true));

					if (Router::$controller != 'default') {
						url::redirect(Kohana::config('routes.log_in_form'));
					}
				}
			} else {
				# fetch the external widget user if any
				$external_widget_user = Kohana::config('external_widget.username');
				if (!empty($external_widget_user) && Auth::instance()->get_user()->username === $external_widget_user) {
					# explicitly whitelist the widget setting method, so that if, say, showing nagvis on an external
					# web server, the map is changeable
					if (Router::$controller !== 'ajax' || (Router::$method !== 'save_dynamic_widget_setting' && Router::$method !== 'widget')) {
						echo _('You are currently logged on as an '.
							'external widget user which means you are not authorized for this action!');
						die(sprintf(_('%sBack%s'), '<br /><a href="javascript:history.go(-1)">', '</a>'));
					}
				}

				$this->user = Auth::instance()->get_user();
			}
		}
		parent::__construct();
	}

	public function is_authenticated()
	{
		return !Auth::instance()->logged_in();
	}

	public function __call($name, $args)
	{
		# don't allow direct access
		# redirect to logged_in_default route as set in routes config
		url::redirect(Kohana::config('routes.logged_in_default'));
	}

	public function to_template(array $content)
	{
		$this->output = array_merge($content, $this->output);
	}
}
