<?php

/**
 * Custom exception for Livestatus errors
 */
class LivestatusException extends Exception {}

/*
 * Livestatus Class
 *
 * usage:
 *
 * access nagios data by various get<Table> methods.
 *
 * options is an hash array which provides filtering and other query options:
 *
 * example:
 *  $ls    = Livestatus::instance();
 *  $hosts = getHosts($options)
 *
 *  options = array(
 *      'auth'      => <bool>,              # authentication is enabled by default.
 *                                          # use this switch to disable it
 *
 *      'limit'     => <nr of records>,     # limit result set
 *
 *      'paging'    => $this,               # use paging. $this is a reference to
 *                                          # a kohana object to access the input
 *                                          # and template reference
 *
 *      'order'     => $order,              # sorting / order by structure, ex.:
 *                                          # array('name' => 'DESC')
 *                                          # array('host_name' => 'DESC', 'description' => 'DESC')
 *
 *      'filter'    => $filter,             # filter structure used to filter the
 *                                          # resulting objects
 *                                          # simple filter:
 *                                          #   array('name' => 'value')
 *                                          # simple filter with operator:
 *                                          #   array('name' => array('!=' => 'value'))
 *                                          # logical operator:
 *                                          #   array('-or' => array('name' => 'value', 'address' => 'othervalue'))
 *                                          # nested filter:
 *                                          #   array('-or' => array('name' => 'value', 'address' => array('~~' => 'othervalue')))
 *                                          #
 *                                          # see livestatus docs for details about available operators
 *  );
 *
 */
class Livestatus {
    private $auth            = false;
    private $connection      = null;
    private $config          = false;
    private $program_start   = false;
    private static $instance = false;

    /* constructor */
    public function __construct($config = null) {
        $config           = $config ? $config : 'livestatus';
        $this->config     = Kohana::config('database.'.$config);
        $this->auth       = Nagios_auth_Model::instance();
        $this->connection = new LivestatusConnection(array('path' => $this->config['path']));
    }

    /* singleton */
    public static function instance($config = null) {
        if (self::$instance === false)
            return new Livestatus($config);
        else
            return $ls;
    }

    /* rawQuery */
    public function rawQuery($query) {
        return $this->connection->writeSocket($query);
    }

