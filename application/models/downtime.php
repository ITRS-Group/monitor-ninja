<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Fetch downtime info from downtime table
 */
class Downtime_Model extends Model
{

	/**
	*	Fetch current downtime information
	*/
	public function get_downtime_data($filter=3, $order_by='downtime_id')
	{
		$db = new Database();
		$filter = empty($filter) ? 3 : $filter;
		$auth = new Nagios_auth_Model();
		$host_query = $auth->authorized_host_query();
		if ($host_query === true) {
			# don't use auth_host fields etc
			$sql = "SELECT d.* FROM scheduled_downtime d WHERE d.downtime_type & " . $filter;
		} else {
			# hosts
			$auth_host_alias = $host_query['host_field'];
			$auth_from = ' ,'.$host_query['from'];
			$auth_where = ' WHERE '.sprintf($host_query['where'], "d.host_name");
			$sql = "SELECT d.* FROM scheduled_downtime d ".$auth_from.$auth_where." AND d.downtime_type & " . $filter;

			$query_contact = "SELECT d.* FROM scheduled_downtime d, host, ".
			"contact, host_contact ".
			"WHERE host.id = host_contact.host ".
			"AND host_contact.contact=contact.id ".
			"AND contact.contact_name=".$db->escape(Auth::instance()->get_user()->username).
			" AND d.host_name=host.host_name ".
			"AND d.downtime_type & " . $filter;

			# services
			$query_svc =
				'SELECT d.* FROM scheduled_downtime d, host, service, contact, contact_contactgroup, service_contactgroup ' .
				'WHERE service.id = service_contactgroup.service ' .
				'AND service_contactgroup.contactgroup = contact_contactgroup.contactgroup ' .
				'AND contact_contactgroup.contact = ' . (int)$auth->id." AND host.host_name=service.host_name ".
				"AND d.host_name=service.host_name AND d.service_description=service.service_description AND d.downtime_type & " . $filter;

			# contact <-> service_contact relation
			$query_svc_contact = "SELECT d.* FROM scheduled_downtime d, host h, service s, contact c, service_contact sc ".
				"WHERE s.id=sc.service AND c.id=sc.contact ".
				"AND sc.contact=c.id ".
				"AND c.contact_name=".$db->escape(Auth::instance()->get_user()->username).
				" AND h.host_name=s.host_name AND d.host_name=s.host_name AND d.service_description=s.service_description AND d.downtime_type &" . $filter;

			$sql = '(' . $sql . ') UNION (' . $query_contact . ') UNION (' . $query_svc . ') UNION (' . $query_svc_contact . ')';
		}
		$sql .= " ORDER BY ".$order_by;

		$result = $db->query($sql);
		return $result->count() ? $result: false;
	}

	/**
	*	Try to figure out if an object already has been scheduled
	*/
	public function check_if_scheduled($type=false, $name=false, $start_time=false, $duration=false)
	{
		if (empty($type) || empty($start_time) || empty($duration)) {
			return false;
		}

		$db = new Database();
		$sql = false;
		switch ($type) {
			case 'hosts':
				$sql = "SELECT * FROM scheduled_downtime WHERE host_name=".$db->escape($name).
					" AND start_time=".$start_time." AND downtime_type=2 ".
					"AND duration=".(int)$duration;
				break;
			case 'services':
				if (strstr($name, ';')) {
					$parts = explode(';', $name);
					$host = $parts[0];
					$service = $parts[1];
					$sql = "SELECT * FROM scheduled_downtime WHERE host_name=".$db->escape($host)." AND service_description=".$db->escape($service).
						" AND start_time=".$start_time." AND downtime_type=1 ".
						"AND duration=".(int)$duration;
				}
				break;
			case 'hostgroups':
				# find members
				$members = Host_Model::get_hosts_for_group($name);
				$found = false;
				$member_cnt = count($members);
				if (count($members)) {
					foreach ($members as $member) {
						if (self::check_if_scheduled('hosts', $member->host_name, $start_time, $duration)) {
							$found++;
						}
					}

					if ($found == $member_cnt) {
						return true;
					}
				}
				return false;
				break;

			case 'servicegroups':
				$members = Service_Model::get_services_for_group($name, 'service');
				$member_cnt = count($members);
				$found = false;
				if (count($members)) {
					foreach ($members as $member) {
						if (self::check_if_scheduled('services', $member->host_name.';'.$member->service_description, $start_time, $duration)) {
							$found++;
						}
					}

					if ($found == $member_cnt) {
						return true;
					}
				}
				return false;
		}

		if (!empty($sql)) {
			$res = $db->query($sql);
			return count($res) ? true : false;
		}
		return false;
	}

