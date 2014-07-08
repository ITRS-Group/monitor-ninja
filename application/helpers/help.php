<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class to provide links to render help texts
 */
class help
{
	/**
	*	Render the help text calls to fetch
	*	translated help texts vis ajax calls.
	*
	* 	Example usage:
	* 		Entering <?php help::render('edit'); ?> somewhere on a page (view) will
	* 		create a clickable icon that fetches the help text from the current
	* 		controller.
	*
	* 		By adding a second parameter to the call:
	* 		<?php help::render('edit', 'extinfo'); ?> the information is instead
	* 		fetched from the extinfo controller.
	*/
	public static function render($key=false, $controller=false)
	{
		if (empty($key)) {
			return false;
		}

		$img_path = url::base(false).'application/views/icons/12x12/help.png';

		$controller = !empty($controller) ? $controller : Router::$controller;

		return '<a class="helptext_target" style="border:0" data-helptext-controller="'.$controller.'" data-helptext-key="'.$key.'" href="#">'.
		'<img src="'.$img_path.'" alt="'._('Click for help').'" style="width: 12px; height: 12px; margin-bottom: -1px" /></a>';
	}
}
