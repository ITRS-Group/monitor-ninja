<?php

/**
 * A model for generating status filters from livestatus
 */
class Status_Model extends Model {
	public $show_filter_table; /**< Whether to show the filter table */
	public $host_statustype_filtername; /**< A string describing the host states included */
	public $host_prop_filtername; /**< A string describing the host flags used for filtering */
	public $service_statustype_filtername; /**< A string describing the servicestates included */
	public $service_prop_filtername; /**< A string describing the service flags used for filtering */

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
				$hostfilter[] 	 = array( 'name'      => array( '~~' => $searchhost ));
				$servicefilter[] = array( 'host_name' => array( '~~' => $searchhost ));
			} else {
				$hostfilter[]    = array( 'name'      => $host );
				$servicefilter[] = array( 'host_name' => $host );
			}
		}
		if ( $hostgroup != 'all' and $hostgroup != '' ) {
			$hostfilter[]       = array( 'groups'      => array( '>=' => $hostgroup ));
			$servicefilter[]    = array( 'host_groups' => array( '>=' => $hostgroup ));
			$hostgroupfilter[]  = array( 'name' => $hostgroup );
		}
		if ( $servicegroup != 'all' and $servicegroup != '' ) {
			$servicefilter[]       = array( 'groups' => array( '>=' => $servicegroup ) );
			$servicegroupfilter[]  = array( 'name' => $servicegroup );
		}

		$hostfilter         = LivestatusBackend::combineFilter( '-and', $hostfilter );
		$hostgroupfilter    = LivestatusBackend::combineFilter( '-or',  $hostgroupfilter );
		$servicefilter      = LivestatusBackend::combineFilter( '-and', $servicefilter );
		$servicegroupfilter = LivestatusBackend::combineFilter( '-or',  $servicegroupfilter );

		list( $hostfilter, $servicefilter, $host_statustype_filtervalue, $host_prop_filtervalue, $service_statustype_filtervalue, $service_prop_filtervalue ) = $this->extend_filter( $hostfilter, $servicefilter, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops );

		return (array( $hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter ));
	}

	private function extend_filter($hostfilter, $servicefilter, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops) {
		$hostfilterlist    = array();
		$servicefilterlist = array();

		$hostfilter    && $hostfilterlist[]    = $hostfilter;
		$servicefilter && $servicefilterlist[] = $servicefilter;

		$this->show_filter_table = 0;

		# host statustype filter (up,down,...)
		list( $hoststatustypes, $this->host_statustype_filtername, $host_statustype_filter, $host_statustype_filter_service ) = $this->get_host_statustype_filter($hoststatustypes);
		$host_statustype_filter         && $hostfilterlist[]    = $host_statustype_filter;
		$host_statustype_filter_service && $servicefilterlist[] = $host_statustype_filter_service;

		$host_statustype_filter && $this->show_filter_table = 1;

		# host props filter (downtime, acknowledged...)
		list( $hostprops, $this->host_prop_filtername, $host_prop_filter, $host_prop_filter_service ) = $this->get_host_prop_filter($hostprops);
		$host_prop_filter         && $hostfilterlist[] =    $host_prop_filter;
		$host_prop_filter_service && $servicefilterlist[] = $host_prop_filter_service;

		$host_prop_filter && $this->show_filter_table = 1;

		# service statustype filter (ok,warning,...)
		list( $servicestatustypes, $this->service_statustype_filtername, $service_statustype_filter_service ) = $this->get_service_statustype_filter($servicestatustypes);
		$service_statustype_filter_service && $servicefilterlist[] = $service_statustype_filter_service;

		$service_statustype_filter_service && $this->show_filter_table = 1;

		# service props filter (downtime, acknowledged...)
		list( $serviceprops, $this->service_prop_filtername, $service_prop_filter_service ) = $this->get_service_prop_filter($serviceprops);
		$service_prop_filter_service && $servicefilterlist[] = $service_prop_filter_service;

		$service_prop_filter_service && $this->show_filter_table = 1;

		$hostfilter    = LivestatusBackend::combineFilter( '-and', $hostfilterlist );
		$servicefilter = LivestatusBackend::combineFilter( '-and', $servicefilterlist );

		return array( $hostfilter, $servicefilter, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops );
	}

	private function get_host_statustype_filter($number) {
		$hoststatusfilter    = array();
		$servicestatusfilter = array();

		$hoststatusfiltername = 'All';
		if(!isset($number) or !is_numeric($number)) { return ( array(nagstat::HOST_ALL, $hoststatusfiltername, "", "" )); }

		if( $number and $number != nagstat::HOST_ALL ) {
		$hoststatusfiltername_list = array();

		if( $number & nagstat::HOST_PENDING ) {    # 1 - pending
			$hoststatusfilter[]    = array( 'has_been_checked'      => 0 );
			$servicestatusfilter[] = array( 'host_has_been_checked' => 0 );
			$hoststatusfiltername_list[] = 'Pending';
		}
		if( $number & nagstat::HOST_UP ) {    # 2 - up
			$hoststatusfilter[]    = array( 'has_been_checked'      => 1, 'state'      => 0 );
			$servicestatusfilter[] = array( 'host_has_been_checked' => 1, 'host_state' => 0 );
			$hoststatusfiltername_list[] = 'Up';
		}
		if( $number & nagstat::HOST_DOWN ) {    # 4 - down
			$hoststatusfilter[]    = array( 'has_been_checked'      => 1, 'state'      => 1 );
			$servicestatusfilter[] = array( 'host_has_been_checked' => 1, 'host_state' => 1 );
			$hoststatusfiltername_list[] = 'Down';
		}
		if( $number & nagstat::HOST_UNREACHABLE ) {    # 8 - unreachable
			$hoststatusfilter[]    = array( 'has_been_checked'      => 1, 'state'      => 2 );
			$servicestatusfilter[] = array( 'host_has_been_checked' => 1, 'host_state' => 2 );
			$hoststatusfiltername_list[] = 'Unreachable';
		}
		$hoststatusfiltername = join( ' | ', $hoststatusfiltername_list );
		if($number == nagstat::HOST_PROBLEM) { $hoststatusfiltername = 'All problems'; };
		}

		$hostfilter    = LivestatusBackend::combineFilter( '-or', $hoststatusfilter );
		$servicefilter = LivestatusBackend::combineFilter( '-or', $servicestatusfilter );

		return ( array($number, $hoststatusfiltername, $hostfilter, $servicefilter ));
	}


	private function get_host_prop_filter($number) {
		$host_prop_filter = array();
		$host_prop_filter_service = array();
		$host_prop_filtername = 'Any';
		if(!isset($number) or !is_numeric($number)) { return ( array( 0, $host_prop_filtername, "", "" )); }

		if( $number > 0 ) {
		$host_prop_filtername_list = array();

		if( $number & nagstat::HOST_SCHEDULED_DOWNTIME ) {    # 1 - In Scheduled Downtime
			$host_prop_filter[] =           array( 'scheduled_downtime_depth'      => array( '>' => 0 ));
			$host_prop_filter_service[] =   array( 'host_scheduled_downtime_depth' => array( '>' => 0 ));
			$host_prop_filtername_list[] = 'In Scheduled Downtime';
		}
		if( $number & nagstat::HOST_NO_SCHEDULED_DOWNTIME ) {    # 2 - Not In Scheduled Downtime
			$host_prop_filter[] =           array( 'scheduled_downtime_depth'      => 0 );
			$host_prop_filter_service[] =   array( 'host_scheduled_downtime_depth' => 0 );
			$host_prop_filtername_list[] = 'Not In Scheduled Downtime';
		}
		if( $number & nagstat::HOST_STATE_ACKNOWLEDGED ) {    # 4 - Has Been Acknowledged
			$host_prop_filter[] =           array( 'acknowledged'      => 1 );
			$host_prop_filter_service[] =   array( 'host_acknowledged' => 1 );
			$host_prop_filtername_list[] = 'Has Been Acknowledged';
		}
		if( $number & nagstat::HOST_STATE_UNACKNOWLEDGED ) {    # 8 - Has Not Been Acknowledged
			$host_prop_filter[] =           array( 'acknowledged'      => 0 );
			$host_prop_filter_service[] =   array( 'host_acknowledged' => 0 );
			$host_prop_filtername_list[] = 'Has Not Been Acknowledged';
		}
		if( $number & nagstat::HOST_CHECKS_DISABLED ) {    # 16 - Checks Disabled
			$host_prop_filter[] =           array( 'checks_enabled'      => 0 );
			$host_prop_filter_service[] =   array( 'host_checks_enabled' => 0 );
			$host_prop_filtername_list[] = 'Checks Disabled';
		}
		if( $number & nagstat::HOST_CHECKS_ENABLED ) {    # 32 - Checks Enabled
			$host_prop_filter[] =           array( 'checks_enabled'      => 1 );
			$host_prop_filter_service[] =   array( 'host_checks_enabled' => 1 );
			$host_prop_filtername_list[] = 'Checks Enabled';
		}
		if( $number & nagstat::HOST_EVENT_HANDLER_DISABLED ) {    # 64 - Event Handler Disabled
			$host_prop_filter[] =           array( 'event_handler_enabled'      => 0 );
			$host_prop_filter_service[] =   array( 'host_event_handler_enabled' => 0 );
			$host_prop_filtername_list[] = 'Event Handler Disabled';
		}
		if( $number & nagstat::HOST_EVENT_HANDLER_ENABLED ) {    # 128 - Event Handler Enabled
			$host_prop_filter[] =           array( 'event_handler_enabled'      => 1 );
			$host_prop_filter_service[] =   array( 'host_event_handler_enabled' => 1 );
			$host_prop_filtername_list[] = 'Event Handler Enabled';
		}
		if( $number & nagstat::HOST_FLAP_DETECTION_DISABLED ) {    # 256 - Flap Detection Disabled
			$host_prop_filter[] =           array( 'flap_detection_enabled'      => 0 );
			$host_prop_filter_service[] =   array( 'host_flap_detection_enabled' => 0 );
			$host_prop_filtername_list[] = 'Flap Detection Disabled';
		}
		if( $number & nagstat::HOST_FLAP_DETECTION_ENABLED ) {    # 512 - Flap Detection Enabled
			$host_prop_filter[] =           array( 'flap_detection_enabled'      => 1 );
			$host_prop_filter_service[] =   array( 'host_flap_detection_enabled' => 1 );
			$host_prop_filtername_list[] = 'Flap Detection Enabled';
		}
		if( $number & nagstat::HOST_IS_FLAPPING ) {    # 1024 - Is Flapping
			$host_prop_filter[] =           array( 'is_flapping'      => 1 );
			$host_prop_filter_service[] =   array( 'host_is_flapping' => 1 );
			$host_prop_filtername_list[] = 'Is Flapping';
		}
		if( $number & nagstat::HOST_IS_NOT_FLAPPING ) {    # 2048 - Is Not Flapping
			$host_prop_filter[] =           array( 'is_flapping'      => 0 );
			$host_prop_filter_service[] =   array( 'host_is_flapping' => 0 );
			$host_prop_filtername_list[] = 'Is Not Flapping';
		}
		if( $number & nagstat::HOST_NOTIFICATIONS_DISABLED ) {    # 4096 - Notifications Disabled
			$host_prop_filter[] =           array( 'notifications_enabled'      => 0 );
			$host_prop_filter_service[] =   array( 'host_notifications_enabled' => 0 );
			$host_prop_filtername_list[] = 'Notifications Disabled';
		}
		if( $number & nagstat::HOST_NOTIFICATIONS_ENABLED) {    # 8192 - Notifications Enabled
			$host_prop_filter[] =           array( 'notifications_enabled'      => 1 );
			$host_prop_filter_service[] =   array( 'host_notifications_enabled' => 1 );
			$host_prop_filtername_list[] = 'Notifications Enabled';
		}
		if( $number & nagstat::HOST_PASSIVE_CHECKS_DISABLED ) {    # 16384 - Passive Checks Disabled
			$host_prop_filter[] =           array( 'accept_passive_checks'      => 0 );
			$host_prop_filter_service[] =   array( 'host_accept_passive_checks' => 0 );
			$host_prop_filtername_list[] = 'Passive Checks Disabled';
		}
		if( $number & nagstat::HOST_PASSIVE_CHECKS_ENABLED ) {    # 32768 - Passive Checks Enabled
			$host_prop_filter[] =           array( 'accept_passive_checks'      => 1 );
			$host_prop_filter_service[] =   array( 'host_accept_passive_checks' => 1 );
			$host_prop_filtername_list[] = 'Passive Checks Enabled';
		}
		if( $number & nagstat::HOST_PASSIVE_CHECK ) {    # 65536 - Passive Checks
			$host_prop_filter[] =           array( 'check_type'      => 1 );
			$host_prop_filter_service[] =   array( 'host_check_type' => 1 );
			$host_prop_filtername_list[] = 'Passive Checks';
		}
		if( $number & nagstat::HOST_ACTIVE_CHECK ) {    # 131072 - Active Checks
			$host_prop_filter[] =           array( 'check_type'      => 0 );
			$host_prop_filter_service[] =   array( 'host_check_type' => 0 );
			$host_prop_filtername_list[] = 'Active Checks';
		}
		if( $number & nagstat::HOST_HARD_STATE ) {    # 262144 - In Hard State
			$host_prop_filter[] =           array( 'state_type'      => 1 );
			$host_prop_filter_service[] =   array( 'host_state_type' => 1 );
			$host_prop_filtername_list[] = 'In Hard State';
		}
		if( $number & nagstat::HOST_SOFT_STATE ) {    # 524288 - In Soft State
			$host_prop_filter[] =           array( 'state_type'      => 0 );
			$host_prop_filter_service[] =   array( 'host_state_type' => 0 );
			$host_prop_filtername_list[] = 'In Soft State';
		}

		$host_prop_filtername = join( ' &amp; ', $host_prop_filtername_list );
		}

		$hostfilter    = LivestatusBackend::combineFilter( '-and', $host_prop_filter );
		$servicefilter = LivestatusBackend::combineFilter( '-and', $host_prop_filter_service );

		return ( array( $number, $host_prop_filtername, $hostfilter, $servicefilter ));
	}


	private function get_service_statustype_filter($number) {
		$servicestatusfilter     = array();
		$servicestatusfilternamelist = array();

		$servicestatusfiltername = 'All';
		if(!isset($number) or !is_numeric($number)) { return(array( nagstat::SERVICE_ALL, $servicestatusfiltername, "" )); }

		if( $number and $number != nagstat::SERVICE_ALL ) {

		if( $number & nagstat::SERVICE_PENDING ) {    # 1 - pending
			$servicestatusfilter[] = array( 'has_been_checked' => 0 );
			$servicestatusfilternamelist[] = 'Pending';
		}
		if( $number & nagstat::SERVICE_OK ) {    # 2 - ok
			$servicestatusfilter[] = array( 'has_been_checked' => 1, 'state' => 0 );
			$servicestatusfilternamelist[] = 'Ok';
		}
		if( $number & nagstat::SERVICE_WARNING ) {    # 4 - warning
			$servicestatusfilter[] = array( 'has_been_checked' => 1, 'state' => 1 );
			$servicestatusfilternamelist[] = 'Warning';
		}
		if( $number & nagstat::SERVICE_UNKNOWN ) {    # 8 - unknown
			$servicestatusfilter[] = array( 'has_been_checked' => 1, 'state' => 3 );
			$servicestatusfilternamelist[] = 'Unknown';
		}
		if( $number & nagstat::SERVICE_CRITICAL ) {    # 16 - critical
			$servicestatusfilter[] = array( 'has_been_checked' => 1, 'state' => 2 );
			$servicestatusfilternamelist[] = 'Critical';
		}
		$servicestatusfiltername = join( ' | ', $servicestatusfilternamelist );
		if($number == nagstat::SERVICE_PROBLEM) { $servicestatusfiltername = 'All problems'; }
		}

		$servicefilter = LivestatusBackend::combineFilter( '-or', $servicestatusfilter );

		return(array( $number, $servicestatusfiltername, $servicefilter ));
	}

	private function get_service_prop_filter($number) {
		$service_prop_filter = array();
		$service_prop_filtername_list = array();
		$service_prop_filtername = 'Any';
		if(!isset($number) or !is_numeric($number)) { return (array( 0, $service_prop_filtername, "" )); }

		if( $number > 0 ) {
		if( $number & nagstat::SERVICE_SCHEDULED_DOWNTIME ) {    # 1 - In Scheduled Downtime
			$service_prop_filter[] = array( 'scheduled_downtime_depth' => array( '>' => 0 ) );
			$service_prop_filtername_list[] = 'In Scheduled Downtime';
		}
		if( $number & nagstat::SERVICE_NO_SCHEDULED_DOWNTIME ) {    # 2 - Not In Scheduled Downtime
			$service_prop_filter[] = array( 'scheduled_downtime_depth' => 0 );
			$service_prop_filtername_list[] = 'Not In Scheduled Downtime';
		}
		if( $number & nagstat::SERVICE_STATE_ACKNOWLEDGED ) {    # 4 - Has Been Acknowledged
			$service_prop_filter[] = array( 'acknowledged' => 1 );
			$service_prop_filtername_list[] = 'Has Been Acknowledged';
		}
		if( $number & nagstat::SERVICE_STATE_UNACKNOWLEDGED ) {    # 8 - Has Not Been Acknowledged
			$service_prop_filter[] = array( 'acknowledged' => 0 );
			$service_prop_filtername_list[] = 'Has Not Been Acknowledged';
		}
		if( $number & nagstat::SERVICE_CHECKS_DISABLED ) {    # 16 - Checks Disabled
			$service_prop_filter[] = array( 'checks_enabled' => 0 );
			$service_prop_filtername_list[] = 'Active Checks Disabled';
		}
		if( $number & nagstat::SERVICE_CHECKS_ENABLED ) {    # 32 - Checks Enabled
			$service_prop_filter[] = array( 'checks_enabled' => 1 );
			$service_prop_filtername_list[] = 'Active Checks Enabled';
		}
		if( $number & nagstat::SERVICE_EVENT_HANDLER_DISABLED ) {    # 64 - Event Handler Disabled
			$service_prop_filter[] = array( 'event_handler_enabled' => 0 );
			$service_prop_filtername_list[] = 'Event Handler Disabled';
		}
		if( $number & nagstat::SERVICE_EVENT_HANDLER_ENABLED ) {    # 128 - Event Handler Enabled
			$service_prop_filter[] = array( 'event_handler_enabled' => 1 );
			$service_prop_filtername_list[] = 'Event Handler Enabled';
		}
		if( $number & nagstat::SERVICE_FLAP_DETECTION_ENABLED ) {    # 256 - Flap Detection Enabled
			$service_prop_filter[] = array( 'flap_detection_enabled' => 1 );
			$service_prop_filtername_list[] = 'Flap Detection Enabled';
		}
		if( $number & nagstat::SERVICE_FLAP_DETECTION_DISABLED ) {    # 512 - Flap Detection Disabled
			$service_prop_filter[] = array( 'flap_detection_enabled' => 0 );
			$service_prop_filtername_list[] = 'Flap Detection Disabled';
		}
		if( $number & nagstat::SERVICE_IS_FLAPPING ) {    # 1024 - Is Flapping
			$service_prop_filter[] = array( 'is_flapping' => 1 );
			$service_prop_filtername_list[] = 'Is Flapping';
		}
		if( $number & nagstat::SERVICE_IS_NOT_FLAPPING ) {    # 2048 - Is Not Flapping
			$service_prop_filter[] = array( 'is_flapping' => 0 );
			$service_prop_filtername_list[] = 'Is Not Flapping';
		}
		if( $number & nagstat::SERVICE_NOTIFICATIONS_DISABLED ) {    # 4096 - Notifications Disabled
			$service_prop_filter[] = array( 'notifications_enabled' => 0 );
			$service_prop_filtername_list[] = 'Notifications Disabled';
		}
		if( $number & nagstat::SERVICE_NOTIFICATIONS_ENABLED ) {    # 8192 - Notifications Enabled
			$service_prop_filter[] = array( 'notifications_enabled' => 1 );
			$service_prop_filtername_list[] = 'Notifications Enabled';
		}
		if( $number & nagstat::SERVICE_PASSIVE_CHECKS_DISABLED ) {    # 16384 - Passive Checks Disabled
			$service_prop_filter[] = array( 'accept_passive_checks' => 0 );
			$service_prop_filtername_list[] = 'Passive Checks Disabled';
		}
		if( $number & nagstat::SERVICE_PASSIVE_CHECKS_ENABLED ) {    # 32768 - Passive Checks Enabled
			$service_prop_filter[] = array( 'accept_passive_checks' => 1 );
			$service_prop_filtername_list[] = 'Passive Checks Enabled';
		}
		if( $number & nagstat::SERVICE_PASSIVE_CHECK ) {    # 65536 - Passive Checks
			$service_prop_filter[] = array( 'check_type' => 1 );
			$service_prop_filtername_list[] = 'Passive Checks';
		}
		if( $number & nagstat::SERVICE_ACTIVE_CHECK ) {    # 131072 - Active Checks
			$service_prop_filter[] = array( 'check_type' => 0 );
			$service_prop_filtername_list[] = 'Active Checks';
		}
		if( $number & nagstat::SERVICE_HARD_STATE ) {    # 262144 - In Hard State
			$service_prop_filter[] = array( 'state_type' => 1 );
			$service_prop_filtername_list[] = 'In Hard State';
		}
		if( $number & nagstat::SERVICE_SOFT_STATE ) {    # 524288 - In Soft State
			$service_prop_filter[] = array( 'state_type' => 0 );
			$service_prop_filtername_list[] = 'In Soft State';
		}

		$service_prop_filtername = join( ' &amp; ', $service_prop_filtername_list );
		}

		$servicefilter = LivestatusBackend::combineFilter( '-and', $service_prop_filter );

		return (array( $number, $service_prop_filtername, $servicefilter ));
	}
}
