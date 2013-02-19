<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Schedule downtime
 */

class ScheduleDate_Model extends Model
{
	/**
	 *	Schedule a recurring downtime if tomorrow matches any saved schedules
	 *	@param $id int
	 *	@param $timestamp int
	 *	@return bool
	 */
	static public function schedule_downtime($id=false, $timestamp=false) {
		$res = self::get_schedule_data($id);
		if (!$res) {
			// no saved schedules
			return;
		}

		// Set timestamp to the following day.
		$timestamp = strtotime('+1 day', $timestamp);

		$tomorrow = array();
		// Gather everything we need to know about tomorrow
		$tomorrow['year'] = date('Y', $timestamp);
		$tomorrow['month'] = date('m', $timestamp);
		$tomorrow['day'] = date('d', $timestamp);
		$tomorrow['weekday'] = date('w', $timestamp);

		foreach ($res as $row) {
			// Unserialize string saved in database
			$data = i18n::unserialize($row->data);
			$data['author'] = $row->author;

			// Check if we should schedule downtime on tomorrows weekday
			if (in_array($tomorrow['weekday'], $data['recurring_day'])) {
				// Check if we should schedule downtime on tomorrows month
				if (in_array($tomorrow['month'], $data['recurring_month'])) {
					// Get object type for downtime (host, service, hostgroup, servicegroup)
					$nagios_cmd = self::determine_downtimetype(arr::search($data, 'report_type'));

					// Get the starttime for the scheduled downtime
					$time = explode(':', $data['time']);
					$starttime = mktime($time[0], $time[1], 0, $tomorrow['month'], $tomorrow['day'], $tomorrow['year']);
					// Send command to nagios
					self::add_downtime($data, $nagios_cmd, $starttime);
				}
			}
		}
		return;
	}

	/**
	*	Returns appropriate nagios command
	*	@param $report_type string
	*	@return string
	*/
	static public function determine_downtimetype($report_type=false)
	{
		if (empty($report_type)) {
			return false;
		}
		$downtime_commands = array(
			'hosts' => 'SCHEDULE_HOST_DOWNTIME',
			'services' => 'SCHEDULE_SVC_DOWNTIME',
			'hostgroups' => 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME',
			'servicegroups' => 'SCHEDULE_SERVICEGROUP_SVC_DOWNTIME'
		); # will schedule downtime for all services - not their hosts!
		return $downtime_commands[$report_type];
	}

	/**
	 *	Save/update a recurring schedule
	 * 	@param $data array
	 *	@param $id int
	 *	@return bool
	 */
	static public function edit_schedule($data = false, $id=false)
	{
		if (!is_array($data)) {
			return false;
		}

		$db = Database::instance();

		$downtime_type = $data['report_type'];
		$data = serialize($data);

		if ((int)$id) {
			# update schedule
			$sql = "UPDATE recurring_downtime SET author = ".$db->escape(Auth::instance()->get_user()->username).
				", data = ".$db->escape($data).", downtime_type=".$db->escape($downtime_type).", last_update=".time().
				" WHERE id = ".(int)$id;
		} else {
			# new schedule
			$sql = "INSERT INTO recurring_downtime (author, data, downtime_type, last_update) ".
				"VALUES(".$db->escape(Auth::instance()->get_user()->username).
				", ".$db->escape($data).", ".$db->escape($downtime_type).", ".time().")";
		}

		$db->query($sql);
		return true;
	}

	/**
	 * Fetch row(s) from db
	 *
	 * @param $id int = false
	 * @param $type string = false
	 * @return array
	 */
	static function get_schedule_data($id = false, $type=false)
	{
		$db = Database::instance();

		$sql = "SELECT * FROM recurring_downtime ";

		if (!empty($type)) {
			$sql .= " WHERE downtime_type=".$db->escape($type)." ORDER BY last_update";
		} else {
			if (!empty($id)) {
				$sql .= " WHERE id=".$id;
			} else {
				$sql .= " ORDER BY downtime_type, last_update";
			}
		}

		$res = $db->query($sql);
		return $res;
	}

	/**
	 *	Send downtime command to nagios
	 *	@param $data array
	 *	@param $nagioscmd string
	 *	@param $start_time int
	 *	@return void
	 */
	static public function add_downtime($data=false, $nagioscmd=false, $start_time=false)
	{
		if (empty($data) || empty($nagioscmd) || empty($start_time)) {
			return false;
		}

		$objfields = array(
				'hosts' => 'host_name',
				'hostgroups' => 'hostgroup',
				'servicegroups' => 'servicegroup',
				'services' => 'service_description'
				);

		# determine if we should loop over host_name, hostgroups etc
		$obj_arr = $data[$objfields[$data['report_type']]];
		$cmd = false;
		$duration = $data['duration'];
		$fixed = isset($data['fixed']) ? (int)$data['fixed'] : 1;
		$triggered_by = isset($data['triggered_by']) && !$fixed ? (int)$data['triggered_by'] : 0;

		if (strstr($duration, ':')) {
			# we have hh::mm
			$timeparts = explode(':', $duration);
			$duration_hours = $timeparts[0];
			$duration_minutes = $timeparts[1];

			#convert to seconds
			$duration = ($duration_hours * 3600);
			$duration += ($duration_minutes * 60);
		} else {
			$duration_hours = (int)$duration;
			$duration = ($duration_hours * 3600);
		}

		$end_time = $start_time + $duration;
		$author = $data['author'];
		$comment = $data['comment'];

		$pipe = System_Model::get_pipe();
		foreach ($obj_arr as $obj) {
			# check if object already scheduled for same start time and duration?
			if (Old_Downtime_Model::check_if_scheduled($data['report_type'], $obj, $start_time, $duration)) {
				fwrite(STDERR, "skipping $obj\n");
				continue;
			}
			$tmp_cmd = "$nagioscmd;$obj;$start_time;$end_time;$fixed;$triggered_by;$duration;$author;AUTO: $comment";
			$result = nagioscmd::submit_to_nagios($tmp_cmd, $pipe);
			$cmd[] = $tmp_cmd.' :'.(int)$result;
		}

		#echo Kohana::debug($cmd);
	}

	/**
	 * Delete a scheduled recurring downtime
	 *
	 * @param $id ID of the downtime to delete
	 * @returns true on success, false otherwise
	 */
	public function delete_schedule($id=false)
	{
		if (!Auth::instance()->authorized_for('system_commands')) {
			return false;
		}

		if (empty($id) || !(int)$id) {
			return false;
		}

		$db = Database::instance();

		$sql = "DELETE FROM recurring_downtime WHERE id=".(int)$id;
		if (!$db->query($sql)) {
			return false;
		}
		return true;
	}
}
