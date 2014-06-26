<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * NOC controller
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
class Rotation_Controller extends Authenticated_Controller {

	public function index()
	{

		$this->template->content = $this->add_view('/rotation/view');
		$this->template->title = _('Monitoring » Rotation');
		$this->template->js[] = $this->add_path('/rotation/js/rotation.js');
		$this->template->js[] = $this->add_path('/js/iframe-adjust.js');
		$this->template->css[] = $this->add_path('rotation/css/rotation.css');
		$this->template->disable_refresh = true;
	}

}
