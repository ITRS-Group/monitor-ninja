<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Schedule downtime
 */

class ScheduleDate_Model extends Model
{
	/**
	 * Fields that a schedule include. These are all valid, and all required.
	 * Mostly public for test reasons.
	 */
	static public $valid_fields = array(
		'author',
		'downtime_type',
		'objects',
		'comment',
		'start_time',
		'end_time',
		'duration',
		'fixed',
		'weekdays',
		'months'
	);

	/**
	 * A list of valid schedule types - same format (no underscore, trailing s)
	 * as in report options.
	 * Mostly public for test reasons.
	 */
	static public $valid_types = array(
		'hosts',
		'services',
		'hostgroups',
		'servicegroups'
	);

	/**
	 * Use a reasonable amount of indicators to determine whether there's
	 * already a matching downtime. This prevents downtimes from being
	 * scheduled more than once.
	 */
	static protected function check_if_scheduled($type, $name, $start_time, $end_time, $is_fixed)
	{
		$ls = Livestatus::instance();
		switch ($type) {
			case 'hosts':
				$res = $ls->getDowntimes(array('filter' => array('is_service' => 0, 'host_name' => $name, 'start_time' => $start_time, 'fixed' => $is_fixed, 'end_time' => $end_time)));
				break;
			case 'services':
				if (!strstr($name, ';'))
					return false;

				$parts = explode(';', $name);
				$host = $parts[0];
				$service = $parts[1];
				$res = $ls->getDowntimes(array('filter' => array('is_service' => 1, 'host_name' => $host, 'service_description' => $service, 'start_time' => $start_time, 'fixed' => $is_fixed, 'end_time' => $end_time)));
				break;
			case 'hostgroups':
				$hosts = $ls->getHosts(array('filter' => array('groups' => array('>=' => $name))));
				$in_dtime = $ls->getDowntimes(array('filter' => array('is_service' => 0, 'host_groups' => array('>=' => $name), 'start_time' => $start_time, 'fixed' => $is_fixed, 'end_time' => $end_time)));
				return (count($hosts) <= count($in_dtime));
				break;

			case 'servicegroups':
				$services = $ls->getServices(array('filter' => array('groups' => array('>=' => $name))));
				$in_dtime = $ls->getDowntimes(array('filter' => array('is_service' => 1, 'service_groups' => array('>=' => $name), 'start_time' => $start_time, 'fixed' => $is_fixed, 'end_time' => $end_time)));
				return (count($services) <= count($in_dtime));
				break;
		}

		return (!empty($res));
	}

	/**
	 *	Schedule a recurring downtime if tomorrow matches any saved schedules
	 *	@param $timestamp int
	 *	@return boolean
	 */
	static public function schedule_downtime($timestamp=false) {
		$schedules = RecurringDowntimePool_Model::all();
		$result = array();

		if ($timestamp === false)
			$timestamp = time();

		// Set timestamp to the following day.
		$timestamp = strtotime('+1 day', $timestamp);

		$tomorrow = array();
		// Gather everything we need to know about tomorrow
		$tomorrow['year'] = date('Y', $timestamp);
		$tomorrow['month'] = date('n', $timestamp);
		$tomorrow['day'] = date('d', $timestamp);
		$tomorrow['weekday'] = date('w', $timestamp);

		foreach ($schedules->it(array('weekdays', 'author', 'months', 'downtime_type', 'start_time', 'end_time', 'duration', 'objects', 'fixed', 'comment')) as $data) {
			if (!in_array($tomorrow['weekday'], $data->get_weekdays()) || !in_array($tomorrow['month'], $data->get_months()))
				continue;

			$nagios_cmd = self::determine_downtimetype($data->get_downtime_type());

			$start_time = mktime(0, 0, $data->get_start_time(), $tomorrow['month'], $tomorrow['day'], $tomorrow['year']);
			$end_time = mktime(0, 0, $data->get_end_time(), $tomorrow['month'], $tomorrow['day'], $tomorrow['year']);
			if ($end_time < $start_time)
				$end_time = mktime(0, 0, $data->get_end_time(), $tomorrow['month'], $tomorrow['day'] + 1, $tomorrow['year']);
			$duration = $data->get_duration();
			foreach ($data->get_objects() as $obj) {
				# check if object already scheduled for same start time and duration?
				if (static::check_if_scheduled($data->get_downtime_type(), $obj, $start_time, $end_time, $data->get_fixed())) {
					fwrite(STDERR, "skipping $obj\n");
					continue;
				}
				$tmp_cmd = "$nagios_cmd;$obj;$start_time;$end_time;{$data->get_fixed()};0;$duration;{$data->get_author()};AUTO: {$data->get_comment()}";
				$result[] = nagioscmd::submit_to_nagios($tmp_cmd);
			}
			return !in_array(false, $result);
		}
	}