	/**
	*	Fetch saved downtime comments for host or service
	* 	This is usually used to display comments on extinfo page
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
		$svc_selection = empty($service) ? " AND (d.service_description='' OR d.service_description is null) "
			: " AND d.service_description=".$db->escape($service);

		# only use LIMIT when NOT counting
		$offset_limit = $count!==false || empty($num_per_page) ? "" : " LIMIT " . $num_per_page." OFFSET ".$offset;

		$host_query = $auth->authorized_host_query();
		if ($host_query === true) {
			# don't use auth_host fields etc
			$auth_host_alias = 'h';
			$auth_from = ', host '.$auth_host_alias;
			$auth_where = ' AND ' . $auth_host_alias . ".host_name = d.host_name";
		} else {
			$auth_host_alias = $host_query['host_field'];
			$auth_from = isset($host_query['from']) && !empty($host_query['from']) ? ' ,'.$host_query['from'] : '';
			$auth_where = ' AND '.sprintf($host_query['where'], "d.host_name");
		}

		$num_per_page = (int)$num_per_page;

		if (!$auth->view_hosts_root) {
			# this part is not necessary when authorized_for_all_hosts
			$service_query = $auth->authorized_service_query();

			$auth_where = !empty($host_query['where']) ? ' AND '.sprintf($host_query['where'], "d.host_name") : '';
			$sql = "SELECT d.* FROM scheduled_downtime d ".$auth_from." WHERE d.host_name=".$db->escape($host).
				$svc_selection.$auth_where;

			if ($service_query !== true) {
				$from = !empty($service_query['from']) ? ','.$service_query['from'] : '';
				# via service_contactgroup

				# @@@FIXME: handle direct relation contact -> {host,service}_contact
				$sql2 = "SELECT d.* FROM scheduled_downtime d ".$from." WHERE d.host_name=".$db->escape($host).
					$svc_selection.' AND '.$service_query['where'];
				$sql = '(' . $sql . ') UNION (' . $sql2 . ')';
			} else {
				$sql = "SELECT d.* FROM scheduled_downtime d WHERE d.host_name=".$db->escape($host).$svc_selection;
			}
		} else {
			$sql = "SELECT d.* FROM scheduled_downtime d ".$auth_from." WHERE d.host_name=".$db->escape($host).
				$svc_selection.$auth_where;
		}

		$sql .= " ORDER BY d.entry_time, d.host_name ".$offset_limit;

		$result = $db->query($sql);
		if ($count !== false) {
			return $result ? count($result) : 0;
		}
		return $result->count() ? $result->result(): false;
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
		$svc_selection = empty($service) ? " AND (d.service_description='' OR d.service_description is null) "
			: " AND d.service_description!='' ";

		# only use LIMIT when NOT counting
		$offset_limit = $count!==false ? "" : " LIMIT " . $num_per_page." OFFSET ".$offset;

		if ($host_query === true) {
			# don't use auth_host fields etc since
			# user is authenticated_for_all_hosts
			$auth_host_alias = 'h';
			$auth_from = ', host '.$auth_host_alias;
			$auth_where = ' AND '.$auth_host_alias . ".host_name = d.host_name";
			$sql = "SELECT d.* FROM scheduled_downtime d ".$auth_from." WHERE".
				" d.host_name!='' ".$svc_selection.$auth_where;
		} else {
			# we only make this check if user isn't authorized_for_all_hosts as above
			$service_query = $auth->authorized_service_query();

			$auth_host_alias = $host_query['host_field'];
			$auth_from = ' ,'.$host_query['from'];
			$auth_where = !empty($host_query['where']) ? ' AND '.sprintf($host_query['where'], "d.host_name") : '';

			if (!$service) { # host comments
				# comments via host_contactgroup
				$sql = "SELECT d.* FROM scheduled_downtime d ".$auth_from." WHERE".
					" d.host_name!='' ".$svc_selection.$auth_where;

				# comments via host_contact
				$from = "FROM scheduled_downtime d, host auth_host, contact auth_contact, host_contact auth_host_contact";
				# via host_contact
				$sql2 = "SELECT d.* ".$from." WHERE".
					" d.host_name!='' ".$svc_selection." AND auth_contact.contact_name=".
					$db->escape(Auth::instance()->get_user()->username).
					" AND auth_host_contact.contact=auth_contact.id ".
					"AND auth_host.id=auth_host_contact.host ".
					"AND auth_host.host_name=d.host_name";
				$sql = '(' . $sql . ') UNION (' . $sql2 . ') ';

			} else { # service comments
				if ($service_query !== true) {

					# comments via service_contactgroup
					$from = ','.$service_query['from'];
					$sql = "SELECT d.* FROM scheduled_downtime d".$from." WHERE ".
						"(d.service_description!='' AND d.service_description is NOT null) AND ".
						$service_query['where']." AND d.service_description=".$service_query['service_field'].".service_description ".
						"AND d.host_name=".$service_query['service_field'].".host_name";

					# comments via service_contact
					$from = "FROM scheduled_downtime d, host auth_host, contact auth_contact, service_contact auth_servicecontact, service auth_service ";
					$sql2 = "SELECT d.* ".$from." WHERE ".
						"(d.service_description!='' AND d.service_description is NOT null) ".
						"AND auth_service.id=auth_servicecontact.service ".
						"AND auth_servicecontact.contact=auth_contact.id ".
						"AND auth_contact.contact_name=".$db->escape(Auth::instance()->get_user()->username).
						" AND auth_service.host_name=auth_host.host_name ".
						"AND d.service_description=auth_service.service_description ".
						"AND d.host_name=auth_service.host_name";
					$sql = '(' . $sql . ') UNION (' . $sql2 . ') ';
				} else {
					$sql = "SELECT * FROM scheduled_downtime WHERE (service_description!='' OR service_description is NOT null) ";
				}
			}
		}

		$sql .= " ORDER BY d.entry_time, d.host_name ".$offset_limit;
		#echo $sql."<br />";

		$result = $db->query($sql);
		if ($count !== false) {
			return $result ? count($result) : 0;
		}

		return $result;
	}
}
