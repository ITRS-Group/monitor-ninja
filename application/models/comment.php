<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle comments for hosts and services
 */
class Comment_Model extends Model {
	/***************************** COMMENT TYPES *******************************/
	const HOST_COMMENT = 1;
	const SERVICE_COMMENT = 2;

	/****************************** ENTRY TYPES ********************************/
	const USER_COMMENT = 1;
	const DOWNTIME_COMMENT = 2;
	const FLAPPING_COMMENT = 3;
	const ACKNOWLEDGEMENT_COMMENT = 4;

	const TABLE_NAME = 'comment_tbl';

	/**
	*	Fetch saved comments for host or service
	*
	*/
	public function fetch_comments($host=false, $service=false, $num_per_page=false, $offset=false, $count=false)
	{
		$host = trim($host);
		$service = trim($service);
		if (empty($host)) {
			return false;
		}
		$db = new Database();
		$auth = new Nagios_auth_Model();

		# service comments or not?
		$svc_selection = empty($service) ?
			'AND c.service_description IS NULL' :
			'AND c.service_description='.$db->escape($service);

		# only use LIMIT when NOT counting
		$offset_limit = $count!==false || empty($num_per_page) ? "" : " LIMIT " . $num_per_page." OFFSET ".$offset;

		if ($auth->view_hosts_root) {
			$sql = 'SELECT * FROM '.self::TABLE_NAME.' c ' .
			       'WHERE c.host_name='.$db->escape($host).' '.$svc_selection;
		} else {
			if (!empty($service)) {
				$svc_from = ' INNER JOIN service s ' .
				            'ON c.service_description = s.service_description ' .
				            'AND c.host_name = s.host_name';
				$by_ca = 'ca.host IS NULL AND ca.service = s.id';
			} else {
				$svc_from = '';
				$by_ca = 'ca.host = h.id AND ca.service IS NULL';
			}
			$sql = 'SELECT c.* FROM '.self::TABLE_NAME.' c'.$svc_from .
			       ' INNER JOIN host h ON c.host_name = h.host_name ' .
			       'INNER JOIN contact_access ca ON '.$by_ca.' ' .
			       'INNER JOIN contact ON ca.contact = contact.id ' .
			       'AND contact.contact_name = '.$db->escape(Auth::instance()->get_user()->username).' ' .
			       'WHERE c.host_name='.$db->escape($host).' '.$svc_selection;
		}

		$sql .= " ORDER BY c.entry_time, c.host_name ".$offset_limit;

		$result = $db->query($sql);
		if ($count !== false) {
			if( $result ) {
				$count = count($result);
				unset($result);
			}
			else {
				$count = 0;
			}
			return $count;
		}
		return $result->count() ? $result->result(): false;
	}

	public function fetch_all_comment_types($entry_type, $host_name, $service_description) {

		$db = new Database();
		switch ($entry_type) {
			case 1: // user comment
				$type = self::TABLE_NAME;
				break;
			case 2: // downtime
				$type = 'scheduled_downtime';
				break;
			case 3: // flapping
				$type = self::TABLE_NAME;
				break;
			case 4: // acknowledged
				$type = self::TABLE_NAME;
				break;
		}
		$and = empty($service_description) ? '' : " AND service_description='".$service_description."'";
		$sql = "SELECT comment_data from ".$type." where host_name = '".$host_name."'".$and."";
		$result = $db->query($sql);

		return $result->count() ? $result->result() : false;
	}

	/**
	*	Wrapper method to fetch nr of comments for host or service
	*/
	public function count_comments($host=false, $service=false)
	{
		return self::fetch_comments($host, $service, false, false, true);
	}

