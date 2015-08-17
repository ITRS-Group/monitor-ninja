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

	/**
	 *	Parses custom variables and returns custom commands
	 *
	 *	@return array
	 */
	public function list_custom_commands() {
		$contact = ContactPool_Model::get_current_contact();
		/*
		 * We need a contact to have contact groups. If no contact groups, we
		 * don't have any custom commands
		 */
		if($contact === false)
			return false;

		/*
		 * Unpack contactgroups for this user as a lookup from contactgroup name
		 */
		$cg_set = $contact->get_groups_set();
		$cgs = array();
		foreach($cg_set->it(array('name')) as $cg) {
			$cgs[$cg->get_name()] = true;
		}


		/* Unpack commands */
		$custom_commands = array();
		foreach ($this->get_custom_variables() as $key => $value) {
			$parts = explode('__', strtolower($key));

			/* Not a valid command */
			if(count($parts) != 3)
				continue;

			list($context, $attr, $command) = $parts;

			// Does custom variable name match pattern?
			if (substr($context, 0, 3) != 'op5')
				continue;

			if (!isset($custom_commands[$command]))
				$custom_commands[$command] = array();

			$custom_commands[$command][$attr] = $value;
		}

		/* Filter out incomplete custom commands */
		$custom_commands = array_filter($custom_commands, function($cmd) {
			if(!isset($cmd['access']))
				return false;
			if(!isset($cmd['action']))
				return false;
			return true;
		});

		/* Filter out unauthorized commands */
		$custom_commands = array_filter($custom_commands, function($cmd) use ($cgs) {
			foreach(explode(',',$cmd['access']) as $grp) {
				if(isset($cgs[$grp]) && $cgs[$grp])
					return true;
			}
			return false;
		});

		/* Unpack commands, to skip access, when returning */
		return array_map(function($cmd) {
			return $cmd['action'];
		}, $custom_commands);
	}
}
