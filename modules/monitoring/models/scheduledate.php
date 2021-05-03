<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Class ScheduleDate_Model
 */
class ScheduleDate_Model extends Model {
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
		'months',
		'start_date',
		'end_date',
		'recurrence',
		'recurrence_on',
		'recurrence_ends',
		'exclude_days'
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

	private $logger;

	/** Return instance of ninja op5 logger
	 *
	 * This function exist as a work-around for type hinting issues with
	 * the op5log dependency.
	 *
	 * @return object (generic)
	 */
	private function get_logger() {
		return op5log::instance('ninja');
	}

	function __construct() {
		parent::__construct();
		$this->logger = $this->get_logger();
	}


	/** Returns a list of objects eligible for downtime
	 *
	 * @param $sched object schedule-object
	 * @param $downtime_window array
	 * @return array list of downtime candidates
	 */
	protected function get_downtime_objects($sched, $downtime_window) {
		$objects = array();
		foreach($sched->get_objects() as $obj_name) {
			$is_scheduled = static::check_if_scheduled(
				$sched->get_downtime_type(),
				$obj_name,
				$downtime_window['start']->getTimeStamp(),
				$downtime_window['end']->getTimeStamp(),
				$sched->get_fixed()
			);

			array_push($objects, array(
				'name' => $obj_name,
				'eligible' => !$is_scheduled
			));
		}

		return $objects;
	}

	/** Returns a formatted downtime command
	 *
	 * @param $downtime Downtime
	 * @param $obj_name string
	 * @return string Command format
	 */
	public function get_downtime_command($downtime, $obj_name) {
		$model = $downtime->model;
		$downtime_type = $downtime->model->get_downtime_type();

		$command = array(
			'%type' => $downtime_type,
			'%cmd' => self::get_nagios_cmd($downtime_type),
			'%obj_name' => $obj_name,
			'%start' => $downtime->start->getTimestamp(),
			'%end' => $downtime->end->getTimestamp(),
			'%is_fixed' => $model->get_fixed(),
			'%duration' => $model->get_duration(),
			'%author' => $model->get_author(),
			'%comment' => 'AUTO: ' . $model->get_comment()
		);

		$cmd_fmt = '%cmd;%obj_name;%start;%end;%is_fixed;0;%duration;%author;%comment';
		return str_replace(array_keys($command), array_values($command), $cmd_fmt);
	}

	/**
	 * @param $downtime RecurringDowntime
	 * @param $target_date NinjaDateTime
	 * @return bool
	 * @throws Exception
	 */
	public function matches_recurrence($downtime, $target_date) {
		$unit = $downtime->recurrence->text;

		switch($unit) {
			case 'day':
				/**
				 * Repeats every Nth day
				 * Conditions:
				 * 1) Targetdate's /day/ matches the scheduled day-stepping
				 */
				return $downtime->match_day_interval($target_date);
			case 'week':
				/**
				 * Repeats every Nth week
				 * Conditions:
				 * 1) Targetdate's /week/ matches the scheduled week-stepping
				 * 2) Targetdate's weekday is one of the scheduled weekdays
				 */
				if($downtime->match_week_interval($target_date)) {
					$days = $downtime->pluck_recurrence('day');
					return in_array($target_date->get_day_of_week(), $days);
				}

				return false;
			case 'month':
				/**
				 * Repeats every Nth month
				 * Conditions:
				 * 1) Targetdate's /month/ matches the scheduled month-interval
				 * 2) Targetdate is the "<ordinal> <day> of <month>"
				 */
				return $downtime->match_month_interval($target_date) && $downtime->match_day_of_month($target_date);
			case 'year':
				/**
				 * Repeats every Nth year
				 * Conditions:
				 * 1) Targetdate's /year/ matches the scheduled year-interval
				 * 2) Targetdate's month matches recurring month
				 * 3) Targetdate is the "<ordinal> <day> of <month>"
				 */
				return $downtime->match_year_interval($target_date) && $downtime->match_day_of_month($target_date);
			default:
				$msg = "Invalid recurrence: $unit";
				$this->logger->log('error', $msg);
				throw new UnexpectedValueException($msg);
		}
	}

