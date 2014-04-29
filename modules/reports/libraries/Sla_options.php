<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Report options for SLA reports
 */
class Sla_options_Core extends Report_options {
	public static $type = 'sla';

	static function discover_options($input = false) {
		# not using $_REQUEST, because that includes weird, scary session vars
		if (!empty($input)) {
			$report_info = $input;
		} else if (!empty($_POST)) {
			$report_info = $_POST;
		} else {
			$report_info = $_GET;
		}


		if(isset($report_info['report_period'], $report_info['start_year'], $report_info['start_month'], $report_info['end_year'], $report_info['end_month'])
			&& $report_info['report_period'] == 'custom'
			&& strval($report_info['start_year']) !== ""
			&& strval($report_info['start_month']) !== ""
			&& strval($report_info['end_year']) !== ""
			&& strval($report_info['end_month']) !== ""
		) {
			$report_info['start_time'] = mktime(0, 0, 0, $report_info['start_month'], 1, $report_info['start_year']);
			$report_info['end_time'] = mktime(0, 0, -1, $report_info['end_month'] + 1, 1, $report_info['end_year']);
			unset(
				$report_info['cal_start'],
				$report_info['cal_end'],
				$report_info['time_start'],
				$report_info['time_end']
			);
		}
		return $report_info;
	}

	public function setup_properties()
	{
		parent::setup_properties();
		// Warning! months is 1-indexed
		$this->properties['months'] = array('type' => 'array', 'default' => false);

		$this->properties['report_period'] = array('type' => 'enum', 'default' => 'thisyear', 'options' => array(
			"thisyear" => _('This year'),
			"lastyear" => _('Last year'),
			"lastmonth" => _('Last month'),
			"last3months" => _('Last 3 months'),
			"last6months" => _('Last 6 months'),
			"lastquarter" => _('Last quarter'),
			"last12months" => _('Last 12 months'),
			'custom' => _('Custom')
		));
	}

	/**
	 * Because this if condition is too long for my pretty little mind to worry about,
	 * I'm'a make a predicate out of it!
	 */
	private static function is_valid_month($month, $start_month, $end_month)
	{
		if ($start_month > $end_month && ($month >= $start_month || $month <= $end_month)) # e.g. report is dec(12)->feb(2), only 12, 1, 2 valid
			return true;
		if ($start_month <= $end_month && ($month >= $start_month && $month <= $end_month)) # e.g. report is feb(2)->apr(4), only 2, 3, 4 valid
			return true;
		return false;
	}

	private static function validate_months($start_time, $end_time, $months)
	{
		$start_month = date('n', $start_time);
		$end_month = date('n', $end_time - 1);
		$res = array();
		if (!is_array($months))
			$months = array();
		if (($end_month - $start_month == 11) || ($end_month - $start_month == -1)) {
			$start = 1;
			$stop = 12;
		}
		else {
			$start = $start_month;
			$stop = $end_month;
		}
		// about the weird modulo: the valid range is 1->12, not 0->11
		for ($i = $start; true; $i = ($i % 12) + 1)
		{
			if (static::is_valid_month($i, $start_month, $end_month)) {
				if (!isset($months[$i]))
					$res[$i] = 0.0;
				else
					$res[$i] = $months[$i];
			}
			if ($i == $stop)
				break;
		}
		return $res;
	}

	public function set($name, $value)
	{
		if ($name == 'months') {
			$value = static::validate_months($this['start_time'], $this['end_time'], $value);
		}
		$resp = parent::set($name, $value);
		if ($resp === false && preg_match('/^month/', trim($name))) {
			$id = (int)str_replace('month_', '', $name);

			if (!$this->is_valid_month($id, date('n', $this['start_time']), date('n', $this['end_time'] - 1)))
				return false;

			if (trim($value) == '')
				return;
			// Because fuck locales, amiright?
			$value = str_replace(',', '.', $value);
			$value = (float)$value;
			// values greater than 100 doesn't make sense
			if ($value>100)
				$value = 100;
			$this->options['months'][$id] = $value;
			return true;
		}
		return $resp;
	}

	protected function calculate_time($report_period)
	{
		$res = parent::calculate_time($report_period);
		if ($res && isset($this->options['start_time']) && isset($this->options['end_time'])) {
			$this->options['months'] = static::validate_months($this['start_time'], $this['end_time'], $this['months']);
		}
		return $res;
	}

	protected function load_options($id)
	{
		$opts = parent::load_options($id);
		if (!$opts)
			return false;

		foreach (array('start_time', 'end_time', 'report_period') as $k) {
			if (isset($opts[$k]))
				$this->set($k, $opts[$k]);
		}
		$this->calculate_time($this['report_period']);

		reset($opts['months']);
		foreach ($this->options['months'] as $key => $_) {
			$this->options['months'][$key] = (float)current($opts['months']);
			next($opts['months']);
		}
		unset($opts['months']);
		return $opts;
	}
}
