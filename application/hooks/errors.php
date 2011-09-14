<?php

class errors {
	public function __construct()
	{
		Event::add('system.403', array($this, 'show_403'));
		Event::replace('system.404', array('Kohana', 'show_404'), array($this, 'show_404'));
	}

	public function show_403() {
		header('HTTP/1.1 403 Forbidden');
		Kohana::$instance = new Error_Controller;
		Kohana::$instance->template = new View('403');
		Kohana::$instance->template->render(TRUE);
		die();
	}

	public function show_404() {
		header('HTTP/1.1 404 File Not Found');
		Kohana::$instance = new Error_Controller;
		Kohana::$instance->template = new View('404');
		Kohana::$instance->template->render(TRUE);
		die();
	}
}

new errors;
?>
