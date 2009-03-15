<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * widget_callback controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Widget_callback_Controller extends Authenticated_Controller {

	public function __construct()
	{
		parent::__construct();
	}


	/**
	 *	wrapper for widget ajax calls
	 */
	public function ajax($widget, $method, $arguments=false)
	{
		// Disable auto-rendering
		$this->auto_render = FALSE;

		# path to widget helper is somehow lost when doing ajax calls
		# so let kohana find it for us
		$widget_core_path = Kohana::find_file('helpers', 'widget', true);
		require_once($widget_core_path);

		# first try custom path
		$path = Kohana::find_file(Kohana::config('widget.custom_dirname').$widget, $widget, false);
		if ($path === false) {
			# try core path if not found in custom
			$path = Kohana::find_file(Kohana::config('widget.dirname').$widget, $widget, true);
		}

		require_once($path);
		$classname = $widget.'_Widget';
		$obj = new $classname;
		# if we have a requested widget method - let's call it
		if (!empty($method)) {
			if (method_exists($obj, $method)) {
				return $obj->$method($arguments);
			}
		}

		# return false if no method defined
		return false;

	}

}