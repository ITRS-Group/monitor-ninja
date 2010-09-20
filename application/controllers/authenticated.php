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

	const ALLOW_PRODUCTION = FALSE;
	public $widgets = array();

	public function __construct()
	{
		parent::__construct();
		# make sure user is authenticated
		$authentic = new Auth;

		# Check if user is accessing through PHP CLI
		if (PHP_SAPI === "cli") {
			$cli_access = Kohana::config('config.cli_access');
			if ($cli_access !== false) {
				if ($cli_access === true) {
					# username should be passed as argv[1]
					if (!empty($_SERVER['argc']) && isset($_SERVER['argv'][2])) {
						Auth::instance()->force_login($_SERVER['argv'][2]);
					}

				} else {
					Auth::instance()->force_login($cli_access);
				}
			} else {
				echo "CLI access denied or not configured\n";
				exit(1);
			}
		} else {
			if (!$authentic->logged_in()) {
				# store requested uri in session for later redirect
				$this->session->set('requested_uri', url::current(true));
				url::redirect(Kohana::config('routes.log_in_form'));
			} else {
				$this->user = Auth::instance()->get_user();
			}
		}
	}

	public function is_authenticated()
	{
		return !Auth::instance()->logged_in();
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
