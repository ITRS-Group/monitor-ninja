<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Tactical overview controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
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
		$this->template->content = '<iframe src="'.Kohana::config('config.nacoma_path').'/'.$target_link.'" style="width: 100%; height: 768px" frameborder="0"></iframe>';
		$this->template->title = $this->translate->_('Configuration » Configure');
	}
}