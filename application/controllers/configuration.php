<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Configuration controller used to connect to Nacoma
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
class Configuration_Controller extends Ninja_Controller {

	/**
	 * Enable links from Ninja to Nacoma
	 *
	 * If 'service' is passed as GET, it will be used
	 * Checks are also made that NACOMA is configured in config file
	 *
	 * @param string $type
	 * @param string $name
	 */
	public function configure($type=false, $name=false)
	{
		$this->_verify_access('ninja.configuration:read');
		$scan = $this->input->get('scan', null);
		$type = $this->input->get('type', $type);
		$name = $this->input->get('name', $name);
		$service = $this->input->get('service', false);
		$page = $this->input->get('page', false);
		if (Kohana::config('config.nacoma_path')===false) {
			return false;
		}
		$type = trim($type);
		$name = trim($name);

		$target_link = null;

		if ($page)
			$target_link = $page;
		if (!empty($type) && !empty($name)) {
			if (strstr($type, 'group')) {
				$target_link = 'edit.php?obj_type='.$type.'&obj_name='.urlencode($name);
			} else {
				if (!empty($service)) {
					$target_link = 'edit.php?obj_type='.$type.'&host='.urlencode($name).'&service='.urlencode($service);
				} else {
					$target_link = 'edit.php?obj_type='.$type.'&'.$type.'='.urlencode($name);
				}
			}
		} elseif (!empty($type)) {
			$target_link = 'edit.php?obj_type='.$type;
		} elseif (!empty($scan)) {
			$target_link = 'host_wizard.php?action='.$scan;
		}

		# set the username so Nacoma can pick it up
		$this->template->disable_refresh = true;
		$this->template->content = '<iframe src="'.Kohana::config('config.nacoma_path').'/'.$target_link.'" style="width: 100%; height: 768px" frameborder="0" id="iframe"></iframe>';
		$this->template->title = _('Configuration » Configure');
		$this->template->nacoma = true;
		$this->template->js[] = 'application/views/js/iframe-adjust.js';
		$this->template->js[] = 'application/views/js/nacoma-urls.js';
	}
}
