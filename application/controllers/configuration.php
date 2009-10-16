<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Configuration controller used to connect to Nacoma
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Configuration_Controller extends Authenticated_Controller {

	public $model = false;

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
		$type = urldecode($this->input->get('type', $type));
		$name = urldecode($this->input->get('name', $name));
		$service = urldecode($this->input->get('service', false));
		if (Kohana::config('config.nacoma_path')===false) {
			return false;
		}
		$type = trim($type);
		$name = trim($name);

		$target_link = 'configure.php';
		if (!empty($type) && !empty($name)) {
			if (!empty($service)) {
				$target_link = 'edit.php?obj_type='.$type.'&host='.$name.'&service='.$service;
			} else {
				$target_link = 'edit.php?obj_type='.$type.'&'.$type.'='.$name;
			}
		}
		$this->template->disable_refresh = true;
		$this->template->content = '<iframe src="'.Kohana::config('config.nacoma_path').'/'.$target_link.'" style="width: 100%; height: 768px" frameborder="0"></iframe>';
		$this->template->title = $this->translate->_('Configuration » Configure');
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}
}