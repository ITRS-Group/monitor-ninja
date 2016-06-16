<?php
Event::add( 'system.post_controller_constructor', function() {
	$controller = Event::$data;

	$basepath = 'modules/form/';

	$controller->template->js [] = $basepath . 'views/form/form.js';
	$controller->template->css [] = $basepath . 'views/form/form.css';
});
