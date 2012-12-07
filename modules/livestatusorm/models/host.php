<?php


class Host_Model extends BaseHost_Model {
	public function __construct($values, $prefix) {
		parent::__construct($values, $prefix);
		$this->export[] = 'state_text';
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
}
