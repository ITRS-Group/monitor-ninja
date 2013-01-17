<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Saved reports model
 * 	Responsible for fetching data for saved reports
 */
class Saved_reports_Model extends Model
{
	const USERFIELD = 'username'; /**< Name of the user field in database */

	/**
	 * Return all saved reports for a given report type
	 *
	 * Note: you get the exact same info from get_all_report_names
	 *
	 * @param $type The report type ('avail', 'sla' or 'summary')
	 */
	public static function get_saved_reports($type='avail')
	{
		assert($type == 'avail' || $type == 'sla' || $type == 'summary');
		$db = Database::instance();
		$auth = Nagios_auth_Model::instance();

		$sql = "SELECT id, report_name FROM ".$type."_config ";
		if (!$auth->view_hosts_root) {
			$user = Auth::instance()->get_user()->username;
			$sql .= "WHERE ".self::USERFIELD."=".$db->escape($user)." OR ".self::USERFIELD."=''";
		}

		$sql .= " ORDER BY report_name";

		$res = $db->query($sql);
		return $res ? $res->result_array(true) : false;
	}


	/**
	 * Save changes to a report, or save a new report.
	 *
	 * @param $type The report type ('avail', 'summary' or 'sla')
	 * @param $id The report id, or false to create a new one.
	 * @param $options The new options to save. For summary reports, this will
	 *        first unset any old options and then set these, for other report
	 *        types only the options that are set will be overwritten.
	 * @return false on error, or the id of the saved report
	 */
	public static function edit_report_info($type, $id, Report_options $options)
	{
		$update = false;
		assert($type == 'avail' || $type == 'sla' || $type == 'summary');
		assert(!empty($options));

		$db = Database::instance();

		# Don't save start- or end_time when we have report_period != custom
		if (isset($options['report_period']) && $options['report_period'] != 'custom') {
			unset($options['start_time']);
			unset($options['end_time']);
		}

		if ($type != 'summary') {
			$objects = $options[$options->get_value('report_type')];
			unset($options[$options->get_value('report_type')]);
			
			$actual_options = array();
			foreach ($options as $option => $val) {
				$actual_options[$option] = $val;
			}
			$options['id'] = $options['report_id'];
			unset($options['report_id']);
		}

		if ($type == 'sla') {
			$months = $options['months'];
			unset($options['months']);
		}

		// INSERT or UPDATE?
		if (!empty($id))
			$update = true;
		else {
			$id = self::get_report_id($type, $options['report_name']);
			$update = $id !== false;
		}
		if (!$update) {
			if ($type == 'summary') {
				$sql = "INSERT INTO ".$type."_config (".self::USERFIELD.", report_name, setting) VALUES(".$db->escape(Auth::instance()->get_user()->username).", ".$db->escape($options['report_name']).", ".$db->escape(serialize($options->options)).")";
			} else {
				$keys = '';
				$values = '';
				foreach ($options as $key => $val) {
					// fuck you, special cases
					switch ($key) {
					 case 'host_name':
					 case 'service_description':
					 case 'hostgroup':
					 case 'servicegroup':
						break; # these are added in save_config_objects
					 case 'host_filter_status':
					 case 'service_filter_status':
						$val = serialize($val);
					 default:
						$keys .= ', '.$key; # safe to use, because Report_options shouldn't allow misc keys
						$values .= ', '.$db->escape($val);
					}
				}

				$sql = "INSERT INTO ".$type."_config (".self::USERFIELD.$keys.") VALUES(".$db->escape(Auth::instance()->get_user()->username).$values.")";
			}
		} else {
			if ($type == 'summary') {
				$sql = "UPDATE ".$type."_config SET report_name = ".$db->escape($options['report_name']).", ".
					"setting=".$db->escape(serialize($options->options))." WHERE id=".$id;
			} else {
				$sql = "UPDATE ".$type."_config SET ";
				foreach ($options as $key => $value) {
					// fuck you, special cases
					switch ($key) {
					 case 'host_name':
					 case 'service_description':
					 case 'hostgroup':
					 case 'servicegroup':
						break; # these are added in save_config_objects
					 case 'host_filter_status':
					 case 'service_filter_status':
						$value = serialize($value);
					 default:
						$a_sql[] = $key." = ".$db->escape($value);
					}
				}
				$sql .= implode(', ', $a_sql);
				$sql .= ", updated = NOW()";
				$sql .= " WHERE id=".$id;
			}
		}
		#echo $sql;
		$res = $db->query($sql);

		unset($res);
		// continue with objects
		if (!$update) $id = (int)self::get_report_id($type, $options['report_name']);

		// insert/update <type>_config_objects
		if ($type!= 'summary' && !self::save_config_objects($type, $id, $objects)) {
			if (!IN_PRODUCTION)
				print "Couldn't save objects";
			return false;
		}

		// Insert/Update sla_periods
		if($type == 'sla' && !self::save_period_info($id, $months)) {
			if (!IN_PRODUCTION)
				print "Couldn't save period info";
			return false;
		}

		return $id;
	}

