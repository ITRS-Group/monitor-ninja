<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * The report options for the Event type of reports in the HTTP API
 */
class HttpApiEvent_options extends Summary_options {
	public static $type = 'httpapievent';

	const MAX_EVENTS = 10000; /**< Pagination limit for events retrieved from HTTP API. Hardcoded, deal with it */

	private $limit;
	/**
	 * Arrays such as host_states and service_states needs to be converted
	 * into a bitmap, which they are here.
	 */
	protected function merge_array(&$name, $value, $obj)
	{
		if (is_array($value)) {
			$res = 0;
			foreach ($value as $val) {
				$res |= $val;
			}
			return $res;
		}
		return $value;
	}

	public function setup_properties()
	{
		parent::setup_properties();
		$this->properties = array_intersect_key(
			$this->properties,
			array_flip(array(
				'report_period',
				'alert_types',
				'state_types',
				'host_states',
				'service_states',
				'start_time',
				'end_time',
				'include_comments',
				'objects',
				'report_type',
			))
		);
		$this->properties['include_downtime'] = array('type' => 'bool', 'default' => false, 'description' => "Include downtime events");
		$this->properties['include_comments'] = array(
			'type' => 'bool',
			'default' => false,
			'description' => "Include events' comments"
		);
		$this->properties['alert_types']['options'] = array(
				1 => 'host',
				2 => 'service',
				3 => 'both',
		);
		$this->properties['state_types']['options'] = array(
			1 => 'soft',
			2 => 'hard',
			3 => 'both',
		);

		$this->properties['report_period']['default'] = 'custom';
		$this->properties['report_period']['options'] = array_combine(
			array_keys($this->properties['report_period']['options']),
			array_keys($this->properties['report_period']['options']));

		$limit = $this->limit = (int) Op5Config::instance()->getConfig('http_api.report.limit');
		if($limit > self::MAX_EVENTS || $limit < 1) {
			$this->limit = self::MAX_EVENTS;
			$limit = $this->limit."; you can decrease this value in http_api.yml";
		} else {
			$limit .= "; you can increase this value in http_api.yml";
		}
		$this->properties['limit'] = array(
			'type' => 'int',
			'default' => $this->limit,
			'description' => 'Include at most this many events (between 1 and '.$limit.')'
		);
		$this->properties['offset'] = array(
			'type' => 'int',
			'default' => 0,
			'description' => 'Skip the first <em>offset</em> events matching the rest of the query, well suited for pagination'
		);
		$this->properties['start_id'] = array(
			'type' => 'int',
			'default' => false,
			'description' => 'The lowest id index to present. Like start_time, but based on the order the events entered the database, which might not be exactly the same order as their time.'
		);
		$this->properties['end_id'] = array(
			'type' => 'int',
			'default' => false,
			'description' => 'The highest id index to present. Like end_time, but based on the order the events entered the database, which might not be exactly the same order as their time.'
		);

		$this->properties['sort'] = array(
			'type' => 'enum',
			'default' => 'timestamp',
			'options' => array(
				"id" => "id",
				"timestamp" => "timestamp",
			),
			'description' => 'Sort on the specified column of the response',
		);
		foreach ($this->properties['sort']['options'] as $col) {
			$this->properties['sort']['options']['-'.$col] = '-'.$col;
		}

		foreach (array('host_name', 'service_description', 'hostgroup', 'servicegroup') as $objtype) {
			$this->properties[$objtype] = $this->properties['objects'];
			$type = explode('_', $objtype);
			$this->properties[$objtype]['description'] = ucfirst($type[0]).'s to include (note: array)';
		}
		$this->properties['objects']['generated'] = true;
		$this->properties['objects']['default'] = Report_options::ALL_AUTHORIZED;
		$this->properties['report_type']['generated'] = true;
		$this->properties['report_type']['default'] = 'hosts';
		$this->properties['host_states'] = array(
			'type' => 'array',
			'default' => 7,
			'description' => _('Limit the result set to a certain kind of host states'),
			'options' => array(
				7 => 'all',
				6 => 'problem',
				1 => 'up',
				2 => 'down',
				4 => 'unreachable',
				0 => 'none')
		);
		$this->properties['service_states'] = array(
			'type' => 'array',
			'default' => 15,
			'description' => _('Limit the result set to a certain kind of service states'),
			'options' => array(
				15 => 'all',
				14 => 'problem',
				1 => 'ok',
				2 => 'warning',
				4 => 'critical',
				8 => 'unknown',
				0 => 'none')
		);

		$this->rename_options['service_states'] = array($this, 'merge_array');
		$this->rename_options['host_states'] = array($this, 'merge_array');
	}