	/**
	 * Schedule downtime
	 *
	 * @param $timestamp false|string Timestamp instead of the default target date (tomorrow)
	 * @param $schedules false|array List of schedules instead of obtaining schedules from the database
	 * @return int 1 if one or more ext commands failed, otherwise 0
	 * @throws Exception
	 */
	public function schedule_downtime($timestamp = false, $schedules = false) {
		// Store results in an array for processing later
		$results = array();

		// Get schedules from DB
		if(!$schedules) {
			$schedules = RecurringDowntimePool_Model::all();
		}

		// Create datetime object from argument, if provided, otherwise set to tomorrow
		$target_date = new NinjaDateTime($timestamp ? '@' . $timestamp : 'tomorrow');

		foreach ($schedules as $sched) {
			$comment = $sched->get_comment();

			try {
				$downtime = new RecurringDowntime($sched);
				$downtime_window = $downtime->get_window($target_date);

				if($downtime->recurrence_ends <= $downtime_window['start']) {
					$this->logger->log('debug',
						"[$comment] Skipping expired recurrence: " . $downtime->recurrence_ends->get_date()
					);
					continue;
				} elseif($downtime->is_excluded($target_date)) {
					$date_str = $target_date->get_date();
					$this->logger->log('debug', "[$comment] Skipping: excluded target date ($date_str)");
					continue;
				}

				$set_downtime = $this->matches_recurrence($downtime, $target_date);
			} catch (NoRecurrenceException $e) {
				// While this shouldn't happen, let's be nice and check if the provided
				// non-recurring schedule matches target date.
				$downtime = new Downtime($sched);
				$downtime_window = $downtime->get_window($target_date);
				$set_downtime = $target_date->diff($downtime->start)->days === 0;
			}

			if(!$set_downtime) {
				$date_str = $target_date->get_date();
				$this->logger->log('debug', "[$comment] Skipping: No downtime scheduled for target ($date_str)");
				continue;
			}


			// ==========
			// Down-time!
			// ==========

			// Create DateTime strings used in logging
			$str_start = $downtime_window['start']->get_datetime();
			$str_end = $downtime_window['end']->get_datetime();

			// Iterate over objects with this schedule and set downtime, unless already scheduled.
			foreach($this->get_downtime_objects($sched, $downtime_window) as $object) {
				$name = $object['name'];
				if(!$object['eligible']) {
					$this->logger->log(
						"debug",
						"[$comment] Skipping: Downtime already set for $name ($str_start - $str_end)"
					);
					continue;
				}

				// Get downtime command string
				$command = $downtime->get_command($name, $downtime_window);

				// Submit the command.
				// Note: submit_to_nagios has its own internal /error/ handling.
				$result = nagioscmd::submit_to_nagios($command);

				if($result) { // success
					$this->logger->log(
						"debug",
						"[$comment] Downtime scheduled for object: $name ($str_start - $str_end)"
					);
				}

				array_push($results, $result);
			}
		}

		// Return false if one or more external commands failed, otherwise true.
		return !in_array(false, $results);
	}

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
				if (!strstr($name, ';')){
					return false;
				}
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
	 * Returns appropriate nagios command
	 *
	 * @param $type string
	 * @return string
	 */
	static protected function get_nagios_cmd($type)
	{
		if (empty($type)) {
			return false;
		}
		$downtime_commands = array(
			'hosts' => 'SCHEDULE_HOST_DOWNTIME',
			'services' => 'SCHEDULE_SVC_DOWNTIME',
			'hostgroups' => 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME',
			'servicegroups' => 'SCHEDULE_SERVICEGROUP_SVC_DOWNTIME'
		); # will schedule downtime for all services - not their hosts!
		return $downtime_commands[$type];
	}

