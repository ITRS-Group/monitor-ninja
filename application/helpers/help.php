<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class to provide links to render help texts
 */
class help_Core
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
	public function render($key=false, $controller=false)
	{
		if (empty($key)) {
			return false;
		}

		# fetch current theme path from registry
		# to make help icon to always be in current theme
		$theme_path = zend::instance('Registry')->get('theme_path');
		$img_rel_path = 'icons/12x12/help.png';
		$img_path = url::base(false).'application/views/'.$theme_path.$img_rel_path;

		$translate = zend::instance('Registry')->get('Zend_Translate');
		$controller = !empty($controller) ? $controller : Router::$controller;

		# build the element ID with random nr | controller | help key
		$id = 'help_'.rand(0, 10000).'|'.$controller.'|'.$key;

		return '<a class="helptext_target" style="border:0" id="'.$id.'" href="#">'.
		'<img src="'.$img_path.'" title="'.$translate->_('Click for help').'" alt="'.$translate->_('Click for help').'" style="width: 12px; height: 12px; margin-bottom: -1px" /></a>';
	}
}
