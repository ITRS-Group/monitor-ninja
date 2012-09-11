<?php defined('SYSPATH') OR die('No direct access allowed.');

class Report_options_core implements ArrayAccess, Iterator {
	protected $hosts = array();
	protected $services = array();
	protected static $rename_options = array(
		't1' => 'start_time',
		't2' => 'end_time',
		'host' => 'host_name',
		'service' => 'service_description',
		'hostgroup_name' => 'hostgroup',
		'servicegroup_name' => 'servicegroup'
	);
	protected $vtypes = array(
		'report_id' => array('type' => 'int', 'default' => false), /**< Saved report id - not to be confused with schedule_id */
		'report_name' => array('type' => 'string', 'default' => false), /**< Name of the report */
		'report_type' => array('type' => 'enum', 'default' => false, 'options' => array( /**< The type of objects in the report, set automatically by setting the actual objects */
			'hosts' => 'host_name',
			'services' => 'service_description',
			'hostgroups' => 'hostgroup',
			'servicegroups' => 'servicegroup')),
		'report_period' => array('type' => 'enum', 'default' => false), /**< A report period to generate the report over, may automatically set {start,end}_time */
		'alert_types' => array('type' => 'enum', 'default' => 3), /**< Bitmap of the types of alerts to include (host, service, both) */
		'state_types' => array('type' => 'enum', 'default' => 3), /**< Bitmap of the types of states to include (soft, hard, both) */
		'host_states' => array('type' => 'enum', 'default' => 7), /**< Bitmap of the host states to include (up, down, unreachable, etc) */
		'service_states' => array('type' => 'enum', 'default' => 15), /**< Bitmap of the service states to include (ok, warning, critical, etc) */
		'summary_items' => array('type' => 'int', 'default' => 25), /**< Number of summary items to include in reports */
		'cluster_mode' => array('type' => 'bool', 'default' => false), /**< Whether to use best or worst case metrics */
		'keep_logs' => array('type' => 'bool', 'default' => false, 'generated' => true), /**< Whether to keep logs around - this turns on if (for example) include_trends is activated */
		'keep_sub_logs' => array('type' => 'bool', 'default' => false, 'generated' => true), /**< Whether sub-reports should keep their logs around too - report_model generally keeps track of this */
		'rpttimeperiod' => array('type' => 'string', 'default' => false), /**< If we are to mask the alerts by a certain (nagios) timeperiod, and if so, which one */
		'scheduleddowntimeasuptime' => array('type' => 'enum', 'default' => 0), /**< Schedule downtime as uptime: yes, no, "yes, but tell me when you cheated" */
		'assumestatesduringnotrunning' => array('type' => 'bool', 'default' => false), /**< Whether to assume states during not running */
		'includesoftstates' => array('type' => 'bool', 'default' => true), /**< Include soft states, yes/no? */
		'host_name' => array('type' => 'array', 'default' => false), /**< Hosts to include (note: array) */
		'service_description' => array('type' => 'array', 'default' => false), /**< Services to include (note: array) */
		'hostgroup' => array('type' => 'array', 'default' => array()), /**< Hostgroups to include (note: array) */
		'servicegroup' => array('type' => 'array', 'default' => array()), /**< Servicegroups to include (note: array) */
		'start_time' => array('type' => 'timestamp', 'default' => 0), /**< Start time for report, timestamp or date-like string */
		'end_time' => array('type' => 'timestamp', 'default' => 0), /**< End time for report, timestamp or date-like string */
		'use_average' => array('type' => 'enum', 'default' => 0), /**< Whether to hide any SLA values and stick to averages */
		'host_filter_status' => array('type' => 'array', 'default' => array( /**< Only include these host states in results */
			Reports_Model::HOST_UP => 1,
			Reports_Model::HOST_DOWN => 1,
			Reports_Model::HOST_UNREACHABLE => 1,
			Reports_Model::HOST_PENDING => 1)),
		'service_filter_status' => array('type' => 'array', 'default' => array( /**< Only include these service states in results */
			Reports_Model::SERVICE_OK => 1,
			Reports_Model::SERVICE_WARNING => 1,
			Reports_Model::SERVICE_CRITICAL => 1,
			Reports_Model::SERVICE_UNKNOWN => 1,
			Reports_Model::SERVICE_PENDING => 1)),
		'include_trends' => array('type' => 'bool', 'default' => false), /**< Include trends graph (if possible for this report type) */
		'master' => array('type' => 'object', 'default' => false, 'generated' => true), /**< The master report, if one */
		'schedule_id' => array('type' => 'int', 'default' => false), /**< A schedule id we're currently running as, not to be confused with report_id. This cannot be calculated, so it must be included */
		'output_format' => array('type' => 'enum', 'default' => 'html', 'options' => array( /**< What type of report to generate (manually selected in web UI, generated from filename for saved reports) */
			'html' => 'html',
			'csv' => 'csv',
			'pdf' => 'csv'), 'generated' => true),
		'skin' => array('type' => 'string', 'default' => ''), /**< Use the following skin for rendering the report */
		'recipients' => array('type' => 'string', 'default' => ''), /**< Comma separated email addresses to report recipients */
		'filename' => array('type' => 'string', 'default' => ''), /**< Filename to use for saving the report, used to set output_format */
		'local_persistent_filepath' => array('type' => 'string', 'default' => ''), /**< Directory (not filename) to store the filename in locally */
	);

