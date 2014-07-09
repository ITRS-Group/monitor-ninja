<?php
require_once (dirname(__FILE__) . '/base/basetimeperiod.php');

/**
 * Describes a single object from livestatus
 */
class TimePeriod_Model extends BaseTimePeriod_Model {
	/**
	 * An array containing the custom column dependencies
	 */
	public static $rewrite_columns = array ('in' => array ('is_active'));

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
	 */
	public function get_in() {
		return $this->get_is_active();
	}
}
