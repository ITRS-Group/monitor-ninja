<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Report options for alert history reports. Alert history reports are specialized summary reports.
 */
class Alert_history_options extends Summary_options {
	public static $type = 'alert_history';

	public function setup_properties() {
		parent::setup_properties();
		$this->properties['report_period']['default'] = 'forever';
		$this->properties['report_type']['default'] = 'hosts';
		$this->properties['summary_items']['default'] = config::get('pagination.default.items_per_page');
		$this->properties['objects']['default'] = Report_options::ALL_AUTHORIZED;
		if(ninja::has_module('synergy')) {
			$this->properties['synergy_events'] = array('type' => 'boolean', 'default' => false);
		}

		$this->properties['page'] = array('type' => 'int', 'default' => 1); /**< Warning! 1 indexed */
		$this->properties['include_downtime'] = array('type' => 'bool', 'default' => false);
		$this->properties['include_flapping'] = array('type' => 'bool', 'default' => false);
		$this->properties['include_process'] = array('type' => 'bool', 'default' => false);
		$this->properties['oldest_first'] = array('type' => 'bool', 'default' => false);
		$this->properties['filter_output'] = array('type' => 'string', 'default' => false);
	}
}
