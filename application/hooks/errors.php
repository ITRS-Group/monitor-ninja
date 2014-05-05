<?php

class errors {
	public function __construct()
	{
		Event::add('system.403', array($this, 'eventhandler'));
		Event::replace('system.404', array('Kohana', 'show_404'), array($this, 'eventhandler'));
		Event::add('application.livestatus', array($this, 'eventhandler'));
	}

	public function eventhandler()
	{
		$error = new Error_Controller;
		Kohana::$instance = $error;
		switch (Event::$name) {
		 case 'system.403':
			$error->show_403();
			break;
		 case 'system.404':
			$error->show_404();
			break;
		 case 'application.livestatus':
			$error->show_livestatus(Event::$data);
			break;
		 default:
			return;
		}
		$error->_render();
		exit(13);
	}
}

new errors;
