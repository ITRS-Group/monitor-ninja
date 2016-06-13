<?php

/**
 * Hook that imports all livestatus javascript everywhere, because we need it, well, everywhere.
 */
class form_library_hook {
	/**
	 * hook setup
	 */
	public function __construct() {

		//autocomplete::add_table('hosts', 'name', "[hosts] name~~\"^%s\"");
		//autocomplete::add_table('saved_filters', 'filter_name', "[saved_filters] filter_name~~\"^%s\"");

		Event::add( 'system.post_controller_constructor', array( $this, 'add_files' ) );

	}

	/**
	 * hook callback
	 */
	public function add_files() {
		$controller = Event::$data;

		$basepath = 'modules/form/';

		$controller->template->js [] = $basepath . 'views/form/form.js';
		$controller->template->js [] = $basepath . 'views/form/form.autocomplete.js';
		$controller->template->css [] = $basepath . 'views/form/form.css';

		$controller->template->js [] = 'application/media/js/lib.set.js';
		$controller->template->js [] = 'application/media/js/jquery.filterable.js';
		$controller->template->css [] = 'application/media/css/jquery.filterable.css';
	}
}

new form_library_hook();
