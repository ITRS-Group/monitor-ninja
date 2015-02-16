<?php

require_once( dirname(__FILE__).'/base/basecontactgroupset.php' );

/**
 * Describes a set of objects from livestatus
 */
class ContactGroupSet_Model extends BaseContactGroupSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitor.monitoring.contactgroups";
	}
}
