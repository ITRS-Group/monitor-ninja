<?php


/**
 * Describes a set of objects from livestatus
 */
class ServiceGroupSet_Model extends BaseServiceGroupSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitor.monitoring.servicegroups";
	}
}
