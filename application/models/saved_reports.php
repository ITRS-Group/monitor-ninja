<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Saved reports model
 * 	Responsible for fetching data for saved reports
 */
class Saved_reports_Model extends Model
{
	const db_name = 'merlin';
	const USERFIELD = 'user';

	public function get_saved_reports($type='avail', $user=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla' && $type != 'summary')
			return false;
		$db = new Database();
		$auth = new Nagios_auth_Model();
		switch ($type) {
			case 'avail':
			case 'summary':
				$name_field = 'report_name';
				break;
			case 'sla':
				$name_field = 'sla_name';
				break;
		}

		$sql = "SELECT id, ".$name_field." FROM ".$type."_config ";
		if (!$auth->view_hosts_root) {
			$user = $user !== false ? $user : Auth::instance()->get_user()->username;
			$sql .= "WHERE ".self::USERFIELD."=".$db->escape($user)." OR ".self::USERFIELD."=''";
		}

		$sql .= " ORDER BY ".$name_field;

		$res = $db->query($sql);
		return $res ? $res : false;
	}


	public function edit_report_info($type='avail', $id=false, $options=false, $objects=false, $months=false)
	{
		$update = false;
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla' && $type != 'summary')
			return false;

		if (empty($options) || ($type!= 'summary' && empty($objects)) ) {
			return false;
		}

		switch ($type) {
			case 'avail':
			case 'summary':
				$name_field = 'report_name';
				break;
			case 'sla':
				$name_field = 'sla_name';
				break;
		}
		$db = new Database();

		# check options for start_time or end_time
		# and convert to timestamp before save
		if (isset($options['report_period']) && $options['report_period'] == 'custom') {
			if (isset($options['start_time']) && !is_numeric($options['start_time'])) {
				$options['start_time'] = strtotime($options['start_time']);
			}
			if (isset($options['end_time']) && !is_numeric($options['end_time'])) {
				$options['end_time'] = strtotime($options['end_time']);
			}
		}

		# Don't save start- or end_time when we have report_period != custom
		if (isset($options['report_period']) && $options['report_period'] != 'custom') {
			unset($options['start_time']);
			unset($options['end_time']);
		}

		// INSERT or UPDATE?
		if (!empty($id))
			$update = true;
		else {
			$sql = "SELECT id FROM ".$type."_config WHERE ".$name_field." = ".$db->escape($options[$name_field]);
			$res = $db->query($sql);
			if(count($res)) {
				$row = $res->current();
				$id = $row->id;
				$update = true;
			}
		}
		if (!$update) {
			if ($type == 'summary') {
				$sql = "INSERT INTO ".$type."_config (".self::USERFIELD.", ".$name_field.", setting) VALUES(".$db->escape(Auth::instance()->get_user()->username).", '".$options['report_name']."', '".serialize($options)."')";
			} else {
				$sql = "INSERT INTO ".$type."_config (".self::USERFIELD.", ".implode(', ', array_keys($options)).") VALUES(".$db->escape(Auth::instance()->get_user()->username).", '".implode('\',\'', array_values($options))."')";
			}
		} else {
			if ($type == 'summary') {
				$sql = "UPDATE ".$type."_config SET report_name = ".$db->escape($options['report_name']).", ".
					"setting=".$db->escape(serialize($options))." WHERE id=".$id;
			} else {
				$sql = "UPDATE ".$type."_config SET ";
				foreach ($options as $key => $value) {
					$a_sql[] = $key." = ".$db->escape($value);
				}
				$sql .= implode(', ', $a_sql);
				$sql .= ", updated = now()";
				$sql .= " WHERE id=".$id;
			}
		}
		#echo $sql;
		$res = $db->query($sql);

		// continue with objects
		if (!$update) $id = (int)$res->insert_id();

		// insert/update <type>_config_objects
		if ($type!= 'summary' && !self::save_config_objects($type, $id, $objects)) {
			return false;
		}

		// Insert/Update sla_periods
		if($type == 'sla' && !self::save_period_info($id, $months)) {
			return false;
		}

		return $id;
	}

	/**
	*	Handle info on SLA period values (monthly)
	* 	Remove old values and enter the new ones
	*/
	function save_period_info($sla_id=false, $months=false)
	{
		if (empty($sla_id))
			return false;

		// remove old records (if any)
		$sql = "DELETE FROM sla_periods WHERE sla_id=".(int)$sla_id;
		$db = new Database();
		$db->query($sql);
		unset($sql);

		if (!empty($months)) {
			foreach ($months as $key => $value) {
				#echo "$key => $value<br />";
				$sql[] = "INSERT INTO sla_periods(sla_id, name, value) VALUES(".(int)$sla_id.", 'month_".$key."', ".floatval($value).")";
			}
		}

		foreach ($sql as $query) {
			$db->query($query);
		}
		return true;
	}

	/**
	 * Save information on what objects are related to this	report
	 * (hosts/services/-groups) and stores the name of the objects.
	 *
	 * @param $type string: Type of report {avail, sla}
	 * @param $id Id of the schedule.
	 * @param $objects Objects this scheduled report concerns
	 * @return true on success, false on errors.
	 */
	public function save_config_objects($type = 'avail', $id=false, $objects=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla' && $type != 'summary')
			return false;

		if (empty($objects) || empty($id)) return false;

		// remove old records (if any)
		$sql = "DELETE FROM ".$type."_config_objects WHERE ".$type."_id=".$id;
		$db = new Database();
		$db->query($sql);

		$_sql = "INSERT INTO ".$type."_config_objects (".$type."_id, name) VALUES(";
		foreach ($objects as $item) {
			try {
				$query = $_sql.(int)$id.", '".$item."')";
				$res = $db->query($query);
			}
			catch (Kohana_Database_Exception $e) {
				# an error occurred
				echo $e;
				return false;
			}
		}
		return true;
	}