	public $options = array();

	public function __construct($options=false) {
		if (isset($this->vtypes['report_period']))
			$this->vtypes['report_period']['options'] = array(
				"today" => _('Today'),
				"last24hours" => _('Last 24 Hours'),
				"yesterday" => _('Yesterday'),
				"thisweek" => _('This Week'),
				"last7days" => _('Last 7 Days'),
				"lastweek" => _('Last Week'),
				"thismonth" => _('This Month'),
				"last31days" => _('Last 31 Days'),
				"lastmonth" => _('Last Month'),
				"thisyear" => _('This Year'),
				"lastyear" => _('Last Year'));
		if (isset($this->vtypes['scheduleddowntimeasuptime']))
			$this->vtypes['scheduleddowntimeasuptime']['options'] = array(
				0 => _('Actual state'),
				1 => _('Uptime'),
				2 => _('Uptime, with difference'));
		if (isset($this->vtypes['use_average']))
			$this->vtypes['use_average']['options'] = array(
				0 => _('Group availability (SLA)'),
				1 => _('Average'));
		if (isset($this->vtypes['alert_types']))
			$this->vtypes['alert_types']['options'] = array(
				3 => _('Host and Service Alerts'),
				1 => _('Host Alerts'),
				2 => _('Service Alerts'));
		if (isset($this->vtypes['state_types']))
			$this->vtypes['state_types']['options'] = array(
				3 => _('Hard and Soft States'),
				2 => _('Hard States'),
				1 => _('Soft States'));
		if (isset($this->vtypes['host_states']))
			$this->vtypes['host_states']['options'] = array(
				7 => _('All Host States'),
				6 => _('Host Problem States'),
				1 => _('Host Up States'),
				2 => _('Host Down States'),
				4 => _('Host Unreachable States'));
		if (isset($this->vtypes['service_states']))
			$this->vtypes['service_states']['options'] = array(
				15 => _('All Service States'),
				14 => _('Service Problem States'),
				1 => _('Service Ok States'),
				2 => _('Service Warning States'),
				4 => _('Service Critical States'),
				8 => _('Service Unknown States'));
		if (isset($this->vtypes['skin']))
			$this->vtypes['skin']['default'] = config::get('config.current_skin', '*');
		if ($options)
			$this->set_options($options);
	}

	public function offsetGet($str)
	{
		if (!isset($this->vtypes[$str]))
			return false;

		return arr::search($this->options, $str, $this->vtypes[$str]['default']);
	}

	public function offsetSet($key, $val)
	{
		$this->set($key, $val);
	}

	public function offsetExists($key)
	{
		return isset($this->vtypes[$key]);
	}

	public function offsetUnset($key)
	{
		unset($this->options[$key]);
	}

	public function get_alternatives($key) {
		if (!isset($this->vtypes[$key]))
			return false;
		if ($this->vtypes[$key]['type'] !== 'enum')
			return false;
		return $this->vtypes[$key]['options'];
	}

	public function get_value($key) {
		if (!isset($this->options[$key]) || !isset($this->vtypes[$key]))
			return false;
		if ($this->vtypes[$key]['type'] !== 'enum')
			return false;
		if (!isset($this->vtypes[$key]['options'][$this->options[$key]]))
			return $key;
		return $this->vtypes[$key]['options'][$this->options[$key]];
	}

