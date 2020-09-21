<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Model for scheduled reports
 */
class Scheduled_reports_Model extends Model
{
	const USERFIELD = 'username'; /**< Name of username column in database */

	/**
	 * Given a scheduled report id, delet it from db
	 */
	static function delete_scheduled_report($id=false)
	{
		$id = (int)$id;
		if (empty($id)) return false;
		$sql = "DELETE FROM scheduled_reports WHERE id=".$id;
		$db = Database::instance();
		$db->query($sql);
		return true;
	}

	/**
	*	Delete ALL schedules for a certain report_id and type
	*/
	static function delete_all_scheduled_reports($type='avail',$id=false)
	{
		$type = strtolower($type);
		$db = Database::instance();

		# what report_type_id do we have?
		$sql = "SELECT id FROM scheduled_report_types WHERE identifier=".$db->escape($type);
		$res = $db->query($sql);
		if (!count($res))
			return false;
			# bail out if we can't find report_type

		$row = $res->current();
		$report_type_id = $row->id;
		$sql = "DELETE FROM scheduled_reports WHERE report_type_id=".$report_type_id." AND report_id=".$id;
		try {
			$db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * Fetches all scheduled reports of current report type (avail/sla)
	 *
	 * @param $type string: {avail, sla, summary}
	 * @return res
	 */
	public static function get_scheduled_reports($type)
	{
		$type = strtolower($type);

		$db = Database::instance();

		$sql_xtra = '';
		$auth = op5auth::instance();
		if (!$auth->authorized_for('host_view_all')) {
			$sql_xtra = ' AND sr.'.self::USERFIELD.'='.$db->escape(Auth::instance()->get_user()->get_username()).' ';
		}

		$sql = "SELECT
				sr.*,
				rp.periodname,
				r.report_name AS reportname
			FROM
				scheduled_reports sr,
				scheduled_report_types rt,
				scheduled_report_periods rp,
				saved_reports r
			WHERE
				rt.identifier='".$type."' AND
				sr.report_type_id=rt.id AND
				rp.id=sr.period_id AND
				sr.report_id=r.id".$sql_xtra."
			ORDER BY
				reportname";

		$res = $db->query($sql);
		return $res;
	}

	/**
	 * Checks if a report is scheduled in autoreports
	 *
	 * @param $type string: {avail, sla}
	 * @param $id int The report id
	 * @return Array on success. False on error.
	 */
	static function report_is_scheduled($type='avail', $id=false)
	{
		$type = strtolower($type);

		$id = (int)$id;
		if (!$id) return false;
		$res = self::get_scheduled_reports($type);
		if (!$res || count($res)==0) {
			return false;
		}
		$return = false;
		$res->result(false);
		foreach ($res as $row) {
			if ($row['report_id'] == $id) {
				$return[] = $row;
			}
		}
		return $return;
	}

	/**
	 * Get available report periods
	 * @return array [id] => string. False on errors.
	 */
	static function get_available_report_periods()
	{
		$sql = "SELECT * from scheduled_report_periods";
		$db = Database::instance();
		$res = $db->query($sql);
		if(!$res || count($res)==0) {
			return false;
		}

		$periods = array();
		foreach ($res as $period_row) {
			$periods[$period_row->id] = $period_row->periodname;
		}
		return $periods;
	}

	/**
	 * Retrieves the value of a db field for a report id
	 * @param $type the database column
	 * @param $id the id of the scheduled report
	 */
	static function fetch_scheduled_field_value($type=false, $id=false)
	{
		$id = (int)$id;
		$type = trim($type);
		if (empty($type) || empty($id)) return false;
		$sql = "SELECT $type FROM scheduled_reports WHERE id=".$id;
		$db = Database::instance();
		$res = $db->query($sql);
		if (!$res || count($res) == 0) {
			return false;
		}
		$row = $res->current();
		return $row->{$type};
	}

	/**
	 * @param $id = false
	 * @param $rep_type = false
	 * @param $saved_report_id = false
	 * @param $period = false
	 * @param $recipients = false comma separated
	 * @param $filename = ''
	 * @param $description = ''
	 * @param $local_persistent_filepath = ''
	 * @param $attach_description = ''
	 * @param $report_time = false
	 * @param $report_on = false
	 * @param $report_period = false
	 * @return string|int either error string or the report's id
	 */
	static public function edit_report($id=false, $rep_type=false, $saved_report_id=false, $period=false, $recipients=false, $filename='', $description='', $local_persistent_filepath = '', $attach_description = 0, $report_time=false, $report_on=false, $report_period=false)
	{

		$local_persistent_filepath = trim($local_persistent_filepath);
		if($local_persistent_filepath && !is_writable(rtrim($local_persistent_filepath, '/').'/')) {
			return _("File path '$local_persistent_filepath' is not writable");
		}
		$db = Database::instance();
		$id = (int)$id;
		$rep_type = (int)$rep_type;
		$saved_report_id = (int)$saved_report_id;
		$period	= (int)$period;
		$report_time = trim($report_time);
		$report_on = trim($report_on);
		$report_period = trim($report_period);
		$recipients = trim($recipients);
		$filename = trim($filename);
		$description = trim($description);
		$attach_description = (int) $attach_description;
		$user = Auth::instance()->get_user()->get_username();

		if (!$rep_type || !$saved_report_id || !$period || empty($recipients)) return _('Missing data');

		// some users might use ';' to separate email adresses
		// just replace it with ',' and continue
		$recipients = str_replace(';', ',', $recipients);
		$rec_arr = explode(',', $recipients);
		if (!empty($rec_arr)) {
			foreach ($rec_arr as $recipient) {
				if (trim($recipient)!='') {
					$checked_recipients[] = trim($recipient);
				}
			}
			$recipients = implode(', ', $checked_recipients);
		}

		if ($id) {
			// UPDATE
			$sql = "UPDATE scheduled_reports SET ".self::USERFIELD."=".$db->escape($user).", report_type_id=".$rep_type.", report_id=".$saved_report_id.", recipients=".$db->escape($recipients).", period_id=".$period.", filename=".$db->escape($filename).", description=".$db->escape($description).", local_persistent_filepath = ".$db->escape($local_persistent_filepath).", attach_description = ".$db->escape($attach_description)." WHERE id=".$id;
		} else {
			$sql = "INSERT INTO scheduled_reports (".self::USERFIELD.", report_type_id, report_id, recipients, period_id, filename, description, local_persistent_filepath, attach_description, report_time, report_on, report_period)VALUES(".$db->escape($user).", ".$rep_type.", ".$saved_report_id.", ".$db->escape($recipients).", ".$period.", ".$db->escape($filename).", ".$db->escape($description).", ".$db->escape($local_persistent_filepath).", ".$db->escape($attach_description).", '".$report_time."', '".$report_on."', '".$report_period."' )";

		}

		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return _('DATABASE ERROR').": {$e->getMessage()}; $sql";
		}

		if (!$id) {
			$id = $res->insert_id();
		}
		return $id;
	}

	/**
	 * Update specific field for certain scheduled report
	 * Called from reports_Controller::save_schedule_item() through ajax
	 *
	 * @param $id int: The id of the report.
	 * @param $field string: The report field to update.
	 * @param $value string: The new value.
	 * @return true on succes. false on errors.
	 */
	static function update_report_field($id=false, $field=false, $value=false)
	{
		$id = (int)$id;
		$field = trim($field);
		$value = trim($value);
		$db = Database::instance();
		$sql = "UPDATE scheduled_reports SET ".$field."= ".$db->escape($value)." WHERE id=".$id;
		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			print $e->getMessage();
			return false;
		}
		return true;
	}

