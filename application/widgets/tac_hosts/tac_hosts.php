<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @author     op5 AB
 */
class Tac_hosts_Widget extends tablestat_Widget {
	public function __construct($widget_model) {
		parent::__construct($widget_model);
		$this->universe = HostPool_Model::all();
	}
}
