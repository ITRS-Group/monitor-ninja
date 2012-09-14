<?php defined('SYSPATH') OR die('No direct access allowed.');

class Summary_options_Core extends Report_options
{
	public function __construct($options=false)
	{
		$this->vtypes['summary_type'] = array('type' => 'enum', 'default' => Summary_Controller::TOP_ALERT_PRODUCERS, 'options' => array(
			Summary_Controller::RECENT_ALERTS => _('Most Recent Alerts'),
			Summary_Controller::ALERT_TOTALS => _('Alert Totals'),
			Summary_Controller::TOP_ALERT_PRODUCERS => _('Top Alert Producers')));
		$this->vtypes['standardreport'] = array('type' => 'enum', 'default' => 1, 'options' => array(
			1 => _('Most Recent Hard Alerts'),
			2 => _('Most Recent Hard Host Alerts'),
			3 => _('Most Recent Hard Service Alerts'),
			4 => _('Top Hard Alert Producers'),
			5 => _('Top Hard Host Alert Producers'),
			6 => _('Top Hard Service Alert Producers')));

		static::$rename_options['displaytype'] = 'summary_type';
		parent::__construct($options);
	}

	protected function update_value($name, $value)
	{
		switch ($name) {
		 case 'standardreport':
			if (!$value)
				return false;
			$this['report_period'] = 'last7days';
			if ($value < 4)
				$this['summary_type'] = Summary_Controller::RECENT_ALERTS;
			else
				$this['summary_type'] = Summary_Controller::TOP_ALERT_PRODUCERS;
			switch ($value) {
			 case 1: case 4:
				$this['alert_types'] = 3;
				$this['state_types'] = 2;
				$this['host_name'] = Nagios_auth_Model::instance()->get_authorized_hosts();
				break;

			 case 2: case 5:
				$this['alert_types'] = 1;
				$this['state_types'] = 2;
				$this['host_name'] = Nagios_auth_Model::instance()->get_authorized_hosts();
				break;

			 case 3: case 6:
				$this['alert_types'] = 2;
				$this['state_types'] = 2;
				$this['service_description'] = Nagios_auth_Model::instance()->get_authorized_services();
				break;

			 default:
				Kohana::debug("Unknown standard report: $value");
				die;
				break;
			}

			break;
		}
		return parent::update_value($name, $value);
	}
}
