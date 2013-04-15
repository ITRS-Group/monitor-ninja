<?php defined('SYSPATH') OR die('No direct access allowed.');

class Alert_history_options extends Summary_options {
	public function setup_properties() {
		parent::setup_properties();
		$this->properties['report_period']['default'] = 'forever';
		$this->properties['report_type']['default'] = 'hosts';
		$this->properties['summary_items']['default'] = 100;
		$this->properties['host_name']['default'] = Report_options::ALL_AUTHORIZED;
		if(ninja::has_module('synergy')) {
			$this->properties['synergy_events'] = array('type' => 'boolean', 'default' => false);
		}
	}
}
