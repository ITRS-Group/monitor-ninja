<?php defined('SYSPATH') OR die('No direct access allowed.');

class Sla_options_Core extends Report_options {
	protected static function discover_options($type, $input = false) {
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
			$report_info['time_start'] = mktime(0, 0, 0, $report_info['start_month'], 1, $report_info['start_year']);
			$report_info['time_end'] = mktime(0, 0, -1, $report_info['end_month'], 1, $report_info['end_year']);
		}
		return $report_info;
	}

	public function setup_properties()
	{
		parent::setup_properties();
		unset($this->properties['include_trends'], $this->properties['include_trends_scaling']);
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

	public function set($name, $value)
	{
		$resp = parent::set($name, $value);
		if ($resp === false && preg_match('/^month/', trim($name))) {
			$id = (int)str_replace('month_', '', $name);
			if (trim($value) == '')
				return;
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
}
