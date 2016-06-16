<?php
Event::add( 'system.post_controller_constructor', function() {
	$controller = Event::$data;

	$basepath = 'modules/form/';

	autocomplete::add_table('hosts', 'name', "[hosts] name~~\"%s\"");
	autocomplete::add_table('saved_filters', 'filter_name', "[saved_filters] filter_name~~\"%s\"");
	autocomplete::add_table('services', 'description', "[services] description~~\"%s\"");

	Event::add( 'system.post_controller_constructor', function () {
		$controller = Event::$data;
		$basepath = 'modules/form/';

		$controller->template->js [] = $basepath . 'views/form/form.js';
		$controller->template->css [] = $basepath . 'views/form/form.css';
	});

});