	/**
	 * Fetch the ID of a saved report
	 *
	 * @param $type The report type
	 * @param $name The report name
	 * @return The id of the report
	 */
	private static function get_report_id($type='avail', $name=false)
	{
		assert($type == 'avail' || $type == 'sla' || $type == 'summary');
		$name = trim($name);
		if (empty($name)) {
			return false;
		}
		$id = false;
		$db = Database::instance();
		$sql = 'SELECT id FROM '.$type.'_config WHERE '.self::USERFIELD.'='.
			$db->escape(Auth::instance()->get_user()->username).' AND '.
			'report_name ='.$db->escape($name);
		$res = $db->query($sql);
		if (count($res)>0) {
			$cur = $res->current();
			$id = $cur->id;
		}
		unset($res);
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
		$db = Database::instance();
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
	 * @param $type string: Type of report {avail, sla, summary}
	 * @param $id Id of the schedule.
	 * @param $objects Objects this scheduled report concerns
	 * @return true on success, false on errors.
	 */
	public static function save_config_objects($type = 'avail', $id=false, $objects=false)
	{
		assert($type == 'avail' || $type == 'sla' || $type == 'summary');

		if (empty($objects) || empty($id)) return false;

		if ($type === 'summary') {
			$info = self::get_report_info($type, $id);
			$settings = unserialize($info['settings']);
			$settings['objects'] = $objects;
			$info->settings = serialize($settings);
			self::edit_report_info($type, $id, $info);
			return true;
		}

		// remove old records (if any)
		$sql = "DELETE FROM ".$type."_config_objects WHERE ".$type."_id=".$id;
		$db = Database::instance();
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
	 * @param $type string: Report type { avail, sla, summary }
	 * @param $id Id of the report to delete.
	 * @return true on success, false on errors
	 */
	public function delete_report($type='avail', $id)
	{
		assert($type == 'avail' || $type == 'sla' || $type == 'summary');

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
		$db = Database::instance();
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
	 * Note: you get the exact same info from get_saved_reports
	 *
	 * @param $type string: Report type. { avail, sla }
	 * @return false on errors. Array of all names on success
	 */
	public static function get_all_report_names($type='avail')
	{
		assert($type == 'avail' || $type == 'sla' || $type == 'summary');

		$db = Database::instance();

		$sql = "SELECT report_name FROM ".$type."_config WHERE ".self::USERFIELD."=".$db->escape(Auth::instance()->get_user()->username).
			" ORDER BY report_name";
		$res = $db->query($sql);
		if (!$res || count($res)==0)
			return false;

		$names = array();
		foreach($res as $row)
		{
			$names[] = $row->report_name;
		}
		return $names;
	}

	/**
	 * Fetch info on single saved report
	 *
	 * @param $type string: Report type { avail, sla }
	 * @param $id int Id of the report.
	 * @return Report info as array on success, empty array when no info was found.
	 */
	public static function get_report_info($type='avail', $id)
	{
		assert($type == 'avail' || $type == 'sla' || $type == 'summary');
		assert(!empty($id));

		$sql = "SELECT * FROM ".$type."_config WHERE id=".(int)$id;
		$db = Database::instance();
		$res = $db->query($sql);
		if (!$res || count($res)==0)
			return array();

		$res->result(false);
		$return = $res->current();
		$return['report_id'] = $return['id'];
		if ($type == 'summary') {
			$ret = unserialize($return['setting']);
			$ret['report_id'] = $return['id'];
			return $ret;
		}

		if ($type !== 'summary') {
			$return['host_filter_status'] = i18n::unserialize($return['host_filter_status']);
			$return['service_filter_status'] = i18n::unserialize($return['service_filter_status']);
		}

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

				# handle dynamic month values
				if (strstr($return['report_period'], 'last')) {
					$month = date('n');
					switch ($return['report_period']) {
						case 'last3months':
							self::adjust_sla_periods(3, $return);
							break;
						case 'last6months':
							self::adjust_sla_periods(6, $return);
							break;
						case 'lastquarter':
							self::adjust_sla_periods(3, $return, true);
							break;
					}
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
	*	Make monthly SLA values dynamic
	*/
	public function adjust_sla_periods($num=false, &$arr, $is_quarter=false)
	{
		if (empty($num)) {
			return false;
		}
		$month = date('n');

		$new_months = false;
		$start = $month - $num;
		if ($is_quarter === true) {
			# quarter
			if ($month <= 3) {
				$start = 10;
			} elseif ($month <= 6) {
				$start = 1;
			} elseif ($month <= 9) {
				$start = 4;
			} else {
				$start = 7;
			}
		}

		$month = $month == 1 ? 12 : $month;
		for ($i=$start;$i<=$month;$i++) {
			$a = $i<0 ? $i + 13 : $i;
			$a = $a == 0 ? 1 : $a;
			$new_months[] = 'month_'.$a;
		}

		$i = 0;
		$unset = false;
		$add = false;
		foreach ($arr as $key => $val) {
			if (strstr($key, 'month_')) {
				$unset[] = $key;
				$add[$new_months[$i]] = $val;
				$i++;
			}
		}
		if (!empty($unset)) {
			foreach ($unset as $k) {
				unset($arr[$k]);
			}
			unset($unset);
		}
		if (!empty($add)) {
			foreach ($add as $key => $val) {
				$arr[$key] = $val;
			}
			unset($add);
		}
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
		$db = Database::instance();
		$res = $db->query($sql);

		return (!$res || count($res)==0) ? false : $res;
	}


	/**
	 * Get config objects
	 * @param $type string: Report type { avail, sla }
	 * @param $id int: Report id
	 * @return false on errors, database result array on success.
	 */
	public static function get_config_objects($type='avail', $id=false)
	{
		assert($type == 'avail' || $type == 'sla');
		if (empty($id))
			return false;

		$sql = "SELECT * FROM ".$type."_config_objects WHERE ".$type."_id=".(int)$id;
		$db = Database::instance();
		$res = $db->query($sql);

		return (!$res || count($res)==0) ? false : $res;
	}
}
