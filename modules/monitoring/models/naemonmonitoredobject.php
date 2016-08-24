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
	 * @return array
	 */
	protected function schedule_downtime_retrospectively($host_name, $service_description, $start_time, $end_time, $comment) {
		/* Make sure they really are integers... not only integer strings */
		$start_time = (int)$start_time;
		$end_time = (int)$end_time;

		if($start_time > time()) {
			// no need to bubble a message
			return array(
				'status' => true,
				'output' => ''
			);
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
		return array(
			'status' => true,
			'output' => 'Scheduled retrospectively for reporting'
		);
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
			return array();

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

	/**
	 * @param $command_name string
	 * @return array
	 */
	public function submit_custom_command($command_name) {
		$commands = $this->list_custom_commands();
		if(!array_key_exists($command_name, $commands)) {
			return array('status' => false, 'output' => "$command_name is not a valid command, or you aren't authorized to execute it");
		}
		$type = rtrim($this->get_table(), 's');
		$properties = array();
		foreach($this->export() as $prop => $value) {
			if(is_array($value)) {
				// ASSUMING that export() only supports 2 levels
				foreach($value as $prop_key => $prop_value) {
					if(is_array($prop_value)) {
						// "we won't find a macro here anyways" / Max
						continue;
					}
					// adapt to old nagstat::process_macros() logic,
					// TODO fixup
					$properties[$prop."_".$prop_key] = $prop_value;
				}
			} else {
				$properties[$prop] = $value;
			}
		}
		$command = nagstat::process_macros($commands[$command_name], (object) $properties, $type);
		$comment_result = $this->add_comment("Executing custom command: ".ucwords(strtolower(str_replace('_', ' ', $command_name))));
		if(!$comment_result['status']) {
			op5log::instance('ninja')->log('warning', sprintf('Failed to comment custom command: %s\nOutput:\n%s', $command_name, implode("\n", $comment_result['output'])));
			return array(
				'status' => false,
				'output' => $comment_result['output']
			);
		}
		exec($command, $output, $status);
		if($status != 0) {
			op5log::instance('ninja')->log('warning', sprintf('Failed to execute custom command: %s\nOutput:\n%s', $command, implode("\n", $output)));
		}
		return array(
			'status' => $status == 0,
			'output' => implode("\n", $output),
		);
	}

}
