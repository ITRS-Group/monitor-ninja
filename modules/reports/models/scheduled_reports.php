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
			$sql_xtra = ' AND sr.'.self::USERFIELD.'='.$db->escape(Auth::instance()->get_user()->username).' ';
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
	 * @return string|int either error string or the report's id
	 */
	static public function edit_report($id=false, $rep_type=false, $saved_report_id=false, $period=false, $recipients=false, $filename='', $description='', $local_persistent_filepath = '', $attach_description = 0)
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
		$recipients = trim($recipients);
		$filename = trim($filename);
		$description = trim($description);
		$attach_description = (int) $attach_description;
		$user = Auth::instance()->get_user()->username;

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
			$sql = "INSERT INTO scheduled_reports (".self::USERFIELD.", report_type_id, report_id, recipients, period_id, filename, description, local_persistent_filepath, attach_description)
				VALUES(".$db->escape($user).", ".$rep_type.", ".$saved_report_id.", ".$db->escape($recipients).", ".$period.", ".$db->escape($filename).", ".$db->escape($description).", ".$db->escape($local_persistent_filepath).", ".$db->escape($attach_description).")";
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
		$sql = "SELECT t.identifier FROM scheduled_reports sr, scheduled_report_types t WHERE ".
			"sr.id=".(int)$id." AND t.id=sr.report_type_id";
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

		$sql = "SELECT sr.recipients, sr.filename, sr.local_persistent_filepath, sr.report_id FROM ".
			"scheduled_reports sr ".
			"WHERE sr.id=".$schedule_id;
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
	 * @param $period_str string: { daily, weekly, monthly }
	 * @return array
	 */
	static function get_period_schedules($period_str)
	{
		$period_str = trim(ucfirst($period_str));
		$db = Database::instance();

		$sql = "SELECT r.id FROM scheduled_report_types rt
			INNER JOIN scheduled_reports r ON rt.id=r.report_type_id
			INNER JOIN scheduled_report_periods p ON r.period_id=p.id
			WHERE p.periodname=".$db->escape($period_str);
		$res = $db->query($sql);
		return $res;
	}
}
