<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * widget helper class.
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */

class widget_Core {

	/**
	*	@name	add
	*	@desc	Add a new widget
	* 	@param  string $name
	*
	*/
	public function add($name=false, $arguments=false, &$master=false)
	{
		$path = Kohana::find_file('widgets/'.$name, $name, true);
		require_once($path);
		$classname = $name.'_Controller';
		$obj = new $classname;
		# if we have a requested widget method - let's call it
		if (!empty($arguments) & is_array($arguments)) {
			$widget_method = $arguments[0];
			if (method_exists($obj, $widget_method)) {
				array_shift($arguments);
				return $obj->$widget_method($arguments, $master);
			}
		}

		# return false if no method defined
		return false;
	}

}
