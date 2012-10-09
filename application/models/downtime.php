<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Fetch downtime info from downtime table
 */
class Downtime_Model extends Comment_Model
{
	/* This is a livestatus table name... */
	const TABLE_NAME = 'downtimes'; /**< The name of the downtimes table */
	
	/**
	 * Fetch current downtime information
	 * 
	 * @param $filter Host, service or both
	 * @param $order_by Field name
	 * @param $generate_links_for_downtime_id If true, do extra work to find the downtime trigger/source
	 */
	public static function get_downtime_data($filter=3, $order_by=array('id'=>'ASC'), $generate_links_for_downtime_id = false)
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
		$ls_filter = '';
		if ($filter == nagstat::HOST_DOWNTIME)
			$ls_filter = 'is_service = 0';
		else if ($filter == nagstat::SERVICE_DOWNTIME)
			$ls_filter = 'is_service = 1';
		$ls = Livestatus::instance();
		$res = $ls->getDowntimes(array('filter' => $ls_filter, 'order' => $order_by));
		if ($generate_links_for_downtime_id) {
			foreach ($res as &$row) {
				$out = $ls->getDowntimes(array('filter' => 'id = '.$row['id'], 'columns' => array('host_name', 'service_description')));
				$row['trigger'] = $out[0];
			}
		}
		return $res;
	}

	/**
	*	Try to figure out if an object already has been scheduled
	*/
	public static function check_if_scheduled($type=false, $name=false, $start_time=false, $duration=false)
	{
		if (empty($type) || empty($start_time) || empty($duration)) {
			return false;
		}

		$ls = Livestatus::instance();
		switch ($type) {
			case 'hosts':
				$res = $ls->getDowntimes(array('filter' => 'is_service = 0 and host_name = '.$name.' and start_time = '.$start_time.' and end_time = '.$end_time.' and duration = '.$duration));
				break;
			case 'services':
				if (!strstr($name, ';'))
					return false;

				$parts = explode(';', $name);
				$host = $parts[0];
				$service = $parts[1];
				$res = $ls->getDowntimes(array('filter' => 'is_service = 0 and host_name = '.$host.' and service_description = '.$service.' and start_time = '.$start_time.' and end_time = '.$end_time.' and duration = '.$duration));
				break;
			case 'hostgroups':
				$hosts = $ls->getHosts(array('columns' => array('host_name'), 'filter' => 'groups >= '.$name));
				$in_dtime = $ls->getDowntimes(array('columns' => array('host_name'), 'filter' => 'is_service = 0 and host_groups >= '.$name.' and start_time = '.$start_time.' and end_time = '.$end_time.' and duration = '.$duration));
				return (count($hosts) == count($in_dtime));
				break;

			case 'servicegroups':
				$services = $ls->getServices(array('columns' => array('description'), 'filter' => 'groups >= '.$name));
				$in_dtime = $ls->getDowntimes(array('filter' => 'is_service = 0 and service_groups >= '.$name.' and start_time = '.$start_time.' and end_time = '.$end_time.' and duration = '.$duration));
				return (count($hosts) == count($in_dtime));
				break;
		}

		return (!empty($res));
	}
}
