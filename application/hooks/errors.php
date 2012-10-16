<?php

class errors {
	public function __construct()
	{
		Event::add('system.403', array($this, 'show_403'));
		Event::replace('system.404', array('Kohana', 'show_404'), array($this, 'show_404'));
		Event::add('application.livestatus', array($this, 'show_livestatus'));
	}

	public function show_403() {
		header('HTTP/1.1 403 Forbidden');
		Kohana::$instance = new Error_Controller;
		Kohana::$instance->template->content = new View('403');
		Kohana::$instance->template->render(TRUE);
		die();
	}

	public function show_404() {
		header('HTTP/1.1 404 Not Found');
		Kohana::$instance = new Error_Controller;
		Kohana::$instance->template->content = new View('404');
		Kohana::$instance->template->render(TRUE);
		die();
	}

	public function show_livestatus() {
		header('HTTP/1.1 503 Service Unavailable');
		Kohana::$instance = new Error_Controller;
		Kohana::$instance->template->content = new View('livestatus');
		Kohana::$instance->template->render(TRUE);
		die();
	}
}

new errors;
?>
