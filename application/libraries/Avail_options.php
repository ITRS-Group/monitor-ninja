<?php defined('SYSPATH') OR die('No direct access allowed.');

class Avail_options extends Report_options {
	public function __construct($options=false) {
		$this->properties['include_pie_charts'] = array(
			'type' => 'bool',
			'default' => false,
			'description' => 'Include pie charts'
		);
		if(ninja::has_module('synergy')) {
			$this->properties['include_synergy_events'] = array(
				'type' => 'bool',
				'default' => false,
				'description' => 'Include BSM events'
			);
		}
		parent::__construct($options);
		$this->properties['report_period']['options'] = array(
			"today" => _('Today'),
			"last24hours" => _('Last 24 Hours'),
			"yesterday" => _('Yesterday'),
			"thisweek" => _('This Week'),
			"last7days" => _('Last 7 Days'),
			"lastweek" => _('Last Week'),
			"thismonth" => _('This Month'),
			"last31days" => _('Last 31 Days'),
			"lastmonth" => _('Last Month'),
			"thisyear" => _('This Year'),
			"lastyear" => _('Last Year'),
			'custom' => _('Custom'));
	}
}
