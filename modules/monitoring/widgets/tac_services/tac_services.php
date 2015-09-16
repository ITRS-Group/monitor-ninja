<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Services widget for tactical overview
 *
 * @author     op5 AB
 */
class Tac_services_Widget extends tablestat_Widget {
	public function __construct($widget_model) {
		parent::__construct($widget_model);
		$this->universe = ServicePool_Model::all();
	}

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'Services',
			'instanceable' => true
		));
	}
}
