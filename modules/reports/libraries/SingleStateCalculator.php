<?php

/**
 * State calculator for a single object - thus more of a state.. keeper?
 */
class SingleStateCalculator extends StateCalculator
{
	protected $st_last_dt_init = 1; /**< set to FALSE on nagios restart, and a timestamp on first DT start after restart, so we can exclude duplicate downtime_start */
	protected $st_real_state = false; /**< The real state of the object */
	protected $st_dt_objects = array(); /**< objects in downtime */
	public $st_log = false; /**< The log array, only used for treds, should use summary reports for this */

	public function initialize($initial_state, $initial_depth, $is_running)
	{
		parent::initialize($initial_state, $initial_depth, $is_running);

		# if user asked for it, we preserve the log
		# TODO: noes :(
		if ($this->options['include_trends']) {
			$this->st_log = array();
		}

		$this->st_real_state = $initial_state;
		# Warning: PHP sucks.
		# In this particular instance, everything array-related breaks if the
		# array is stored in an array object. Hence, recover and then call current()
		$arr = $this->options['objects'];
		if ($this->st_is_service) {
			$this->st_source = current($arr);
			$srv = explode(';', $this->st_source);
			$this->host_name = $srv[0];
			$this->service_description = $srv[1];
		} else {
			$this->host_name = $this->st_source = current($arr);
		}
		$this->calculate_object_state();
		$this->prev_row['state'] = $this->st_obj_state;
	}

	/**
	 * Take a database row object, and parse it
	 * @param $row Database row
	 */
	public function add_event($row = false)
	{
		$obj_name = $obj_type = false;
		if (!empty($row['service_description'])) {
			$obj_name = $row['host_name'] . ";" . $row['service_description'];
			$obj_type = 'Service';
		}
		elseif (!empty($row['host_name'])) {
			$obj_name = $row['host_name'];
			$obj_type = 'Host';
		}

		if ($obj_name !== $this->st_source && ($obj_name !== $this->host_name || $row['event_type'] < Reports_Model::DOWNTIME_START) && $row['event_type'] > Reports_Model::PROCESS_SHUTDOWN)
			return;

		$this->st_update($row['the_time']);

		switch($row['event_type']) {
		 case Reports_Model::PROCESS_START:
		 case Reports_Model::PROCESS_SHUTDOWN:
			if ($row['event_type'] == Reports_Model::PROCESS_START) {
				$row['output'] = 'Monitor started';
			}
			else {
				$row['output'] = 'Monitor shut down';
			}
			$this->st_last_dt_init = false;
			if ($row['event_type'] == Reports_Model::PROCESS_START) {
				$row['state'] = $this->st_real_state;
				$this->st_running = 1;
			} else if ($this->options['assumestatesduringnotrunning']) {
				$row['state'] = $this->st_real_state;
			} else {
				$row['state'] = -1;
				$this->st_running = 0;
			}
			break;
		 case Reports_Model::DOWNTIME_START:
			if(!isset($row['output']) || !$row['output']) {
				$row['output'] = $obj_type . ' has entered a period of scheduled downtime';
			}
			$add = 0;
			# we are always spammed with downtime events after restart, so
			# don't increase the downtime depth if we're already in downtime
			if (!$this->st_last_dt_init || $this->st_last_dt_init === $row['the_time']) {
				$this->st_last_dt_init = $row['the_time'];
				if (!$this->st_dt_depth) {
					$add = 1;
				}
			}
			else {
				$add = 1;
			}
			if ($add) {
				if (isset($this->st_dt_objects[$obj_name]))
					break;
				$this->st_dt_objects[$obj_name] = True;
				$this->st_dt_depth+=1;
			}
			break;
		 case Reports_Model::DOWNTIME_STOP:
			if(!isset($row['output']) || !$row['output']) {
				$row['output'] = $obj_type . ' has exited a period of scheduled downtime';
			}
			# old merlin versions created more end events than start events, so
			# never decrement if we're already at 0.
			if ($this->st_dt_depth) {
				$this->st_dt_depth-=1;
			}
			unset($this->st_dt_objects[$obj_name]);
			break;
		 case Reports_Model::SERVICECHECK:
		 case Reports_Model::HOSTCHECK:
			$state = $row['state'];

			# update the real state of the object
			if ($this->st_source === $obj_name) {
				$this->st_real_state = $row['state'];
			}
			break;
		 default:
			//ERROR
		}

		$this->calculate_object_state();
		# TODO: Ahrm...
		$this->st_update_log($row);
	}

