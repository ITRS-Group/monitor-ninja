<?php defined('SYSPATH') OR die('No direct access allowed.');

class Sla_options_Core extends Report_options {
	public function __construct($options) {
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
			"last12months" => _('Last 12 months')
		));

	}

	/**
	 * Special case the prevalidation of weird month-column names in
	 * parent::create_options_obj()
	 *
	 * @param $key string a key that could be in the properties array but isn't,
	 * but we still need to know about it
	 * @return boolean
	 */
	public function always_allow_option_to_be_set($key) {
		return preg_match('/^month/', trim($key));
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
