<?php


class Host_Model extends BaseHost_Model {
	public function __construct($values, $prefix) {
		parent::__construct($values, $prefix);
		$this->export[] = 'state_text';
		$this->export[] = 'checks_disabled';
	}
	
	public function get_state_text() {
		if( !$this->get_has_been_checked() )
			return 'pending';
		switch( $this->get_state() ) {
			case 0: return 'up';
			case 1: return 'down';
			case 2: return 'unreachable';
		}
		return 'unknown'; // should never happen
	}
	
	public function get_checks_disabled() {
		//FIXME: passive as active
		return !$this->get_active_checks_enabled();
	}
}
