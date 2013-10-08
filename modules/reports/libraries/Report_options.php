<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Report_options is an object representing the user-selected report
 *
 * It's created to improve consistency between report types and frontend/backend
 */
class Report_options implements ArrayAccess, Iterator, Countable {
	/**
	 * Can contains options that must be renamed when provided - for legacy links
	 */
	protected $rename_options;

	/**
	 * Contains a definition of all legal keys for this type of Report_options
	 */
	protected $properties = array(
		'report_id' => array(
			'type' => 'int',
			'default' => false,
			'description' => 'Saved report id - not to be confused with schedule_id'
		),
		'report_name' => array(
			'type' => 'string',
			'default' => false,
			'description' => 'Name of the report'
		),
		'report_type' => array(
			'type' => 'enum',
			'default' => false,
			'options' => array(
				'hosts' => 'host_name',
				'services' => 'service_description',
				'hostgroups' => 'hostgroup',
				'servicegroups' => 'servicegroup'
			),
			'description' => 'The type of objects in the report, set automatically by setting the actual objects'
		),
		'report_period' => array(
			'type' => 'enum',
			'default' => 'last7days',
			'description' => 'A report period to generate the report over, may automatically set {start,end}_time'
		),
		'rpttimeperiod' => array(
			'type' => 'enum',
			'default' => false,
			'description' => 'If we are to mask the alerts by a certain (nagios) timeperiod, and if so, which one'
		),
		'scheduleddowntimeasuptime' => array(
			'type' => 'enum',
			'default' => 0,
			'description' => 'Schedule downtime as uptime: yes, no, "yes, but tell me when you cheated"'
		),
		'assumestatesduringnotrunning' => array(
			'type' => 'bool',
			'default' => true,
			'description' => 'Whether to assume states during not running'
		),
		'includesoftstates' => array(
			'type' => 'bool',
			'default' => false, 'description' => 'Include soft states, yes/no?'
		),
		'host_name' => array(
			'type' => 'objsel',
			'default' => array(),
			'description' => 'Hosts to include (note: array)'
		),
		'service_description' => array(
			'type' => 'objsel',
			'default' => array(),
			'description' => 'Services to include (note: array)'
		),
		'hostgroup' => array(
			'type' => 'array',
			'default' => array(),
			'description' => 'Hostgroups to include (note: array)'
		),
		'servicegroup' => array(
			'type' => 'array',
			'default' => array(),
			'description' => 'Servicegroups to include (note: array)'
		),
		'start_time' => array(
			'type' => 'timestamp',
			'default' => 0,
			'description' => 'Start time for report, timestamp or date-like string'
		),
		'end_time' => array(
			'type' => 'timestamp',
			'default' => 0,
			'description' => 'End time for report, timestamp or date-like string'
		),
		'sla_mode' => array(
			'type' => 'enum',
			'default' => 0,
			'description' => 'Use worst, average, or best state for calculating overall health'
		),
		'host_filter_status' => array(
			'type' => 'array',
			'default' => array(),
			'description' => 'Key: hide these. Value: map them to this instead (-2 means "secret")'
		),
		'service_filter_status' => array(
			'type' => 'array',
			'default' => array(),
			'description' => 'Key: hide these. Value: map them to this instead (-2 means "secret")'
		),
		'output_format' => array(
			'type' => 'enum',
			'default' => 'html',
			'options' => array(
				'html' => 'html',
				'csv' => 'csv',
				'pdf' => 'pdf'
			),
			'generated' => true,
			'description' => 'What type of report to generate (manually selected in web UI, generated from filename for saved reports)'
		),
		'skin' => array(
			'type' => 'string',
			'default' => '',
			'description' => 'Use the following skin for rendering the report'
		),
		'use_alias' => array(
			'type' => 'bool',
			'default' => false,
			'description' => "Use object's aliases instead of their names"
		),
		'description' => array(
			'type' => 'string',
			'default' => false
		),
		'include_alerts' => array(
			'type' => 'bool',
			'default' => false
		)
	);