	/**
	 * Schedule a downtime by submitting it to nagios
	 *
	 * @param $objects array
	 * @param $object_type string
	 * @param $start_time string
	 * @param $end_time string
	 * @param $start_date string
	 * @param $end_date string
	 * @param $fixed string
	 * @param $duration string
	 * @param $comment string
	 * @return boolean
	 **/
	public static function insert_downtimes($objects, $object_type, $start_time, $end_time, $start_date, $end_date, $fixed, $duration, $comment)
	{
		$result = array();
		$nagios_cmd = self::determine_downtimetype($object_type);
		$author = op5auth::instance()->get_user()->get_username();
		$month = date('n');
		$day = date('d');
		$year = date('Y');
		$strt_d = explode('-',$start_date);
		$sy = (int)$strt_d[0];
		$sm = (int)$strt_d[1];
		$sd = (int)$strt_d[2];
		$end_d = explode('-',$end_date);
		$ey = (int)$end_d[0];
		$em = (int)$end_d[1];
		$ed = (int)$end_d[2];
		$start_time = mktime(0, 0, self::time_to_seconds($start_time), $sm, $sd, $sy);
		$end_time = mktime(0, 0, self::time_to_seconds($end_time), $em, $ed, $ey);
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
		if (is_string($time)) {
			$parts = explode(':', $time);
			if (isset($parts[0]))
				$seconds += $parts[0] * 3600;
			if (isset($parts[1]))
				$seconds += $parts[1] * 60;
			if (isset($parts[2]))
				$seconds += $parts[2];
		}
		return $seconds;
	}

	/**
	 * Returns appropriate nagios command
	 *
	 * @param $report_type string
	 * @return string
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
	 * Save/update a recurring schedule
	 *
	 * @param $data array
	 * @param $id int
	 * @throws Exception
	 */
	public function edit_schedule($data, &$id = false) {
		if (!$data) {
			throw new Exception("Missing data for editing a scheduled downtime");
		}
		foreach (static::$valid_fields as $field) {
			if (!isset($data[$field])) {
				throw new Exception("Missing field $field");
			}
		}
		$db = Database::instance();
		$downtime_type = $data['downtime_type'];
		if (!in_array($downtime_type, static::$valid_types, true)) {
			throw new Exception("Downtime type $downtime_type is invalid");
		}
		$type = substr($data['downtime_type'], 0, -1);
		if (!op5auth::instance()->authorized_for($type.'_edit_contact') && !op5auth::instance()->authorized_for($type.'_edit_all')) {
			throw new Exception("Not authorized for editing $type objects");
		}

		$start_time = static::time_to_seconds($data['start_time']);
		$end_time = static::time_to_seconds($data['end_time']);
		$duration = static::time_to_seconds($data['duration']);

		if ((int)$id) {
			$set = RecurringDowntimePool_Model::get_by_query('[recurring_downtimes] id = '.(int)$id);
			if (!count($set)) {
				throw new Exception("Schedule was supposed to be for an existing recurring downtime, but none could be found for $id");
			}
			$db->query("DELETE FROM recurring_downtime_objects WHERE recurring_downtime_id = ".(int)$id);
			# update schedule
			$sql = "UPDATE recurring_downtime SET author = %s," .
				" downtime_type = %s, last_update = %s, comment = %s," .
				" start_time = %s, end_time = %s, duration = %s, fixed = %s," .
				" weekdays = %s, months = %s, start_date = %s, end_date = %s," .
				" recurrence = %s, recurrence_on = %s, recurrence_ends = %s, exclude_days = %s  WHERE id = ".(int)$id;
		} else {
			# new schedule
			$sql = "INSERT INTO recurring_downtime (author, downtime_type," .
				" last_update, comment, start_time, end_time, duration," .
				" fixed, weekdays, months, start_date, end_date, recurrence, recurrence_on, recurrence_ends, exclude_days) VALUES (%s, %s, %s, %s, %s, %s," .
				" %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)";
		}
		$res = $db->query(sprintf($sql, $db->escape($data['author']),
			$db->escape($data['downtime_type']), $db->escape(time()),
			$db->escape($data['comment']), $db->escape($start_time),
			$db->escape($end_time), $db->escape($duration),
			$db->escape($data['fixed']),
			$db->escape(serialize($data['weekdays'])),
			$db->escape(serialize($data['months'])),
			$db->escape($data['start_date']),
			$db->escape($data['end_date']),
			$db->escape($data['recurrence']),
			$db->escape($data['recurrence_on']),
			$db->escape($data['recurrence_ends']),
			$db->escape($data['exclude_days'])
		));
		if (!$id)
			$id = $res->insert_id();
		foreach ($data['objects'] as $object) {
			$db->query("INSERT INTO recurring_downtime_objects" .
				" (recurring_downtime_id, object_name) VALUES (" .
				(int)$id.", ".$db->escape($object).")");
		}
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
		if (!op5auth::instance()->authorized_for($type.'_edit_contact') && !op5auth::instance()->authorized_for($type.'_edit_all'))
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
