<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Report options class for availability reports
 */
class Avail_options extends Report_options {
	public function setup_properties() {
		parent::setup_properties();
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
	}
}
