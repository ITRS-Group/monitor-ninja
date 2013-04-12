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
	}
}