	/**
	 * Placeholder used instead of fetching all objects the user is
	 * authorized to see, to be able to fetch lazily and avoid large
	 * queries or filtering large result sets.
	 */
	const ALL_AUTHORIZED = '*';

	/**
	 * The the explicitly set options. Should not be accessed directly,
	 * outside of debugging.
	 */
	public $options = array();

	/**
	 * Properties should be completely setup - with translations and all - before
	 * loading any options, and options are loaded by the construct, so do
	 * initialization here.
	 */
	public function setup_properties()
	{
		if (isset($this->properties['report_period']))
			$this->properties['report_period']['options'] = array(
				"today" => _('Today'),
				"last24hours" => _('Last 24 hours'),
				"yesterday" => _('Yesterday'),
				"thisweek" => _('This week'),
				"last7days" => _('Last 7 days'),
				"lastweek" => _('Last week'),
				"thismonth" => _('This month'),
				"last31days" => _('Last 31 days'),
				"lastmonth" => _('Last month'),
				'last3months' => _('Last 3 months'),
				'lastquarter' => _('Last quarter'),
				'last6months' => _('Last 6 months'),
				'last12months' => _('Last 12 months'),
				"thisyear" => _('This year'),
				"lastyear" => _('Last year'),
				'custom' => _('Custom'));
		if (isset($this->properties['scheduleddowntimeasuptime']))
			$this->properties['scheduleddowntimeasuptime']['options'] = array(
				0 => _('Actual state'),
				1 => _('Uptime'),
				2 => _('Uptime, with difference'));
		if (isset($this->properties['sla_mode']))
			$this->properties['sla_mode']['options'] = array(
				0 => _('Group availability (Worst state)'),
				1 => _('Average'),
				2 => _('Cluster mode (Best state)'));
		if (isset($this->properties['rpttimeperiod']))
			$this->properties['rpttimeperiod']['options'] = Old_Timeperiod_Model::get_all();
		if (isset($this->properties['skin']))
			$this->properties['skin']['default'] = config::get('config.current_skin', '*');

		$this->rename_options = array(
			't1' => 'start_time',
			't2' => 'end_time',
			'host' => 'host_name',
			'service' => 'service_description',
			'hostgroup_name' => 'hostgroup',
			'servicegroup_name' => 'servicegroup'
		);
	}

	/**
	 * Public constructor, which optionally takes an iterable with properties to set
	 */
	public function __construct($options=false) {
		$this->setup_properties();
		if ($options)
			$this->set_options($options);
	}

	/**
	 * Required by ArrayAccess
	 */
	public function offsetGet($str)
	{
		if (!isset($this->properties[$str]))
			return NULL;

		return arr::search($this->options, $str, $this->properties[$str]['default']);
	}

	/**
	 * Required by ArrayAccess
	 */
	public function offsetSet($key, $val)
	{
		$this->set($key, $val);
	}

	/**
	 * Required by ArrayAccess
	 */
	public function offsetExists($key)
	{
		return isset($this->properties[$key]);
	}

	/**
	 * Required by ArrayAccess
	 */
	public function offsetUnset($key)
	{
		unset($this->options[$key]);
	}

	/**
	 * This looks silly...
	 */
	function properties()
	{
		return $this->properties;
	}

	/**
	 * For applicable keys, this returns a list of all possible values
	 */
	public function get_alternatives($key) {
		if (!isset($this->properties[$key]))
			return false;
		if ($this->properties[$key]['type'] !== 'enum' && $this->properties[$key]['type'] !== 'array')
			return false;
		return $this->properties[$key]['options'];
	}

	/**
	 * Returns the user-friendly value, given a machine-friendly option key
	 */
	public function get_value($key) {
		if (!isset($this->properties[$key]))
			return false;
		if ($this->properties[$key]['type'] !== 'enum')
			return false;
		if (!isset($this->properties[$key]['options'][$this[$key]]))
			return $key;
		return $this->properties[$key]['options'][$this[$key]];
	}

