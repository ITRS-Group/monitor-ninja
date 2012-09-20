<?php defined('SYSPATH') OR die('No direct access allowed.');

class Avail_options extends Report_options {
	public function __construct($options = false) {
		$this->vtypes['report_period']['default'] = 'last7days';
		parent::__construct($options);
	}
}
