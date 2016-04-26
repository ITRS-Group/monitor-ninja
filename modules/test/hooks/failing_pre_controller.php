<?php

Event::add('system.pre_controller', function () {
	if (Router::$controller === 'failing' && Router::$method === 'hook') {
		throw new ORMDriverException('This page should display a Service Unavailable message');
	}
});
