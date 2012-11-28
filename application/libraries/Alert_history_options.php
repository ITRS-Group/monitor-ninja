<?php defined('SYSPATH') OR die('No direct access allowed.');

class Alert_history_options extends Summary_options {
	public function __construct($options = false) {
		$this->properties['report_period']['default'] = 'forever';
		$this->properties['summary_items']['default'] = 100;
		$this->properties['host_name']['default'] = Report_options::ALL_AUTHORIZED;
		parent::__construct($options);
	}
}