	/**
	 * Return all objects (hosts or services) this report applies to
	 * This will return hosts or services regardless if the report object selection
	 * uses groups or not.
	 */
	public function get_report_members() {
		switch ($this['report_type']) {
		 case 'hosts':
		 case 'services':
			return $this[$this->get_value('report_type')];
		 case 'hostgroups':
			$filter = array();
			foreach ($this['hostgroup'] as $group) {
				$filter[] = 'in "'.$group.'"';
			}
			$filter = implode(' or ', $filter);
			if (!$filter)
				$filter = 'all';
			$out = ObjectPool_Model::get_by_query('[hosts] '.$filter);
			$out = $out->it(array('name'), array());
			$res = array();
			foreach ($out as $arr) {
				$res[] = $arr->get_key();
			}
			return $res;
		 case 'servicegroups':
			$filter = array();
			foreach ($this['servicegroup'] as $group) {
				$filter[] = 'in "'.$group.'"';
			}
			$filter = implode(' or ', $filter);
			if (!$filter)
				$filter = 'all';
			$out = ObjectPool_Model::get_by_query('[services] '.$filter);
			$out = $out->it(array('host.name', 'description'), array());
			$res = array();
			foreach ($out as $arr) {
				$res[] = $arr->get_key();
			}
			return $res;
		}
		return false;
	}

	/**
	 * Update the options for the report
	 * @param $options New options
	 */
	public function set_options($options)
	{
		$errors = false;
		foreach ($options as $name => $value) {
			$errors |= intval(!$this->set($name, $value));
		}

		return $errors ? false : true;
	}


	/**
	 * Calculates $this['start_time'] and $this['end_time'] based on an
	 * availability report style period such as "today", "last24hours"
	 * or "lastmonth".
	 *
	 * @param $report_period The textual period to set our options by
	 * @return false on errors, true on success
	 */
	protected function calculate_time($report_period)
	{
		$now = time();
		$year_now 	= date('Y', $now);
		$month_now 	= date('m', $now);
		$day_now	= date('d', $now);
		$week_now 	= date('W', $now);
		$weekday_now = date('w', $now)-1;
		$time_start	= false;
		$time_end	= false;

		switch ($report_period) {
			case 'today':
			       $time_start = mktime(0, 0, 0, $month_now, $day_now, $year_now);
			       $time_end 	= $now;
			       break;
			case 'last24hours':
			       $time_start = mktime(date('H', $now), date('i', $now), date('s', $now), $month_now, $day_now -1, $year_now);
			       $time_end 	= $now;
			       break;
			case 'yesterday':
			       $time_start = mktime(0, 0, 0, $month_now, $day_now -1, $year_now);
			       $time_end 	= mktime(0, 0, 0, $month_now, $day_now, $year_now);
			       break;
			case 'thisweek':
			       $time_start = strtotime('today - '.$weekday_now.' days');
			       $time_end 	= $now;
			       break;
			case 'last7days':
			       $time_start	= strtotime('now - 7 days');
			       $time_end	= $now;
			       break;
			case 'lastweek':
			       $time_start = strtotime('midnight last monday -7 days');
			       $time_end	= strtotime('midnight last monday');
			       break;
			case 'thismonth':
			       $time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			       $time_end	= $now;
			       break;
			case 'last31days':
			       $time_start = strtotime('now - 31 days');
			       $time_end	= $now;
			       break;
			case 'lastmonth':
			       $time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01 -1 month');
			       $time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			       break;
			case 'thisyear':
			       $time_start = strtotime('midnight '.$year_now.'-01-01');
			       $time_end	= $now;
			       break;
			case 'lastyear':
			       $time_start = strtotime('midnight '.$year_now.'-01-01 -1 year');
			       $time_end	= strtotime('midnight '.$year_now.'-01-01');
			       break;
			case 'last12months':
			       $time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -12 months');
			       $time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			       break;
			case 'last3months':
			       $time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -3 months');
			       $time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			       break;
			case 'last6months':
			       $time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -6 months');
			       $time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			       break;
			case 'lastquarter':
			       $t = getdate();
			       if($t['mon'] <= 3){
				$lqstart = ($t['year']-1)."-10-01";
				$lqend = ($t['year']-1)."-12-31";
			       } elseif ($t['mon'] <= 6) {
				$lqstart = $t['year']."-01-01";
				$lqend = $t['year']."-03-31";
			       } elseif ($t['mon'] <= 9){
				$lqstart = $t['year']."-04-01";
				$lqend = $t['year']."-06-30";
			       } else {
				$lqstart = $t['year']."-07-01";
				$lqend = $t['year']."-09-30";
			       }
			       $time_start = strtotime($lqstart);
			       $time_end = strtotime($lqend);
			       break;
			case 'custom':
			       # we'll have "start_time" and "end_time" in
			       # the options when this happens
			       return true;
			default:
				# unknown option, ie bogosity
				return false;
		}

		if($time_start > $now)
			$time_start = $now;

		if($time_end > $now)
			$time_end = $now;

		$this->options['start_time'] = $time_start;
		$this->options['end_time'] = $time_end;
		return true;
	}

