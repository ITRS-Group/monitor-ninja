<?php

/**
 * Class that provides API/helpers for generating the state of a report based on
 * a number of events.
 */
abstract class StateCalculator
{
	/**
	 * For hysterical reasons, we expect every single report type to return
	 * the report data in a brand new way. Jay.
	 *
	 * @return array
	 */
	abstract public function get_data();
	/**
	 * Given an event (a database row), add it to the current report
	 */
	abstract public function add_event($row);

	protected $st_text = array(); /**< Mapping between state integers and state text */

	protected $st_source = false; /**< The source object. Can be object array, can be host_name, can be host_name;service_description, can drive you mad. */
	protected $host_name = false; /**< The source object's host name, if it's just one. Set for services. */
	protected $service_description = false; /**< The source object's service description, if it's just one. Only description for services */

	/** The state template for hosts */
	protected $state_tpl_host = array(
		'HOST_NAME' => '',
		'TIME_UP_SCHEDULED' => 0,
		'TIME_UP_UNSCHEDULED' => 0,
		'TIME_DOWN_SCHEDULED' => 0,
		'TIME_DOWN_UNSCHEDULED' => 0,
		'TIME_UNREACHABLE_SCHEDULED' => 0,
		'TIME_UNREACHABLE_UNSCHEDULED' => 0,
		'TIME_UNDETERMINED_NOT_RUNNING' => 0,
		'TIME_UNDETERMINED_NO_DATA' => 0,
		);

	/** The state template for services */
	protected $state_tpl_svc = array(
		'HOST_NAME' => '',
		'SERVICE_DESCRIPTION' => '',
		'TIME_OK_SCHEDULED' => 0,
		'TIME_OK_UNSCHEDULED' => 0,
		'TIME_WARNING_SCHEDULED' => 0,
		'TIME_WARNING_UNSCHEDULED' => 0,
		'TIME_UNKNOWN_SCHEDULED' => 0,
		'TIME_UNKNOWN_UNSCHEDULED' => 0,
		'TIME_CRITICAL_SCHEDULED' => 0,
		'TIME_CRITICAL_UNSCHEDULED' => 0,
		'TIME_UNDETERMINED_NOT_RUNNING' => 0,
		'TIME_UNDETERMINED_NO_DATA' => 0,
	);

	protected $st_is_service = false; /**< Whether this is a service */
	protected $st_running = false; /**< Is nagios running? */
	protected $st_inactive = 0; /**< Time we have no information about */
	protected $st_dt_depth = 0; /**< The downtime depth */
	/**
	 * The calculated state of the object, taking such things
	 * as scheduled downtime counted as uptime into consideration
	 */
	protected $st_obj_state = false;
	protected $st_raw = array(); /**< Mapping between the raw states and the time spent there */
	protected $prev_row; /**< The last db row, so we can get duration */

	protected $options; /**< A Report_options object for this report */

	/**
	 * Create a new state calculator
	 * @param $options A report_options object that describes the report
	 * @param $timeperiod A (resolved) timeperiod to use throughout calculations
	 */
	public function __construct(Report_options $options, $timeperiod)
	{
		$this->options = $options;
		$this->timeperiod = $timeperiod;
	}

