<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Report options for SLA reports
 */
class Sla_options extends Report_options {
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
				$report_info['start_year'],
				$report_info['end_year'],
				$report_info['start_month'],
				$report_info['end_month']
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
		if(ninja::has_module('synergy')) {
			$this->properties['include_synergy_events'] = array(
				'type' => 'bool',
				'default' => false,
				'description' => 'Include BSM events'
			);
		}
	}

	/**
	 * Validated/assure that we have an array with 12 elements indexed from 1
	 * to 12, one for each month. Move the values from the param into a new
	 * array that has 0.0 as the default value if none is provided with the
	 * param.
	 *
	 * @param array $months
	 *
	 * @return array
	 */
	private static function validate_months($months)
	{
		$res = array();
		if (!is_array($months))
			$months = array();

		for ($i = 1; $i <= 12; $i++) {
			$res[$i] = 0.0;
			if (isset($months[$i])) {
				$res[$i] = $months[$i];
			}
		}
		return $res;
	}

	public function set($name, $value)
	{
		if ($name == 'months') {
			$value = static::validate_months($value);
		}
		$resp = parent::set($name, $value);
		if ($resp === false && preg_match('/^month/', trim($name))) {
			$id = (int)str_replace('month_', '', $name);

			if (trim($value) == '')
				return;

			// adjust locales to our format
			$value = str_replace(',', '.', $value);

			// values are percentages
			if ($value>100) {
				$value = 100;
			} elseif($value < 0) {
				$value = 0;
			}

			$this->options['months'][$id] = (float)$value;
			return true;
		}
		return $resp;
	}

	protected function calculate_time($report_period)
	{
		$res = parent::calculate_time($report_period);
		if ($res && isset($this->options['start_time']) && isset($this->options['end_time'])) {
			$this->options['months'] = static::validate_months($this['months']);
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
		/**
		 * The months array is indexed from 1 instead of 0 so make a new array
		 * with index from 1 to 12 with the SLA values from the db and fill the
		 * rest of the array with 0.0.
		 */
		$array_result = $opts['months'] + array_fill(1, 12, 0.0);
		ksort($array_result);
		$opts['months'] = $array_result;
		return $opts;
	}
}
