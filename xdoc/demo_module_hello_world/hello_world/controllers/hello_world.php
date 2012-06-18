<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Hello World controller.
 *
 * First character in controller class name must be capital letter
 * followed by _controller.
 *
 * If it extends Template_Controller, no login is required - use
 * Authenticated_Controller if this is a requirement.
 *
 * This example is created as a module and needs to be enabled in application/config/config.php
 * Just add
 * 	MODPATH.'hello_world',     // Hello World example
 * to the $config['modules'] array in config.php or you will get a "Page Not Found" error.
 * This means that Ninja will look for the folder ninja/modules/hello_world
 *
 * Add link to the ninja menu by editing controllers/ninja but unfortunately this will be
 * overwritten when upgrading Ninja so keep a backup.
 *
 */
class Hello_world_Controller extends Authenticated_Controller {

	/**
	*	This is the index method ("page")
	* 	Naming a controller method means that it will be loaded if
	* 	no method is given in the URL.
	*/
	public function index()
	{
		# load our template file
		$this->template->content = $this->add_view('hello_world/hello');

		# create a template alias to save us some typing
		$content = $this->template->content;

		$this->template->title = $this->translate->_('Test » Hello World');

		# disable page refresh - set to false (or remove) to enable
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_js[] = 'modules/hello_world/views/themes/default/hello_world/js/hello';

		# add our javascript file to master template
		$this->template->js_header->js = $this->xtra_js;

		# pass the string to the translation object if you
		# would like to be able to translate it using gettext tools
		$content->msg_header = $this->translate->_('Hello World');
		$content->header_titlestring = $this->translate->_('Click me to show/hide the data below.');
		$content->msg_description = $this->translate->_('This is a simple Hello World example using a module.');

		$content->data = Hello_world_Model::get_some_data();
	}
}