	/**
	*	Fetch all host- or service comments
	*/
	public function fetch_all_comments($host=false, $service=false, $num_per_page=false, $offset=false, $count=false)
	{
		$host = trim($host);
		$service = trim($service);
		$num_per_page = (int)$num_per_page;
		$db = new Database();
		$auth = new Nagios_auth_Model();

		$host_query = $auth->authorized_host_query();

		# service comments or not?
		$svc_selection = empty($service) ? " AND c.service_description IS NULL "
			: " AND c.service_description IS NOT NULL ";

		# only use LIMIT when NOT counting
		$offset_limit = $count!==false ? "" : " LIMIT " . $num_per_page." OFFSET ".$offset;


		if ($host_query === true) {
			# don't use auth_host fields etc since
			# user is authenticated_for_all_hosts
			$auth_host_alias = 'h';
			$auth_from = ', host '.$auth_host_alias;
			$auth_where = ' AND '.$auth_host_alias . ".host_name = c.host_name";
			$sql = "SELECT c.* FROM ".self::TABLE_NAME." c ".$auth_from." WHERE".
				" c.host_name IS NOT NULL ".$svc_selection.$auth_where;
		} else {
			# we only make this check if user isn't authorized_for_all_hosts as above
			$service_query = $auth->authorized_service_query();

			$auth_host_alias = $host_query['host_field'];
			$auth_from = ' ,'.$host_query['from'];
			$auth_where = !empty($host_query['where']) ? ' AND '.sprintf($host_query['where'], "c.host_name") : '';

			if (!$service) { # host comments
				$sql = "SELECT c.* FROM ".self::TABLE_NAME." c WHERE c.id IN (SELECT DISTINCT c.id FROM ".self::TABLE_NAME." c, contact_access ca, contact, host h ".
					"WHERE contact.contact_name=".
					$db->escape(Auth::instance()->get_user()->username).
					" AND ca.contact=contact.id ".
					"AND c.host_name=h.host_name ".
					"AND (c.service_description='' OR c.service_description is null) ".
					"AND ca.host=h.id AND ca.service is null) ";
			} else { # service comments
				if ($service_query !== true) {
					$sql = "SELECT c.* FROM ".self::TABLE_NAME." c WHERE c.id IN (SELECT DISTINCT c.id FROM ".self::TABLE_NAME." c, contact_access ca, contact, host h, service s
						WHERE contact.contact_name=".$db->escape(Auth::instance()->get_user()->username).
						" AND ca.contact=contact.id ".
						"AND c.host_name=h.host_name ".
						"AND s.host_name=c.host_name ".
						"AND ca.service=s.id ".
						"AND (c.service_description is NOT null)) ";
				} else {
					$sql = "SELECT * FROM ".self::TABLE_NAME." WHERE (service_description is NOT null) ";
				}
			}
		}

		$sql .= " ORDER BY c.entry_time, c.host_name ".$offset_limit;
		#echo $sql."<br />";

		$result = $db->query($sql);
		if ($count !== false) {
			return $result ? count($result) : 0;
		}

		return $result;
	}

	/**
	*	Wrapper method to fetch a count of all service- or host comments
	*/
	public function count_all_comments($host=false, $service=false)
	{
		return self::fetch_all_comments($host, $service, false, false, true);
	}

	/**
	*	Fetch comment counts for all objects that has comments
	*	Returned array will contain object name as key and count
	* 	as value for all objects with comments.
	*/
	public function count_comments_by_object($service=false)
	{
		if ($service === false) { # only host comments
			$sql = "SELECT COUNT(*) as cnt, host_name as obj_name FROM ".self::TABLE_NAME." WHERE ".
			"service_description = '' OR service_description is NULL ".
			"GROUP BY host_name ORDER BY host_name";
		} else { # service comments
			$sql = "SELECT count(*) as cnt, obj_name FROM (SELECT ".sql::concat('host_name', ';', 'service_description')." AS obj_name FROM ".self::TABLE_NAME." WHERE ".
			"service_description != '' OR service_description is not NULL) tmpname ".
			"GROUP BY obj_name ORDER BY obj_name";
		}

		$db = new Database();
		$result = $db->query($sql);
		if (!$result || count($result) == 0) {
			return false;
		}
		$data = false;
		foreach ($result as $row) {
			if ($row->cnt != 0) {
				$data[$row->obj_name] = $row->cnt;
			}
		}
		return $data;
	}

