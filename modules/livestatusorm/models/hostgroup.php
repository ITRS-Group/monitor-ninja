<?php


class HostGroup_Model extends BaseHostGroup_Model {
	public function __construct($values, $prefix) {
		parent::__construct($values, $prefix);
		$this->export[] = 'host_stats';
		$this->export[] = 'service_stats';
	}
	
	public function get_host_stats() {
		return "FIXME! (when saved queries is done)";
	}
	public function get_service_stats() {
		return "FIXME! (when saved queries is done)";
	}
}
