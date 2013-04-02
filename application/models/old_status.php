<?php

/**
 * A model for generating status filters from livestatus
 */
class Old_Status_Model extends Model {
	/**
	 * Combine a sequence of filter-like parameters
	 * @param $type Probably "and" or "or"
	 * @param $filter Usually array, but can also be string
	 * @returns String representing the filter
	 */
	private function filter_combine($type, $filter) {
		# No filter, anything goes
		if (empty($filter)) {
			return "all";
		}
		# Multi-parameter filter, use parentheses galore
		else if (is_array($filter)) {
			return '('.implode($filter, ') '.$type.' (').')';
		}
		# String, I guess? Just return it, I'm sure it's awesome
		else {
			return $filter;
		}
	}

	/**
	 * Livestatus port of the classic ninja bitmask-based filters
	 */
	public function classic_filter($type, $host = false, $hostgroup = false, $servicegroup = false, $hoststatustypes = false, $hostprops = false, $servicestatustypes = false, $serviceprops = false) {
		# classic search
		$errors       = 0;

		$hostfilter = array();
		$hostgroupfilter = array();
		$servicefilter = array();
		$servicegroupfilter = array();
		if( $host != 'all' and $host != '' ) {
			# check for wildcards
			if( strpos( $host, '*' ) !== false ) {
				# convert wildcards into real regexp
				$searchhost = str_replace('.*', '*', $host);
				$searchhost = str_replace('*', '.*', $searchhost);
				/* TODO: validate regex */
				#$errors++ unless Livestatus::is_valid_regular_expression( $searchhost );
				$hostfilter[] 	 = "name ~~ $searchhost";
				$servicefilter[] = "host.name ~~ $searchhost";
			} else {
				$hostfilter[]    = "name = $host";
				$servicefilter[] = "host.name = $host";
			}
		}
		if ( $hostgroup != 'all' and $hostgroup != '' ) {
			$hostfilter[]       = "in $hostgroup";
			$servicefilter[]    = "host in $hostgroup";
			$hostgroupfilter[]  = "name = $hostgroup";
		}
		if ( $servicegroup != 'all' and $servicegroup != '' ) {
			$servicefilter[]       = "in $servicegroup";
			$servicegroupfilter[]  = "name = $servicegroup";
		}

		$hostfilter         = $this->filter_combine("and", $hostfilter);
		$hostgroupfilter    = $this->filter_combine("or", $hostgroupfilter);
		$servicefilter      = $this->filter_combine("and", $servicefilter);
		$servicegroupfilter = $this->filter_combine("or",  $servicegroupfilter);

		list( $hostfilter, $servicefilter, $host_statustype_filtervalue, $host_prop_filtervalue, $service_statustype_filtervalue, $service_prop_filtervalue ) = $this->extend_filter( $hostfilter, $servicefilter, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops );

		return (array( '[hosts] '.$hostfilter, '[services] '.$servicefilter, '[hostgroups] '.$hostgroupfilter, '[servicegroups] '.$servicegroupfilter ));
	}

	private function extend_filter($hostfilter, $servicefilter, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops) {
		$hostfilterlist    = array();
		$servicefilterlist = array();

		$hostfilter    && $hostfilterlist[]    = $hostfilter;
		$servicefilter && $servicefilterlist[] = $servicefilter;

		# host statustype filter (up,down,...)
		list( $hoststatustypes, $host_statustype_filter, $host_statustype_filter_service ) = $this->get_host_statustype_filter($hoststatustypes);
		$host_statustype_filter         && $hostfilterlist[]    = $host_statustype_filter;
		$host_statustype_filter_service && $servicefilterlist[] = $host_statustype_filter_service;

		# host props filter (downtime, acknowledged...)
		list( $hostprops, $host_prop_filter, $host_prop_filter_service ) = $this->get_host_prop_filter($hostprops);
		$host_prop_filter         && $hostfilterlist[] =    $host_prop_filter;
		$host_prop_filter_service && $servicefilterlist[] = $host_prop_filter_service;

		# service statustype filter (ok,warning,...)
		list( $servicestatustypes, $service_statustype_filter_service ) = $this->get_service_statustype_filter($servicestatustypes);
		$service_statustype_filter_service && $servicefilterlist[] = $service_statustype_filter_service;

		# service props filter (downtime, acknowledged...)
		list( $serviceprops, $service_prop_filter_service ) = $this->get_service_prop_filter($serviceprops);
		$service_prop_filter_service && $servicefilterlist[] = $service_prop_filter_service;

		$hostfilter    = $this->filter_combine("and", $hostfilterlist);
		$servicefilter = $this->filter_combine("and", $servicefilterlist);

		return array( $hostfilter, $servicefilter, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops );
	}

