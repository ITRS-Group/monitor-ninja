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
	 * @todo be able to throw exceptions here to give feedback of
	 * *which* error we experienced, since, you know, there's at
	 * least one user (you) exposed to this API.. Help yourself
	 *
	 * @param $key string
	 * @param $value mixed
	 * @return boolean
	 */
	protected function validate_value($key, &$value)
	{
		if (!isset($this->properties[$key])) {
			return false;
		}
		switch ($this->properties[$key]['type']) {
			case 'enum':
				$v = array_search($value, $this->properties[$key]['options'], true);
				if ($v === false)
					return false;
				else
					$value = $v;
				break;
		}
		if ($key == 'objects' && !is_array($value))
			$value = array($value);

		return parent::validate_value($key, $value);
	}
	/**
	 * Final step in the "from merlin.report_data row to API-output" process
	 *
	 * @param $row array
	 * @return array
	 */
	function to_output($row)
	{
		// challenge: write this with variable variables!
		if (isset($row['service_description']))
			$row['state'] = Reports_Model::$service_states[$row['state']];
		else
			$row['state'] = Reports_Model::$host_states[$row['state']];

		return $row;
	}
}
