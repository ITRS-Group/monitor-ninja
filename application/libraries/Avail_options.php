<?php defined('SYSPATH') OR die('No direct access allowed.');

class Avail_options extends Report_options {
	public function __construct($options) {

		$this->properties['include_pie_charts'] = array(
			'type' => 'bool',
			'default' => false,
			'description' => 'Include pie charts'
		);

		parent::__construct($options);

	}
}
