<?php

require_once( dirname(__FILE__).'/base/basestatusset.php' );

/**
 * Describes a set of objects from livestatus
 */
class StatusSet_Model extends BaseStatusSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitoring.status";
	}
}
