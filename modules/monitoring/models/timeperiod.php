<?php
require_once (dirname(__FILE__) . '/base/basetimeperiod.php');

/**
 * Describes a single object from livestatus
 */
class TimePeriod_Model extends BaseTimePeriod_Model {
	/**
	 * For backward compatibility with the filter API
	 *
	 * We can't use the name 'in', since it conflicts with the lsfilter-operator
	 * 'in'.
	 *
	 * In all cases, use the is_active instead.
	 *
	 * @deprecated
	 * @return boolean
	 *
	 * @ninja orm depend[] is_active
	 */
	public function get_in() {
		return $this->get_is_active();
	}
}
