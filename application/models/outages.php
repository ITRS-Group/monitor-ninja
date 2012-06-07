<?php

/**
 * usort callback to order outages by severity
 */
function sort_outages ($a, $b) {
	// shouldn't happen, but let's not crash...
	if (!isset($a['severity']))
		return -1;
	if (!isset($b['severity']))
		return 1;

	if ($a['severity'] == $b['severity'])
		return 0;
	return ($a['severity'] < $b['severity']) ? 1 : -1;
}

/**
 * Model for outage data
 */
class Outages_Model extends Model
{
	const SERVICE_SEVERITY_DIVISOR = 4; /**< Magical constant that tells us how many times less interesting a service is compared to a host */
	private $auth = false;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

	/**
	* determine what hosts are causing network outages
	*
	* @returns [hostname => [outage information]]
	*/
	public function fetch_outage_data()
	{
		/* user must be authorized for all hosts in order to see outages */
		if(!$this->auth->view_hosts_root)
			return;

		$status = Current_status_Model::instance();
		$status->find_hosts_causing_outages();
		$outages = array();

		if (empty($status->unreachable_hosts))
			# nothing to display
			return false;

		$host_model = new Host_Model();
		$host_model->set_host_list(array_keys($status->unreachable_hosts));
		$host_model->show_services = false;
		$host_data = $host_model->get_host_status();

		# loop over hosts causing outages
		foreach ($host_data as $row) {
			$services = false;
			if (!$status->unreachable_hosts[$row->host_name])
				continue;

			$outages[$row->host_name]['current_state'] = $row->current_state;
			$outages[$row->host_name]['duration'] = (int)$row->duration;
			$outages[$row->host_name]['affected_hosts'] = $status->affected_hosts[$row->host_name];

			$outages[$row->host_name]['affected_services'] = $status->affected_services[$row->host_name];

			# calculate severity
			if (!isset($outages[$row->host_name]['severity'])) {
				$outages[$row->host_name]['severity'] = 0;
			}

			$outages[$row->host_name]['severity'] += sprintf('%d', ($outages[$row->host_name]['affected_hosts'] + ($outages[$row->host_name]['affected_services']/self::SERVICE_SEVERITY_DIVISOR)));
			$comment_data = Comment_Model::count_comments($row->host_name);
			if (!isset($outages[$row->host_name]['comments'])) {
				$outages[$row->host_name]['comments'] = 0;
			}
			$outages[$row->host_name]['comments'] += count($comment_data);
		}

		uasort($outages, 'sort_outages');

		return $outages;
	}
}
