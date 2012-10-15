<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle comments for hosts and services
 */
class Scheduling_queue_Model extends Model {

	/**
	 * Fetch scheduled events
	 *
	 * @param $sort_order string
	 * @param $sort_column string
	 * @param $service_filter string = null
	 * @param $host_filter string = null
	 * @return Database result object or false if none if $count is false or unset, otherwise the number of result rows
	 */
	public function show_scheduling_queue($sort_order, $sort_column, $service_filter = null, $host_filter = null)
	{
		$result = array();
		$ls = Livestatus::instance();

		$service_options = array(
			'columns' => array(
				'host_name',
				'description',
				'last_check',
				'next_check',
				'check_type', // 0 == active, 1 == passive
				'active_checks_enabled'
			),
			'has_been_checked' => 0
		);
		// try to adjust service sorting to host sorting
		if(in_array($sort_column, $service_options['columns'])) {
			$service_options['order'][$sort_column] = $sort_order;
		} elseif($sort_column == 'host') {
			$service_options['order']['host_name'] = $sort_order;
		}
		if($service_filter) {
			$service_options['filter']['description'] = array("~~" => ".*$service_filter.*");
		}
		if($host_filter) {
			$service_options['filter']['host_name'] = array("~~" => ".*$host_filter.*");
		}
		$service_checks = $ls->getServices($service_options);

		$host_options = $service_options;
		$host_options['columns'] = array(
			'name',
			'last_check',
			'next_check',
			'check_type', // 0 == active, 1 == passive
			'active_checks_enabled'
		);
		// try to adjust host sorting to service sorting
		if(isset($service_options['order']['host_name']) || isset($service_options['order']['description'])) {
			// let's just sort on host name, I'll take the blame
			$host_options['order'] = array('name' => $sort_order);
		}
		if($host_filter) {
			$host_options['filter'] = array(
				'host_name' => array("~~" => ".*$host_filter.*")
			);
		} else {
			unset($host_options['filter']);
		}
		$host_checks = $ls->getHosts($host_options);
		if(!$host_checks && !$service_checks) {
			return array();
		}
		return array('host' => $host_checks, 'service' => $service_checks);
	}
}