	/**
	 * @param $name string
	 * @param $type string
	 * @return string
	 */
	function format_default($name, $type)
	{
		if($type == 'bool') {
			return (int) $this[$name];
		}
		if($type == 'array' || $type == 'objsel') {
			if ($name == 'host_states' || $name == 'service_states')
				return '\'all\'';
			if(empty($this[$name])) {
				return "[empty]";
			}
			return implode(", ", $this[$name]);
		}
		if($type == 'string' && !$this[$name]) {
			return '[empty]';
		}
		if($type == 'enum') {
			return "'".$this->get_value($name)."'";
		}
		if($type == 'int' && empty($this[$name]) && $this[$name] !== 0) {
			return "[empty]";
		}
		return (string) $this[$name];
	}

	/**
	 * Not as forgiving as the parent. (Why is parent forgiving?)
	 *
	 * @param $options array
	 * @throws ReportValidationException
	 */
	function set_options($options) {
		foreach($options as $name => $value) {
			if (isset($this->properties[$name])) {
				switch ($this->properties[$name]['type']) {
				case 'array':
					$res = array();
					if (!is_array($value))
						$value = array($value);
					foreach ($value as $v) {
						$v = array_search($v, $this->properties[$name]['options'], true);
						if ($v === false)
							throw new ReportValidationException("Invalid value for option '$name'");
						$res[] = $v;
					}
					$value = $res;
					break;
				case 'enum':
					$value = array_search($value, $this->properties[$name]['options'], true);
					if ($value === false)
						throw new ReportValidationException("Invalid value for option '$name'");
					break;
				}
			}
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
		$type = $row['service_description'] ? 'service' : 'host';
		$row['event_type'] = Reports_Model::event_type_to_string($row['event_type'], $type, true);
		$row['state'] = Reports_Model::state_name($type, $row['state']);

		if ($row['event_type'] == "scheduled_downtime_start" || $row['event_type'] == "scheduled_downtime_stop") {
			unset($row['hard']);
			unset($row['output']);
			unset($row['retry']);
			unset($row['state']);
		}
		unset($row['downtime_depth']);
		// rename properties
		if(isset($row['username'])) {
			// comments are included and we've got one of them!
			// let's produce some hierarchy
			$row['comment'] = array(
				'username' => $row['username'],
				'comment' => $row['user_comment'],
				'timestamp' => $row['comment_timestamp'],
			);
		}
		unset($row['username'], $row['user_comment'], $row['comment_timestamp']);

		return $row;
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
		if ($this->properties[$key]['type'] == 'array')
			return true;
		if ($key == 'objects' && !is_array($value))
			$value = array($value);
		if($key == 'limit') {
			if(!$value) {
				$value = $this->limit;
				return true;
			}
			if(!is_numeric($value)) {
				return false;
			}
			$value = (int) $value;
			if($value > $this->limit || $value < 1) {
				return false;
			}
			return true;
		}
		if($key == 'start_time' && $this['report_period'] == 'custom') {
			if (!isset($this->options['end_time']))
				$this->options['end_time'] = time();
			elseif ($value > $this->options['end_time'])
				return false;
		}
		if($key == 'end_time' && isset($this->options['start_time']) && $value < $this->options['start_time']) {
			return false;
		}
		if ($key == 'start_id') {
			if (isset($this->options['end_id']) && $value > $this->options['end_id'])
				return false;
		}
		if ($key == 'end_id') {
			if (isset($this->options['start_id']) && $value < $this->options['start_id'])
				return false;
		}

		return parent::validate_value($key, $value);
	}
}
