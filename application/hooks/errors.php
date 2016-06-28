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
		switch (Event::$name) {
		case 'system.403':
			throw new Kohana_Reroute_Exception('Error', 'show_403');
			break;
		 case 'system.404':
			throw new Kohana_Reroute_Exception('Error', 'show_404');
			break;
		 case 'application.livestatus':
			throw new Kohana_Reroute_Exception('Error', 'show_livestatus', array(Event::$data));
			break;
		 default:
			return;
		}
	}
}

new errors;
