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
			$sql = "SELECT d.* FROM scheduled_downtime AS d WHERE d.downtime_type & " . $filter;
		} else {
			# hosts
			$auth_host_alias = $host_query['host_field'];
			$auth_from = ' ,'.$host_query['from'];
			$auth_where = ' WHERE '.sprintf($host_query['where'], "d.host_name");
			$sql = "SELECT d.* FROM scheduled_downtime AS d ".$auth_from.$auth_where." AND d.downtime_type & " . $filter;

			$query_contact = "SELECT d.* FROM scheduled_downtime AS d, host, ".
			"contact, host_contact ".
			"WHERE host.id = host_contact.host ".
			"AND host_contact.contact=contact.id ".
			"AND contact.contact_name=".$db->escape(Auth::instance()->get_user()->username).
			" AND d.host_name=host.host_name ".
			"AND d.downtime_type & " . $filter;

			# services
			$query_svc =
				'SELECT d.* FROM scheduled_downtime AS d, host, service, contact, contact_contactgroup, service_contactgroup ' .
				'WHERE service.id = service_contactgroup.service ' .
				'AND service_contactgroup.contactgroup = contact_contactgroup.contactgroup ' .
				'AND contact_contactgroup.contact = ' . (int)$auth->id." AND host.host_name=service.host_name ".
				"AND d.host_name=service.host_name AND d.service_description=service.service_description AND d.downtime_type & " . $filter;

			# contact <-> service_contact relation
			$query_svc_contact = "SELECT d.* FROM scheduled_downtime AS d, host h, service s, contact c, service_contact sc ".
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
}