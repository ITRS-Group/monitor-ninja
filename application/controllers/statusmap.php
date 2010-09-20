<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Statusmap controller
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
class Statusmap_Controller extends Authenticated_Controller {
	public function index()
	{
		url::redirect('underconstruction/');
	}

	public function __call($method, $arguments)
	{
		url::redirect('underconstruction/');
	}

	/**
	*	Wrapper for automap call to show host
	* 	Used from group grid and group overview
	*/
	public function host($host_name = false)
	{
		url::redirect('nagvis/automap/host/'. $host_name);
	}
}
