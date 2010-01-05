<?php defined('SYSPATH') OR die('No direct access allowed.');

class Scheduled_reports_Model extends Model
{
	public $db_name = 'monitor_reports';
	const db_name = 'monitor_reports';

	public function delete_scheduled_report($type='avail',$id=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla')
			return false;

		$id = (int)$id;
		if (empty($id)) return false;
		$res = self::get_scheduled_reports($type);
		if (empty($res)) return false;
		$scheduled_id = false;
		foreach ($res as $row) {
			if ($row->report_id == $id) {
				$scheduled_id = $row->id;
				break;
			}
		}
		if (!empty($scheduled_id)) {
			$sql = "DELETE FROM scheduled_reports WHERE id=".$scheduled_id;
			$db = new Database(self::db_name);
			$db->query($sql);
			return true;
		} else {
			return false;
		}
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
		$sql = "SELECT
				sr.*,
				rp.periodname
			FROM
				scheduled_reports sr,
				scheduled_report_types rt,
				scheduled_report_periods rp
			WHERE
				rt.identifier='".$type."' AND
				sr.report_type_id=rt.id AND
				rp.id=sr.period_id
			ORDER BY
				sr.id";
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
		global $dba;
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
}