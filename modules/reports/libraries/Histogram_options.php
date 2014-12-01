<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Report options for histogram reports
 */
class Histogram_options extends Summary_options
{
	public static $type = 'histogram';

	public function __construct($options=false)
	{
		$this->properties['breakdown'] = array('type' => 'enum', 'default' => 'hourly', 'options' => array(
			"monthly" => _('Monthly'),
			"dayofmonth" => _('Day of month'),
			"dayofweek" => _('Day of week'),
			"hourly" => _('Hourly')));
		$this->properties['newstatesonly'] = array('type' => 'bool', 'default' => false);
		parent::__construct($options);
	}
	protected function update_value($name, $value)
	{
		parent::update_value($name, $value);
		switch ($name) {
		 case 'report_type':
			if ($value == 'services' || $value == 'servicegroups')
				foreach ($this->get_alternatives('host_filter_status') as $k => $v)
					$this->options['host_filter_status'][$k] = -2;
			else
				foreach ($this->get_alternatives('service_filter_status') as $k => $v)
					$this->options['service_filter_status'][$k] = -2;
			break;
		}
	}
}
