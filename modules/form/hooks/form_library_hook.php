<?php
Event::add( 'system.post_controller_constructor', function() {

	$controller = Event::$data;
	$controller->template->css [] = 'modules/form/views/form/form.css';

	autocomplete::add_table('hosts', 'name', "[hosts] name~~\"%s\"");
	autocomplete::add_table('saved_filters', 'filter_name', "[saved_filters] filter_name~~\"%s\"");
	autocomplete::add_table('services', 'description', "[services] description~~\"%s\"");


});

