<?php defined('SYSPATH') OR die('No direct access allowed.');

class Alert_history_options extends Summary_options {
	public function __construct($options = false) {
		$this->vtypes['report_period']['default'] = 'forever';
		$this->vtypes['summary_items']['default'] = 100;
		$this->vtypes['host_name']['default'] = Report_options::ALL_AUTHORIZED;
		parent::__construct($options);
	}
}
