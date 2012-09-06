<?php defined('SYSPATH') OR die('No direct access allowed.');

class Avail_options extends Report_options {
	public function __construct($options = false) {
		parent::__construct($options);
		$this->vtypes['report_period']['default'] = 'last7days';
	}
}