	/**
	 * Delete info on a saved report
	 * @param $type string: Report type { avail, sla }
	 * @param $id Id of the report to delete.
	 * @return true on success, false on errors
	 */
	public function delete_report($type='avail', $id)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla' && $type != 'summary')
			return false;

		if (empty($id)) return false;
		$err = false;
		if ($type == 'summary') {
			$sql[] = 'DELETE FROM summary_config WHERE id='.$id;
		} else {
			$sql[] = "DELETE FROM ".$type."_config_objects WHERE ".$type."_id=".$id;
			$sql[] = "DELETE FROM ".$type."_config WHERE id=".$id;
		}
		if ($type == 'sla') {
			$sql[] = "DELETE FROM sla_periods WHERE sla_id=".$id;
		}
		$db = new Database();
		foreach ($sql as $query) {
			try {
				$db->query($query);
			} catch (Kohana_Database_Exception $e) {
				$err++;
			}
		}
		if ($err !== false) {
			return false;
		}
		Scheduled_reports_Model::delete_all_scheduled_reports($type, $id);
		return true;
	}

	/**
	 * Fetches all info names from {avail,sla}_config
	 *
	 * @param $type string: Report type. { avail, sla }
	 * @return false on errors. Array of all names on success
	 */
	public function get_all_report_names($type='avail')
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla' && $type != 'summary')
			return false;

		$db = new Database();
		switch ($type) {
			case 'avail':
			case 'summary':
				$name_field = 'report_name';
				break;
			case 'sla':
				$name_field = 'sla_name';
				break;
		}

		$sql = "SELECT ".$name_field." FROM ".$type."_config WHERE ".self::USERFIELD."=".$db->escape(Auth::instance()->get_user()->username).
			" ORDER BY ".$name_field;
		$res = $db->query($sql);
		if (!$res || count($res)==0)
			return false;

		$names = array();
		foreach($res as $row)
		{
			$names[] = $row->{$name_field};
		}
		return $names;
	}

	/**
	 * Fetch info on single saved report
	 *
	 * @param $type string: Report type { avail, sla }
	 * @param $id Id of the report.
	 * @return false on error. Report info as array on success.
	 */
	public function get_report_info($type='avail', $id=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla' && $type != 'summary')
			return false;

		if (empty($id)) return false;

		$sql = "SELECT * FROM ".$type."_config WHERE id=".(int)$id;
		$db = new Database();
		$res = $db->query($sql);
		if (!$res || count($res)==0)
			return false;

		$res->result(false);
		$return = $res->current();
		if ($type == 'sla') {
			$period_info = self::get_period_info($id);
			if ($period_info !== false) {
				foreach ($period_info as $row) {
					$month_key =  $row->name;
					if ($return['report_period'] == 'lastmonth') {
						# special case lastmonth report period to work as expected,
						# i.e to use the entered SLA value for every month
						# no matter what month it was scheduled
						$month = date('n');
						$month = $month == 1 ? 12 : ($month-1);
						$month_key = 'month_'.$month;
					}

					$return[$month_key] = $row->value;
				}
			}
		}

		$object_info = self::get_config_objects($type, $id);
		$objects = false;
		if ($object_info) {
			foreach ($object_info as $row) {
				$objects[] = $row->name;
			}
		}
		$return['objects'] = $objects;
		return $return;
	}

	/**
	 * Fetch saved SLA values and month info from db
	 * @param $sla_id int: Id of the report schedule
	 * @return false on errors. Database result array on success.
	 */
	public function get_period_info($sla_id=false)
	{
		if (empty($sla_id))
			return false;

		$sql = "SELECT * FROM sla_periods WHERE sla_id=".(int)$sla_id." ORDER BY id";
		$db = new Database();
		$res = $db->query($sql);

		return (!$res || count($res)==0) ? false : $res;
	}


	/**
	 * Get config objects
	 * @param $type string: Report type { avail, sla }
	 * @param $id int: Report id
	 * @return false on errors, database result array on success.
	 */
	public function get_config_objects($type='avail', $id=false)
	{
		$type = strtolower($type);
		if (($type != 'avail' && $type != 'sla') || empty($id))
			return false;

		$sql = "SELECT * FROM ".$type."_config_objects WHERE ".$type."_id=".(int)$id;
		$db = new Database();
		$res = $db->query($sql);

		return (!$res || count($res)==0) ? false : $res;
	}

	public function get_sla_from_saved_reports($sla_id, $user=false)
	{
		$db = new Database();
		$auth = new Nagios_auth_Model();

		$sql = "SELECT name, value FROM sla_periods WHERE sla_id = '".$sla_id."'";
		if (!$auth->view_hosts_root) {
			$user = $user !== false ? $user : Auth::instance()->get_user()->username;
			$sql .= " AND ".self::USERFIELD."=".$db->escape($user)." OR ".self::USERFIELD."=''";
		}

		$res = $db->query($sql);
		return $res ? $res : false;
	}

}
