<?php


class ServiceGroup_Model extends BaseServiceGroup_Model {
	public function __construct($values, $prefix) {
		parent::__construct($values, $prefix);
		$this->export[] = 'service_stats';
	}
	
	public function get_service_stats() {
		return "FIXME! (when saved queries is done)";
	}
}