	/**
	 * Manually excluded states are excluded here.
	 *
	 * @param $state int
	 * @return int
	 */
	protected function filter_excluded_state($state) {
		if ($this->st_is_service && isset($this->options['service_filter_status'][$state]))
			return $this->options['service_filter_status'][$state];
		else if (!$this->st_is_service && isset($this->options['host_filter_status'][$state]))
			return $this->options['host_filter_status'][$state];
		return $state;
	}

	/**
	 * Calculate the object state, based on the chosen state calculator.
	 *
	 * If there is sub reports, the argument will be ignored. Otherwise, use
	 * either the argument or the object's real state, according to magical
	 * properties inherent in the numbers themselves.
	 */
	public function calculate_object_state()
	{
		$this->st_obj_state = $this->filter_excluded_state($this->st_real_state);
	}

	public function get_data()
	{
		$converted_state = $this->convert_state_table($this->st_raw, $this->st_text);

		# state template array depends on what we are checking
		$tpl = $this->state_tpl_host;
		if ($this->st_is_service)
			$tpl = $this->state_tpl_svc;
		foreach ($tpl as $t => $discard)
			if (!isset($converted_state[$t]))
				$converted_state[$t] = 0;

		$converted_state['HOST_NAME'] = $this->host_name;
		if ($this->st_is_service)
			$converted_state['SERVICE_DESCRIPTION'] = $this->service_description;

		# now add the time we didn't count due
		# to the selected timeperiod
		$converted_state['TIME_INACTIVE'] = $this->st_inactive;

		$total_time = $this->options['end_time'] - $this->options['start_time'];

		return array('source' => $this->st_source, 'log' => $this->st_log, 'states' => $converted_state, 'tot_time' => $total_time);
	}

	/**
	 * Deprecated method that keeps the log around for the benefit of the trend graph
	 */
	protected function st_update_log($row = false)
	{
		if($row) {
			$row['state'] = $this->st_obj_state;
		}
		if (!$this->options['include_trends']) {
			$this->prev_row = $row;
			return;
		}

		# called from finalize(), so bail out early
		if (!$row) {
			$this->prev_row['duration'] = $this->options['end_time'] - $this->prev_row['the_time'];
			$active = $this->timeperiod->active_time($this->prev_row['the_time'], $this->options['end_time']);
			if ($active > 0 || $active === $this->prev_row['duration'])
				$this->st_log[] = $this->prev_row;
			else
				$this->st_log[] = array(
					'output' => '(event outside of timeperiod)',
					'the_time' => $this->prev_row['the_time'],
					'duration' => $this->prev_row['duration'],
					'state' => $this->prev_row['state'],
					'hard' => 1
				);
			// This prevents the close event from being added multiple times
			$this->prev_row['the_time']= $this->options['end_time'];
			return;
		}

		# we mangle $row here, since $this->prev_row is always
		# derived from it, except when it's the initial
		# state which always has (faked) output
		if (empty($row['output']))
			$row['output'] = '(No output)';

		if ($this->options['scheduleddowntimeasuptime'] && $this->st_dt_depth)
			$row['state'] = Reports_Model::STATE_OK;

		# don't save states without duration for master objects
		$duration = $row['the_time'] - $this->prev_row['the_time'];
		if ($duration) {
			$this->prev_row['duration'] = $duration;
			$active = $this->timeperiod->active_time($this->prev_row['the_time'], $row['the_time']);
			if ($active > 0 || ($duration === $active))
				$this->st_log[] = $this->prev_row;
			else
				$this->st_log[] = array(
					'output' => '(event outside of timeperiod)',
					'the_time' => $this->prev_row['the_time'],
					'duration' => $this->prev_row['duration'],
					'state' => -2,
					'hard' => 1
				);
		}

		$this->prev_row = $row;
	}

	public function finalize()
	{
		parent::finalize();
		// TODO: Ahrm...
		$this->st_update_log();
	}
}
