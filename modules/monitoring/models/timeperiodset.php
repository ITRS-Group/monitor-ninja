<?php


/**
 * Describes a set of objects from livestatus
 */
class TimePeriodSet_Model extends BaseTimePeriodSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitor.monitoring.timeperiods";
	}
}
