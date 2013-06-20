<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Report options for all kinds of Summary reports
 */
class Summary_options extends Report_options
{
	const RECENT_ALERTS = 1; /**< A summary that lists alerts from newest to oldest */
	const ALERT_TOTALS = 2; /**< A summary that displays which ones and how many alerts each object has retrieved */
	const TOP_ALERT_PRODUCERS = 3; /**< A summary that displays a top list of the most frequently alerting objects */

	public function setup_properties()
	{
		parent::setup_properties();
		$this->properties['summary_type'] = array('type' => 'enum', 'default' => self::TOP_ALERT_PRODUCERS, 'options' => array(
			self::RECENT_ALERTS => _('Most recent alerts'),
			self::ALERT_TOTALS => _('Alert totals'),
			self::TOP_ALERT_PRODUCERS => _('Top alert producers')));
		$this->properties['standardreport'] = array('type' => 'enum', 'default' => '', 'options' => array(
			1 => _('Most recent hard alerts'),
			2 => _('Most recent hard host alerts'),
			3 => _('Most recent hard service alerts'),
			4 => _('Top hard alert producers'),
			5 => _('Top hard host alert producers'),
			6 => _('Top hard service alert producers')));
		// Currently only used by alert history subreports, but we add them
		// here so build_alert_summary_query can depend on them being around
		$this->properties['page'] = array('type' => 'int', 'default' => 1); /**< Warning! 1 indexed */
		$this->properties['include_downtime'] = array('type' => 'bool', 'default' => false);
		$this->properties['include_process'] = array('type' => 'bool', 'default' => false);
		$this->properties['oldest_first'] = array('type' => 'bool', 'default' => false);
		$this->properties['filter_output'] = array('type' => 'string', 'default' => false);

		$this->rename_options['displaytype'] = 'summary_type';
		$this->properties['report_period']['options']['forever'] = _('Forever');
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
						$this['report_type'] = 'hosts';
						$this->options['host_name'] = Report_options::ALL_AUTHORIZED;
						break;

					case 2: case 5:
						$this['alert_types'] = 1;
						$this['state_types'] = 2;
						$this['report_type'] = 'hosts';
						$this->options['host_name'] = Report_options::ALL_AUTHORIZED;
						break;

					case 3: case 6:
						$this['alert_types'] = 2;
						$this['state_types'] = 2;
						$this['report_type'] = 'services';
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
