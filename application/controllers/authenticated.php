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
	public function __construct()
	{
		parent::__construct();

		if (!Auth::instance()->logged_in()) {
			// store requested uri in session for later redirect
			if (!request::is_ajax() && $this->session)
				$this->session->set('requested_uri', url::current(true));

			// url::redirect sends the headers and exits. Execution stops after this line
			url::redirect(Kohana::config('routes.log_in_form'));
		}
	}
}
