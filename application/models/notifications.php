<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle notifications for hosts and services
 */
class Notifications_Model extends Model {

	public $sort_field ='next_check'; /**< The field to sort on */
	public $sort_order='ASC'; /**< The sort order */
	public $where=''; /**< Arbitrary SQL to add to filtering */

	/**
	*	Fetch scheduled events
	*
	*/
	public function show_notifications($num_per_page=false, $offset=false, $count=false)
	{

		$db = Database::instance();
		$auth = Nagios_auth_Model::instance();

		$num_per_page = (int)$num_per_page;

		# only use LIMIT when NOT counting
		if ($offset !== false)
			$offset_limit = $count === true ? "" : " LIMIT " . intval($num_per_page)." OFFSET ".intval($offset);
		else
			$offset_limit = '';

		if (substr($this->where, 0, 4) == ' AND') {
			$this->where = preg_replace('/^ AND/', '', $this->where);
		}
		$where_string = (!empty($this->where)) ? 'WHERE '.$this->where : '';

		if (!empty($where_string)) {
			$where_string .= " AND ";
		} else {
			$where_string .= " WHERE ";
		}

		$where_string .= " contact_name IS NOT NULL AND command_name IS NOT NULL ";

		$fields = " host_name, service_description, start_time, ".
			"end_time, reason_type, state, contact_name, ".
			"notification_type, output, command_name ";

		$where_order = $offset_limit;
		if( preg_match( '/^[a-zA-Z0-9_]+$/', $this->sort_field ) ) {
			$where_order = " ORDER BY ".$this->sort_field." ".(strtolower($this->sort_order)=='asc'?'ASC':'DESC').$where_order;
		}

		$sql = "SELECT ".($count === true ? 'count(1) AS cnt' : $fields)." FROM notification ".$where_string;

		# query for limited contact
		# make joins with contact_access to get all notifications
		# for user's hosts and services
		if (!$auth->view_hosts_root) {
			$hosts = Livestatus::instance()->getHosts(array('columns' => 'name'));
			$sql_host = "SELECT ".($count === true ? 'count(1)' : 'id')." FROM notification ".
				"WHERE host_name IN ('".implode("' ,'", $hosts) .
				"') AND service_description IS NULL ".
				"AND command_name IS NOT NULL";

			if (!$auth->view_services_root) {
				$services = Livestatus::instance()->getServices(array('columns' => array('host_name', 'description')));
				$sqlsvc = "SELECT ".($count === true ? 'count(1)' : 'id')." FROM notification ".
					"WHERE command_name IS NOT NULL AND (";
				$first = true;
				foreach ($services as $row) {
					if (!$first)
						$sqlsvc .= ' OR ';
					$sqlsvc .= "(host_name = '{$row['host_name']}' AND service_description = ".$db->escape($row['description']).")";
					$first = false;
				}
				$sqlsvc .= ')';
			}
			else {
				$sqlsvc = 'SELECT '.($count === true ? 'count(1)' : 'id').' FROM notification WHERE notification.service_description IS NOT NULL';
			}
			if ($count !== true)
				$sql = "SELECT $fields FROM notification WHERE id IN ($sql_host) OR id IN ($sqlsvc)";
			else
				$sql = "SELECT ($sql_host) + ($sqlsvc) AS cnt";
		}

		if ($count !== true)
			$sql .= $where_order;
		$result = $db->query($sql);
		if ($count === true) {
			$row = $result->current();
			return $row->cnt;
		}
		return $result->count() ? $result: false;
	}

	/**
	*	Wrapper method to fetch no of hosts in the scheduling queue
	*/
	public function count_notifications()
	{
		return self::show_notifications(false, false, true);
	}

}