	private function get_host_statustype_filter($number) {
		$hoststatusfilter    = array();
		$servicestatusfilter = array();
		if ($number < 0) {
			$number = 0;
		}

		if( $number & nagstat::HOST_PENDING ) {    # 1 - pending
			$hoststatusfilter[]    = 'has_been_checked = 0';
			$servicestatusfilter[] = 'host.has_been_checked = 0';
		}
		if( $number & nagstat::HOST_UP ) {    # 2 - up
			$hoststatusfilter[]    = 'has_been_checked = 1 and state = 0';
			$servicestatusfilter[] = 'host.has_been_checked = 1 and host.state = 0';
		}
		if( $number & nagstat::HOST_DOWN ) {    # 4 - down
			$hoststatusfilter[]    = 'has_been_checked = 1 and state = 1';
			$servicestatusfilter[] = 'host.has_been_checked = 1 and host.state = 1';
		}
		if( $number & nagstat::HOST_UNREACHABLE ) {    # 8 - unreachable
			$hoststatusfilter[]    = 'has_been_checked = 1 and state = 2';
			$servicestatusfilter[] = 'host.has_been_checked = 1 and host.state = 2';
		}

		$hostfilter    = $this->filter_combine('or', $hoststatusfilter );
		$servicefilter = $this->filter_combine('or', $servicestatusfilter );

		return ( array($number, $hostfilter, $servicefilter ));
	}


