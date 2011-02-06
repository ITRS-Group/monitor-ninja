<?php defined('SYSPATH') OR die('No direct access allowed.');

class Scheduled_reports_Model extends Model
{
	public $db_name = 'merlin';
	const db_name = 'merlin';
	const USERFIELD = 'username';

	public function delete_scheduled_report($id=false)
	{
		$id = (int)$id;
		if (empty($id)) return false;
		$sql = "DELETE FROM scheduled_reports WHERE id=".$id;
		$db = new Database();
		$db->query($sql);
		return true;
	}

	/**
	*	Delete ALL schedules for a certain report_id and type
	*/
	public function delete_all_scheduled_reports($type='avail',$id=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla' && $type != 'summary'	)
			return false;
		$db = new Database();

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
	 * @param $type string: {avail, sla}
	 * @return res
	 */
	public function get_scheduled_reports($type='avail')
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla' && $type != 'summary')
			return false;

		$fieldname = false;
		switch ($type) {
			case 'avail':
			case 'summary':
				$fieldname = 'report_name';
				break;
			case 'sla':
				$fieldname = 'sla_name';
				break;
		}
		if (empty($fieldname)) {
			return false;
		}

		$db = new Database();

		$sql_xtra = '';
		$auth = new Nagios_auth_Model();
		if (!$auth->view_hosts_root) {
			$sql_xtra = ' AND sr.'.self::USERFIELD.'='.$db->escape(Auth::instance()->get_user()->username).' ';
		}

		$sql = "SELECT
				sr.*,
				rp.periodname,
				r.".$fieldname." AS reportname
			FROM
				scheduled_reports sr,
				scheduled_report_types rt,
				scheduled_report_periods rp,
				".$type."_config r
			WHERE
				rt.identifier='".$type."' AND
				sr.report_type_id=rt.id AND
				rp.id=sr.period_id AND
				sr.report_id=r.id".$sql_xtra."
			ORDER BY
				reportname";

		$res = $db->query($sql);
		return $res ? $res : false;
	}

	/**
	 * Checks if a report is scheduled in autoreports
	 *
	 * @param $id The report id
	 * @param $type string: {avail, sla}
	 * @return Array on success. False on error.
	 */
	public function report_is_scheduled($type='avail', $id=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla' && $type != 'summary')
			return false;

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
	 * @return Database result object on success. False on errors.
	 */
	public function get_available_report_periods()
	{
		$sql = "SELECT * from scheduled_report_periods";
		$db = new Database();
		$res = $db->query($sql);
		return (!$res || count($res)==0) ? false : $res;
	}

	public function fetch_scheduled_field_value($type=false, $id=false)
	{
		$id = (int)$id;
		$type = trim($type);
		if (empty($type) || empty($id)) return false;
		$sql = "SELECT * FROM scheduled_reports WHERE id=".$id;
		$db = new Database();
		$res = $db->query($sql);
		if (!$res || count($res) == 0) {
			return false;
		}
		$row = $res->current();
		return $row->{$type};
	}

	/**
	 * Delete a schedule from database
	 *
	 * @param $id int: The id of the report to delete.
	 * @param $context string: Enables us to take different actions
	 * 			depending on where it is called from
	 * @return ajax output
	 */
	public function delete_schedule_ajax($id=false, $context=false)
	{
		$id = (int)$id;
		$xajax = get_xajax::instance();
		$objResponse = new xajaxResponse();

		$translate = zend::instance('Registry')->get('Zend_Translate');
		$objResponse->call("show_progress", "progress", $translate->_('Please wait...'));
		if (!$id) {
			$objResponse->assign("err_msg","innerHTML", $translate->_("Missing ID so nothing to delete"));
			return $objResponse;
		}
		$sql = "DELETE FROM scheduled_reports WHERE id=".$id;
		$db = new Database();
		$res = $db->query($sql);
		$objResponse->call('hide_progress');
		switch ($context) {
			case 'setup':
				$objResponse->call('remove_deleted_rows', $id);
				break;
			case 'edit':
				$objResponse->call('remove_schedule', $id);
				break;
		}
		return $objResponse;
	}

	public function edit_report($id=false, $rep_type=false, $saved_report_id=false, $period=false, $recipients=false, $filename='', $description='')
	{
		$db 			= new Database();
		$id 			= (int)$id;
		$rep_type 		= (int)$rep_type;
		$saved_report_id = (int)$saved_report_id;
		$period			= (int)$period;
		$recipients 	= trim($recipients);
		$filename		= trim($filename);
		$description	= trim($description);
		$user 			= Auth::instance()->get_user()->username;

		if (!$rep_type || !$saved_report_id || !$period || empty($recipients)) return $this->translate->_('Missing data');

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
			$sql = "UPDATE scheduled_reports SET ".self::USERFIELD."=".$db->escape($user).", report_type_id=".$rep_type.", report_id=".$saved_report_id.",
				recipients=".$db->escape($recipients).", period_id=".$period.", filename=".$db->escape($filename).", description=".$db->escape($description)." WHERE id=".$id;
		} else {
			$sql = "INSERT INTO scheduled_reports (".self::USERFIELD.", report_type_id, report_id, recipients, period_id, filename, description)
				VALUES(".$db->escape($user).", ".$rep_type.", ".$saved_report_id.", ".$db->escape($recipients).", ".$period.", ".$db->escape($filename).", ".$db->escape($description).")";
		}

		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return $this->translate->_('DATABASE ERROR').": $sql";
		}

