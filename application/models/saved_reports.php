<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Saved reports model
 * 	Responsible for fetching data for saved reports
 */
class Saved_reports_Model extends Model
{
	const db_name = 'monitor_reports';

	public function get_saved_reports($type='avail', $user=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla')
			return false;
		$db = new Database(self::db_name);
		$auth = new Nagios_auth_Model();
		$name_field = ($type == 'avail') ? 'report_name' : 'sla_name';
		$sql = "SELECT id, ".$name_field." FROM ".$type."_config ";
		if (!$auth->view_hosts_root) {
			$user = $user !== false ? $user : Auth::instance()->get_user()->username;
			$sql .= "WHERE user=".$db->escape($user)." OR user=''";
		}

		$sql .= " ORDER BY ".$name_field;

		$res = $db->query($sql);
		return $res ? $res : false;
	}

	public function edit_report_info($type='avail', $id=false, $options=false, $objects=false, $months=false)
	{
		$update = false;
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla')
			return false;

		if (empty($options) || empty($objects)) {
			return false;
		}

		$name_field = ($type == 'avail') ? 'report_name' : 'sla_name';
		$db = new Database(self::db_name);

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
			$sql = "INSERT INTO ".$type."_config (user, ".implode(', ', array_keys($options)).") VALUES(".$db->escape(Auth::instance()->get_user()->username).", '".implode('\',\'', array_values($options))."')";
		} else {
			$sql = "UPDATE ".$type."_config SET ";
			foreach ($options as $key => $value) {
				$a_sql[] = $key." = ".$db->escape($value);
			}
			$sql .= implode(', ', $a_sql);
			$sql .= ", updated = now()";
			$sql .= " WHERE id=".$id;
		}
		#echo $sql;
		$res = $db->query($sql);

		// continue with objects
		if (!$update) $id = (int)$res->insert_id();

		// insert/update <type>_config_objects
		if (!self::save_config_objects($type, $id, $objects)) {
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
		$db = new Database(self::db_name);
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
	*	@name 	save_config_objects
	*	@desc 	save information on what objects are related to this
	* 			report - hosts/services/-groups
	* 			Stores the name of the objects
	*/
	public function save_config_objects($type = 'avail', $id=false, $objects=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla')
			return false;

		if (empty($objects) || empty($id)) return false;

		// remove old records (if any)
		$sql = "DELETE FROM ".$type."_config_objects WHERE ".$type."_id=".$id;
		$db = new Database(self::db_name);
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
	*	@name 	delete_report
	*	@desc 	Delete info on a saved report
	* 	@param  int $avail_id
	*
	*/
	public function delete_report($type='avail', $id)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla')
			return false;

		if (empty($id)) return false;
		$err = false;
		$sql[] = "DELETE FROM ".$type."_config_objects WHERE ".$type."_id=".$id;
		$sql[] = "DELETE FROM ".$type."_config WHERE id=".$id;
		$db = new Database(self::db_name);
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
	* @name 	get_all_report_names
	* @desc   	Fetches all info names from {avail,sla}_config
	* @return array all names
	*/
	public function get_all_report_names($type='avail')
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla')
			return false;

		$db = new Database(self::db_name);
		$name_field = ($type == 'avail') ? 'report_name' : 'sla_name';
		$sql = "SELECT ".$name_field." FROM ".$type."_config WHERE user=".$db->escape(Auth::instance()->get_user()->username).
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
	*	@name 	get_report_info
	*	@desc 	Fetch info on single saved report
	*	@return Array
	*/
	public function get_report_info($type='avail', $id=false)
	{
		$type = strtolower($type);
		if ($type != 'avail' && $type != 'sla')
			return false;

		if (empty($id)) return false;

		$sql = "SELECT * FROM ".$type."_config WHERE id=".(int)$id;
		$db = new Database(self::db_name);
		$res = $db->query($sql);
		if (!$res || count($res)==0)
			return false;

		$res->result(false);
		$return = $res->current();
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
	*	Fetch saved SLA values and month info from db
	*/
	public function get_period_info($sla_id=false)
	{
		if (empty($sla_id))
			return false;

		$sql = "SELECT * FROM sla_periods WHERE sla_id=".(int)$sla_id." ORDER BY id";
		$db = new Database(self::db_name);
		$res = $db->query($sql);

		return (!$res || count($res)==0) ? false : $res;
	}


	/**
	*	@name get_config_objects
	*/
	public function get_config_objects($type='avail', $id=false)
	{
		$type = strtolower($type);
		if (($type != 'avail' && $type != 'sla') || empty($id))
			return false;

		$sql = "SELECT * FROM ".$type."_config_objects WHERE ".$type."_id=".(int)$id;
		$db = new Database(self::db_name);
		$res = $db->query($sql);

		return (!$res || count($res)==0) ? false : $res;
	}


}