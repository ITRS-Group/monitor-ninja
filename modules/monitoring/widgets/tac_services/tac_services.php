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
}
