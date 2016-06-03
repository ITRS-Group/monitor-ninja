<?php

/**
 * Hook that imports all livestatus javascript everywhere, because we need it, well, everywhere.
 */
class form_library_hook {
	/**
	 * hook setup
	 */
	public function __construct() {
		Event::add( 'system.post_controller_constructor', array( $this, 'add_files' ) );
	}

	/**
	 * hook callback
	 */
	public function add_files() {
		$controller = Event::$data;

		$basepath = 'modules/form/';

		$controller->template->js [] = $basepath . 'views/form/form.js';
		$controller->template->css [] = $basepath . 'views/form/form.css';
	}
}

new form_library_hook();
