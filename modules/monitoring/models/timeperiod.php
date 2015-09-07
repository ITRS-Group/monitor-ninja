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

	/**
	 * Get all exceptions, augmented with types
	 *
	 * @ninja orm depend[] exceptions_calendar_dates
	 * @ninja orm depend[] exceptions_month_date
	 * @ninja orm depend[] exceptions_month_day
	 * @ninja orm depend[] exceptions_month_week_day
	 * @ninja orm depend[] exceptions_week_day
	 */
	public function get_exceptions() {
		return array_merge(
				array_map(function($r) {$r['type'] = 'calendar_date'; return $r;}, $this->get_exceptions_calendar_dates()),
				array_map(function($r) {$r['type'] = 'month_date'; return $r;}, $this->get_exceptions_month_date()),
				array_map(function($r) {$r['type'] = 'month_day'; return $r;}, $this->get_exceptions_month_day()),
				array_map(function($r) {$r['type'] = 'month_week_day'; return $r;}, $this->get_exceptions_month_week_day()),
				array_map(function($r) {$r['type'] = 'week_day'; return $r;}, $this->get_exceptions_week_day())
		);
	}
}