	/**
	 * Prepare a state calculator for action
	 * Takes a number of initialization arguments, simply because they can be
	 * more efficiently retrieved in bulk somewhere else.
	 * @param $initial_state The state for the object when the report starts
	 * @param $initial_depth The downtime depth when the report starts - in practice a boolean
	 * @param $is_running Is nagios itself running when the report starts?
	 */
	public function initialize($initial_state, $initial_depth, $is_running)
	{
		$this->st_running = $is_running;
		$this->st_obj_state = $initial_state;
		$this->st_dt_depth = $initial_depth;

		if ($this->options['service_description'] || $this->options['servicegroup']) {
			$this->st_is_service = true;
		}
		else {
			# we need at least a service or a host
			if (!$this->options['host_name'] && !$this->options['hostgroup'])
				return false;
		}

		$this->st_text = empty($this->st_is_service) ? Reports_Model::$host_states : Reports_Model::$service_states;
		$this->st_text = array_map('strtoupper', $this->st_text);

		$fout = "";
		if (!$this->st_running)
			$fevent_type = Reports_Model::PROCESS_SHUTDOWN;
		else {
			if ($this->st_is_service)
				$fevent_type = Reports_Model::SERVICECHECK;
			else
				$fevent_type = Reports_Model::HOSTCHECK;
		}
		$state = ($this->st_running || $this->options['assumestatesduringnotrunning']) ? $this->st_obj_state : -1;
		$this->prev_row = array
			('state' => $state,
			 'the_time' => $this->options['start_time'],
			 'event_type' => $fevent_type,
			 'downtime_depth' => $this->st_dt_depth);

		# if we're actually going to use the log, we'll need
		# to generate a faked initial message for it.
		if ($this->options['include_trends']) {
			$fout = sprintf("Report period start. Daemon is%s running, " .
			                "we're%s in scheduled downtime, state is %s (%d)",
			                $this->st_running ? '' : ' not',
			                $this->st_dt_depth ? '' : ' not',
			                $this->st_text[$state], $state);
			$this->prev_row['output'] = $fout;

			if (!empty($hostname) && is_string($hostname))
				$this->prev_row['host_name'] = $hostname;

			if (!empty($servicename) && is_string($servicename))
				$this->prev_row['service_description'] = $servicename;
		}
	}

	/**
	 * Update the raw uptime array
	 *
	 * @param $end_time When the event ends - start time is taken from prev_row
	 */
	public function st_update($end_time)
	{
		$prev_time = $this->prev_row['the_time'];
		$duration = $end_time - $prev_time;
		$active = intval($this->timeperiod->active_time($prev_time, $end_time));
		$this->st_inactive += ($end_time - $prev_time) - $active;

		$st = "$this->st_running:$this->st_dt_depth:$this->st_obj_state";
		if ($active) {
			if (!isset($this->st_raw[$st]))
				$this->st_raw[$st] = $active;
			else
				$this->st_raw[$st] += $active;
		}
	}

