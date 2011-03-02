<?php

class Outages_Model extends Model
{
	const SERVICE_SEVERITY_DIVISOR = 4;
	private $auth = false;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

	/**
	 * determine what hosts are causing network outages
	 */
	public function fetch_outage_data()
	{
		/* user must be authorized for all hosts in order to see outages */
		if(!$this->auth->view_hosts_root)
			return;

		$status = new Current_status_Model();
		$status->find_hosts_causing_outages();
		$outages = false;

		$affected_hosts = $status->affected_hosts;
		$unreachable_hosts = $status->unreachable_hosts;
		$children_services = $status->children_services;
		if (!empty($status->hostoutage_list)) {
			# loop over hosts causing outages
			foreach ($status->hostoutage_list as $outage_host) {
				# fetch status of unreachable host
				$host_model = new Host_Model();
				$host_model->set_host_list($outage_host);
				$host_model->show_services = true;

				$host_data = $host_model->get_host_status();
				$services = false;
				foreach ($host_data as $row) {
					if (!isset($outages[$outage_host]['current_state'])) {
						$outages[$outage_host]['current_state'] = $row->host_state;
					}
					if (!isset($outages[$outage_host]['duration'])) {
						$outages[$outage_host]['duration'] = (int)$row->duration;
					}
					$services[] = $row->service_description;
				}

				$outages[$outage_host]['affected_hosts'] = $affected_hosts[$outage_host] +1;
				if (isset($unreachable_hosts[$outage_host]) && !empty($unreachable_hosts[$outage_host])) {
					foreach ($unreachable_hosts[$outage_host] as $host_id => $host_name) {
						if (!isset($outages[$outage_host]['affected_services'])) {
							$outages[$outage_host]['affected_services'] = 0;
						}

						$outages[$outage_host]['affected_services'] += $children_services[$host_id];
					}
				}

				if (!isset($outages[$outage_host]['affected_services'])) {
					$outages[$outage_host]['affected_services'] = 0;
				}

				# add services for the host causing the outage
				$outages[$outage_host]['affected_services'] += sizeof($services);

				# calculate severity
				if (!isset($outages[$outage_host]['severity'])) {
					$outages[$outage_host]['severity'] = 0;
				}

				$outages[$outage_host]['severity'] += sprintf('%d', ($outages[$outage_host]['affected_hosts'] + ($outages[$outage_host]['affected_services']/self::SERVICE_SEVERITY_DIVISOR)));
				$comment_data = Comment_Model::count_comments($outage_host);
				if (!isset($outages[$outage_host]['comments'])) {
					$outages[$outage_host]['comments'] = 0;
				}
				$outages[$outage_host]['comments'] += count($comment_data);
			}
		}

		$return = false;
		foreach ($status->unreachable_hosts as $host => $data) {
			if (!empty($data)) {
				if (!isset($outages[$host]['current_state'])) {
					$hostinfo = Host_Model::get_where('host_name', $host, false, true);
					if (count($hostinfo)) {
						$hostinfo = $hostinfo->current();
						$outages[$host]['current_state'] = $hostinfo->current_state;
					} else {
						$outages[$host]['current_state'] = Current_status_Model::HOST_UNREACHABLE;
					}
				}
				if (!isset($outages[$host]['duration'])) $outages[$host]['duration'] = 0;
				$return[$host] = $outages[$host];
			}
		}
		return $return;
	}
}
