<?php

Event::add('system.pre_controller', function() {

	if ((PHP_SAPI === 'cli') || (!in_array($_SERVER['REQUEST_METHOD'], array('POST', 'PUT', 'DELETE')))) {
		return;
	}

	$log = op5log::instance('ninja');
	$log->log('debug', 'Validating CSRF token');
	if (!isset($_REQUEST['csrf_token']) || !Session::instance()->csrf_token_valid($_REQUEST['csrf_token'])) {
		$log->log('warning', 'CSRF token validation failed');
		Event::run('system.403');
	}

	$log->log('debug', 'CSRF token validation successful');
});
