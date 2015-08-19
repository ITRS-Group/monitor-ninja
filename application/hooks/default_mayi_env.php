<?php

require_once('op5/mayi.php');

class monitor_mayi_actor implements op5MayI_Actor {
	private $actorinfo = array();

	private function get_installation_time() {
		$db = Database::instance();
		// 1103 and 1104 is downtimes, which can be added retrospectivly
		$timerow = $db->query('SELECT MIN(timestamp) FROM report_data WHERE event_type NOT IN (1103,1104)')->result_array(false, MYSQL_NUM);
		$installation_time = $timerow[0][0];
		if($installation_time === NULL) {
			$installation_time = time();
		}
		return intval($installation_time);
	}

	public function __construct() {
		$this->actorinfo['installation_time'] = $this->get_installation_time();
		op5mayi::instance()->be('monitor', $this);
	}

	public function getActorInfo() {
		return $this->actorinfo;
	}
}


new monitor_mayi_actor();

class system_mayi_actor implements op5MayI_Actor {
	public function __construct() {
		op5mayi::instance()->be('system', $this);
	}

	public function getActorInfo() {
		return array(
			'time' => time()
		);
	}
}

new system_mayi_actor();