    /* getHosts */
    public function getHosts($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'accept_passive_checks', 'acknowledged', 'action_url', 'action_url_expanded',
                'active_checks_enabled', 'address', 'alias', 'check_command', 'check_freshness', 'check_interval',
                'check_options', 'check_period', 'check_type', 'checks_enabled', 'childs', 'comments', 'current_attempt',
                'current_notification_number', 'display_name', 'event_handler_enabled', 'execution_time',
                'custom_variable_names', 'custom_variable_values',
                'first_notification_delay', 'flap_detection_enabled', 'groups', 'has_been_checked',
                'high_flap_threshold', 'icon_image', 'icon_image_alt', 'icon_image_expanded',
                'is_executing', 'is_flapping', 'last_check', 'last_notification', 'last_state_change',
                'latency', 'long_plugin_output', 'low_flap_threshold', 'max_check_attempts', 'name',
                'next_check', 'notes', 'notes_expanded', 'notes_url', 'notes_url_expanded', 'notification_interval',
                'notification_period', 'notifications_enabled', 'num_services_crit', 'num_services_ok',
                'num_services_pending', 'num_services_unknown', 'num_services_warn', 'num_services', 'obsess',
                'parents', 'percent_state_change', 'perf_data', 'plugin_output', 'process_performance_data',
                'retry_interval', 'scheduled_downtime_depth', 'state', 'state_type', 'modified_attributes_list',
                'pnpgraph_present'
            );
            $now = time();
            $options['callbacks'] = array(
                'duration' => function($row) use ($now) { return $row['last_state_change'] ? ($now - $row['last_state_change']) : ($now - $this->program_start); }
            );
        }
        return $this->getTable('hosts', $options);
    }

    /* getHostgroups */
    public function getHostgroups($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'name', 'alias', 'members', 'action_url', 'notes', 'notes_url',
            );
        }
        return $this->getTable('hostgroups', $options);
    }

    /* getHostsByGroup */
    public function getHostsByGroup($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'icon_image', 'icon_image_alt', 'name', 'services_with_state', 'action_url',
                'action_url', 'notes_url','pnpgraph_present'
            );
        }
        return $this->getTable('hostsbygroup', $options);
    }

    /* getServices */
    public function getServices($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'accept_passive_checks', 'acknowledged', 'action_url', 'action_url_expanded',
                'active_checks_enabled', 'check_command', 'check_interval', 'check_options',
                'check_period', 'check_type', 'checks_enabled', 'comments', 'current_attempt',
                'current_notification_number', 'description', 'event_handler', 'event_handler_enabled',
                'custom_variable_names', 'custom_variable_values', 'display_name',
                'execution_time', 'first_notification_delay', 'flap_detection_enabled', 'groups',
                'has_been_checked', 'high_flap_threshold', 'host_acknowledged', 'host_action_url_expanded',
                'host_active_checks_enabled', 'host_address', 'host_alias', 'host_checks_enabled', 'host_check_type',
                'host_comments', 'host_groups', 'host_has_been_checked', 'host_icon_image_expanded', 'host_icon_image_alt',
                'host_is_executing', 'host_is_flapping', 'host_name', 'host_notes_url_expanded',
                'host_notifications_enabled', 'host_scheduled_downtime_depth', 'host_state', 'host_accept_passive_checks',
                'icon_image', 'icon_image_alt', 'icon_image_expanded', 'is_executing', 'is_flapping',
                'last_check', 'last_notification', 'last_state_change', 'latency', 'long_plugin_output',
                'low_flap_threshold', 'max_check_attempts', 'next_check', 'notes', 'notes_expanded',
                'notes_url', 'notes_url_expanded', 'notification_interval', 'notification_period',
                'notifications_enabled', 'obsess', 'percent_state_change', 'perf_data',
                'plugin_output', 'process_performance_data', 'retry_interval', 'scheduled_downtime_depth',
                'state', 'state_type', 'modified_attributes_list', 'pnpgraph_present'
            );
            $now = time();
            $options['callbacks'] = array(
                'duration' => function($row) use ($now) { return $row['last_state_change'] ? ($now - $row['last_state_change']) : ($now - $this->program_start); }
            );
        }
        return $this->getTable('services', $options);
    }

    /* getServicegroups */
    public function getServicegroups($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'name', 'alias', 'members', 'action_url', 'notes notes_url',
            );
        }
        return $this->getTable('servicegroups', $options);
    }

    /* getContacts */
    public function getContacts($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'name', ' alias', 'email', 'pager', 'service_notification_period', 'host_notification_period',
            );
        }
        return $this->getTable('contacts', $options);
    }

    /* getContactgroups */
    public function getContactgroups($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'name', 'alias', 'members',
            );
        }
        return $this->getTable('contactgroups', $options);
    }

    /* getCommands */
    public function getCommands($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'name', 'line',
            );
        }
        return $this->getTable('commands', $options);
    }

    /* getTimeperiods */
    public function getTimeperiods($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'name', 'alias',
            );
        }
        return $this->getTable('timeperiods', $options);
    }

    /* getLogs */
    public function getLogs($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'class', 'time', 'type', 'state', 'host_name', 'service_description', 'plugin_output',
                'message', 'options', 'contact_name', 'command_name', 'state_type', 'current_host_groups',
                'current_service_groups',
            );
        }
        return $this->getTable('log', $options);
    }

    /* getComments */
    public function getComments($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'author', 'comment', 'entry_time', 'entry_type', 'expires',
                'expire_time', 'host_name', 'id', 'persistent', 'service_description',
                'source', 'type',
            );
        }
        return $this->getTable('comments', $options);
    }

    /* getDowntimes */
    public function getDowntimes($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'author', 'comment', 'end_time', 'entry_time', 'fixed', 'host_name',
                'id', 'start_time', 'service_description', 'triggered_by',
            );
        }
        return $this->getTable('downtimes', $options);
    }

    /* getProcessInfo */
    public function getProcessInfo($options = null) {
        if(!isset($options['columns'])) {
            $options['columns'] = array(
                'accept_passive_host_checks', 'accept_passive_service_checks', 'check_external_commands',
                'check_host_freshness', 'check_service_freshness', 'enable_event_handlers', 'enable_flap_detection',
                'enable_notifications', 'execute_host_checks', 'execute_service_checks', 'last_command_check',
                'last_log_rotation', 'livestatus_version', 'nagios_pid', 'obsess_over_hosts', 'obsess_over_services',
                'process_performance_data', 'program_start', 'program_version', 'interval_length',
                'cached_log_messages', 'connections', 'connections_rate', 'host_checks',
                'host_checks_rate', 'requests', 'requests_rate', 'service_checks',
                'service_checks_rate', 'neb_callbacks', 'neb_callbacks_rate',
            );
        }
        $objects = $this->getTable('status', $options);
        $this->program_start = $objects[0]['program_start'];
        return (object) $objects[0];
    }


    /* getHostTotals */
    public function getHostTotals($options = null) {
/*
TODO: implement
		if (config::get('checks.show_passive_as_active', '*')) {
			$active_checks_condition = "Stats: active_checks_enabled = 1\nStats: accept_passive_checks = 1\nStatsOr: 2";
			$disabled_checks_condition = "Stats: active_checks_enabled != 1\nStats: accept_passive_checks != 1\nStatsAnd: 2";
		} else {
			$active_checks_condition = "Stats: active_checks_enabled = 1";
			$disabled_checks_condition = "Stats: active_checks_enabled != 1";
		}
*/
        $stats = array(
            'total'                             => array( 'state' => array( '!=' => 999 )),
            'total_active'                      => array( 'check_type' => 0 ),
            'total_passive'                     => array( 'check_type' => 1 ),
            'pending'                           => array( 'has_been_checked' => 0 ),
            'pending_and_disabled'              => array( 'has_been_checked' => 0, 'active_checks_enabled' => 0 ),
            'pending_and_scheduled'             => array( 'has_been_checked' => 0, 'scheduled_downtime_depth' => array('>' => 0 )),
            'up'                                => array( 'has_been_checked' => 1, 'state' => 0 ),
            'up_and_disabled_active'            => array( 'check_type' => 0, 'has_been_checked' => 1, 'state' => 0, 'active_checks_enabled' => 0 ),
            'up_and_disabled_passive'           => array( 'check_type' => 1, 'has_been_checked' => 1, 'state' => 0, 'active_checks_enabled' => 0 ),
            'up_and_scheduled'                  => array( 'has_been_checked' => 1, 'state' => 0, 'scheduled_downtime_depth' => array( '>' => 0 )),
            'down'                              => array( 'has_been_checked' => 1, 'state' => 1 ),
            'down_and_ack'                      => array( 'has_been_checked' => 1, 'state' => 1, 'acknowledged' => 1 ),
            'down_and_scheduled'                => array( 'has_been_checked' => 1, 'state' => 1, 'scheduled_downtime_depth' => array( '>' => 0 )),
            'down_and_disabled_active'          => array( 'check_type' => 0, 'has_been_checked' => 1, 'state' => 1, 'active_checks_enabled' => 0 ),
            'down_and_disabled_passive'         => array( 'check_type' => 1, 'has_been_checked' => 1, 'state' => 1, 'active_checks_enabled' => 0 ),
            'down_and_unhandled'                => array( 'has_been_checked' => 1, 'state' => 1, 'active_checks_enabled' => 1, 'acknowledged' => 0, 'scheduled_downtime_depth' => 0 ),
            'unreachable'                       => array( 'has_been_checked' => 1, 'state' => 2 ),
            'unreachable_and_ack'               => array( 'has_been_checked' => 1, 'state' => 2, 'acknowledged' => 1 ),
            'unreachable_and_scheduled'         => array( 'has_been_checked' => 1, 'state' => 2, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
            'unreachable_and_disabled_active'   => array( 'check_type' => 0, 'has_been_checked' => 1, 'state' => 2, 'active_checks_enabled' => 0 ),
            'unreachable_and_disabled_passive'  => array( 'check_type' => 1, 'has_been_checked' => 1, 'state' => 2, 'active_checks_enabled' => 0 ),
            'unreachable_and_unhandled'         => array( 'has_been_checked' => 1, 'state' => 2, 'active_checks_enabled' => 1, 'acknowledged' => 0, 'scheduled_downtime_depth' => 0 ),
            'flapping'                          => array( 'is_flapping' => 1 ),
            'flapping_disabled'                 => array( 'flap_detection_enabled' => 0 ),
            'notifications_disabled'            => array( 'notifications_enabled' => 0 ),
            'eventhandler_disabled'             => array( 'event_handler_enabled' => 0 ),
            'active_checks_disabled_active'     => array( 'check_type' => 0, 'active_checks_enabled' => 0 ),
            'active_checks_disabled_passive'    => array( 'check_type' => 1, 'active_checks_enabled' => 0 ),
            'passive_checks_disabled'           => array( 'accept_passive_checks' => 0 ),
            'outages'                           => array( 'state' => 1, 'childs' => array( '!=' => '' ) ),
        );
        return (object) $this->getStats('hosts', $stats, $options);
    }


    /* getServiceTotals */
    public function getServiceTotals($options = null) {
/*
TODO: implement
		if (config::get('checks.show_passive_as_active', '*')) {
			$active_checks_condition = "Stats: active_checks_enabled = 1\nStats: accept_passive_checks = 1\nStatsOr: 2";
			$disabled_checks_condition = "Stats: active_checks_enabled != 1\nStats: accept_passive_checks != 1\nStatsAnd: 2";
		} else {
			$active_checks_condition = "Stats: active_checks_enabled = 1";
			$disabled_checks_condition = "Stats: active_checks_enabled != 1";
		}
*/
        $stats = array(
            'total'                             => array( 'description' => array( '!=' => '' ) ),
            'total_active'                      => array( 'check_type' => 0 ),
            'total_passive'                     => array( 'check_type' => 1 ),
            'pending'                           => array( 'has_been_checked' => 0 ),
            'pending_and_disabled'              => array( 'has_been_checked' => 0, 'active_checks_enabled' => 0 ),
            'pending_and_scheduled'             => array( 'has_been_checked' => 0, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
            'ok'                                => array( 'has_been_checked' => 1, 'state' => 0 ),
            'ok_and_scheduled'                  => array( 'has_been_checked' => 1, 'state' => 0, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
            'ok_and_disabled_active'            => array( 'check_type' => 0, 'has_been_checked' => 1, 'state' => 0, 'active_checks_enabled' => 0 ),
            'ok_and_disabled_passive'           => array( 'check_type' => 1, 'has_been_checked' => 1, 'state' => 0, 'active_checks_enabled' => 0 ),
            'warning'                           => array( 'has_been_checked' => 1, 'state' => 1 ),
            'warning_and_scheduled'             => array( 'has_been_checked' => 1, 'state' => 1, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
            'warning_and_disabled_active'       => array( 'check_type' => 0, 'has_been_checked' => 1, 'state' => 1, 'active_checks_enabled' => 0 ),
            'warning_and_disabled_passive'      => array( 'check_type' => 1, 'has_been_checked' => 1, 'state' => 1, 'active_checks_enabled' => 0 ),
            'warning_and_ack'                   => array( 'has_been_checked' => 1, 'state' => 1, 'acknowledged' => 1 ),
            'warning_on_down_host'              => array( 'has_been_checked' => 1, 'state' => 1, 'host_state' => array( '!=' => 0 ) ),
            'warning_and_unhandled'             => array( 'has_been_checked' => 1, 'state' => 1, 'host_state' => 0, 'active_checks_enabled' => 1, 'acknowledged' => 0, 'scheduled_downtime_depth' => 0 ),
            'critical'                          => array( 'has_been_checked' => 1, 'state' => 2 ),
            'critical_and_scheduled'            => array( 'has_been_checked' => 1, 'state' => 2, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
            'critical_and_disabled_active'      => array( 'check_type' => 0, 'has_been_checked' => 1, 'state' => 2, 'active_checks_enabled' => 0 ),
            'critical_and_disabled_passive'     => array( 'check_type' => 1, 'has_been_checked' => 1, 'state' => 2, 'active_checks_enabled' => 0 ),
            'critical_and_ack'                  => array( 'has_been_checked' => 1, 'state' => 2, 'acknowledged' => 1 ),
            'critical_on_down_host'             => array( 'has_been_checked' => 1, 'state' => 2, 'host_state' => array( '!=' => 0 ) ),
            'critical_and_unhandled'            => array( 'has_been_checked' => 1, 'state' => 2, 'host_state' => 0, 'active_checks_enabled' => 1, 'acknowledged' => 0, 'scheduled_downtime_depth' => 0 ),
            'unknown'                           => array( 'has_been_checked' => 1, 'state' => 3 ),
            'unknown_and_scheduled'             => array( 'has_been_checked' => 1, 'state' => 3, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
            'unknown_and_disabled_active'       => array( 'check_type' => 0, 'has_been_checked' => 1, 'state' => 3, 'active_checks_enabled' => 0 ),
            'unknown_and_disabled_passive'      => array( 'check_type' => 1, 'has_been_checked' => 1, 'state' => 3, 'active_checks_enabled' => 0 ),
            'unknown_and_ack'                   => array( 'has_been_checked' => 1, 'state' => 3, 'acknowledged' => 1 ),
            'unknown_on_down_host'              => array( 'has_been_checked' => 1, 'state' => 3, 'host_state' => array( '!=' => 0 ) ),
            'unknown_and_unhandled'             => array( 'has_been_checked' => 1, 'state' => 3, 'host_state' => 0, 'active_checks_enabled' => 1, 'acknowledged' => 0, 'scheduled_downtime_depth' => 0 ),
            'flapping'                          => array( 'is_flapping' => 1 ),
            'flapping_disabled'                 => array( 'flap_detection_enabled' => 0 ),
            'notifications_disabled'            => array( 'notifications_enabled' => 0 ),
            'eventhandler_disabled'             => array( 'event_handler_enabled' => 0 ),
            'active_checks_disabled_active'     => array( 'check_type' => 0, 'active_checks_enabled' => 0 ),
            'active_checks_disabled_passive'    => array( 'check_type' => 1, 'active_checks_enabled' => 0 ),
            'passive_checks_disabled'           => array( 'accept_passive_checks' => 0 ),
        );
        return (object) $this->getStats('services', $stats, $options);
    }

    /* getHostPerformance */
    public function getHostPerformance($last_program_start, $options = null) {
        return $this->getPerformanceStats('hosts', $last_program_start, $options);
    }

    /* getServicePerformance */
    public function getServicePerformance($last_program_start, $options = null) {
        $stats = $this->getPerformanceStats('services', $last_program_start, $options);
        return $stats;
    }

    /* getPerformanceStats */
    public function getPerformanceStats($type, $last_program_start, $options = null) {
        $result = array();
        $now    = time();
        $min1   = $now - 60;
        $min5   = $now - 300;
        $min15  = $now - 900;
        $min60  = $now - 3600;
        $minall = $last_program_start;

        $stats = array(
            'active_sum'      => array( 'check_type' => 0 ),
            'active_1_sum'    => array( 'check_type' => 0, 'has_been_checked' => 1, 'last_check' => array( '>=' => $min1 ) ),
            'active_5_sum'    => array( 'check_type' => 0, 'has_been_checked' => 1, 'last_check' => array( '>=' => $min5 ) ),
            'active_15_sum'   => array( 'check_type' => 0, 'has_been_checked' => 1, 'last_check' => array( '>=' => $min15 ) ),
            'active_60_sum'   => array( 'check_type' => 0, 'has_been_checked' => 1, 'last_check' => array( '>=' => $min60 ) ),
            'active_all_sum'  => array( 'check_type' => 0, 'has_been_checked' => 1, 'last_check' => array( '>=' => $minall ) ),

            'passive_sum'     => array( 'check_type' => 1 ),
            'passive_1_sum'   => array( 'check_type' => 1, 'has_been_checked' => 1, 'last_check' => array( '>=' => $min1 ) ),
            'passive_5_sum'   => array( 'check_type' => 1, 'has_been_checked' => 1, 'last_check' => array( '>=' => $min5 ) ),
            'passive_15_sum'  => array( 'check_type' => 1, 'has_been_checked' => 1, 'last_check' => array( '>=' => $min15 ) ),
            'passive_60_sum'  => array( 'check_type' => 1, 'has_been_checked' => 1, 'last_check' => array( '>=' => $min60 ) ),
            'passive_all_sum' => array( 'check_type' => 1, 'has_been_checked' => 1, 'last_check' => array( '>=' => $minall ) ),
        );
        $data   = $this->getStats($type, $stats);
        $result = array_merge($result, $data);

        # add stats for active checks
        $stats = array(
            'execution_time_sum'      => array( '-sum' => 'execution_time' ),
            'latency_sum'             => array( '-sum' => 'latency' ),
            'active_state_change_sum' => array( '-sum' => 'percent_state_change' ),
            'execution_time_min'      => array( '-min' => 'execution_time' ),
            'latency_min'             => array( '-min' => 'latency' ),
            'active_state_change_min' => array( '-min' => 'percent_state_change' ),
            'execution_time_max'      => array( '-max' => 'execution_time' ),
            'latency_max'             => array( '-max' => 'latency' ),
            'active_state_change_max' => array( '-max' => 'percent_state_change' ),
            'execution_time_avg'      => array( '-avg' => 'execution_time' ),
            'latency_avg'             => array( '-avg' => 'latency' ),
            'active_state_change_avg' => array( '-avg' => 'percent_state_change' ),
        );

        $data   = $this->getStats($type, $stats, array('filter' => array('has_been_checked' => 1, 'check_type' => 0)));
        $result = array_merge($result, $data);

        # add stats for passive checks
        $stats = array(
            'passive_state_change_sum' => array( '-sum' => 'percent_state_change' ),
            'passive_state_change_min' => array( '-min' => 'percent_state_change' ),
            'passive_state_change_max' => array( '-max' => 'percent_state_change' ),
            'passive_state_change_avg' => array( '-avg' => 'percent_state_change' ),
        );
        $data   = $this->getStats($type, $stats, array('filter' => array('has_been_checked' => 1, 'check_type' => 1)));
        $result = array_merge($result, $data);

        return (object) $result;
    }

    /* combineFilter */
    public function combineFilter($operator, $filter) {

        if(!isset($operator) and $operator != '-or' and $operator != '-and') {
            throw new LivestatusException("unknown operator in combine_filter(): ".$operator);
        }

        if(!isset($filter)) { return ""; }

        if(!is_array($filter)) {
            throw new LivestatusException("expected array in combine_filter(): ");
        }

        if(count($filter) == 0) { return ""; }

        if(count($filter) == 1) {
            return($filter[0]);
        }

        return array($operator => $filter );
    }


    /********************************************************
     * INTERNAL FUNCTIONS
     *******************************************************/
    private function getTable($table, $options = null) {
        $filter = "";
        if(isset($options['filter'])) {
            $filter = $this->getQueryFilter(false, $options['filter']);
        }
        $this->page_step1($table, $options);

        if(isset($options['extracolumns']) and is_array($options['extracolumns'])) {
            $options['columns'] = array_merge($options['columns'], $options['extracolumns']);
        }
        $columns = join(" ", $options['columns']);

        $query  = "Columns: ".$columns."\n";
        $query .= $filter;
        $objects = $this->query($table, $query, $options['columns']);
        $objects = $this->page_step2($objects, $options);
        $objects = $this->objects2Assoc($objects, $options['columns'], isset($options['callbacks']) ? $options['callbacks'] : null);
        $objects = $this->sort($objects, $table, $options);
        return $objects;
    }

    private function getStats($table, $stats, $options = null) {
        $queryFilter = "";
        if(isset($options['filter'])) {
            $queryFilter = $this->getQueryFilter(false, $options['filter']);
        }
        $columns     = array();
        foreach($stats as $key => $filter) {
            $queryFilter .= $this->getQueryFilter(true, $filter, null, null, 'And');
            array_push($columns, $key);
        }
        $queryFilter .= $this->auth($table, $options);
        $objects = $this->query($table, $queryFilter, $columns);
        $objects = $this->objects2Assoc($objects, $columns);
        return $objects[0];
    }

    private function auth($table, $options = null) {
        if(isset($options['auth']) && $options['auth'] === true) {
            return "";
        }
        if($table == 'hosts' && $this->auth->view_hosts_root) {
            return "";
        }
        if($table == 'services' && $this->auth->view_services_root) {
            return "";
        }
        return "AuthUser: ".$this->auth->user."\n";
    }

    private function page_step1($table, $options = null) {
        if(isset($options['paging'])) {
            $page = $options['paging'];
            $items_per_page = $page->input->get('items_per_page', config::get('pagination.default.items_per_page', '*'));
        }
        elseif(isset($options['paginggroup'])) {
            $page = $options['paginggroup'];
            $items_per_page = $page->input->get('items_per_page', config::get('pagination.group_items_per_page', '*'));
        } else {
            return;
        }
        $items_per_page = $page->input->get('custom_pagination_field', $items_per_page);
        $current_page   = $page->input->get('page', 1);
        $total          = $this->get_query_size($table, $options);
        if(isset($total)) {
            # then set the limit for the real query
            $options['options']['limit'] = $current_page * $items_per_page + 1;
        }

        $pagination = new Pagination(array(
                            'total_items'     => $total,
                            'items_per_page'  => $items_per_page,
                          ));
        $page->template->content->pagination     = $pagination;
        $page->template->content->total_items    = $total;
        $page->template->content->items_per_page = $items_per_page;
        $page->template->content->page           = $current_page;
        return;
    }

    private function page_step2($result, $options = null) {
        if(isset($options['paging'])) {
            $page = $options['paging'];
        }
        elseif(isset($options['paginggroup'])) {
            $page = $options['paginggroup'];
        } else {
            return $result;
        }
        $items_per_page = $page->template->content->items_per_page;
        $current_page   = $page->template->content->page;
        $offset         = ($current_page-1) * $items_per_page;
        if($offset >= $page->template->content->total_items) { $offset = 0; }
        $result         = array_splice($result, $offset, $items_per_page);
        return $result;
    }

    private function sort($objects, $table, $options = null) {
        if($options == null || !isset($options['order'])) {
            return $objects;
        }

        $orderby = array();
        foreach($options['order'] as $field => $order) {
            $orderby[] = $field;
            $orderby[] = $order;
        }

        # don't sort when using the default order already
        if(count($orderby) == 2) {
            switch($table) {
                case 'hosts':    if($orderby[0] == 'name' && $orderby[1] == 'ASC') { return $objects; }
                                 break;
                case 'services': if($orderby[0] == 'description' && $orderby[1] == 'ASC') { return $objects; }
                                 break;
                default:         throw new LivestatusException("unsupported table $table in sort()");
                                 break;
            }
        }

        usort($objects, $this->build_sorter($orderby));
        return $objects;
    }

    public function build_sorter($orderby) {
/* TODO: support multiple sort fields */
        $key   = $orderby[0];
        $order = $orderby[1];
        if($order == 'DESC') {
            return function ($a, $b) use ($key) {
                return -1*strnatcmp($a[$key], $b[$key]);
            };

        }
        return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    }

    private function get_query_size($table, $options = null) {
        if(!isset($options['paging']) and !isset($options['paginggroup'])) { return; }
/* TODO: use paging only when using default sorting */

        switch($table) {
            case 'services':
            case 'hosts':           $filter = array('state' => array('!=' => 999));
                                    break;
            case 'servicegroups':
            case 'hostgroups':      $filter = array('name' => array('!=' => ""));
                                    break;
            default:                throw new LivestatusException("table $table not implemented in get_query_size()");

        }
        $stats = array( 'total' => $filter );
        $data  = $this->getStats($table, $stats, $options);
        return $data['total'];
    }

    private function query($table, $filter, $columns) {
        $query  = "GET $table\n";
        $query .= "OutputFormat:json\n";
        $query .= "KeepAlive: on\n";
        $query .= "ResponseHeader: fixed16\n";
        $query .= $filter."\n";

        $start   = microtime(true);
        $rc      = $this->rawQuery($query);
        $head    = $this->connection->readSocket(16);
        $status  = substr($head, 0, 3);
        if($status != 200)
            throw new LivestatusException("Invalid request: $head");
        $len     = intval(trim(substr($head, 4, 15)));
        $body    = $this->connection->readSocket($len);
        if(empty($body))
            throw new LivestatusException("empty body");
        $objects = json_decode(utf8_encode($body));

        $stop = microtime(true);
        if ($this->config['benchmark'] == TRUE) {
            Database::$benchmarks[] = array('query' => $query, 'time' => $stop - $start, 'rows' => count($objects));
        }
        if ($objects === null) {
            throw new LivestatusException("Invalid output");
        }
        return $objects;
    }

    public function getQueryFilter($stats = false, $filter = null, $op = null, $name = null, $listop = null) {
        if($filter === null) { return ""; }
/* TODO: implement proper escaping */

        # remove empty elements
        while(is_array($filter) and isset($filter[0]) and $filter[0] === '') { array_shift($filter); }

        $query = "";
        if($this->is_assoc($filter)) {
            $iter = 0;
            foreach($filter as $key => $val) {
                $iter++;
                switch($key) {
                    case '-or':  $query .= $this->getQueryFilter($stats, $val, $op, $name, 'Or');
                                 break;
                    case '-and': $query .= $this->getQueryFilter($stats, $val, $op, $name, 'And');
                                 break;
                    case  '=':
                    case  '~':
                    case  '=~':
                    case  '~~':
                    case  '<':
                    case  '>':
                    case  '<=':
                    case  '>=':
                    case  '~':
                    case '!=':
                    case '!~':
                    case '!=~':
                    case '!~~':  $query .= $this->getQueryFilter($stats, $val, $key, $name, 'And');
                                 break;
                    case '-sum':
                    case '-avg':
                    case '-min':
                    case '-max': $query .= $this->getQueryFilter($stats, $val, substr($key, 1), '');
                                 break;
                    default:     $query .= $this->getQueryFilter($stats, $val, '=', $key, 'And');
                                 break;
                }
            }
            if($iter > 1 && $listop === null) { $listop = 'And'; }
            if($iter > 1 && $listop !== null) {
                $query .= ($stats ? 'Stats' : '').$listop.": ".$iter."\n";
            }
            return $query;
        }

        if($op === null and $listop !== null) {
            $op = "=";
        }

        if($op !== null) {
            if(!is_array($filter)) {
                $query = ($stats ? 'Stats' : 'Filter').": $name $op $filter\n";
            }
            else {
                foreach($filter as $val) {
                    if(is_array($val)) {
                        $query .= $this->getQueryFilter($stats, $val, $op, $name);
                    } else {
                        $query .= ($stats ? 'Stats' : 'Filter').": $name $op $val\n";
                    }
                }
                if(count($filter) > 1) {
                    $query .= ($stats ? 'Stats' : '').$listop.": ".count($filter)."\n";
                }
            }
            return $query;
        }

        if($filter === "") { return ""; }

        throw new LivestatusException("broken filter");
    }

    private function is_assoc($array) {
        return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
    }

    private function objects2Assoc($objects, $columns, $callbacks = null) {
        $cols = $columns;
        if(!is_array($cols)) {
            $cols = array($columns);
        }
        $result = array();
        foreach($objects as $o) {
            $n = array();
            $i = 0;
            foreach($cols as $c) {
                $n[$c] = $o[$i];
                $i++;
            }
            if($callbacks != null) {
                foreach($callbacks as $key => $cb) {
                    $n[$key] = $cb($n);
                }
            }
            array_push($result, $n);
        }
        return $result;
    }
}

/*
 * Livestatus Connection Class
 */
class LivestatusConnection {
    private $connection  = null;
    private $timeout     = 10;

    public function __construct($options) {
        $this->connectionString = $options['path'];
        $this->connect();
        return $this;
    }

    public function __destruct() {
        $this->close();
    }

    public function connect() {
        list($type, $address) = explode(':', $this->connectionString, 2);

        if($type == 'tcp') {
            list($host, $port) = explode(':', $address, 2);
            $this->connection = fsockopen($address, $port, $errno, $errstr, $this->timeout);
        }
        elseif($type == 'unix') {
            if(!file_exists($address)) {
                throw new LivestatusException("connection failed, make sure $address exists\n");
            }
            $this->connection = fsockopen('unix://'.$address, NULL, $errno, $errstr, $this->timeout);
        }
        else {
            throw new LivestatusException("unknown connection type: '$type', valid types are 'tcp' and 'unix'\n");
        }

        if(!$this->connection) {
            throw new LivestatusException("connection ".$this->connectionString." failed: ".$errstr);
        }
    }

    public function close() {
        if($this->connection != null) {
            fclose($this->connection);
            $this->connection = null;
        }
    }

    public function writeSocket($str) {
        return fwrite($this->connection, $str);
    }

    public function readSocket($len) {
        $offset     = 0;
        $socketData = '';

        while($offset < $len) {
            if(($data = fread($this->connection, $len - $offset)) === false) {
                return false;
            }

            if(($dataLen = strlen($data)) === 0) {
                break;
            }

            $offset     += $dataLen;
            $socketData .= $data;
        }

        return $socketData;
    }
}

?>
