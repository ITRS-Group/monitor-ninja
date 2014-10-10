<?php

require_once( dirname(__FILE__).'/base/basetimeperiodset.php' );

/**
 * Describes a set of objects from livestatus
 */
class TimePeriodSet_Model extends BaseTimePeriodSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitoring.timeperiods";
	}
}
