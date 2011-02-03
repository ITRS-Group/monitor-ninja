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
		$svc_selection = empty($service) ? " AND (c.service_description='' OR c.service_description is null) "
			: " AND c.service_description=".$db->escape($service);

		# only use LIMIT when NOT counting
		$offset_limit = $count!==false || empty($num_per_page) ? "" : " LIMIT " . $num_per_page." OFFSET ".$offset;

		$host_query = $auth->authorized_host_query();
		if ($host_query === true) {
			# don't use auth_host fields etc
			$auth_host_alias = 'h';
			$auth_from = ', host '.$auth_host_alias;
			$auth_where = ' AND ' . $auth_host_alias . ".host_name = c.host_name";
		} else {
			$auth_host_alias = $host_query['host_field'];
			$auth_from = isset($host_query['from']) && !empty($host_query['from']) ? ' ,'.$host_query['from'] : '';
			$auth_where = ' AND '.sprintf($host_query['where'], "c.host_name");
		}

		$num_per_page = (int)$num_per_page;

		if (!$auth->view_hosts_root) {
			$sql = "SELECT * FROM ".self::TABLE_NAME." c WHERE id IN (SELECT DISTINCT c.id FROM ".self::TABLE_NAME." c, contact_access ca, contact, host h ".
				"WHERE contact.contact_name=".$db->escape(Auth::instance()->get_user()->username).
				" AND ca.contact=contact.id ".$svc_selection.
				" AND c.host_name=".$db->escape($host).
				"AND ca.host=h.id ".
				" AND ca.service is null) ";
		} else {
			$sql = "SELECT c.* FROM ".self::TABLE_NAME." c ".$auth_from." WHERE c.host_name=".$db->escape($host).
				$svc_selection.$auth_where;
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
				$sql = "SELECT DISTINCT c.* FROM ".self::TABLE_NAME." c, contact_access ca, contact, host h ".
					"WHERE contact.contact_name=".
					$db->escape(Auth::instance()->get_user()->username).
					" AND ca.contact=contact.id ".
					"AND c.host_name=h.host_name ".
					"AND (c.service_description='' OR c.service_description is null) ".
					"AND ca.host=h.id AND ca.service is null ";
			} else { # service comments
				if ($service_query !== true) {
					$sql = "SELECT DISTINCT c.* FROM ".self::TABLE_NAME." c, contact_access ca, contact, host h, service s
						WHERE contact.contact_name=".$db->escape(Auth::instance()->get_user()->username).
						" AND ca.contact=contact.id ".
						"AND c.host_name=h.host_name ".
						"AND s.host_name=c.host_name ".
						"AND ca.service=s.id ".
						"AND (c.service_description is NOT null) ";
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
}