	/**
	 * Calculate the time spent in different states as total and percentage.
	 *
	 * @param $state State times. Has the format:<br>
	 * array("X:Y:Z" => seconds, ...). Where X, Y and Z are numeric states and rhs argument is the number of seconds in that state
	 * @param $conv State translation table. E.g. for hostgroups:<br> array(0 => 'UP', '1' => 'DOWN', '2' => 'UNREACHABLE', '-1' => 'PENDING')
	 * @return array A huge array with all possible states and time spent in that state. States called PERCENT_* contains percentages rather than a number of seconds.
	 */
	public function convert_state_table($state, $conv)
	{
		$cstate = array();
		$cstate['TIME_UNDETERMINED_NO_DATA'] = 0;
		$cstate['TIME_UNDETERMINED_NOT_RUNNING'] = 0;
		$cstate['TIME_DOWN_COUNTED_AS_UP'] = 0;
		$cstate['TOTAL_TIME_UNSCHEDULED'] = 0;
		$cstate['TOTAL_TIME_SCHEDULED'] = 0;
		$cstate['TOTAL_TIME_UNDETERMINED'] = 0;
		$cstate['TOTAL_TIME_KNOWN'] = 0;
		$cstate['TOTAL_TIME_ACTIVE'] = 0;
		foreach ($state as $s => $duration) {
			$known = false;
			$cstate['TOTAL_TIME_ACTIVE'] += $duration;
			$ary = explode(':', $s);
			$is_running = intval($ary[0]);
			$current_state = intval($ary[2]);
			$in_dt = $ary[1] != 0;
			$p3 = $in_dt ? '' : 'UN';
			$p3 .= 'SCHEDULED';

			if (!$is_running)
				$cstate['TIME_UNDETERMINED_NOT_RUNNING'] += $duration;

			$p1 = $is_running ? '_' : '_UNKNOWN_';

			# this is where we hack in scheduled downtime as uptime
			if ($in_dt && $this->options['scheduleddowntimeasuptime']) {
				$real_state = $conv[$current_state];
				$p2 = $conv[0];
				if ($real_state !== 'UP' && $real_state !== 'OK')
					$cstate['TIME_DOWN_COUNTED_AS_UP'] += $duration;
			}
			elseif (isset($conv[$current_state])) {
				$p2 = $conv[$current_state];

				if ($p2 === 'PENDING')
					$cstate['TIME_UNDETERMINED_NO_DATA'] += $duration;
			}
			else {
				$p2 = "BAD_BUG_ERROR";
			}

			if (!$is_running || $p2 === 'PENDING') {
				$known = false;
				$cstate['TOTAL_TIME_UNDETERMINED'] += $duration;
			}
			else {
				$cstate['TOTAL_TIME_KNOWN'] += $duration;
				$known = true;
			}

			$tot_state = "TOTAL_TIME_$p2";
			if (!isset($cstate[$tot_state]))
				$cstate[$tot_state] = $duration;
			else
				$cstate[$tot_state] += $duration;

			if ($known) {
				$kstate = "KNOWN_TIME_$p2";
				if (!isset($cstate[$kstate]))
					$cstate[$kstate] = $duration;
				else
					$cstate[$kstate] += $duration;
			}

			# scheduled/unscheduled totals
			$cstate['TOTAL_TIME_' . $p3] += $duration;

			$cname = 'TIME' . $p1 . $p2 . '_' . $p3;
			if (!isset($cstate[$cname]))
				$cstate[$cname] = $duration;
			else
				$cstate[$cname] += $duration;

			if ($known) {
				$cname = 'KNOWN_' . $cname;
				if (!isset($cstate[$cname]))
					$cstate[$cname] = $duration;
				else
					$cstate[$cname] += $duration;
			}
		}

		$sched_junk = array('_SCHEDULED', '_UNSCHEDULED');
		foreach (array('KNOWN_', '') as $known) {
			foreach ($conv as $s) {
				foreach ($sched_junk as $dt_str) {
					$entry = $known . "TIME_$s" . $dt_str;
					if (!isset($cstate[$entry]))
						$cstate[$entry] = 0;
					$entry = "KNOWN_TIME_$s" . $dt_str;
				}
			}
		}

		# For each $state, we need to calculate
		# PERCENT_TOTAL_TIME_$state,
		# PERCENT_TIME_$state_SCHEDULED,
		# PERCENT_TIME_$state_UNSCHEDULED,
		# PERCENT_KNOWN_TIME_$state,
		# PERCENT_KNOWN_TIME_$state_SCHEDULED,
		# PERCENT_KNOWN_TIME_$state_UNSCHEDULED
		$conv['UNDETERMINED'] = 'UNDETERMINED';
		$div = $cstate['TOTAL_TIME_ACTIVE'];
		foreach ($conv as $state) {
			$str = 'TIME_' . $state;
			foreach (array('TOTAL_', 'KNOWN_') as $prefix) {
				$full_str = $prefix . $str;

				if (!isset($cstate[$full_str]))
					$cstate[$full_str] = 0;
				$cstate['PERCENT_' . $full_str] =
					reports::percent($cstate[$full_str], $div);
			}

			foreach (array('', 'KNOWN_') as $known) {
				foreach ($sched_junk as $dt_str) {
					$perc_str = 'PERCENT_' . $known . $str . $dt_str;
					$cstate[$perc_str] =
						reports::percent(arr::search($cstate, $str . $dt_str), $div);
				}
			}

			$str = 'PERCENT_KNOWN_TIME_' . $state;
			$cstate[$str] =
				$cstate[$str . '_SCHEDULED'] + $cstate[$str . '_UNSCHEDULED'];
		}

		# mop up the oddballs and special cases
		$cstate['PERCENT_TIME_UNDETERMINED_NOT_RUNNING'] =
			reports::percent($cstate['TIME_UNDETERMINED_NOT_RUNNING'], $div);
		$cstate['PERCENT_TIME_UNDETERMINED_NO_DATA'] =
			reports::percent($cstate['TIME_UNDETERMINED_NO_DATA'], $div);
		$cstate['PERCENT_TIME_DOWN_COUNTED_AS_UP'] =
			reports::percent($cstate['TIME_DOWN_COUNTED_AS_UP'], $div);

		return $cstate;
	}

	/**
	 * Finalize the report, calculating real uptime from our internal
	 * meta-format.
	 */
	public function finalize()
	{
		// gather remaining time. If they match, it'll be 0
		$this->st_update($this->options['end_time']);
	}
}
