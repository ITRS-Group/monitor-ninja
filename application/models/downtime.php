<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Fetch downtime info from downtime table
 */
class Downtime_Model extends Comment_Model
{
	const TABLE_NAME = 'scheduled_downtime';
	/**
	 * Fetch current downtime information
	 * 
	 * @param $filter Host, service or both
	 * @param $order_by Field name
	 * @param $generate_links_for_downtime_id If true, do extra work to find the downtime trigger/source
	 */
	public static function get_downtime_data($filter=3, $order_by='downtime_id', $generate_links_for_downtime_id = false)
	{
		$db = Database::instance();
		$filter = empty($filter) ? 3 : $filter;
		$bitary = db::bitmask_to_array($filter);
		$bits = '';
		foreach ($bitary as $bit => $is_set) {
			if ($is_set) {
				$bits .= ','.($bit+1);
			}
		}
		$bits = substr($bits, 1);
		$auth = Nagios_auth_Model::instance();
		if ($auth->view_hosts_root) {
			if($generate_links_for_downtime_id) {
				$query = "SELECT
						d.*,
						d2.host_name AS triggering_host,
						d2.service_description AS triggering_service
					FROM
						scheduled_downtime d
					LEFT JOIN
						scheduled_downtime d2
						ON
							d.triggered_by = d2.downtime_id
					WHERE
						d.downtime_type IN ($bits)";
			} else {
				$query = "SELECT d.* FROM scheduled_downtime d WHERE d.downtime_type IN ($bits)";
			}
		} else {
			# hosts
			$sql = "SELECT d.* ";
			if($generate_links_for_downtime_id) {
				$sql .= ", d2.host_name AS triggering_host, d2.service_description AS triggering_service ";
			}
			$sql .= "FROM scheduled_downtime d 
				INNER JOIN host ON host.host_name=d.host_name 
				INNER JOIN contact_access ON contact_access.host=host.id ";
			if($generate_links_for_downtime_id) {
				$sql .= "LEFT JOIN
						scheduled_downtime AS d2
						ON
							d.triggered_by = d2.downtime_id ";
			}
			$sql .= "WHERE contact_access.service IS NULL 
				AND d.service_description IS NULL 
				AND contact_access.contact=".$auth->id."
				AND d.downtime_type IN ($bits)";

			# services
			$query_svc = "SELECT d.* ";
			if($generate_links_for_downtime_id) {
				$query_svc .= ", d2.host_name AS triggering_host, d2.service_description AS triggering_service ";
			}
			$query_svc .=
				"FROM scheduled_downtime d ".
				"INNER JOIN host ON host.host_name=d.host_name ".
				"INNER JOIN service ON service.host_name=d.host_name ".
				"INNER JOIN contact_access ON contact_access.service=service.id ";
			if($generate_links_for_downtime_id) {
				$query_svc .= "LEFT JOIN
						scheduled_downtime AS d2
						ON
							d.triggered_by = d2.downtime_id ";
			}
			$query_svc .=
				"WHERE d.service_description=service.service_description ".
				"AND d.host_name=service.host_name ".
				"AND contact_access.contact=".$auth->id.
				" AND d.downtime_type IN (".$bits.")";

			switch ($bits) {
				case 2:
					$query = $sql." ORDER BY d.host_name";
					break;
				case 1:
					$query = $query_svc." ORDER BY d.host_name";
					break;
				default:
					$query = '(' . $sql . ') UNION ALL (' . $query_svc . ')';
			}
		}

		$result = $db->query($query);
		return $result;
	}

	/**
	*	Try to figure out if an object already has been scheduled
	*/
	public function check_if_scheduled($type=false, $name=false, $start_time=false, $duration=false)
	{
		if (empty($type) || empty($start_time) || empty($duration)) {
			return false;
		}

		$db = Database::instance();
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
				$membels = array();
				if (PHP_SAPI == 'cli') {
					# if using cli, get all hosts in host group
					$sql = 'SELECT * FROM host WHERE id IN (SELECT DISTINCT h.id ' .
						'FROM host h, hostgroup hg, host_hostgroup hhg ' .
						'WHERE hg.hostgroup_name = '.$db->escape($name) .
						' AND hhg.hostgroup = hg.id AND h.id = hhg.host)';
					$db  = Database::instance();
					$members = $db->query($sql);
				}
				else {
					$members = Host_Model::get_hosts_for_group($name);
				}
				$found = 0;
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
				$members = array();
				if (PHP_SAPI == 'cli') {
					# if using cli, get all hosts in host group
					$sql = 'SELECT * FROM host WHERE id IN (SELECT DISTINCT h.id ' .
						'FROM host h, hostgroup hg, host_hostgroup hhg ' .
						'WHERE hg.hostgroup_name = '.$db->escape($name) .
						' AND hhg.hostgroup = hg.id AND h.id = hhg.host)';
					$db  = Database::instance();
					$members = $db->query($sql);
				}
				else {
					$members = Service_Model::get_services_for_group($name, 'service');
				}
				$member_cnt = count($members);
				$found = 0;
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