	/**
	 * Get the type of a report.
	 *
	 * @param $id The id of the report.
	 * @return Report type on success. False on errors.
	 */
	static function get_typeof_report($id=false)
	{
		$sql = "SELECT t.identifier FROM scheduled_reports sr, scheduled_report_types t WHERE "."sr.id=".(int)$id." AND t.id=sr.report_type_id";
		$db = Database::instance();
		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return false;
		}

		return count($res)!=0 ? $res->current()->identifier : false;
	}

	/**
	 * Get the id of a named report
	 *
	 * @param $identifier string: The name of the report
	 * @return False on errors. Id of the report on success.
	 */
	static function get_report_type_id($identifier=false)
	{
		$db = Database::instance();
		$sql = "SELECT id FROM scheduled_report_types WHERE identifier=".$db->escape($identifier);
		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return false;
		}

		$id = false;
		if (count($res)!=0) {
			$res = $res->current();
			$id = $res->id;
		}
		return $id;
	}

	/**
	*	Fetch info on all defined report types, i.e all
	* 	types we can schedule
	*/
	static function get_all_report_types()
	{
		$db = Database::instance();
		$sql = "SELECT * FROM scheduled_report_types ORDER BY id";
		$res = $db->query($sql);
		return count($res) != 0 ? $res : false;
	}

	/**
	 * Fetch all info for a specific schedule.
	 * This includes all relevant data about both schedule
	 * and the report.
	 *
	 * @param $schedule_id The id of the schedule we're interested in.
	 * @return False on errors. Array with scheduling information on success.
	 */
	static function get_scheduled_data($schedule_id=false)
	{
		$schedule_id = (int)$schedule_id;
		if (!$schedule_id) {
			return false;
		}

		$sql = "SELECT sr.recipients, sr.filename, sr.local_persistent_filepath, sr.report_id FROM "."scheduled_reports sr "."WHERE sr.id=".$schedule_id;
		$db = Database::instance();
		$res = $db->query($sql)->result_array(false);
		if (!$res)
			return false;
		return $res[0];
	}

	/**
	 * Fetch info on reports to be sent for specific
	 * period (daily/weekly/monthly)
	 *
	 * @param $period_str string: { schedule }
	 * @return array
	 */
	static function get_period_schedules($period_str)
	{
		$schedules = array();
		$send_date = array();
		$default_timezone = date_default_timezone_get();

		$db = Database::instance();

		$sql = <<<'SQL'
		SELECT sr.*, rp.periodname, opt.value AS timezone
		FROM scheduled_reports sr
		INNER JOIN scheduled_report_periods rp ON rp.id = sr.period_id
		LEFT JOIN saved_reports_options opt ON opt.report_id = sr.report_id
			AND opt.name = 'report_timezone'
SQL;
		$res = $db->query($sql);

		foreach($res as $row){
			$report_period = json_decode($row->report_period);
			$report_time = $row->report_time;
			if($row->timezone){
				// All times for this schedule use the timezone of the associated report.
				date_default_timezone_set($row->timezone);
			}

			$repeat_no = $report_period->no;
			$last_sent = $row->last_sent;
			$now = new DateTime();

			/* Avoid sending reports before report_time each day, even if they
			 * are late for some reason. This means that a report that is due
			 * to be sent at tuesday 08:00 but for some reason couldn't be sent
			 * on tuesday, it will be sent the next day, but not before 08:00.
			 */
			$report_time_of_day = new DateTime($now->format('Y-m-d') . " $report_time");
			if ($now < $report_time_of_day) {
				continue;
			}

			if ($row->periodname == 'Daily') {
				$last_sent_date = $last_sent ? $last_sent : "today - $repeat_no days";
				$prev_period_start = new DateTime("$last_sent_date $report_time");

				// Period where report should be generated next time.
				$period_start = new DateTime(
					$prev_period_start->format('c') . " + $repeat_no days");

				if ($now >= $period_start) {
					// We're beyond period_start, create the report.
					$schedules[] = $row->id;
					$send_date[] = $now->format('Y-m-d');
				}
			}
			elseif ($row->periodname == 'Weekly') {
				$last_sent_date = $last_sent ? $last_sent : "today - $repeat_no weeks";
				$prev_period_start = new DateTime("$last_sent_date $report_time");

				// Period where report should be generated next time.
				$period_start = new DateTime(
					$prev_period_start->format('c') . " + $repeat_no weeks");

				$report_days = json_decode($row->report_on);

				if ($now >= $period_start) {
					// We're beyond period_start, in a new period, proceed with
					// creating the report if correct day of week.
					foreach ($report_days as $day) {
						if ($day->day == $now->format('w')) {
							$schedules[] = $row->id;
							$send_date[] = $now->format('Y-m-d');
						}
					}
				}
				elseif ($now > $prev_period_start) {
					// Skip if day of week is the same as that of last_sent.
					if ($now->format('w') == $prev_period_start->format('w')) {
						continue;
					}
					// Still in previous period, check if report should be
					// created this weekday also.
					foreach ($report_days as $day) {
						if ($day->day == $now->format('w')) {
							$schedules[] = $row->id;
							$send_date[] = $now->format('Y-m-d');
						}
					}
				}
			}
			elseif ($row->periodname == 'Monthly') {
				$last_sent_month = new DateTime(
					$last_sent ? $last_sent : "today - $repeat_no months");
				// Reset to first day of month, since day of month is not relevant.
				$last_sent_month->modify('first day of this month');

				// Month in which report should be generated next time.
				$next_send_month = new DateTime(
					$last_sent_month->format('Y-m-d') . "+ $repeat_no months");

				// Skip if we're not yet in next_send_month.
				if ($now < $next_send_month) {
					continue;
				}

				$report_on = json_decode($row->report_on);
				$day_of_week = $report_on->day;  # 1-7, last, first

				$is_report_day = false;
				if ($day_of_week == 'last') {
					// Send if this is the last day of the month.
					$tomorrow = strtotime('+ 1 day', $now->getTimestamp());
					if (date('n', $tomorrow) != $now->format('n')) {
						$is_report_day = true;
					}
				}
				elseif ($day_of_week == 'first') {
					// Send if this is the first day of the month.
					if ($now->format('j') == '1') {
						$is_report_day = true;
					}
				}
				elseif ($day_of_week == $now->format('w')) {
					$day_ordinal = $report_on->day_no;  # 1-4, last
					if ($day_ordinal == 'last') {
						// Check if this is the last $day_of_week of the month
						$next_week = strtotime('+ 1 week', $now->getTimestamp());
						if (date('n', $next_week) != $now->format('n')) {
							$is_report_day = true;
						}
					}
					else {
						// Check if today is $day_ordinal $day_of_week (e.g. 3rd Friday)
						$check_weeks_before = $day_ordinal - 1;
						$this_month = new DateTime("- $check_weeks_before week");
						$prev_month = new DateTime("- $day_ordinal week");
						if ($this_month->format('n') == $now->format('n')
								&& $prev_month->format('n') != $now->format('n')) {
							$is_report_day = true;
						}
					}
				}

				// Send report if today is the report day and if time of day
				// has passed the scheduled report time.
				if ($is_report_day) {
					$schedules[] = $row->id;
					$send_date[] = $now->format('Y-m-d');
				}
			}
			date_default_timezone_set($default_timezone);
		}

		foreach($schedules as $i => $schedule_id ){
			self::update_report_field($schedule_id, "last_sent", $send_date[$i]);
		}

		return $schedules;
	}
}
