<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * History controller
 * Requires authentication
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class History_Controller extends Authenticated_Controller {
	public function index()
	{
		url::redirect('underconstruction/');
	}

	public function __call($method, $arguments)
	{
		url::redirect('underconstruction/');
	}
}
