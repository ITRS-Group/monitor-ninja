<?php

class WorstStateCalculator extends StateCalculator
{
	protected $sub_reports = array(); /**< An array of sub-reports for this report */
	protected $st_sub = array(); /**< Map of sub report [state => [downtime_status => [indexes]]] */

	public function set_sub_reports($subs)
	{
		$this->sub_reports = $subs;
	}

	public function initialize($initial_state, $initial_depth, $is_running)
	{
		parent::initialize($initial_state, $initial_depth, $is_running);

		$this->st_source = $this->options[$this->options->get_value('report_type')];

		foreach ($this->st_text as $st => $discard)
			$this->st_sub[$st] = array();

		foreach ($this->sub_reports as $idx => $rpt) {
			$this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx] = $idx;
		}
		$this->calculate_object_state();
	}

	public function add_event($row = false)
	{
		$this->st_update($row['the_time']);

		foreach ($this->sub_reports as $idx => $rpt) {
			unset($this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx]);
			$rpt->add_event($row);
			$this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx] = $idx;
		}
		switch($row['event_type']) {
		 case Reports_Model::PROCESS_START:
			$this->st_running = 1;
			break;
		 case Reports_Model::PROCESS_SHUTDOWN:
			$this->st_running = 0;
			break;
		 default:
			break;
		}

		$this->calculate_object_state();
		$this->prev_row = $row;
	}

	public function calculate_object_state()
	{
		/*
		 * Welcome to todays installment of "The world sucks and I'm tired of
		 * trying to fix it"!
		 *
		 * So, states. States have codes. If you've written plugins
		 * you'll think the "badness" increases with the numeric code. This is
		 * incorrect, of course, because then state comparison would be simple.
		 */
		if ($this->st_is_service)
			$states = array(Reports_Model::SERVICE_CRITICAL, Reports_Model::SERVICE_WARNING, Reports_Model::SERVICE_UNKNOWN, Reports_Model::SERVICE_OK, Reports_Model::SERVICE_PENDING);
		else
			$states = array(Reports_Model::HOST_DOWN, Reports_Model::HOST_UNREACHABLE, Reports_Model::HOST_UP, Reports_Model::HOST_PENDING);

		$final_state = Reports_Model::SERVICE_OK;

		// Loop through states in order of badness.
		foreach ($states as $state) {
			$keys = array_keys($this->st_sub[$state]);
			// Sort downtime states outside downtime first
			sort($keys);
			foreach ($keys as $in_dt) {
				if (empty($this->st_sub[$state][$in_dt]))
					continue;
				// This would look OK but isn't, go look for non-OK
				// (remember, we sorted, so $in_dt is only true after passing false)
				if ($this->options['scheduleddowntimeasuptime'] && $in_dt)
					break 1;
				// Else, we're done, this is the worst.
				$this->st_dt_depth = $in_dt;
				$final_state = $state;
				break 2;
			}
		}

		// So, scheduleddowntimeasuptime and worst not in sched_down is OK?
		// Maybe there's a non-OK in sched_down...
		if ($this->options['scheduleddowntimeasuptime'] && $final_state === 0) {
			foreach ($states as $state) {
				foreach ($this->st_sub[$state] as $dt_depth => $ary) {
					if (!empty($ary)) {
						$this->st_dt_depth = $dt_depth;
						$final_state = $state;
						break;
					}
				}
			}
		}

		$this->st_obj_state = $this->filter_excluded_state($final_state);
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

		if ($this->st_is_service) {
			unset($converted_state['HOST_NAME']);
			$converted_state['SERVICE_DESCRIPTION'] = $this->st_source;
		}
		else {
			$converted_state['HOST_NAME'] = $this->st_source;
		}

		# now add the time we didn't count due
		# to the selected timeperiod
		$converted_state['TIME_INACTIVE'] = $this->st_inactive;

		$total_time = $this->options['end_time'] - $this->options['start_time'];
		$res =  array('log' => array());
		foreach ($this->sub_reports as $sr) {
			$res['log'][$sr->st_source] = $sr->st_log;
			$res[] = $sr->get_data();
		}
		$res['source'] = $this->st_source;
		$res['states'] = $converted_state;
		$res['tot_time'] = $total_time;

		$groupname = $this->options['hostgroup'] ? $this->options['hostgroup'] : $this->options['servicegroup'];
		# HOWTO: induce bugs and make enemies:
		if (count($groupname) === 1)
			$res['groupname'] = $groupname[0];
		else
			$res['groupname'] = $groupname;

		return $res;
	}

	public function finalize()
	{
		foreach ($this->sub_reports as $report) {
			$report->finalize();
		}
		parent::finalize();
	}
}