	private function get_host_prop_filter($number) {
		$host_prop_filter = array();
		$host_prop_filter_service = array();
		if ($number < 0) {
			$number = 0;
		}

		if( $number & nagstat::HOST_SCHEDULED_DOWNTIME ) {    # 1 - In Scheduled Downtime
			$host_prop_filter[] = 'scheduled_downtime_depth > 0';
			$host_prop_filter_service[] = 'host.scheduled_downtime_depth > 0';
		}
		if( $number & nagstat::HOST_NO_SCHEDULED_DOWNTIME ) {    # 2 - Not In Scheduled Downtime
			$host_prop_filter[] = 'scheduled_downtime_depth = 0';
			$host_prop_filter_service[] = 'host.scheduled_downtime_depth = 0';
		}
		if( $number & nagstat::HOST_STATE_ACKNOWLEDGED ) {    # 4 - Has Been Acknowledged
			$host_prop_filter[] = 'acknowledged = 1';
			$host_prop_filter_service[] = 'host.acknowledged = 1';
		}
		if( $number & nagstat::HOST_STATE_UNACKNOWLEDGED ) {    # 8 - Has Not Been Acknowledged
			$host_prop_filter[] = 'acknowledged = 0';
			$host_prop_filter_service[] = 'host.acknowledged = 0';
		}
		if( $number & nagstat::HOST_CHECKS_DISABLED ) {    # 16 - Checks Disabled
			$host_prop_filter[] = 'checks_enabled = 0';
			$host_prop_filter_service[] = 'host.checks_enabled = 0';
		}
		if( $number & nagstat::HOST_CHECKS_ENABLED ) {    # 32 - Checks Enabled
			$host_prop_filter[] = 'checks_enabled = 1';
			$host_prop_filter_service[] = 'host.checks_enabled = 1';
		}
		if( $number & nagstat::HOST_EVENT_HANDLER_DISABLED ) {    # 64 - Event Handler Disabled
			$host_prop_filter[] = 'event_handler_enabled = 0';
			$host_prop_filter_service[] = 'host.event_handler_enabled = 0';
		}
		if( $number & nagstat::HOST_EVENT_HANDLER_ENABLED ) {    # 128 - Event Handler Enabled
			$host_prop_filter[] = 'event_handler_enabled = 1';
			$host_prop_filter_service[] = 'host.event_handler_enabled = 1';
		}
		if( $number & nagstat::HOST_FLAP_DETECTION_DISABLED ) {    # 256 - Flap Detection Disabled
			$host_prop_filter[] = 'flap_detection_enabled = 0';
			$host_prop_filter_service[] = 'host.flap_detection_enabled = 0';
		}
		if( $number & nagstat::HOST_FLAP_DETECTION_ENABLED ) {    # 512 - Flap Detection Enabled
			$host_prop_filter[] = 'flap_detection_enabled = 1';
			$host_prop_filter_service[] = 'host.flap_detection_enabled = 1';
		}
		if( $number & nagstat::HOST_IS_FLAPPING ) {    # 1024 - Is Flapping
			$host_prop_filter[] = 'is_flapping = 1';
			$host_prop_filter_service[] = 'host.is_flapping = 1';
		}
		if( $number & nagstat::HOST_IS_NOT_FLAPPING ) {    # 2048 - Is Not Flapping
			$host_prop_filter[] = 'is_flapping = 0';
			$host_prop_filter_service[] = 'host.is_flapping = 0';
		}
		if( $number & nagstat::HOST_NOTIFICATIONS_DISABLED ) {    # 4096 - Notifications Disabled
			$host_prop_filter[] = 'notifications_enabled = 0';
			$host_prop_filter_service[] = 'host.notifications_enabled = 0';
		}
		if( $number & nagstat::HOST_NOTIFICATIONS_ENABLED) {    # 8192 - Notifications Enabled
			$host_prop_filter[] = 'notifications_enabled = 1';
			$host_prop_filter_service[] = 'host.notifications_enabled = 1';
		}
		if( $number & nagstat::HOST_PASSIVE_CHECKS_DISABLED ) {    # 16384 - Passive Checks Disabled
			$host_prop_filter[] = 'accept_passive_checks = 0';
			$host_prop_filter_service[] = 'host.accept_passive_checks = 0';
		}
		if( $number & nagstat::HOST_PASSIVE_CHECKS_ENABLED ) {    # 32768 - Passive Checks Enabled
			$host_prop_filter[] = 'accept_passive_checks = 1';
			$host_prop_filter_service[] = 'host.accept_passive_checks = 1';
		}
		if( $number & nagstat::HOST_PASSIVE_CHECK ) {    # 65536 - Passive Checks
			$host_prop_filter[] = 'check_type = 1';
			$host_prop_filter_service[] = 'host.check_type = 1';
		}
		if( $number & nagstat::HOST_ACTIVE_CHECK ) {    # 131072 - Active Checks
			$host_prop_filter[] = 'check_type = 0';
			$host_prop_filter_service[] = 'host.check_type = 0';
		}
		if( $number & nagstat::HOST_HARD_STATE ) {    # 262144 - In Hard State
			$host_prop_filter[] = 'state_type = 1';
			$host_prop_filter_service[] = 'host.state_type = 1';
		}
		if( $number & nagstat::HOST_SOFT_STATE ) {    # 524288 - In Soft State
			$host_prop_filter[] = 'state_type = 0';
			$host_prop_filter_service[] = 'host.state_type = 0';
		}

		$hostfilter    = $this->filter_combine('and', $host_prop_filter );
		$servicefilter = $this->filter_combine('and', $host_prop_filter_service );

		return ( array( $number, $hostfilter, $servicefilter ));
	}


	private function get_service_statustype_filter($number) {
		$servicestatusfilter     = array();
		if ($number < 0) {
			$number = 0;
		}

		if( $number & nagstat::SERVICE_PENDING ) {    # 1 - pending
			$servicestatusfilter[] = 'has_been_checked = 0';
		}
		if( $number & nagstat::SERVICE_OK ) {    # 2 - ok
			$servicestatusfilter[] = 'has_been_checked = 1 and state = 0';
		}
		if( $number & nagstat::SERVICE_WARNING ) {    # 4 - warning
			$servicestatusfilter[] = 'has_been_checked = 1 and state = 1';
		}
		if( $number & nagstat::SERVICE_UNKNOWN ) {    # 8 - unknown
			$servicestatusfilter[] = 'has_been_checked = 1 and state = 3';
		}
		if( $number & nagstat::SERVICE_CRITICAL ) {    # 16 - critical
			$servicestatusfilter[] = 'has_been_checked =  1 and state = 2';
		}

		$servicefilter = $this->filter_combine('or', $servicestatusfilter );

		return(array( $number, $servicefilter ));
	}