	/**
	 * Set an option, with some validation
	 *
	 * @param $name Option name
	 * @param $value Option value
	 * @return false on error, else true
	 */
	public function set($name, $value)
	{
		if (isset($this->rename_options[$name])) {
			if (is_callable($this->rename_options[$name])) {
				try {
					$value = call_user_func($this->rename_options[$name], $name, $value, $this);
				} catch (Exception $e) {
					return false;
				}
			}
			else if (is_string($this->rename_options[$name])) {
				$name = $this->rename_options[$name];
			}
		}

		if (!$this->validate_value($name, $value)) {
			return false;
		}
		return $this->update_value($name, $value);
	}

	/**
	 * Validates that $value isn't obviously unsuitable for $key
	 *
	 * Warning: you probably want to use set() or utilize the ArrayAccess API
	 */
	protected function validate_value($key, &$value)
	{
		if (!isset($this->properties[$key])) {
			return false;
		}
		switch ($this->properties[$key]['type']) {
		 case 'bool':
			if ($value == 1 || !strcasecmp($value, "true") || !empty($value))
				$value = true;
			else
				$value = false;
			break;
		 case 'int':
			if (!is_numeric($value) || $value != intval($value))
				return false;
			$value = intval($value);
			break;
		 case 'string':
			if (!is_string($value))
				return false;
			break;
		 case 'objsel':
			if ($value == self::ALL_AUTHORIZED)
				return true;
		 case 'array':
			if (!is_array($value))
				return false;
			break;
		 case 'timestamp':
			if (!is_numeric($value)) {
			if (strstr($value, '-') === false)
				return false;
			$value = strtotime($value);
			if ($value === false)
				return false;
			}
			break;
		 case 'object':
			if (!is_object($value)) {
				return false;
			}
			break;
		 case 'enum':
			if (!isset($this->properties[$key]['options'][$value]))
				return false;
			break;
		 default:
			# this is an exception and should never ever happen
			return false;
		}
		return true;
	}

