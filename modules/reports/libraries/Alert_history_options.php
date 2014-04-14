<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Report options for alert history reports. Alert history reports are specialized summary reports.
 */
class Alert_history_options extends Summary_options {
	/**
	 * Callback helper for getting the bitmask host/service states into array form
	 */
	function rewrite_states($name, $value, $obj) {
		if (is_array($value))
			return $value;
		$res = array();
		foreach ($obj->properties[$name]['options'] as $bit => $_) {
			if ($value & $bit)
				$res[$bit] = $bit;
		}
		return $res;
	}

	public function setup_properties() {
		parent::setup_properties();
		$this->properties['host_states']['type'] = 'array';
		$this->properties['service_states']['type'] = 'array';

		$this->properties['report_period']['default'] = 'forever';
		$this->properties['report_type']['default'] = 'hosts';
		$this->properties['summary_items']['default'] = config::get('pagination.default.items_per_page', '*');
		$this->properties['objects']['default'] = Report_options::ALL_AUTHORIZED;
		if(ninja::has_module('synergy')) {
			$this->properties['synergy_events'] = array('type' => 'boolean', 'default' => false);
		}
		$this->properties['host_states']['options'] = array(
			1 => _('Up'),
			2 => _('Down'),
			4 => _('Unreachable'),
		);
		$this->properties['service_states']['options'] = array(
			1 => _('Ok'),
			2 => _('Warning'),
			4 => _('Critical'),
			8 => _('Unknown'),
		);

		$this->properties['page'] = array('type' => 'int', 'default' => 1); /**< Warning! 1 indexed */
		$this->properties['include_downtime'] = array('type' => 'bool', 'default' => false);
		$this->properties['include_process'] = array('type' => 'bool', 'default' => false);
		$this->properties['oldest_first'] = array('type' => 'bool', 'default' => false);
		$this->properties['filter_output'] = array('type' => 'string', 'default' => false);

		$this->rename_options['service_states'] = array($this, 'rewrite_states');
		$this->rename_options['host_states'] = array($this, 'rewrite_states');
	}

	protected function update_value($name, $value)
	{
		switch ($name) {
			case 'host_states':
			case 'service_states':
				$value = array_sum(array_keys($value));
				break;
		}
		parent::update_value($name, $value);
	}
}
