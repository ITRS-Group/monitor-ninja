<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Report options for all kinds of Summary reports
 */
class Summary_options extends Report_options
{
	public static $type = 'summary';

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

		$this->properties['alert_types'] = array(
			'type' => 'enum',
			'default' => 3,
			'description' => 'Bitmap of the types of alerts to include',
			'options' => array(
				3 => _('Host and service alerts'),
				1 => _('Host alerts'),
				2 => _('Service alerts'))
		);
		$this->properties['state_types'] = array(
			'type' => 'enum',
			'default' => 3,
			'description' => 'Bitmap of the types of states to include (soft, hard, both)',
			'options' => array(
				3 => _('Hard and soft states'),
				2 => _('Hard states'),
				1 => _('Soft states'))
		);
		$this->properties['host_states'] = array(
			'type' => 'enum',
			'default' => 7,
			'description' => 'Bitmap of the host states to include (up, down, unreachable, etc)',
			'options' => array(
				7 => _('All host states'),
				6 => _('Host problem states'),
				1 => _('Host up states'),
				2 => _('Host down states'),
				4 => _('Host unreachable states'))
		);
		$this->properties['service_states'] = array(
			'type' => 'enum',
			'default' => 15,
			'description' => 'Bitmap of the service states to include (ok, warning, critical, etc)',
			'options' => array(
				15 => _('All service states'),
				14 => _('Service problem states'),
				1 => _('Service OK states'),
				2 => _('Service warning states'),
				4 => _('Service critical states'),
				8 => _('Service unknown states'))
		);
		$this->properties['summary_items'] = array(
			'type' => 'int',
			'default' => 25,
			'description' => 'Number of summary items to include in reports'
		);
		$this->properties['include_long_output'] = array(
			'type' => 'bool',
			'default' => false,
			'description' => 'Set this to include the full plugin output with the output of your reports'
		);

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
						$this->options['objects'] = Report_options::ALL_AUTHORIZED;
						break;

					case 2: case 5:
						$this['alert_types'] = 1;
						$this['state_types'] = 2;
						$this['report_type'] = 'hosts';
						$this->options['objects'] = Report_options::ALL_AUTHORIZED;
						break;

					case 3: case 6:
						$this['alert_types'] = 2;
						$this['state_types'] = 2;
						$this['report_type'] = 'services';
						$this->options['objects'] = Report_options::ALL_AUTHORIZED;
						break;

					default:
						var_dump('unknown standard report');
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