	/**
	 * Will actually set the provided $name to the value $value
	 *
	 * Warning: you probably want to use set() or utilize the ArrayAccess API
	 */
	protected function update_value($name, $value)
	{
		switch ($name) {
		 case 'report_period':
			if (!$this->calculate_time($value))
				return false;
			break;
		 case 'summary_items':
			if ($value < 0)
				return false;
			break;
		 case 'host_filter_status':
			$value = array_intersect_key($value, Reports_Model::$host_states);
			$value = array_filter($value, function($val) {
				return is_numeric($val);
			});
			break;
		 case 'service_filter_status':
			$value = array_intersect_key($value, Reports_Model::$service_states);
			$value = array_filter($value, function($val) {
				return is_numeric($val);
			});
			break;
		 case 'host_name':
			if (!$value)
				return false;
			$this->options['hostgroup'] = array();
			$this->options['servicegroup'] = array();
			$this->options['service_description'] = array();
			$this->options['host_name'] = $value;
			$this['report_type'] = 'hosts';
			$this->hosts = array();
			return true;
		 case 'service_description':
			if (!$value)
				return false;
			if($value != self::ALL_AUTHORIZED) {
				foreach ($value as $svc) {
					if (strpos($svc, ';') === false)
						return false;
				}
			}
			$this->options['hostgroup'] = array();
			$this->options['servicegroup'] = array();
			$this->options['host_name'] = array();
			$this->options['service_description'] = $value;
			$this['report_type'] = 'services';
			$this->services = array();
			return true;
		 case 'hostgroup':
			if (!$value)
				return false;
			$this->options['host_name'] = array();
			$this->options['service_description'] = array();
			$this->options['servicegroup'] = array();
			$this->options['hostgroup'] = $value;
			$this['report_type'] = 'hostgroups';
			$this->hosts = array();
			return true;
		 case 'servicegroup':
			if (!$value)
				return false;
			$this->options['host_name'] = array();
			$this->options['service_description'] = array();
			$this->options['hostgroup'] = array();
			$this->options['servicegroup'] = $value;
			$this['report_type'] = 'servicegroups';
			$this->services = array();
			return true;
		 case 'start_time':
		 case 'end_time':
			// value "impossible", or value already set by report_period
			// (we consider anything before 1980 impossible, or at least unreasonable)
			if ($value <= 315525600 || $value === 'undefined' || (isset($this->options[$name]) && isset($this->options['report_period']) && $this->options['report_period'] != 'custom'))
				return false;
			if (!is_numeric($value))
				$value = strtotime($value);
			break;
		 default:
			break;
		}
		if (!isset($this->properties[$name]))
			return false;
		$this->options[$name] = $value;
		return true;
	}

	/**
	 * Generate a standard HTTP keyval string, suitable for URLs or POST bodies.
	 * @param $anonymous If true, any option on the exact objects in this report
	 *                   will be purged, so it's suitable for linking to sub-reports.
	 *                   If false, all options will be kept, completely describing
	 *                   this exact report.
	 * @param $obj_only Does more-or-less the inverse of $anonymous - if true, don't
	 *                  include anything that does not refer to the members of the report.
	 */
	public function as_keyval_string($anonymous=false, $obj_only=false) {
		$opts_str = '';
		foreach ($this as $key => $val) {
			if ($obj_only && !in_array($key, array('host_name', 'service_description', 'hostgroup', 'servicegroup', 'report_type')))
				continue;
			if ($anonymous && in_array($key, array('host_name', 'service_description', 'hostgroup', 'servicegroup', 'report_type', 'report_id')))
				continue;
			if (is_array($val)) {
				foreach ($val as $vk => $member) {
					$opts_str .= "&amp;{$key}[$vk]=".urlencode($member);
				}
				continue;
			}
			$opts_str .= "&$key=".urlencode($val);
		}
		return substr($opts_str, 1);
	}

	/**
	 * Return the report as a HTML string of hidden form elements
	 */
	public function as_form($anonymous=false, $obj_only=false) {
		$html_options = '';
		foreach ($this as $key => $val) {
			if ($obj_only && !in_array($key, array('host_name', 'service_description', 'hostgroup', 'servicegroup', 'report_type')))
				continue;
			if ($anonymous && in_array($key, array('host_name', 'service_description', 'hostgroup', 'servicegroup', 'report_type', 'report_id')))
				continue;
			if (is_array($val)) {
				foreach ($val as $k => $v)
				$html_options .= form::hidden($key.'['.$k.']', $v);
			}
			else {
				$html_options .= form::hidden($key, $val);
			}
		}
		return $html_options;
	}

	/**
	 * Return the report as a JSON string
	 */
	public function as_json() {
		$opts = $this->options;
		if ($this->get_value('report_type')) {
			// because the person who wrote the js became sick of all our special cases,
			// it expects the objects to be called 'objects'. Which makes sense, really...
			if ($this[$this->get_value('report_type')])
				$opts['objects'] = $this[$this->get_value('report_type')];
			unset($opts[$this->get_value('report_type')]);
		}
		return json_encode($opts);
	}

	/**
	 * Expand the private structure, to make a traversal iterate over all the properties
	 */
	public function expand() {
		foreach ($this->properties as $key => $_) {
			$this[$key] = $this[$key];
		}
	}

	/**
	 * Return the given timestamp typed property as a date string of the configured kind
	 */
	public function get_date($var) {
		$format = cal::get_calendar_format(true);
		return date($format, $this[$var]);
	}

