<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * model for DB based showlog
 */
class Alertlog_Model extends Model
{
	static $bla = false;
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
	 * @param $options Pretty much magical array of options to filter objects
	 * @param $limit The number of rows to get
	 * @param $offset The number of rows to skip before fetching
	 * @param $count Skip the whole row fetching, only return the number of lines
	 * @return If count is false, database object or false on error or empty. If count is true, line count
	 */
	function get_log_entries($options=false, $limit=false, $offset=0, $count=false)
	{
		$auth = new Nagios_auth_Model();
		$db = Database::instance();
		if ($count !== true)
			$sql = 'SELECT report_data.* FROM report_data';
		else
			$sql = 'SELECT count(1) AS cnt FROM report_data';
		$sql_join = false;
		$sql_where = false;
		$sql_or_where = false;
		if (!$auth->view_hosts_root) {
			$sql_join['host'] = 'host.host_name = report_data.host_name';
			$sql_join['contact_access'] = 'contact_access.host=host.id';
			$sql_where[] = 'contact_access.contact='.(int)$auth->id;
		}
		if (isset($options['hosts']) && !empty($options['hosts'])) {
			$sql_join['host'] = 'host.host_name = report_data.host_name';
			$host_cond = array();
			foreach ($options['hosts'] as $host)
				$host_cond[] = 'host.host_name = '.$db->escape($host);
			$sql_where[] = implode(' OR ', $host_cond);
		}
		if (isset($options['services']) && !empty($options['services'])) {
			$sql_join['service'] = '(service.service_description = report_data.service_description OR (report_data.service_description=\'\' AND report_data.event_type > 1000)) AND service.host_name = report_data.host_name';
			$svc_cond = array();
			foreach ($options['services'] as $service) {
				$parts = explode(';', $service);
				$svc_cond[] = '(service.host_name = '.$db->escape($parts[0]).' AND service.service_description = '.$db->escape($parts[1]).')';
			}
			$sql_where[] = implode(' OR ', $svc_cond);
		}
		if (isset($options['hostgroups']) && !empty($options['hostgroups'])) {
			$sql_join['host'] = 'host.host_name = report_data.host_name';
			$sql_join['host_hostgroup'] = 'host.id = host_hostgroup.host';
			$sql_join['hostgroup'] = 'hostgroup.id = host_hostgroup.hostgroup';
			$hg_cond = array();
			foreach ($options['hostgroups'] as $hostgroup)
				$hg_cond[] = 'hostgroup.hostgroup_name = '.$db->escape($hostgroup);
			$sql_where[] = implode(' OR ', $hg_cond);
		}
		if (isset($options['servicegroups']) && !empty($options['servicegroups'])) {
			$sql_join['service'] = '(service.service_description = report_data.service_description OR (report_data.service_description=\'\' AND report_data.event_type > 900)) AND service.host_name = report_data.host_name';
			$sql_join['service_servicegroup'] = 'service.id = service_servicegroup.service';
			$sql_join['servicegroup'] = 'servicegroup.id = service_servicegroup.servicegroup';
			$hg_cond = array();
			foreach ($options['servicegroups'] as $servicegroup)
				$hg_cond[] = 'servicegroup.servicegroup_name = '.$db->escape($servicegroup);
			$sql_where[] = implode(' OR ', $hg_cond);
		}

		if (isset($options['state_type'])) {
			if (isset($options['state_type']['soft']) && (int)$options['state_type']['soft'] && isset($options['state_type']['hard']) && (int)$options['state_type']['hard'])
				;
			else if (isset($options['state_type']['soft']) && (int)$options['state_type']['soft'])
				$sql_where[] = 'hard = 0 OR (event_type != 701 AND event_type != 801)';
			else
				$sql_where[] = 'hard = 1 OR (event_type != 701 AND event_type != 801)';
		}

		// keep track of if we're including or excluding objects
		// based on simple checkbox logic™
		$service_host_and_or = 'OR';

		$host_state_wheres = array('event_type=801');
		if (isset($options['host_state_options'])) {
			$cond = array();
			foreach ($options['host_state_options'] as $state => $ison) {
				if ((int)$ison)
					$cond[] = 'state = '. $this->host_ccode_to_ncode[$state];
			}
			if (count($cond) == 3) {
				// all cases are included
				$host_state_wheres = array();
			} else if (!empty($cond)) {
				$host_state_wheres[] = implode(' OR ', $cond);
			} else {
				# only check non host events when checkboxes are unchecked
				$host_state_wheres = array("event_type != 801");
				$service_host_and_or = 'AND';
			}
		}
		$service_state_wheres = array('event_type=701');
		if (isset($options['service_state_options'])) {
			$cond = array();
			foreach ($options['service_state_options'] as $state => $ison) {
				if ((int)$ison)
					$cond[] = 'state = '. $this->service_ccode_to_ncode[$state];
			}
			if (count($cond) == 4) {
				// all cases are included
				$service_state_wheres = array();
			} else if (!empty($cond)) {
				$service_state_wheres[] = implode(' OR ', $cond);
			} else {
				# only check non service events when checkboxes are unchecked
				$service_state_wheres = array("event_type != 701");
				$service_host_and_or = 'AND';
			}
		}
		if($host_state_wheres && $service_state_wheres) {
			$sql_where[] = ' ((('.implode(') AND (', $host_state_wheres) . ")) $service_host_and_or ((".implode(') AND (', $service_state_wheres) . ')))';
		} elseif($host_state_wheres) {
			$sql_where = $sql_where ? $sql_where : array();
			$sql_where = array_merge($sql_where, $host_state_wheres);
		} elseif($service_state_wheres) {
			$sql_where = $sql_where ? $sql_where : array();
			$sql_where = array_merge($sql_where, $service_state_wheres);
		}

		if (!isset($options['hide_downtime']) || !$options['hide_downtime']) {
			$extra_and_or_where = '(event_type < 1200 AND event_type > 1100)';
			if (isset($options['hide_process']) && $options['hide_process']) {
				$sql_where[] = '(event_type < 1200 OR event_type > 1100)';
			} else {
				$extra_and_or_where = '('.$extra_and_or_where.' OR event_type < 200 AND event_type >= 100)';
			}
			$sql_or_where[] = $extra_and_or_where;
		} elseif(!isset($options['hide_process']) || !$options['hide_process']) {
			$sql_or_where[] = '(event_type < 200 AND event_type >= 100)';
		} elseif(isset($options['hide_process']) && $options['hide_process']) {
			$sql_where[] = '(event_type > 1200 OR event_type < 1100)';
		}

		if (isset($options['first']) && $options['first'])
			$sql_where[] = 'timestamp >= '.$db->escape($options['first']);;
		if (isset($options['last']) && $options['last'])
			$sql_where[] = 'timestamp <= '.$db->escape($options['last']);;

		if (!empty($sql_where) || !empty($sql_or_where)) {
			if($sql_where) {
				$sql_where = ' WHERE ('.implode(') AND (', $sql_where) . ')';
				if($sql_or_where) {
					$sql_where .= 'OR ('.implode(') OR (', $sql_or_where).')';
				}
			} elseif($sql_or_where) {
				$sql_where = ' WHERE ('.implode(') OR (', $sql_or_where) . ')';
			}
		}
		if (!empty($sql_join)) {
			$real_join = '';
			foreach ($sql_join as $key => $val)
				$real_join .= " INNER JOIN $key ON $val";
			$sql_join = $real_join;
		}
		$sql = $sql . $sql_join . $sql_where . ' ORDER BY timestamp ';
		if (isset($options['parse_forward']) && $options['parse_forward'])
			$sql .= 'ASC';
		else
			$sql .= 'DESC';
		if ($limit !== false && $count !== true)
			$sql .= " LIMIT $limit OFFSET $offset";

		if(!self::$bla) {
			echo "<pre>";
			self::$bla = true;
			var_dump($options);
		}
		var_dump($sql);

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
