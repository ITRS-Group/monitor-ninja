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
						echo $this->translate->_('You are currently logged on as an '.
							'external widget user which means you are not authorized for this action!');
						die(sprintf($this->translate->_('%sBack%s'), '<br /><a href="javascript:history.go(-1)">', '</a>'));
					}
				}

				$this->user = Auth::instance()->get_user();
			}
		}
	}

	public function index()
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
