<?php

require_once('op5/mayi.php');

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
