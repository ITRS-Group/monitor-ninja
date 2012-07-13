<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * model for DB based showlog
 */
class Alertlog_Model extends Model
{
	private static function combine() {
		$args = func_get_args();
		$function = array_shift($args);
		$res = false;
		foreach ($args as $arg) {
			if (!empty($arg))
				$res[] = $arg;
		}
		if (!empty($res))
			$res = '(' . implode(') '.$function.' (', $res) . ')';
		return $res;
	}

	private $host_ccode_to_ncode = array(
		'r' => 0,
		'd' => 1,
		'u' => 2,
	);
	private $service_ccode_to_ncode = array(
		'r' => 0,
		'w' => 1,
		'c' => 2,
		'u' => 3
	);
	/**
	 * Return all log entries matching the specified options
	 *
	 * @param $options Pretty much magical array of options to filter objects
	 * @param $limit The number of rows to get
	 * @param $offset The number of rows to skip before fetching
	 * @param $count Skip the whole row fetching, only return the number of lines
	 * @return If count is false, database object or false on error or empty. If count is true, line count
	 */
	function get_log_entries($options=false, $limit=false, $offset=0, $count=false)
	{
		$auth = Nagios_auth_Model::instance();
		$db = Database::instance();
		if ($count !== true)
			$sql = 'SELECT report_data.* FROM report_data';
		else
			$sql = 'SELECT count(1) AS cnt FROM report_data';
		$sql_join = false;

		$softorhard = false;
		$hostopts = false;
		$svcopts = false;
		$downtime = false;
		$process = false;
		$time_first = false;
		$time_last = false;
		$objsel = array();

		# Don't think this auth stuff makes that much sense - whaddabout services and system_information for restarts?
		# Also think it's gosh darn slow already, and would be unusable if done Right™, so I don't want to be the one
		# to break it.
		if (!$auth->view_hosts_root) {
			$sql_join['host'] = 'host.host_name = report_data.host_name';
			$sql_join['contact_access'] = 'contact_access.host=host.id AND contact_access.contact='.(int)$auth->id;
		}

		if (isset($options['hosts']) && !empty($options['hosts'])) {
			$host_cond = array();
			foreach ($options['hosts'] as $host)
				$host_cond[] = 'report_data.host_name = '.$db->escape($host);
			$objsel[] = implode(' OR ', $host_cond);
		}
		if (isset($options['services']) && !empty($options['services'])) {
			$svc_cond = array();
			foreach ($options['services'] as $service) {
				$parts = explode(';', $service);
				// an extra OR to allow a host's downtime to end up in service's alert log
				$svc_cond[] = '(report_data.host_name = '.$db->escape($parts[0]).' AND (report_data.service_description = '.$db->escape($parts[1]).' OR event_type > 900))';
			}
			$objsel[] = implode(' OR ', $svc_cond);
		}
		if (isset($options['hostgroups']) && !empty($options['hostgroups'])) {
			$hg_cond = array();
			foreach ($options['hostgroups'] as $hostgroup)
				$hg_cond[] = $db->escape($hostgroup);
			$objsel[] = 'service_description = \'\' AND host_name IN (SELECT host_name FROM host INNER JOIN host_hostgroup ON host.id = host_hostgroup.host INNER JOIN hostgroup ON hostgroup.id = host_hostgroup.hostgroup WHERE hostgroup_name IN ('.implode(', ', $hg_cond).'))';
		}
		if (isset($options['servicegroups']) && !empty($options['servicegroups'])) {
			$sg_cond = array();
			foreach ($options['servicegroups'] as $servicegroup)
				$sg_cond[] = $db->escape($servicegroup);

			$objsel[] = '(host_name, service_description) IN (SELECT host_name, service_description FROM service INNER JOIN service_servicegroup ON service.id = service_servicegroup.service INNER JOIN servicegroup ON servicegroup.id = service_servicegroup.servicegroup WHERE servicegroup_name IN ('.implode(', ', $sg_cond).'))';
		}

		if (isset($options['state_type']) && !empty($options['state_type'])) {
			if (isset($options['state_type']['soft']) && (int)$options['state_type']['soft'] && isset($options['state_type']['hard']) && (int)$options['state_type']['hard'])
				;
			else if (isset($options['state_type']['soft']) && (int)$options['state_type']['soft'])
				$softorhard = 'hard = 0';
			else
				$softorhard = 'hard = 1';
		}

		if (isset($options['host_state_options'])) {
			$cond = array();
			foreach ($options['host_state_options'] as $state => $ison) {
				if ((int)$ison)
					$cond[] = 'state = '. $this->host_ccode_to_ncode[$state];
			}
			if (!empty($cond)) {
				$hostopts = 'event_type = 801';
				if (count($cond) != 3)
					$hostopts .= ' AND ('.implode(' OR ', $cond).')';
			}
		}

		if (isset($options['service_state_options'])) {
			$cond = array();
			foreach ($options['service_state_options'] as $state => $ison) {
				if ((int)$ison)
					$cond[] = 'state = '. $this->service_ccode_to_ncode[$state];
			}
			if (!empty($cond)) {
				$svcopts = 'event_type = 701';
				if (count($cond) != 4)
					$svcopts .= ' AND ('.implode(' OR ', $cond).')';
			}
		}

		if (!isset($options['hide_downtime']) || !$options['hide_downtime'])
			$downtime = 'event_type < 1200 AND event_type > 1100';

		if (!isset($options['hide_process']) || !$options['hide_process'])
			$process = 'event_type < 200';

		if (isset($options['first']) && $options['first'])
			$time_first = 'timestamp >= '.$db->escape($options['first']);;
		if (isset($options['last']) && $options['last'])
			$time_last = 'timestamp <= '.$db->escape($options['last']);;

		$objsel = implode(') OR (', $objsel);

		$sql_where =
			self::combine('and',
				$time_first,
				$time_last,
				self::combine('or',
					$process,
					self::combine('and',
						$objsel,
						self::combine('or',
							$downtime,
							self::combine('and',
								$softorhard,
								self::combine('or',
									$hostopts,
									$svcopts))))));
		if (!empty($sql_join)) {
			$real_join = '';
			foreach ($sql_join as $key => $val)
				$real_join .= " INNER JOIN $key ON $val";
			$sql_join = $real_join;
		}
		$sql = $sql . $sql_join . ' WHERE ' . $sql_where;
		if ($count !== true) {
			$sql .= ' ORDER BY timestamp ';
			if (isset($options['parse_forward']) && $options['parse_forward'])
				$sql .= 'ASC';
			else
				$sql .= 'DESC';
			if ($limit !== false)
				$sql .= " LIMIT $limit OFFSET $offset";
		}

		$res = $db->query($sql);
		if ($count === true) {
			$cnt = $res->current();
			return $cnt->cnt;
		}
		if (!$res)
			return false;
		return $res;
	}
}
