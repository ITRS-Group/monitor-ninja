<?php defined('SYSPATH') OR die('No direct access allowed.');

class Scheduled_reports_Model extends Model
{
	public $db_name = 'monitor_reports';
	const db_name = 'monitor_reports';

	public function delete_scheduled_report($id=false)
	{
		$id = (int)$id;
		if (empty($id)) return false;
		$sql = "DELETE FROM scheduled_reports WHERE id=".$id;
		$db = new Database(self::db_name);
		$db->query($sql);
		return true;
	}

	/**
	*	Delete ALL schedules for a certain report_id and type
	*/
	public function delete_all_scheduled_reports($type='avail',$id=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla')
			return false;
		$db = new Database(self::db_name);

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
	 * @param str $type {avail, sla}
	 * @return res
	 */
	public function get_scheduled_reports($type='avail')
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla')
			return false;
		$fieldname = $type == 'avail' ? 'report_name' : 'sla_name';
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
				sr.report_id=r.id
			ORDER BY
				reportname";
		$db = new Database(self::db_name);
		$res = $db->query($sql);
		return $res ? $res : false;
	}

	/**
	 * Checks if a report is scheduled
	 * in autoreports
	 *
	 * @param int $id
	 * @param str $type {avail, sla}
	 * @return mixed array or false on error
	 */
	public function report_is_scheduled($type='avail', $id=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla')
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

	public function get_available_report_periods()
	{
		$sql = "SELECT * from scheduled_report_periods";
		$db = new Database(self::db_name);
		$res = $db->query($sql);
		return (!$res || count($res)==0) ? false : $res;
	}

	public function fetch_scheduled_field_value($type=false, $id=false, $elem_id=false)
	{
		$id = (int)$id;
		$type = trim($type);
		$elem_id = trim($elem_id);
		if (empty($type) || empty($id) || empty($elem_id)) return false;
		$xajax = get_xajax::instance();
		$objResponse = new xajaxResponse();
		$sql = "SELECT * FROM scheduled_reports WHERE id=".$id;
		$db = new Database(self::db_name);
		$res = $db->query($sql);
		$translate = zend::instance('Registry')->get('Zend_Translate');
		$objResponse->call("show_progress", "progress", $translate->_('Please wait...'));
		$row = $res->current();
		$objResponse->assign($elem_id,"innerHTML", $row->{$type});
		$objResponse->call('setup_hide_content', 'progress');
		return $objResponse;
	}

	/**
	 * Delete the schedule from database
	 *
	 * @param 	int $id
	 * @param 	str $context enables us to take different actions
	 * 			depending on where it is called from
	 * @return 	ajax output
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
		$db = new Database;(self::db_name);
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
		$db 			= new Database(self::db_name);
		$id 			= (int)$id;
		$rep_type 		= (int)$rep_type;
		$saved_report_id = (int)$saved_report_id;
		$period			= (int)$period;
		$recipients 	= trim($recipients);
		$filename		= trim($filename);
		$description	= trim($description);
		$user 			= Auth::instance()->get_user()->username;

		if (!$rep_type || !$saved_report_id || !$period || empty($recipients)) return false;

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
			$sql = "UPDATE scheduled_reports SET user=".$db->escape($user).", report_type_id=".$rep_type.", report_id=".$saved_report_id.",
				recipients=".$db->escape($recipients).", period_id=".$period.", filename=".$db->escape($filename).", description=".$db->escape($description)." WHERE id=".$id;
		} else {
			$sql = "INSERT INTO scheduled_reports (user, report_type_id, report_id, recipients, period_id, filename, description)
				VALUES(".$db->escape($user).", ".$rep_type.", ".$saved_report_id.", ".$db->escape($recipients).", ".$period.", ".$db->escape($filename).", ".$db->escape($description).");";
		}

		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return false;
		}

		if (!$id) {
			$id = (int)$res->insert_id();
		}
		return $id;
	}

	/**
	* Update specific field for certain scheduled report
	* Called from reports_Controller::save_schedule_item() through ajax
	*
	* @param 	int $id
	* @param 	str $field
	* @param 	mixed $value
	* @return 	bool
	*/
	public function update_report_field($id=false, $field=false, $value=false)
	{
		$id = (int)$id;
		$field = trim($field);
		$value = trim($value);
		$db = new Database(self::db_name);
		$sql = "UPDATE scheduled_reports SET `".$field."`= ".$db->escape($value)." WHERE id=".$id;
		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return false;
		}
		return true;
	}

	public function get_typeof_report($id=false)
	{
		$sql = "SELECT t.identifier FROM scheduled_reports sr, scheduled_report_types t WHERE ".
			"sr.id=".(int)$id." AND t.id=sr.report_type_id";
		$db = new Database(self::db_name);
		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return false;
		}

		return count($res)!=0 ? $res->current()->identifier : false;
	}

	public function get_report_type_id($identifier=false)
	{
		$db = new Database(self::db_name);
		$sql = "SELECT id FROM scheduled_report_types WHERE identifier=".$db->escape($identifier).";";
		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return false;
		}

		return count($res)!=0 ? $res->current()->id : false;
	}

	public function fetch_module_reports($type_id=false,$is_ajax=true)
	{
		$translate = zend::instance('Registry')->get('Zend_Translate');
		if ($is_ajax || request::is_ajax()) {
			$xajax = get_xajax::instance();
			$objResponse = new xajaxResponse();
			$objResponse->call("show_progress", "progress", $translate->_('Please wait...'));
		}
		$db = new Database(self::db_name);
		if (is_array($type_id)) $type_id = $type_id[0];
		$type_id = (int)$type_id;
		// fetch info on selected id
		if (!$type_id) {
			$sql = "SELECT * FROM scheduled_report_types";
		} else {
			$sql = "SELECT * FROM scheduled_report_types WHERE id=".$type_id;
		}
		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return false;
		}

		if (!$res || count($res)==0) {
			if ($is_ajax) {
				$objResponse->assign("err_msg","innerHTML", $translate->_("FAILED fetching path"));
			} else {
				return false;
			}
		}

		// if we got no type and we aren't in the middle of an ajax call
		// we are only interested in the actual result for further
		// processing by PHP
		if (!$type_id && !$is_ajax) {
			return $res;
		}

		$info = $res->current();
		$path = $info->script_reports_path;

		// fetch data from module by including the file
		// with path stored in db
		$return = false;
		include($path);
		if (!empty($return)) {
			if ($is_ajax) {
				$objResponse->call('show_reports', json::encode($return));
			} else {
				return $return;
			}
		} else {
			if ($is_ajax) {
				$objResponse->assign("err_msg","innerHTML", sprintf($translate->_("Found no saved reports.%sPlease create and save a report using the links in the menu on the left."), '<br />'));
				$objResponse->call("hide_rows");
			} else {
				return false;
			}
		}
		$objResponse->call('hide_progress');
		return $objResponse;
	}
}
