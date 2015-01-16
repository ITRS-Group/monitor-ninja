<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Report options class for availability reports
 */
class Avail_options extends Report_options {
	public static $type = 'avail';

	public function setup_properties() {
		parent::setup_properties();
		$this->properties['include_pie_charts'] = array(
			'type' => 'bool',
			'default' => false,
			'description' => 'Include pie charts'
		);
		$this->properties['include_trends'] = array(
			'type' => 'bool',
			'default' => false,
			'description' => 'Include trends graph'
		);
		$this->properties['include_trends_scaling'] = array(
			'type' => 'bool',
			'default' => false,
			'description' => 'Scale up active sections of the trends graph'
		);
		$this->properties['collapse_green_trends'] = array(
			'type' => 'bool',
			'default' => false,
			'description' => 'Hide trend graphs that are 100% green'
		);
		$this->properties['time_format'] = array(
			'type' => 'enum',
			'default' => 1,
			'description' => 'How to render the portion of time a check has had a certain state',
			'options' => array(
				1 => _('Percentage'),
				2 => _('Absolute time'),
				3 => _('Both'))
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
