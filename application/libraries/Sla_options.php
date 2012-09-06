<?php defined('SYSPATH') OR die('No direct access allowed.');

class Sla_options_Core extends Report_options {
	public function __construct($options) {
		$this->vtypes['start_year'] = array('type' => 'int', 'default' => false);
		$this->vtypes['start_month'] = array('type' => 'int', 'default' => false);
		$this->vtypes['end_year'] = array('type' => 'int', 'default' => false);
		$this->vtypes['end_month'] = array('type' => 'int', 'default' => false);
		unset($this->vtypes['include_trends']);
		$this->vtypes['report_period'] = array('type' => 'enum', 'default' => 'thisyear', 'options' => array(
			"thisyear" => _('This Year'),
			"lastyear" => _('Last Year'),
			"lastmonth" => _('Last Month'),
			"last3months" => _('Last 3 Months'),
			"last6months" => _('Last 6 months'),
			"lastquarter" => _('Last Quarter'),
			"last12months" => _('Last 12 months')
		));
		// Warning! months is 1-indexed
		$this->vtypes['months'] = array('type' => 'array', 'default' => false);

		parent::__construct($options);
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

	public function update_value($name, $value) {
		switch ($name) {
		 case 'start_time':
			if (!is_numeric($value))
				$value = strtotime($value);
			$this->set('start_year', date('Y', $value));
			$this->set('start_month', date('m', $value));
			break;
		 case 'end_time':
			$this->set('end_year', date('Y', $value));
			$this->set('end_month', date('m', $value));
			break;
		 case 'start_year':
		 case 'start_month':
			if ($this['start_year'] && $this['start_month'])
				$this->options['start_time'] = mktime(0, 0, 0, $this['start_month'], 1, $this['start_year']);
			$this->options[$name] = $value;
			return true;
		 case 'end_year':
		 case 'end_month':
			if ($this['end_year'] && $this['end_month']) {
				$this->options['end_time'] = mktime(0, 0, 0, $this['end_month']+1, 1, $this['end_year']);
			}
			$this->options[$name] = $value;
			return true;
		 default:
			return parent::update_value($name, $value);
		}
	}
}