	private function get_service_prop_filter($number) {
		$service_prop_filter = array();
		if ($number < 0) {
			$number = 0;
		}

		if( $number & nagstat::SERVICE_SCHEDULED_DOWNTIME ) {    # 1 - In Scheduled Downtime
			$service_prop_filter[] = 'scheduled_downtime_depth > 0';
		}
		if( $number & nagstat::SERVICE_NO_SCHEDULED_DOWNTIME ) {    # 2 - Not In Scheduled Downtime
			$service_prop_filter[] = 'scheduled_downtime_depth = 0';
		}
		if( $number & nagstat::SERVICE_STATE_ACKNOWLEDGED ) {    # 4 - Has Been Acknowledged
			$service_prop_filter[] = 'acknowledged = 1';
		}
		if( $number & nagstat::SERVICE_STATE_UNACKNOWLEDGED ) {    # 8 - Has Not Been Acknowledged
			$service_prop_filter[] = 'acknowledged = 0';
		}
		if( $number & nagstat::SERVICE_CHECKS_DISABLED ) {    # 16 - Checks Disabled
			$service_prop_filter[] = 'checks_enabled = 0';
		}
		if( $number & nagstat::SERVICE_CHECKS_ENABLED ) {    # 32 - Checks Enabled
			$service_prop_filter[] = 'checks_enabled = 1';
		}
		if( $number & nagstat::SERVICE_EVENT_HANDLER_DISABLED ) {    # 64 - Event Handler Disabled
			$service_prop_filter[] = 'event_handler_enabled = 0';
		}
		if( $number & nagstat::SERVICE_EVENT_HANDLER_ENABLED ) {    # 128 - Event Handler Enabled
			$service_prop_filter[] = 'event_handler_enabled = 1';
		}
		if( $number & nagstat::SERVICE_FLAP_DETECTION_ENABLED ) {    # 256 - Flap Detection Enabled
			$service_prop_filter[] = 'flap_detection_enabled = 1';
		}
		if( $number & nagstat::SERVICE_FLAP_DETECTION_DISABLED ) {    # 512 - Flap Detection Disabled
			$service_prop_filter[] = 'flap_detection_enabled = 0';
		}
		if( $number & nagstat::SERVICE_IS_FLAPPING ) {    # 1024 - Is Flapping
			$service_prop_filter[] = 'is_flapping = 1';
		}
		if( $number & nagstat::SERVICE_IS_NOT_FLAPPING ) {    # 2048 - Is Not Flapping
			$service_prop_filter[] = 'is_flapping = 0';
		}
		if( $number & nagstat::SERVICE_NOTIFICATIONS_DISABLED ) {    # 4096 - Notifications Disabled
			$service_prop_filter[] = 'notifications_enabled = 0';
		}
		if( $number & nagstat::SERVICE_NOTIFICATIONS_ENABLED ) {    # 8192 - Notifications Enabled
			$service_prop_filter[] = 'notifications_enabled = 1';
		}
		if( $number & nagstat::SERVICE_PASSIVE_CHECKS_DISABLED ) {    # 16384 - Passive Checks Disabled
			$service_prop_filter[] = 'accept_passive_checks = 0';
		}
		if( $number & nagstat::SERVICE_PASSIVE_CHECKS_ENABLED ) {    # 32768 - Passive Checks Enabled
			$service_prop_filter[] = 'accept_passive_checks = 1';
		}
		if( $number & nagstat::SERVICE_PASSIVE_CHECK ) {    # 65536 - Passive Checks
			$service_prop_filter[] = 'check_type = 1';
		}
		if( $number & nagstat::SERVICE_ACTIVE_CHECK ) {    # 131072 - Active Checks
			$service_prop_filter[] = 'check_type = 0';
		}
		if( $number & nagstat::SERVICE_HARD_STATE ) {    # 262144 - In Hard State
			$service_prop_filter[] = 'state_type = 1';
		}
		if( $number & nagstat::SERVICE_SOFT_STATE ) {    # 524288 - In Soft State
			$service_prop_filter[] = 'state_type = 0';
		}

		$servicefilter = $this->filter_combine('and', $service_prop_filter );

		return (array( $number, $servicefilter ));
	}
}
