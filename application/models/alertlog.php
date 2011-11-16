<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * model for DB based showlog
 */
class Alertlog_Model extends Model
{
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
				$sql_where[] = 'hard = 0 OR event_type < 700 OR event_type > 900';
			else
				$sql_where[] = 'hard = 1 OR event_type < 700 OR event_type > 900';
		}
		if (isset($options['host_state_options'])) {
			$cond = array();
			foreach ($options['host_state_options'] as $state => $ison) {
				if ((int)$ison)
					$cond[] = 'event_type state = '. $this->host_ccode_to_ncode[$state];
			}
			if (count($cond) == 3)
				;
			else if (!empty($cond))
				$sql_where[] = implode(' OR ', $cond);
			else
				$sql_where[] = "report_data.service_description != '' OR event_type < 700 OR event_type > 900";
		}
		if (isset($options['service_state_options'])) {
			$cond = array();
			foreach ($options['service_state_options'] as $state => $ison) {
				if ((int)$ison)
					$cond[] = 'state = '. $this->service_ccode_to_ncode[$state];
			}
			if (count($cond) == 4)
				;
			else if (!empty($cond))
				$sql_where[] = implode(' OR ', $cond);
			else
				$sql_where[] = "report_data.service_description = '' OR event_type < 700 OR event_type > 900";
		}

		if (isset($options['hide_downtime']) && $options['hide_downtime'])
			$sql_where[] = 'event_type >= 1200 OR event_type < 1100';
		if (isset($options['hide_process']) && $options['hide_process'])
			$sql_where[] = 'event_type >=200 OR event_Type < 100';

		if (isset($options['first']) && $options['first'])
			$sql_where[] = 'timestamp >= '.$db->escape($options['first']);;
		if (isset($options['last']) && $options['last'])
			$sql_where[] = 'timestamp <= '.$db->escape($options['last']);;

		if (!empty($sql_where))
			$sql_where = ' WHERE ('.implode(') AND (', $sql_where) . ')';
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