		/**
	*	Search through several fields for a specific value
	*/
	public function search($value=false, $limit=false)
	{
		if (empty($value)) return false;
		$db = new Database();
		$auth = new Nagios_auth_Model();
		$contact_id = (int)$auth->id;
		$limit_str = sql::limit_parse($limit);
		$join_host = false;
		$where_host = false;
		$join_svc = false;
		$where_svc = false;
		if (!$auth->view_hosts_root) {
			$join_host = "INNER JOIN contact_access ON host.id = contact_access.host ";
			$where_host = "AND contact_access.contact = ".$contact_id." ";
			$join_svc = "INNER JOIN contact_access ON service.id = contact_access.service ";
			$where_svc = "AND contact_access.contact = ".$contact_id." ".
				"AND service.host_name = host.host_name ";
		}
		if (is_array($value) && !empty($value)) {
			$query = false;
			$sql = false;
			foreach ($value as $val) {
				$val = '%'.$val.'%';
				$query[] = "SELECT c.id FROM ".self::TABLE_NAME." c ".
				" INNER JOIN host on host.host_name = c.host_name ".$join_host.
				" WHERE (LCASE(comment_data) LIKE LCASE(".$db->escape($val).") OR ".
				"LCASE(c.host_name) LIKE LCASE(".$this->db->escape($val).") ) ".
				" AND c.service_description IS NULL ".$where_host.
				" UNION ".
				"SELECT c.id FROM ".self::TABLE_NAME." c ".
				"INNER JOIN host on host.host_name = c.host_name ".
				"INNER JOIN service ON service.service_description = c.service_description ".$join_svc.
				" WHERE (LCASE(comment_data) LIKE LCASE(".$db->escape($val).") OR ".
				"LCASE(c.host_name) LIKE LCASE(".$this->db->escape($val).") OR ".
				"LCASE(c.service_description) LIKE LCASE(".$db->escape($val).") ) ".
				" AND c.service_description IS NOT NULL ".
				"AND service.host_name = host.host_name ".$where_svc;
			}
			if (!empty($query)) {
				$sql = 'SELECT * FROM '.self::TABLE_NAME.' WHERE id IN ('.implode(' UNION ', $query).') ORDER BY host_name, service_description, entry_time '.$limit_str;
			}
		} else {
			$value = '%'.$value.'%';
			$sql = "(SELECT c.* FROM ".self::TABLE_NAME." c ".
				" INNER JOIN host on host.host_name = c.host_name ".$join_host.
				"WHERE (LCASE(comment_data) LIKE LCASE(".$db->escape($value).") OR ".
				"LCASE(c.host_name) LIKE LCASE(".$this->db->escape($value).") )".
				" AND c.service_description IS NULL ".$where_host.
				") UNION ALL (".
				"SELECT c.* FROM ".self::TABLE_NAME." c ".
				"INNER JOIN host on host.host_name = c.host_name ".
				"INNER JOIN service ON service.service_description = c.service_description ".$join_svc.
				"WHERE (LCASE(comment_data) LIKE LCASE(".$db->escape($value).") OR ".
				"LCASE(c.host_name) LIKE LCASE(".$this->db->escape($value).") OR ".
				"LCASE(c.service_description) LIKE LCASE(".$db->escape($value).") ) ".
				"AND c.service_description IS NOT NULL ".
				"AND service.host_name = host.host_name ".$where_svc." )".$limit_str;
		}
		$obj_info = $db->query($sql);
		return $obj_info;
	}

	/**
	*	Fetch comment info filtered on specific field and value
	*/
	public function get_where($field=false, $value=false, $limit=false)
	{
		if (empty($field) || empty($value)) {
			return false;
		}
		$db = new Database();
		$auth = new Nagios_auth_Model();
		$field = trim($field);
		$value = trim($value);
		$contact_id = (int)$auth->id;
		$limit_str = sql::limit_parse($limit);
		$join_host = false;
		$where_host = false;
		$join_svc = false;
		$where_svc = false;
		if (!$auth->view_hosts_root) {
			$join_host = "INNER JOIN contact_access ON host.id = contact_access.host ";
			$where_host = "AND contact_access.contact = ".$contact_id;
			$join_svc = "INNER JOIN contact_access ON service.id = contact_access.service ";
			$where_svc = "AND contact_access.contact = ".$contact_id." AND service.host_name = host.host_name ";;
		}

		$limit_str = sql::limit_parse($limit);
		$value = '%' . $value . '%';
		$sql = "(SELECT c.* FROM ".self::TABLE_NAME." c ".
			" INNER JOIN host on host.host_name = c.host_name ".$join_host.
			"WHERE LCASE(".$field.") LIKE LCASE(".$db->escape($value).")".
			" AND c.service_description IS NULL ".$where_host.
			") UNION ALL (".
			"SELECT c.* FROM ".self::TABLE_NAME." c ".
			"INNER JOIN host on host.host_name = c.host_name ".
			"INNER JOIN service ON service.service_description = c.service_description ".$join_svc.
			"WHERE LCASE(".$field.") LIKE LCASE(".$db->escape($value).") ".
			"AND c.service_description IS NOT NULL ".
			"AND service.host_name = host.host_name ".$where_svc." )".$limit_str;
		$obj_info = $this->db->query($sql);
		return count($obj_info) > 0 ? $obj_info : false;
	}

}
