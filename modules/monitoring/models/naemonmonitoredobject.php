<?php

/**
 * Naemon Object Model
 **/
class NaemonMonitoredObject_Model extends NaemonObject_Model {

	/**
	 * Entry point for scheduling any downtime, not only retrospectively
	 * added ones (TODO change method name?)
	 */
	protected function schedule_downtime_retrospectively() {
		$args = func_get_args();
		$query = array_shift($args);
		// always submit the command for all downtimes that starts >= now
		$ret = call_user_func_array(array($this, 'submit_naemon_command'), $args);

		// spelled out $args for documentation reasons
		$naemon_command = $args[0];
		$start_time = $args[1];
		$end_time = $args[2];
		$fixed = $args[3];
		$trigger_id = $args[4];
		$duration = $args[5];
		$comment = $args[6];
		if($fixed && $start_time + 300 < time()) {
			/**
			 * if the starting time of a scheduled downtime is older than
			 * X seconds, it's considered to have been added
			 * retrospectively
			 */
			if($query) {
				// some action wants to apply the downtime to
				// other objects than itself
				$this->schedule_retrospectively_for_query($query, $start_time, $end_time, $comment);
			}
			if($this instanceof Host_Model || $this instanceof Service_Model) {
				$this->schedule_downtime_retrospectively_for_object($this, $start_time, $end_time, $comment);
			}
		}
		return $ret;
	}

	/**
	 * Insert downtime events to report database tables
	 *
	 * @param $obj NaemonObject_Model
	 * @param $start_time int UNIX timestamp
	 * @param $end_time int UNIX timestamp
	 * @param $comment string
	 */
	protected function schedule_downtime_retrospectively_for_object(NaemonObject_Model $obj, $start_time, $end_time, $comment) {
		$db = Database::instance();
		if($obj instanceof Service_Model) {
			$type = 'service';
			$parts = explode(";", $obj->get_key(), 2);
			$host_name = $db->escape($parts[0]);
			$service_description = $db->escape($parts[1]);
		} elseif($obj instanceof Host_Model) {
			$type = 'host';
			$host_name = $db->escape($obj->get_key());
			$service_description = "''";
		} else {
			return;
		}
		$u = $this->get_current_user();
		$start_msg = $db->escape(ucfirst($type)." has entered a period of retroactively added scheduled downtime, reported by '$u', reason: '$comment'");
		$end_msg = $db->escape(ucfirst($type)." has exited a period of retroactively added scheduled downtime, reported by '$u', reason: '$comment'");
		$db->query("INSERT INTO report_data(timestamp, event_type, host_name, service_description, downtime_depth, output) VALUES ($start_time, 1103, $host_name, $service_description, 1, $start_msg)");
		$db->query("INSERT INTO report_data_extras(timestamp, event_type, host_name, service_description, downtime_depth, output) VALUES ($start_time, 1103, $host_name, $service_description, 1, $start_msg)");
		$db->query("INSERT INTO report_data(timestamp, event_type, host_name, service_description, downtime_depth, output) VALUES ($end_time, 1104, $host_name, $service_description, 0, $end_msg)");
		$db->query("INSERT INTO report_data_extras(timestamp, event_type, host_name, service_description, downtime_depth, output) VALUES ($end_time, 1104, $host_name, $service_description, 0, $end_msg)");
	}

	/**
	 * Schedule downtime for past dates for all objects matching $query
	 *
	 * @param $query string like [hosts] name = "sven"
	 * @param $start_time int UNIX timestamp
	 * @param $end_time int UNIX timestamp
	 * @param $comment string
	 */
	protected function schedule_retrospectively_for_query($query, $start_time, $end_time, $comment) {
		try {
			$pool = ObjectPool_Model::get_by_query($query);
		} catch(LSFilterException $e) {
			op5log::instance('ninja')->log('error', 'Tried to schedule downtime, found bad query: '.$query);
			return;
		}
		if($pool instanceof ServicePool_Model) {
			$columns = array('host.name', 'description');
		} elseif($pool instanceof HostPool_Model) {
			$columns = array('name');
		} else {
			return;
		}
		foreach($pool->it($columns) as $obj) {
			$this->schedule_downtime_retrospectively_for_object($obj, $start_time, $end_time, $comment);
		}
	}
}
