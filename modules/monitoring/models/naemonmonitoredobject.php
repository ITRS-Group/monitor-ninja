<?php

/**
 * Naemon Object Model
 **/
class NaemonMonitoredObject_Model extends NaemonObject_Model {

	/**
	 * Insert downtime events to report database tables
	 *
	 * @param $host_name Name of the host
	 * @param $service_description Service description, or false
	 * @param $start_time int UNIX timestamp
	 * @param $end_time int UNIX timestamp
	 * @param $comment string
	 */
	protected function schedule_downtime_retrospectively($host_name, $service_description, $start_time, $end_time, $comment) {

		/* Make sure it's really are integers... not only integer strings */
		$start_time = (int)$start_time;
		$end_time = (int)$end_time;

		/* Only schedule if downtime is in the past, but have a grace time */
		if ($start_time > time() - 300) {
			return;
		}

		$type = empty($service_description) ? 'Host' : 'Service';
		$service_description = $service_description === false ? '' : $service_description;

		$u = $this->get_current_user();
		$db = Database::instance();

		$host_name_e = $db->escape($host_name);
		$service_description_e = $db->escape($service_description);

		$start_msg_e = $db->escape("$type has entered a period of retroactively added scheduled downtime, reported by '$u', reason: '$comment'");
		$end_msg_e = $db->escape("$type has exited a period of retroactively added scheduled downtime, reported by '$u', reason: '$comment'");
		$db->query("INSERT INTO report_data(timestamp, event_type, host_name, service_description, downtime_depth, output) VALUES ($start_time, 1103, $host_name_e, $service_description_e, 1, $start_msg_e)");
		$db->query("INSERT INTO report_data_extras(timestamp, event_type, host_name, service_description, downtime_depth, output) VALUES ($start_time, 1103, $host_name_e, $service_description_e, 1, $start_msg_e)");
		$db->query("INSERT INTO report_data(timestamp, event_type, host_name, service_description, downtime_depth, output) VALUES ($end_time, 1104, $host_name_e, $service_description_e, 0, $end_msg_e)");
		$db->query("INSERT INTO report_data_extras(timestamp, event_type, host_name, service_description, downtime_depth, output) VALUES ($end_time, 1104, $host_name_e, $service_description_e, 0, $end_msg_e)");
	}
}
