<?php defined('SYSPATH') OR die('No direct access allowed.');

class Sla_options_Core extends Report_options {
	public function __construct($options=false) {
		unset($this->properties['include_trends'], $this->properties['include_trends_scaling']);
		// Warning! months is 1-indexed
		$this->properties['months'] = array('type' => 'array', 'default' => false);

		parent::__construct($options);
		$this->properties['report_period'] = array('type' => 'enum', 'default' => 'thisyear', 'options' => array(
			"thisyear" => _('This Year'),
			"lastyear" => _('Last Year'),
			"lastmonth" => _('Last Month'),
			"last3months" => _('Last 3 Months'),
			"last6months" => _('Last 6 months'),
			"lastquarter" => _('Last Quarter'),
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

	protected function update_value($name, $value)
	{
		switch($name) {
			case 'host_filter_status':
				$value = array_intersect_key($value, Reports_Model::$host_states);
				$value = array_filter($value, function($val) {
					return is_numeric($val) && $val == 0;
				});
				$this->options[$name] = $value;
				return true;
			case 'service_filter_status':
				$value = array_intersect_key($value, Reports_Model::$service_states);
				$value = array_filter($value, function($val) {
					return is_numeric($val) && $val == 0;
				});
				$this->options[$name] = $value;
				return true;
		}
		return parent::update_value($name, $value);
	}
}
