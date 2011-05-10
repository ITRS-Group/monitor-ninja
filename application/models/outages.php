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
	public function fetch_outage_data(&$current_status_model=false)
	{
		/* user must be authorized for all hosts in order to see outages */
		if(!$this->auth->view_hosts_root)
			return;

		if (is_object($current_status_model)) {
			$status = $current_status_model;
			if (!$status->outage_data_present) {
				$status->find_hosts_causing_outages();
			}
		} else {
			$status = new Current_status_Model();
			$status->find_hosts_causing_outages();
		}
		$outages = false;

		$affected_hosts = $status->affected_hosts;
		$unreachable_hosts = false;
		$children_services = $status->children_services;

		# we're only interested in hosts with children so let's filter them out
		if (!empty($status->unreachable_hosts)) {
			foreach ($status->unreachable_hosts as $host => $children) {
				if (!empty($children)) {
					$unreachable_hosts[$host] = $children;
				}
			}
		} else {
			# nothing to display
			return false;
		}

		if (!empty($unreachable_hosts)) {
			# loop over hosts causing outages
			foreach ($unreachable_hosts as $outage_host => $children) {
				# fetch status of unreachable host

				$hosts = array_values($children);
				$hosts[] = $outage_host;

				$host_model = new Host_Model();
				$host_model->set_host_list($hosts);
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

				$outages[$outage_host]['affected_services'] = 0;
				foreach ($children as $host_id => $host_name) {
					$outages[$outage_host]['affected_services'] += $children_services[$host_id];
				}

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

		/**
		 * 	Remove hosts that is already calculated by being
		 * 	a child to another host to prevent them from being
		 * 	displayed twice.
		 */
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

				foreach ($data as $h) {
					if (in_array($h, $hosts) && isset($return[$h])) {
						unset($return[$h]);
					}
				}

				$return[$host] = $outages[$host];
			}
		}

		return $return;
	}
}