	/**
	 * Schedule a downtime by submitting it to nagios
	 *
	 * @param $objects array
	 * @param $object_type string
	 * @param $start_time string
	 * @param $end_time string
	 * @param $fixed string
	 * @param $duration string
	 * @param $comment string
	 * @return boolean
	 **/
	public static function insert_downtimes($objects, $object_type, $start_time, $end_time, $fixed, $duration, $comment)
	{
		$result = array();
		$nagios_cmd = self::determine_downtimetype($object_type);
		$author = Auth::instance()->get_user()->username;
		$month = date('n');
		$day = date('d');
		$year = date('Y');
		$start_time = mktime(0, 0, self::time_to_seconds($start_time), $month, $day, $year);
		$end_time = mktime(0, 0, self::time_to_seconds($end_time), $month, $day, $year);
		foreach ($objects as $object) {
			if (static::check_if_scheduled($object_type, $object, $start_time, $end_time, $fixed)) {
				// Skip object if it is already scheduled for downtime
				continue;
			}
			$tmp_cmd = "$nagios_cmd;$object;$start_time;$end_time;$fixed;0;$duration;$author;AUTO: $comment";
			$result[] = nagioscmd::submit_to_nagios($tmp_cmd);
		}
		return !in_array(false, $result);
	}

	/**
	 * Given a time-like string (hh[:mm[:ss]]),
	 * return the number of seconds involved.
	 */
	static public function time_to_seconds($time)
	{
		$seconds = 0;
		$parts = explode(':', $time);
		if (isset($parts[0]))
			$seconds += $parts[0] * 3600;
		if (isset($parts[1]))
			$seconds += $parts[1] * 60;
		if (isset($parts[2]))
			$seconds += $parts[2];
		return $seconds;
	}

	/**
	*	Returns appropriate nagios command
	*	@param $report_type string
	*	@return string
	*/
	static protected function determine_downtimetype($report_type=false)
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
	public function edit_schedule($data, &$id=false)
	{
		if (!is_array($data)) {
			return false;
		}

		foreach (static::$valid_fields as $field) {
			if (!isset($data[$field]))
				return false;
		}

		$db = Database::instance();

		$downtime_type = $data['downtime_type'];
		if (!in_array($downtime_type, static::$valid_types)) {
			return false;
		}
		$type = substr($data['downtime_type'], 0, -1);
		if (!Auth::instance()->authorized_for($type.'_edit_contact') && !Auth::instance()->authorized_for($type.'_edit_all'))
			return false;

		$start_time = static::time_to_seconds($data['start_time']);
		$end_time = static::time_to_seconds($data['end_time']);
		$duration = static::time_to_seconds($data['duration']);

		if ((int)$id) {
			$set = RecurringDowntimePool_Model::get_by_query('[recurring_downtimes] id = '.(int)$id);
			if (!count($set))
				return false;
			$db->query("DELETE FROM recurring_downtime_objects WHERE recurring_downtime_id = ".(int)$id);
			# update schedule
			$sql = "UPDATE recurring_downtime SET author = %s," .
				" downtime_type = %s, last_update = %s, comment = %s," .
				" start_time = %s, end_time = %s, duration = %s, fixed = %s," .
				" weekdays = %s, months = %s WHERE id = ".(int)$id;
		} else {
			# new schedule
			$sql = "INSERT INTO recurring_downtime (author, downtime_type," .
				" last_update, comment, start_time, end_time, duration," .
				" fixed, weekdays, months) VALUES (%s, %s, %s, %s, %s, %s," .
				" %s, %s, %s, %s)";
		}

		$res = $db->query(sprintf($sql, $db->escape($data['author']),
			$db->escape($data['downtime_type']), $db->escape(time()),
			$db->escape($data['comment']), $db->escape($start_time),
			$db->escape($end_time), $db->escape($duration),
			$db->escape($data['fixed']),
			$db->escape(serialize($data['weekdays'])),
			$db->escape(serialize($data['months']))));
		if (!$id)
			$id = $res->insert_id();
		foreach ($data['objects'] as $object) {
			$db->query("INSERT INTO recurring_downtime_objects" .
				" (recurring_downtime_id, object_name) VALUES (" .
				(int)$id.", ".$db->escape($object).")");
		}
		return true;
	}

	/**
	 * Delete a scheduled recurring downtime
	 *
	 * @param $id ID of the downtime to delete
	 * @returns true on success, false otherwise
	 */
	public function delete_schedule($id)
	{
		$set = RecurringDowntimePool_Model::get_by_query('[recurring_downtimes] id = '.(int)$id);
		if (!count($set))
			return false;

		$obj = $set->it(array('downtime_type'))->current();
		$type = substr($obj->get_downtime_type(), 0, -1);
		// *_add_delete is for the objects, and because this manipulates the
		// state of an existing object, *_add_delete is not required. OK?
		if (!Auth::instance()->authorized_for($type.'_edit_contact') && !Auth::instance()->authorized_for($type.'_edit_all'))
			return false;

		$db = Database::instance();

		$sql = "DELETE FROM recurring_downtime WHERE id=".(int)$id;
		if (!$db->query($sql)) {
			return false;
		}
		$sql = "DELETE FROM recurring_downtime_objects WHERE recurring_downtime_id=".(int)$id;
		if (!$db->query($sql)) {
			return false;
		}
		return true;
	}
}
