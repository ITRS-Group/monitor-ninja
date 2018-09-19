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
 *	Schedule a recurring downtime if tomorrow matches any saved schedules
 *	@param $timestamp int
 *	@return boolean
 */

static public function schedule_downtime($timestamp=false) {
	$schedules = RecurringDowntimePool_Model::all();
	$result = array();
	if ($timestamp === false){
		$timestamp = time();
	}
	// Set timestamp to the following day.
	$timestamp = strtotime('+1 day', $timestamp);
	$tomorrow = array();
	// Gather everything we need to know about tomorrow
	$tomorrow['year'] = date('Y', $timestamp);
	$tomorrow['month'] = date('n', $timestamp);
	$tomorrow['day'] = date('d', $timestamp);
	$tomorrow['weekday'] = date('w', $timestamp);
	$months=array('january','februry','march','april','may','june','july','august','september','october','november','december');

	foreach ($schedules->it(array('weekdays', 'author', 'months', 'downtime_type', 'start_time', 'end_time', 'duration', 'objects', 'fixed', 'comment', 'start_date', 'end_date', 'recurrence', 'recurrence_on', 'recurrence_ends', 'exclude_days')) as $data) {
		$weekday = $data->get_weekdays();
		if($weekday[0] != ''){
			if (!in_array($tomorrow['weekday'], $data->get_weekdays()) || !in_array($tomorrow['month'], $data->get_months())){
				continue;
			}
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
		} else {
			$ends_on = $data->get_recurrence_ends();
			$start_date = $data->get_start_date();
			$end_date = $data->get_end_date();
			$recurrence = json_decode($data->get_recurrence());
			$recurrence_on = json_decode($data->get_recurrence_on()); 
			$strt_d = explode('-',$start_date);
			$sy = (int)$strt_d[0];
			$sm = (int)$strt_d[1];
			$sd = (int)$strt_d[2];
			$end_d = explode('-',$end_date);
			$ey = (int)$end_d[0];
			$em = (int)$end_d[1];
			$ed = (int)$end_d[2];
			$start_time_init = mktime(0, 0, $data->get_start_time(), $sm, $sd, $sy);
			$end_time_init = mktime(0, 0, $data->get_end_time(), $em, $ed, $ey);
			$start_init = new DateTime("@$start_time_init");
			$end_init = new DateTime("@$end_time_init");
			$diff_init = $start_init->diff($end_init);
			$exclude_days = $data->get_exclude_days();
			if($exclude_days && $exclude_days != '' && $exclude_days != 0){
	            $exclude_days = explode(',',$exclude_days);
		        foreach($exclude_days as $day_range){
		            $day_range = explode('to',$day_range);
		            if($day_range[0] != ''){
		                $exc_day_s = $day_range[0];
		                if(array_key_exists(1,$day_range)){
		                    $exc_day_e = $day_range[1];
		                }else{
		                    $exc_day_e = $day_range[0];
		                }
		            }
	                $strt_d = explode('-',$exc_day_s);
	                $sy = (int)$strt_d[0];
	                $sm = (int)$strt_d[1];
	                $smn = $months[$sm-1];
	                $sd = (int)$strt_d[2];
	                $exc_start_day = strtotime("$sd $smn $sy");
	                $end_d = explode('-',$exc_day_e);
	                $ey = (int)$end_d[0];
	                $em = (int)$end_d[1];
	                $emn = $months[$em-1];
	                $ed = (int)$end_d[2];
	                $exc_end_day = strtotime("$ed $emn $ey");
	                if($timestamp >= $exc_start_day && $timestamp <= $exc_end_day){
	                    continue;
	                }
		        }
		    }
			if($ends_on && $ends_on != ''){
				$ends_on = explode('-',$ends_on);
				$ey = (int)$ends_on[0];
				$em = (int)$ends_on[1];
				$emn = $months[$em-1];
				$ed = (int)$ends_on[2];
				$end_on_timestamp = strtotime("$ed $emn $ey");
				if($timestamp > $end_on_timestamp){
					continue;
				};
			}
			if($start_date && $start_date != ''){
				$strt_d = explode('-',$start_date);
				$sy = (int)$strt_d[0];
				$sm = (int)$strt_d[1];
				$smn = $months[$sm-1];
				$sd = (int)$strt_d[2];
				$start_timestamp = strtotime("$sd $smn $sy");
				if($timestamp < $start_timestamp){
					continue;
				}
			}

			if($recurrence != ''){
				$rec_num = $recurrence->no;
				$rec_text = $recurrence->text;
				$diff_time = $timestamp-$start_timestamp;
				if($rec_text == "year"){
					$rec_day = $recurrence_on->day;
					$rec_day_no = $recurrence_on->day_no;
					$rec_month = $recurrence_on->month+1;
					$start = new DateTime("@$start_timestamp");
					$end   = new DateTime("@$timestamp");
					$diff  = $start->diff($end);
					$year = $diff->y;
					$check_year = $year % $rec_num;
					if($check_year != 0){
						continue;
					} else {
						if($rec_month != $tomorrow['month']){
							continue;
						} else {
							if($tomorrow['weekday'] != $rec_day){
								continue;
							} else {
								if($rec_day_no == "last"){
									$check_last_weekday = strtotime("+7 day", $timestamp);
									if(date('n', $check_last_weekday) == ($tomorrow['month'])){
										continue;
									}
								} else {
									$day_no_this = 7 * ($rec_day_no-1);
									$day_no_pre = 7 * ($rec_day_no);
									$check_day_no_this = strtotime("-$day_no_this day", $timestamp);
									$check_day_no_pre = strtotime("-$day_no_pre day", $timestamp);
									if(date('n', $check_day_no_this) == ($tomorrow['month']-1) || date('n', $check_day_no_pre) == $tomorrow['month']){
										continue;
									}
								}
							}
						}
					}
				} elseif($rec_text == "month"){
					$rec_day = $recurrence_on->day;
					$rec_day_no = $recurrence_on->day_no;
					$start = new DateTime("@$start_timestamp");
					$end   = new DateTime("@$timestamp");
					$diff  = $start->diff($end);
					$month = $diff->m;
					$check_month = $month % $rec_num;
					if($check_month != 0){
						continue;
					} else {
						if($rec_day_no != "last" ){
							if($tomorrow['weekday'] != $rec_day){
								continue;
							} else {
								$day_no_this = 7 * ($rec_day_no-1);
								$day_no_pre = 7 * ($rec_day_no);
								$check_day_no_this = strtotime("-$day_no_this day", $timestamp);
								$check_day_no_pre = strtotime("-$day_no_pre day", $timestamp);
								if(date('n', $check_day_no_this) == ($tomorrow['month']-1) || date('n', $check_day_no_pre) == $tomorrow['month']){
									continue;
								}
							}
						} else {
							if($tomorrow['weekday'] == $rec_day){
								$check_last_weekday = strtotime("+7 day", $timestamp);
								if(date('n', $check_last_weekday) == ($tomorrow['month'])){
									continue;
								}
							} elseif($rec_day == "last"){
								$check_last_weekday = strtotime("+1 day", $timestamp);
								if(date('n', $check_last_weekday) == ($tomorrow['month']+1)){
									continue;
								}                                  
							}
						}
					}
				} elseif($rec_text == "week"){
					$rec_day = $recurrence_on;
					$week = floor($diff_time/(60*60*24*7));
					$check_week = $week % $rec_num;
					foreach($rec_day as $week_day){
						$week_day = $week_day->day;
						if($check_week != 0 || $tomorrow['weekday'] != $rec_day){
							continue;
						}
					}
				} elseif($rec_text == "day"){
					$day = floor($diff_time/(60*60*24));
					$check_day = $day % $rec_num;
					if($check_day != 0){
						continue;
					}
				}
			}
			$nagios_cmd = self::determine_downtimetype($data->get_downtime_type());
			$start_time = mktime(0, 0, $data->get_start_time(), $tomorrow['month'], $tomorrow['day'], $tomorrow['year']);
			$end_time = mktime($diff_init->h, $diff_init->i, $data->get_start_time() + ($diff_init->s), $tomorrow['month'] + ($diff_init->m), $tomorrow['day'] + ($diff_init->d), $tomorrow['year'] + ($diff_init->y));
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
		}
	}
	return !in_array(false, $result);
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
