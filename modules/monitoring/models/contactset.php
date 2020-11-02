<?php


/**
 * Describes a set of objects from livestatus
 */
class ContactSet_Model extends BaseContactSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitor.monitoring.contacts";
	}
}