	public function get_report_members() {
		switch ($this['report_type']) {
		 case 'hosts':
		 case 'services':
			return $this[$this->get_value('report_type')];
		 case 'hostgroups':
			$model = new Hostgroup_Model();
			foreach ($this[$this->get_value('report_type')] as $group)
				$this->hosts = $model->member_names($group);
			return $this->hosts;
		 case 'servicegroups':
			$model = new Servicegroup_Model();
			foreach ($this[$this->get_value('report_type')] as $group)
				$this->services = $model->member_names($group);
			return $this->services;
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
		$year_now 	= date('Y', time());
		$month_now 	= date('m', time());
		$day_now	= date('d', time());
		$week_now 	= date('W', time());
		$weekday_now = date('w', time())-1;
		$time_start	= false;
		$time_end	= false;
		$now = time();

		switch ($report_period) {
		 case 'today':
			$time_start = mktime(0, 0, 0, $month_now, $day_now, $year_now);
			$time_end 	= time();
			break;
		 case 'last24hours':
			$time_start = mktime(date('H', time()), date('i', time()), date('s', time()), $month_now, $day_now -1, $year_now);
			$time_end 	= time();
			break;
		 case 'yesterday':
			$time_start = mktime(0, 0, 0, $month_now, $day_now -1, $year_now);
			$time_end 	= mktime(0, 0, 0, $month_now, $day_now, $year_now);
			break;
		 case 'thisweek':
			$time_start = strtotime('today - '.$weekday_now.' days');
			$time_end 	= time();
			break;
		 case 'last7days':
			$time_start	= strtotime('now - 7 days');
			$time_end	= time();
			break;
		 case 'lastweek':
			$time_start = strtotime('midnight last monday -7 days');
			$time_end	= strtotime('midnight last monday');
			break;
		 case 'thismonth':
			$time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			$time_end	= time();
			break;
		 case 'last31days':
			$time_start = strtotime('now - 31 days');
			$time_end	= time();
			break;
		 case 'lastmonth':
			$time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01 -1 month');
			$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			break;
		 case 'thisyear':
			$time_start = strtotime('midnight '.$year_now.'-01-01');
			$time_end	= time();
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
	 */
	public function set($name, $value)
	{
		if (isset(static::$rename_options[$name]))
			$name = static::$rename_options[$name];

		if (!$this->validate_value($name, $value)) {
			return false;
		}
		return $this->update_value($name, $value);
	}

	protected function validate_value($key, &$value)
	{
		if (!isset($this->vtypes[$key]))
			return false;
		switch ($this->vtypes[$key]['type']) {
		 case 'bool':
			if ($value == 1 || !strcasecmp($value, "true") || !empty($value))
				$value = true;
			else
				$value = false;
			if (!is_bool($value))
				return false;
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
		 case 'list':
			if (is_array($value) && count($value) === 1)
				$value = array_pop($value);
			if (is_string($value))
				break;
			/* fallthrough */
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
			if (!isset($this->vtypes[$key]['options'][$value]))
				return false;
			break;
		 default:
			# this is an exception and should never ever happen
			return false;
		}
		return true;
	}
	
	protected function update_value($name, $value)
	{
		switch ($name) {
		 case 'report_period':
			if (!$this->calculate_time($value))
				return false;
			break;
		 # lots of fallthroughs. lowest must come first
		 case 'state_types': case 'alert_types':
			if ($value > 3)
				return false;
		 case 'host_states':
			if ($value > 7)
				return false;
		 case 'service_states':
			if ($value > 15)
				return false;
		 case 'summary_items':
			if ($value < 0)
				return false;
			break;
		 # fallthrough end
		 case 'host_filter_status':
		 case 'service_filter_status':
			if ($value === null)
				$value = false;
			else if (!is_array($value))
				$value = i18n::unserialize($value);
			break;
		 case 'include_trends':
			if ($value === true) {
				$this->set('keep_logs', true);
				$this->set('keep_sub_logs', true);
			}
			break;
		 case 'host_name':
			if (!$value)
				return false;
			$this->options['hostgroup'] = array();
			$this->options['servicegroup'] = array();
			$this->options['service_description'] = array();
			$this->options['host_name'] = $value;
			$this->options['report_type'] = 'hosts';
			$this->hosts = array();
			return true;
		 case 'service_description':
			if (!$value)
				return false;
			foreach ($value as $svc) {
				if (strpos($svc, ';') === false)
					return false;
			}
			$this->options['hostgroup'] = array();
			$this->options['servicegroup'] = array();
			$this->options['host_name'] = array();
			$this->options['service_description'] = $value;
			$this->options['report_type'] = 'services';
			$this->services = array();
			return true;
		 case 'hostgroup':
			if (!$value)
				return false;
			$this->options['host_name'] = array();
			$this->options['service_description'] = array();
			$this->options['servicegroup'] = array();
			$this->options['hostgroup'] = $value;
			$this->options['report_type'] = 'hostgroups';
			$this->hosts = array();
			return true;
		 case 'servicegroup':
			if (!$value)
				return false;
			$this->options['host_name'] = array();
			$this->options['service_description'] = array();
			$this->options['hostgroup'] = array();
			$this->options['servicegroup'] = $value;
			$this->options['report_type'] = 'servicegroups';
			$this->services = array();
			return true;
		 case 'start_time':
		 case 'end_time':
			// value "impossible", or value already set by report_period
			// (we consider anything before 1980 impossible, or at least unreasonable)
			if ($value <= 315525600 || $value === 'undefined' || (isset($this->options[$name]) && isset($this->options['report_period'])))
				return false;
			if (!is_numeric($value))
				$value = strtotime($value);
			break;
		 case 'filename':
			if (strpos($value, '.pdf') !== false) {
				$this->options['output_format'] = 'pdf';
			}
			if (strpos($value, '.csv') !== false)
				$this->options['output_format'] = 'csv';
			break;
		 case 'output_format':
			# this is the only thing preventing summary reports from breaking when saved as HTML reports
			if (isset($this->options['filename']))
				return false;
		 default:
			break;
		}
		if (!isset($this->vtypes[$name]))
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
	 */
	public function as_keyval_string($anonymous=false, $obj_only=false) {
		$opts_str = '';
		foreach ($this as $key => $val) {
			if ($obj_only && !in_array($key, array('host_name', 'service_description', 'hostgroup', 'servicegroup', 'report_type')))
				continue;
			if ($anonymous && in_array($key, array('host_name', 'service_description', 'hostgroup', 'servicegroup', 'report_type')))
				continue;
			if (is_array($val)) {
				foreach ($val as $vk => $member) {
					$opts_str .= "&{$key}[$vk]=$member";
				}
				continue;
			}
			$opts_str .= "&$key=$val";
		}
		return substr($opts_str, 1);
	}

	public function as_form($anonymous=false, $obj_only=false) {
		$html_options = '';
		foreach ($this as $key => $val) {
			if ($obj_only && !in_array($key, array('host_name', 'service_description', 'hostgroup', 'servicegroup', 'report_type')))
				continue;
			if ($anonymous && in_array($key, array('host_name', 'service_description', 'hostgroup', 'servicegroup', 'report_type')))
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

	public function as_json() {
		// because the person who wrote the js became sick of all our special cases,
		// it expects the objects to be called 'objects'. Which makes sense, really...
		$opts = $this->options;
		if ($this->get_value('report_type')) {
			$opts['objects'] = $opts[$this->get_value('report_type')];
			unset($opts[$this->get_value('report_type')]);
		}
		return json_encode($opts);
	}

	public function get_date($var) {
		$format = cal::get_calendar_format(true);
		return date($format, $this[$var]);
	}

	public function get_time($var) {
		return date('H:i', $this[$var]);
	}

	function rewind() { reset($this->options); }
	function current() { return current($this->options); }
	function key() { return key($this->options); }
	function next() {
		do {
			$x = next($this->options);
		} while ($x !== false && isset($this->vtypes[key($this->options)]['generated']));
	}
	function valid() { return array_key_exists(key($this->options), $this->options); }

	protected static function discover_options($type, $input = false)
	{
		# not using $_REQUEST, because that includes weird, scary session vars
		if (!empty($input)) {
			$report_info = $input;
		} else if (!empty($_POST)) {
			$report_info = $_POST;
		} else {
			$report_info = $_GET;
		}

		if (isset($report_info['report_id'])) {
			$saved_report_info = Saved_reports_Model::get_report_info($type, $report_info['report_id']);
			if ($saved_report_info) {
				foreach ($saved_report_info as $key => $sri) {
					if (!isset($report_info->options[$key]) || $report_info->options[$key] === $report_info->vtypes[$key]['default']) {
						$report_info[$key] = $sri;
					}
				}
			}
		}
		return $report_info;
	}

	protected static function create_options_obj($type, $report_info = false) {
		$class = ucfirst($type) . '_options';
		if (!class_exists($class))
			$class = 'Report_options';
		$options = new $class($report_info);
		if (isset($report_info['report_id'])) {
			# now that report_type is set, ship off objects to the correct var
			if (!$options[$options->get_value('report_type')] && isset($report_info['objects']))
				$options[$options->get_value('report_type')] = $report_info['objects'];
		}
		return $options;
	}

	public static function setup_options_obj($type, $input = false)
	{
		$report_info = static::discover_options($type, $input);
		$options = static::create_options_obj($type, $report_info);
		return $options;
	}
}