		if (!$id) {
			$id = (int)self::insert_id($rep_type, $saved_report_id, $recipients, $period, $filename, $description);
		}
		return $id;
	}

	/**
	*	Fetch the ID of a scheduled report
	* 	Since a user could have several schedules
	* 	for each report, we need to check all fields to be "sure"
	* 	that we get the correct ID. Not entirely sure though since
	* 	it is perfectly legal to create several identical schedules.
	* 	This should, however, be quite rare.
	*/
	public function insert_id($report_type_id=false, $report_id=false, $recipients='',
		$period_id=false, $filename='', $description='')
	{
		if (empty($report_type_id) || empty($report_id) ||empty($period_id)) {
			return false;
		}

		$id = false;
		$db = new Database();
		$sql = 'SELECT id FROM scheduled_reports WHERE '.self::USERFIELD.'='.
			$db->escape(Auth::instance()->get_user()->username).' AND '.
			'report_type_id='.(int)$report_type_id.' AND report_id='.(int)$report_id.
			' AND period_id='.(int)$period_id.' AND recipients='.$db->escape($recipients).
			' AND filename='.$db->escape($filename).' AND description='.$db->escape($description);
		$res = $db->query($sql);
		if (count($res)>0) {
			$cur = $res->current();
			$id = $cur->id;
		}
		unset($res);
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
	public function update_report_field($id=false, $field=false, $value=false)
	{
		$id = (int)$id;
		$field = trim($field);
		$value = trim($value);
		$db = new Database();
		$sql = "UPDATE scheduled_reports SET `".$field."`= ".$db->escape($value)." WHERE id=".$id;
		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
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
	public function get_typeof_report($id=false)
	{
		$sql = "SELECT t.identifier FROM scheduled_reports sr, scheduled_report_types t WHERE ".
			"sr.id=".(int)$id." AND t.id=sr.report_type_id";
		$db = new Database();
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
	public function get_report_type_id($identifier=false)
	{
		$db = new Database();
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
	public function get_all_report_types()
	{
		$db = new Database();
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
	 * @return False on errors. Array with schedule-info on succes.
	 */
	public function get_scheduled_data($schedule_id=false)
	{
		$schedule_id = (int)$schedule_id;
		if (!$schedule_id) {
			return false;
		}

		$type = self::get_typeof_report($schedule_id);

		switch ($type) {
			case 'avail':
				$sql = "SELECT sr.".self::USERFIELD.", sr.recipients, sr.filename, c.* FROM ".
					"scheduled_reports sr, avail_config c ".
					"WHERE sr.id=".$schedule_id." AND ".
					"c.id=sr.report_id";
				break;
			case 'sla':
				$sql = "SELECT sr.".self::USERFIELD.", sr.recipients, sr.filename, c.* FROM ".
					"scheduled_reports sr, sla_config c ".
					"WHERE sr.id=".$schedule_id." AND ".
					"c.id=sr.report_id";
				break;
			case 'summary':
				$sql = "SELECT sr.".self::USERFIELD.", sr.recipients, sr.filename, c.* FROM ".
					"scheduled_reports sr, summary_config c ".
					"WHERE sr.id=".$schedule_id." AND ".
					"c.id=sr.report_id";
				break;
			default: return false;
		}

		$db = new Database();
		$res = $db->query($sql);
		$return = false;
		if (count($res) != 0) {
			$return = $res->result(false)->current();
			$id = $return['id'];
			$object_info = Saved_reports_Model::get_config_objects($type, $id);
			$objects = false;
			if ($object_info !== false && count($object_info) != 0) {
				foreach ($object_info as $row) {
					$objects[] = $row->name;
				}
			}
			$return['objects'] = $objects;
			if ($type == 'sla') {
				$period_info = Saved_reports_Model::get_period_info($id);
				if ($period_info !== false) {
					foreach ($period_info as $row) {
						$month_key =  $row->name;
						if (isset($return['report_period']) && $return['report_period'] == 'lastmonth') {
							# special case lastmonth report period to work as expected,
							# i.e to use the entered SLA value for every month
							# no matter what month it was scheduled
							$month = date('n');
							$month = $month == 1 ? 12 : ($month-1);
							$month_key = 'month_'.$month;
						}
						$return['month'][$month_key] = $row->value;
					}
				}
			}

		} else {
			return false;
		}
		return $return;
	}

	/**
	 * Fetch info on reports to be sent for specific
	 * period (daily/weekly/monthly)
	 *
	 * @param $period_str string: { daily, weekly, monthly }
	 * @return Array of schedules for the specific period type
	 */
	public function get_period_schedules($period_str=false)
	{
		$period_str = trim(ucfirst($period_str));
		$db = new Database();

		$sql = "SELECT rt.identifier, r.id FROm scheduled_report_types rt, scheduled_reports r, scheduled_report_periods p ".
			"WHERE p.periodname=".$db->escape($period_str)." AND r.period_id=p.id AND rt.id=r.report_type_id";
		$res = $db->query($sql);
		return count($res) != 0 ? $res : false;
	}
}
