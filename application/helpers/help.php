<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class to provide links to render help texts
 */
class help
{
	/**
 	 * Render the help text calls to fetch
 	 * translated help texts vis ajax calls.
	 *
	 * Example usage:
	 *
	 * Entering <?php help::render('edit'); ?> somewhere on a page (view) will
	 * create a clickable icon that fetches the help text from the current
	 * controller.
	 *
	 * By adding a second parameter to the call:
	 * <?php help::render('edit', 'extinfo'); ?> the information is instead
	 * fetched from the extinfo controller.
	 *
	 * @param $key string
	 * @param $controller string = null
	 * @return string
	 */
	public static function render($key, $controller = null)
	{
		$img_path = url::base(false).'application/views/icons/12x12/help.png';
		$controller = $controller !== null ? $controller : Router::$controller;
		return '<a class="help-icon" data-popover="help:'.$controller.'.'.$key.'"><img src="'.$img_path.'" style="width: 12px; height: 12px; margin-bottom: -1px" /></a>';
	}
}
