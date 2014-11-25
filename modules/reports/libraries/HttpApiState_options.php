<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * The report options for the State type of reports in the HTTP API
 */
class HttpApiState_options extends Report_options {
	public static $type = 'httpapistate';

	public function setup_properties()
	{
		parent::setup_properties();
		$this->properties = array_intersect_key(
			$this->properties,
			array_flip(array(
				'state_types',
				'start_time',
				'objects',
				'report_type',
			))
		);

		$this->properties['state_types']['options'] = array(
			1 => 'soft',
			2 => 'hard',
			3 => 'both',
		);
		$this->properties['state_types']['default'] = 3; // default for summary-style reports, used for consistency

		foreach (array('host_name', 'service_description', 'hostgroup', 'servicegroup') as $objtype) {
			$this->properties[$objtype] = $this->properties['objects'];
			$type = explode('_', $objtype);
			$this->properties[$objtype]['description'] = ucfirst($type[0]).'s to include (note: array)';
		}
		$this->properties['objects']['generated'] = true;
		$this->properties['report_type']['generated'] = true;
		$this->properties['report_type']['default'] = 'hosts';
		$this->properties['time'] = $this->properties['start_time'];
		$this->properties['time']['description'] = _("A UNIX timestamp at which you want the included objects' state");
		$this->properties['start_time']['generated'] = true;
		$this->rename_options['time'] = 'start_time';
	}

	/**
	 * @param $value mixed
	 * @param $type string
	 * @return string
	 */
	function format_default($value, $type)
	{
		if($type == 'bool') {
			return (int) $this[$value];
		}
		if($type == 'array' || $type == 'objsel') {
			if(empty($this[$value])) {
				return "[empty]";
			}
			return implode(", ", $this[$value]);
		}
		if($type == 'string' && !$this[$value]) {
			return '[empty]';
		}
		if($type == 'enum') {
			return "'".$this->get_value($value)."'";
		}
		if($type == 'int' && empty($this[$value]) && $this[$value] !== 0) {
			return "[empty]";
		}
		return (string) $this[$value];
	}

	/**
	 * Not as forgiving as the parent. (Why is parent forgiving?)
	 *
	 * @param $options array
	 * @throws ReportValidationException
	 */
	function set_options($options) {
		foreach($options as $name => $value) {
			if(!$this->set($name, $value)) {
				throw new ReportValidationException("Invalid value for option '$name'");
			}
		}
	}

	/**
	 * Final step in the "from merlin.report_data row to API-output" process
	 *
	 * @param $row array
	 * @return array
	 */
	function to_output($row)
	{
		// transform values
		$type = isset($row['service_description']) ? 'service' : 'host';
		$row['state'] = strtolower(Current_status_Model::status_text($row['state'], true, $type));

		return $row;
	}
}