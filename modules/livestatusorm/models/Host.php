<?php


class Host_Model extends BaseHost_Model {
	public function __construct($values, $prefix) {
		parent::__construct($values, $prefix);
		$this->export[] = 'duration';
	}
	
	public function get_duration() {
		$now = time();
		$last_state_change = $this->get_last_state_change();
		return $last_state_change !== false ? ($now - $last_state_change) : -1;//FIXME ($now - $this->program_start);
	}
}
