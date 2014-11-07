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
				$this['host_states'] = array();
			else
				$this['service_states'] = array();
			break;
		}
	}
}