	/**
	 * Return the given timestamp typed property as a time string
	 */
	public function get_time($var) {
		return date('H:i', $this[$var]);
	}

	/** Required by Iterator */
	function rewind() { reset($this->options); }
	/** Required by Iterator */
	function current() { return current($this->options); }
	/** Required by Iterator */
	function key() { return key($this->options); }
	/** Required by Iterator */
	function next() {
		do {
			$x = next($this->options);
		} while ($x !== false && isset($this->properties[key($this->options)]['generated']));
	}
	/** Required by Iterator */
	function valid() { return array_key_exists(key($this->options), $this->options); }
	/** Required by Countable */
	function count() { return count($this->options); }

	/** Print the options themselves when printing the object */
	function __toString() { return var_export($this->options, true); }

	/**
	 * Finds properties to inject into.. myself
	 *
	 * You probably want setup_options_obj instead.
	 *
	 * @param $input array = false Autodiscovers options using superglobals: $input > POST > GET
	 * @return array
	 */
	static function discover_options($input = false)
	{
		# not using $_REQUEST, because that includes weird, scary session vars
		if (!empty($input)) {
			$report_info = $input;
		} else if (!empty($_POST)) {
			$report_info = $_POST;
		} else {
			$report_info = $_GET;
		}

		if(isset($report_info['cal_start'], $report_info['cal_end'], $report_info['report_period']) &&
				$report_info['cal_start'] &&
				$report_info['cal_end'] &&
				$report_info['report_period'] == 'custom'
			) {

			if(!isset($report_info['time_start']) || $report_info['time_start'] === "") {
				$report_info['time_start'] = "00:00";
			}
			if(!isset($report_info['time_end']) || $report_info['time_end'] === "") {
				$report_info['time_end'] = "23:59";
			}

			$report_info['start_time'] = DateTime::createFromFormat(
				nagstat::date_format(),
				$report_info['cal_start'].' '.$report_info['time_start'].':00'
			)->getTimestamp();
			$report_info['end_time'] = DateTime::createFromFormat(
				nagstat::date_format(),
				$report_info['cal_end'].' '.$report_info['time_end'].':00'
			)->getTimestamp();

			unset(
				$report_info['cal_start'],
				$report_info['cal_end'],
				$report_info['time_start'],
				$report_info['time_end']
			);
		}

		return $report_info;
	}

	/**
	 * Combines the provided properties with any saved information.
	 *
	 * You probably want setup_options_obj instead.
	 */
	protected static function create_options_obj($type, $report_info = false) {
		$options = new static($report_info);

		if (isset($report_info['report_id']) && !empty($report_info['report_id'])) {
			$saved_report_info = Saved_reports_Model::get_report_info($type, $report_info['report_id']);

			$options = new static($report_info);
			if (!empty($saved_report_info)) {
				foreach ($saved_report_info as $key => $sri) {
					if (isset($report_info[$key]) || !isset($options->properties[$key]) || $options[$key] !== $options->properties[$key]['default'] || ($options->properties[$key]['type'] === 'bool' && count($report_info) > 3))
						continue;
					$options[$key] = $sri;
				}
				if ($options['report_type'] && isset($saved_report_info['objects']) && !$options[$options->get_value('report_type')])
					$options[$options->get_value('report_type')] = $saved_report_info['objects'];
			} else {
				unset($options['report_id']);
			}
		}
		if (isset($options->properties['report_period']) && !isset($options->options['report_period']) && isset($options->properties['report_period']['default']))
			$options->calculate_time($options['report_period']);
		return $options;
	}

	/**
	 * @param $type string avail|sla|summary
	 * @param $input array = false
	 * @return Report_options
	 */
	public static function setup_options_obj($type, $input = false)
	{
		if (is_a($input, 'Report_options')) {
			$class = get_class($input);
			return new $class($input);
		}
		$class = ucfirst($type) . '_options';
		if (!class_exists($class))
			$class = 'Report_options';

		$report_info = $class::discover_options($input);
		$options = $class::create_options_obj($type, $report_info);
		return $options;
	}
}
