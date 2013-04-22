<?php defined('SYSPATH') OR die('No direct access allowed.');

class Alert_history_options extends Summary_options {
	public function setup_properties() {
		parent::setup_properties();
		$this->properties['host_states']['type'] = 'array';
		$this->properties['service_states']['type'] = 'array';

		$this->properties['report_period']['default'] = 'forever';
		$this->properties['summary_items']['default'] = 100;
		$this->properties['host_name']['default'] = Report_options::ALL_AUTHORIZED;
		if(ninja::has_module('synergy')) {
			$this->properties['synergy_events'] = array('type' => 'boolean', 'default' => false);
		}
		parent::__construct($options);
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

		$this->properties['report_period']['default'] = 'forever';
		$this->properties['report_type']['default'] = 'hosts';
		$this->properties['summary_items']['default'] = 100;
		$this->properties['host_name']['default'] = Report_options::ALL_AUTHORIZED;
		if(ninja::has_module('synergy')) {
			$this->properties['synergy_events'] = array('type' => 'boolean', 'default' => false);
		}
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
