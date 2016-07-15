<?php
Event::add( 'system.post_controller_constructor', function() {

	$controller = Event::$data;
	$controller->template->css [] = 'modules/form/views/form/form.css';

});
