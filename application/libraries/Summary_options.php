<?php defined('SYSPATH') OR die('No direct access allowed.');

class Summary_options_Core extends Report_options
{
	const RECENT_ALERTS = 1;
	const ALERT_TOTALS = 2;
	const TOP_ALERT_PRODUCERS = 3;

	public function __construct($options=false)
	{
		$this->vtypes['summary_type'] = array('type' => 'enum', 'default' => self::TOP_ALERT_PRODUCERS, 'options' => array(
			self::RECENT_ALERTS => _('Most Recent Alerts'),
			self::ALERT_TOTALS => _('Alert Totals'),
			self::TOP_ALERT_PRODUCERS => _('Top Alert Producers')));
		$this->vtypes['standardreport'] = array('type' => 'enum', 'default' => '', 'options' => array(
			1 => _('Most Recent Hard Alerts'),
			2 => _('Most Recent Hard Host Alerts'),
			3 => _('Most Recent Hard Service Alerts'),
			4 => _('Top Hard Alert Producers'),
			5 => _('Top Hard Host Alert Producers'),
			6 => _('Top Hard Service Alert Producers')));
		// Currently only used by alert history subreports, but we add them
		// here so build_alert_summary_query can depend on them being around
		$this->vtypes['page'] = array('type' => 'int', 'default' => 1); /**< Warning! 1 indexed */
		$this->vtypes['include_downtime'] = array('type' => 'bool', 'default' => false);
		$this->vtypes['include_process'] = array('type' => 'bool', 'default' => false);
		$this->vtypes['oldest_first'] = array('type' => 'bool', 'default' => false);

		static::$rename_options['displaytype'] = 'summary_type';
		parent::__construct($options);
		$this->vtypes['report_period']['options']['forever'] = _('Forever');
	}

	protected function update_value($name, $value)
	{
		switch ($name) {
			case 'standardreport':
				if (!$value)
					return false;
				$this['report_period'] = 'last7days';
				if ($value < 4)
					$this['summary_type'] = self::RECENT_ALERTS;
				else
					$this['summary_type'] = self::TOP_ALERT_PRODUCERS;
				switch ($value) {
					// By utilizing Report_options::ALL_AUTHORIZED, we pass on the
					// explicit selection to the report model
					case 1: case 4:
						$this['alert_types'] = 3;
						$this['state_types'] = 2;
						$this->options['host_name'] = Report_options::ALL_AUTHORIZED;
						break;

					case 2: case 5:
						$this['alert_types'] = 1;
						$this['state_types'] = 2;
						$this->options['host_name'] = Report_options::ALL_AUTHORIZED;
						break;

					case 3: case 6:
						$this['alert_types'] = 2;
						$this['state_types'] = 2;
						$this->options['service_description'] = Report_options::ALL_AUTHORIZED;
						break;

					default:
						Kohana::debug("Unknown standard report: $value");
						die;
				}
				break;
		}
		return parent::update_value($name, $value);
	}

	protected function calculate_time($report_period) {
		if ($report_period === 'forever') {
			$this->options['start_time'] = 0;
			$this->options['end_time'] = time();
			return true;
		}
		return parent::calculate_time($report_period);
	}
}
