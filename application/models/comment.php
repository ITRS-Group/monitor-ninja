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
		$offset_limit = $count!==false ? "" : " LIMIT " . $offset.", ".$num_per_page;

		$host_query = $auth->authorized_host_query();
		if ($host_query === true) {
			# don't use auth_host fields etc
			$auth_host_alias = 'h';
			$auth_from = ', host AS '.$auth_host_alias;
			$auth_where = ' AND ' . $auth_host_alias . ".host_name = c.host_name";
		} else {
			$auth_host_alias = $host_query['host_field'];
			$auth_from = isset($host_query['from']) && !empty($host_query['from']) ? ' ,'.$host_query['from'] : '';
			$auth_where = ' AND '.sprintf($host_query['where'], "c.host_name");
		}

		$num_per_page = (int)$num_per_page;

		if (!$auth->view_hosts_root) {
			# this part is not necessary when authorized_for_all_hosts
			$service_query = $auth->authorized_service_query();

			$auth_where = !empty($host_query['where']) ? ' AND '.sprintf($host_query['where'], "c.host_name") : '';
			$sql = "SELECT c.* FROM comment c ".$auth_from." WHERE c.host_name=".$db->escape($host).
				$svc_selection.$auth_where;

			if ($service_query !== true) {
				$from = !empty($service_query['from']) ? ','.$service_query['from'] : '';
				# via service_contactgroup

				# @@@FIXME: handle direct relation contact -> {host,service}_contact
				$sql2 = "SELECT c.* FROM comment c ".$from." WHERE c.host_name=".$db->escape($host).
					$svc_selection.' AND '.$service_query['where'];
				$sql = '(' . $sql . ') UNION (' . $sql2 . ')';
			}
		} else {
			$sql = "SELECT c.* FROM comment c ".$auth_from." WHERE c.host_name=".$db->escape($host).
				$svc_selection.$auth_where;
		}

		$sql .= " ORDER BY host_name ".$offset_limit;

		$result = $db->query($sql);
		if ($count !== false) {
			return $result ? count($result) : 0;
		}
		return $result->count() ? $result->result(): false;
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
		$svc_selection = empty($service) ? " AND (c.service_description='' OR c.service_description is null) "
			: " AND c.service_description!='' ";

		# only use LIMIT when NOT counting
		$offset_limit = $count!==false ? "" : " LIMIT " . $offset.", ".$num_per_page;

		if ($host_query === true) {
			# don't use auth_host fields etc since
			# user is authenticated_for_all_hosts
			$auth_host_alias = 'h';
			$auth_from = ', host AS '.$auth_host_alias;
			$auth_where = ' AND '.$auth_host_alias . ".host_name = c.host_name";
			$sql = "SELECT c.* FROM comment c ".$auth_from." WHERE".
				" c.host_name!='' ".$svc_selection.$auth_where;
		} else {
			# we only make this check if user isn't authorized_for_all_hosts as above
			$service_query = $auth->authorized_service_query();

			$auth_host_alias = $host_query['host_field'];
			$auth_from = ' ,'.$host_query['from'];
			$auth_where = !empty($host_query['where']) ? ' AND '.sprintf($host_query['where'], "c.host_name") : '';
			$sql = "SELECT c.* FROM comment c ".$auth_from." WHERE".
				" c.host_name!='' ".$svc_selection.$auth_where;

			if ($service_query !== true) {
				$from = "FROM comment c, host AS auth_host, contact AS auth_contact, host_contact AS auth_host_contact";
				# via host_contact
				$sql2 = "SELECT c.* ".$from." WHERE".
					" c.host_name!='' ".$svc_selection." AND auth_contact.contact_name=".
					$db->escape(Auth::instance()->get_user()->username).
					" AND auth_host_contact.contact=auth_contact.id ".
					"AND auth_host.host_name=c.host_name";
				$sql = '(' . $sql . ') UNION (' . $sql2 . ')';
			}
		}

		$sql .= " ORDER BY host_name ".$offset_limit;
		#echo $sql."<br />";

		$result = $db->query($sql);
		if ($count !== false) {
			return $result ? count($result) : 0;
		}

		return $result->result();
	}

	/**
	*	Wrapper method to fetch a count of all service- or host comments
	*/
	public function count_all_comments($host=false, $service=false)
	{
		return self::fetch_all_comments($host, $service, false, false, true);
	}
}
