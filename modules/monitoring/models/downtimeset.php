<?php


/**
 * Describes a set of objects from livestatus
 */
class DowntimeSet_Model extends BaseDowntimeSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitor.monitoring.downtimes";
	}
}
