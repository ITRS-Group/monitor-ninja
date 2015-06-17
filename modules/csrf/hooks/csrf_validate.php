<?php

Event::add('system.pre_controller', function() {

	if ((PHP_SAPI === 'cli') || (!in_array($_SERVER['REQUEST_METHOD'], array('POST', 'PUT', 'DELETE')))) {
		return;
	}

	$csrf_header = 'X-op5-csrf-token';

	$log = op5log::instance('ninja');
	$log->log('debug', 'Validating CSRF token');
	$headers = getallheaders();
	if (
		(!isset($_REQUEST['csrf_token']) || !Session::instance()->csrf_token_valid($_REQUEST['csrf_token']))
		&&
		(!array_key_exists($csrf_header, $headers) || !Session::instance()->csrf_token_valid($headers[$csrf_header]))
	) {
		$log->log('warning', 'CSRF token validation failed');
		Event::run('system.403');
		return;
	}

	$log->log('debug', 'CSRF token validation successful');
});
