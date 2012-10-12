<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * display configurations
 */
class Config_Model extends Model {

	const SERVICE_NOTIFICATION_COMMANDS =  'service_notification_commands'; /**< DB column name for service notification commands */
	const HOST_NOTIFICATION_COMMANDS = 'host_notification_commands'; /**< DB column name for host notification commands */

	/**
	 Workaround for PDO queries: runs $db->query($sql), copies
	 the resultset to an array, closes the resultset, and returns
	 the array.
	 */
	private function query($db,$sql)
	{
		$res = $db->query($sql);
		if (!$res)
			return NULL;

		$rc = array();
		foreach($res as $row) {
			$rc[] = $row;
		}
		unset($res);
		return $rc;
	}

	/**
	 * Fetch config info for a specific type
	 * @param $type The object type
	 * @param $num_per_page The number of rows to get
	 * @param $offset The number of rows to skip
	 * @param $count Skip fetching config info, fetch the number of matching database rows
	 * @param $free_text Only fetch items that match this free text
	 * @return If count is false, database object or false on error or empty. If count is true, number
	 */
	public function list_config($type = 'hosts', $num_per_page=false, $offset=false, $count=false, $free_text=null)
	{
		$db = Database::instance();
		$options = array();

		switch($type) {
			case 'hosts':
				$options['extra_columns'] = array(
					'contact_groups',
					'event_handler',
					'contacts'
				);
				break;
			case 'services':
				$options['extra_columns'] = array(
					'active_checks_enabled',
					'accept_passive_checks',
					'check_freshness',
					'contact_groups',
					'contacts'
				);
				break;
			case 'contactgroups':
				$options['columns'] = array(
					'name',
					'alias',
					'members'
				);
				break;
		}

		if (!Auth::instance()->authorized_for('host_view_all')) {
			return false;
		}

		if (false !== $offset && $num_per_page) {
			$options['limit'] = $num_per_page;
			$options['offset'] = $offset;
		}

		if($type != 'timeperiods') {
			$res = Livestatus::instance()->{'get'.$type}($options);
			return $res;
		}

		$table = "timeperiod";
		$primary = "timeperiod_name";
		$sql = "SELECT
				timeperiod_name,
				alias,
				monday,
				tuesday,
				wednesday,
				thursday,
				friday,
				saturday,
				sunday
			FROM
				timeperiod
			ORDER BY
				timeperiod_name";

		if ($count) {
			$sql = "SELECT COUNT(1) AS count FROM $table";
			$primary = preg_replace('~.*\.~', null, $primary);
			if($free_text) {
				$sql .= " WHERE $primary LIKE '%$free_text%'";
			}
			$result = $this->query($db,$sql);
			return $result[0]->count;
		}
		if($free_text) {
			if(stripos($sql, 'WHERE') === false) {
				$sql .= " WHERE $primary LIKE '%$free_text%'";
			} else {
				$sql .= " AND $primary LIKE '%$free_text%'";
			}
		}

		return $this->query($db,$sql);
	}

	/**
	 * Wrapper around list_config to only return the number of $type objects
	 * @param $type The object type
	 * @param $free_text Only fetch items that match this free text
	 */
	public function count_config($type, $free_text = null)
	{
		return $this->list_config($type, false, false, true, $free_text);
	}
}
