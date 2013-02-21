<?php

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
 *      'auth'       => <bool>,             # authentication is enabled by default.
 *                                          # use this switch to disable it
 *
 *      'limit'      => <nr of records>,    # limit result set
 *
 *      'paging'     => $this,              # use paging. $this is a reference to
 *                                          # a kohana object to access the input
 *                                          # and template reference
 *
 *      'order'      => $order,             # sorting / order by structure, ex.:
 *                                          # array('name' => 'DESC')
 *                                          # array('host_name' => 'DESC', 'description' => 'DESC')
 *      'order_mappings' => $map,           # Makes it possible to map a fictive column name to a real
 *                                          # column name for sorting. Useful when using 'callback' columns.
 *
 *      'filter'     => $filter,            # filter structure used to filter the
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
 *                                          # filter can also be a string containing an expression with livestatus operations,
 *                                          # grouped with logical operators. Example:
 *                                          #
 *                                          # 'host_name ~~ "name regexp" and (status = 1 or status = 2)'
 *                                          #
 *                                          # see livestatus docs for details about available operators
 *      'extra_header' =>                   # A raw livestatus header block, useful for example to raw filters.
 *  );
 *
 */
require_once("op5/livestatus.php");

class Livestatus {
	private static $instance = false;

	/* singleton */
	public static function instance($config = null) {
		if (self::$instance !== false) {
			return self::$instance;
		}
		return self::$instance = new self($config);
	}

	private $program_start = false;
	private $backend = false;

	public function __construct($config = null) {
		$this->backend = new LiveStatusBackend($config);
	}

	public function getBackend() {
		return $this->backend;
	}

	public function calc_duration($row) {
		$now = time();
		return $row['last_state_change'] ? ($now - $row['last_state_change']) : ($now - $this->program_start);
	}

	/**
	 * Fugly but the right place to .. place it.
	 * Originally intended for config listing.
	 *
	 * @todo this only suits string columns, not lists, not ints
	 *
	 * @return array
	 */
	function filterable_columns() {
		return array(
			'services' => array(
				'host_name',
				'description'
			),
			'hosts' => array(
				'name'
			),
			'hostgroups' => array(
				'name'
			),
			'servicegroups' => array(
				'name'
			),
			'contacts' => array(
				'name'
			),
			'contactgroups' => array(
				'name'
			),
			'commands' => array(
				'name'
			)
		);
	}

	public function handle_passive_as_active($row) {
		static $passive_as_active = null;
		if ($passive_as_active === null)
			$passive_as_active = config::get('checks.show_passive_as_active', '*');

		if ($passive_as_active)
			return $row['active_checks_enabled'] || $row['accept_passive_checks'];
		else
			return $row['active_checks_enabled'];
	}

	public function handle_host_passive_as_active($row) {
		static $passive_as_active = null;
		if ($passive_as_active === null)
			$passive_as_active = config::get('checks.show_passive_as_active', '*');

		if ($passive_as_active)
			return $row['host_active_checks_enabled'] || $row['host_accept_passive_checks'];
		else
			return $row['host_active_checks_enabled'];
	}

	/* getHosts */
	public function getHosts($options = null) {
		if(!isset($options['columns'])) {
			$options['columns'] = array(
					'accept_passive_checks', 'acknowledged', 'action_url', 'action_url_expanded',
					'active_checks_enabled', 'address', 'alias', 'check_command', 'check_freshness', 'check_interval',
					'check_options', 'check_period', 'check_type', 'checks_enabled', 'childs', 'comments', 'current_attempt',
					'current_notification_number', 'display_name', 'event_handler_enabled', 'execution_time',
					'custom_variable_names', 'custom_variable_values', 'hourly_value',
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
			$options['callbacks'] = array(
				'duration' => array($this, 'calc_duration'),
				'checks_enabled' => array($this, 'handle_passive_as_active')
			);

			$options['order_mappings'] = array(
				'duration'              => array( '!last_state_change' ),
				
				/* This is not actually correct... But isn't possible to do better in LS*/
				'checks_enabled' => array( 'active_checks_enabled', 'accept_passive_checks' )
			);
		}
		return $this->backend->getTable('hosts', $options);
	}

	/* getHostgroups */
	public function getHostgroups($options = null) {
		if(!isset($options['columns'])) {
			$options['columns'] = array(
					'name', 'alias', /*'members',*/ 'action_url', 'notes', 'notes_url',

					/* Slow, skip by default
					'members_with_state',
					'worst_host_state',
					'num_hosts',
					'num_hosts_pending',
					'num_hosts_up',
					'num_hosts_down',
					'num_hosts_unreach',
					'num_services',
					'worst_service_state',
					'num_services_pending',
					'num_services_ok',
					'num_services_warn',
					'num_services_crit',
					'num_services_unknown',
					'worst_service_hard_state',
					'num_services_hard_ok',
					'num_services_hard_warn',
					'num_services_hard_crit',
					'num_services_hard_unknown'
					*/
						
			);
		}
		return $this->backend->getTable('hostgroups', $options);
	}

	/* getHostsByGroup */
	public function getHostsByGroup($options = null) {
		if(!isset($options['columns'])) {
			$options['columns'] = array(
					'icon_image', 'icon_image_alt', 'name', 'services_with_state', 'action_url',
					'action_url', 'notes_url','pnpgraph_present'
			);
		}
		return $this->backend->getTable('hostsbygroup', $options);
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
					'host_notifications_enabled', 'host_scheduled_downtime_depth', 'host_state', 'host_state_type',
					'host_accept_passive_checks', 'host_current_attempt', 'host_max_check_attempts',
					'host_display_name',
					'icon_image', 'icon_image_alt', 'icon_image_expanded', 'is_executing', 'is_flapping',
					'last_check', 'last_notification', 'last_state_change', 'latency', 'long_plugin_output',
					'low_flap_threshold', 'max_check_attempts', 'next_check', 'notes', 'notes_expanded',
					'notes_url', 'notes_url_expanded', 'notification_interval', 'notification_period',
					'notifications_enabled', 'obsess', 'percent_state_change', 'perf_data',
					'plugin_output', 'process_performance_data', 'retry_interval', 'scheduled_downtime_depth',
					'state', 'state_type', 'modified_attributes_list', 'pnpgraph_present'
			);
			$options['callbacks'] = array(
				'duration' => array($this, 'calc_duration'),
				'checks_enabled' => array($this, 'handle_passive_as_active'),
				'host_checks_enabled' => array($this, 'handle_host_passive_as_active')
			);

			$options['order_mappings'] = array(
				'duration'              => array( '!last_state_change' ),
				
				/* This is not actually correct... But isn't possible to do better in LS*/
				'checks_enabled' => array( 'active_checks_enabled', 'accept_passive_checks' )
			);
		}
		return $this->backend->getTable('services', $options);
	}

	/* getServicegroups */
	public function getServicegroups($options = null) {
		if(!isset($options['columns'])) {
			$options['columns'] = array(
					'name', 'alias', /*'members',*/ 'action_url', 'notes', 'notes_url',
					/* Slow, skip by default
					'members_with_state',
					'worst_service_state',
					'num_services',
					'num_services_ok',
					'num_services_warn',
					'num_services_crit',
					'num_services_unknown',
					'num_services_pending',
					'num_services_hard_ok',
					'num_services_hard_warn',
					'num_services_hard_crit',
					'num_services_hard_unknown'
					*/
			);
		}
		return $this->backend->getTable('servicegroups', $options);
	}

	/* getContacts */
	public function getContacts($options = null) {
		if(!isset($options['columns'])) {
			$options['columns'] = array(
					'name',
					'alias',
					'email',
					'pager',
					'service_notification_period',
					'host_notification_period',
					'can_submit_commands'
			);
		}
		return $this->backend->getTable('contacts', $options);
	}

	/* getContactgroups */
	public function getContactgroups($options = null) {
		if(!isset($options['columns'])) {
			$options['columns'] = array(
					'name', 'alias', /*'members',*/
			);
		}
		return $this->backend->getTable('contactgroups', $options);
	}

	/* getCommands */
	public function getCommands($options = null) {
		if(!isset($options['columns'])) {
			$options['columns'] = array(
					'name', 'line',
			);
		}
		return $this->backend->getTable('commands', $options);
	}

	/* getTimeperiods */
	public function getTimeperiods($options = null) {
		if(!isset($options['columns'])) {
			$options['columns'] = array(
					'name', 'alias',
			);
		}
		return $this->backend->getTable('timeperiods', $options);
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
		return $this->backend->getTable('log', $options);
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
		return $this->backend->getTable('comments', $options);
	}

	/* getDowntimes */
	public function getDowntimes($options = null) {
		if(!isset($options['columns'])) {
			$options['columns'] = array(
					'author', 'comment', 'end_time', 'entry_time', 'duration', 'fixed', 'host_name',
					'id', 'start_time', 'service_description', 'triggered_by', 'type'
			);
		}
		return $this->backend->getTable('downtimes', $options);
	}

	/* getProcessInfo */
	public function getProcessInfo($options = null) {
		if(!isset($options['columns'])) {
			$options['columns'] = array(
					'accept_passive_host_checks', 'accept_passive_service_checks', 'check_external_commands',
					'check_host_freshness', 'check_service_freshness', 'enable_event_handlers', 'enable_flap_detection',
					'enable_notifications', 'execute_host_checks', 'execute_service_checks',
					'last_log_rotation', 'livestatus_version', 'nagios_pid', 'obsess_over_hosts', 'obsess_over_services',
					'process_performance_data', 'program_start', 'program_version', 'interval_length',
					'cached_log_messages', 'connections', 'connections_rate', 'host_checks',
					'host_checks_rate', 'requests', 'requests_rate', 'service_checks',
					'service_checks_rate', 'neb_callbacks', 'neb_callbacks_rate',
			);
		}
		$objects = $this->backend->getTable('status', $options);
		if(count($objects) == 0) return null;
		$this->program_start = $objects[0]['program_start'];
		return (object) $objects[0];
	}


	/* getHostTotals */
	public function getHostTotals($options = null) {
		if (config::get('checks.show_passive_as_active', '*')) {
			$active_checks_condition = array('-or' => array('active_checks_enabled' => 1, 'accept_passive_checks' => 1));
			$disabled_checks_condition = array('active_checks_enabled' => 0, 'accept_passive_checks' => 0);
		} else {
			$active_checks_condition = array('active_checks_enabled' => 1);
			$disabled_checks_condition = array('active_checks_enabled' => 0);
		}
		$stats = array(
				'total'                             => array( 'state' => array( '!=' => 999 )),
				'total_active'                      => array( 'check_type' => 0 ),
				'total_passive'                     => array( 'check_type' => 1 ),
				'pending'                           => array( 'has_been_checked' => 0 ),
				'pending_and_disabled'              => array_merge(array('has_been_checked' => 0), $disabled_checks_condition),
				'pending_and_scheduled'             => array( 'has_been_checked' => 0, 'scheduled_downtime_depth' => array('>' => 0 )),
				'up'                                => array( 'has_been_checked' => 1, 'state' => 0 ),
				'up_and_disabled_active'            => array_merge(array('check_type' => 0, 'has_been_checked' => 1, 'state' => 0), $disabled_checks_condition),
				'up_and_disabled_passive'           => array_merge(array('check_type' => 1, 'has_been_checked' => 1, 'state' => 0), $disabled_checks_condition),
				'up_and_scheduled'                  => array( 'has_been_checked' => 1, 'state' => 0, 'scheduled_downtime_depth' => array( '>' => 0 )),
				'down'                              => array( 'has_been_checked' => 1, 'state' => 1 ),
				'down_and_ack'                      => array( 'has_been_checked' => 1, 'state' => 1, 'acknowledged' => 1 ),
				'down_and_scheduled'                => array( 'has_been_checked' => 1, 'state' => 1, 'scheduled_downtime_depth' => array( '>' => 0 )),
				'down_and_disabled_active'          => array_merge(array('check_type' => 0, 'has_been_checked' => 1, 'state' => 1), $disabled_checks_condition),
				'down_and_disabled_passive'         => array_merge(array('check_type' => 1, 'has_been_checked' => 1, 'state' => 1), $disabled_checks_condition),
				'down_and_unhandled'                => array_merge(array('has_been_checked' => 1, 'state' => 1, 'acknowledged' => 0, 'scheduled_downtime_depth' => 0), $active_checks_condition),
				'unreachable'                       => array( 'has_been_checked' => 1, 'state' => 2 ),
				'unreachable_and_ack'               => array( 'has_been_checked' => 1, 'state' => 2, 'acknowledged' => 1 ),
				'unreachable_and_scheduled'         => array( 'has_been_checked' => 1, 'state' => 2, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
				'unreachable_and_disabled_active'   => array_merge(array('check_type' => 0, 'has_been_checked' => 1, 'state' => 2), $disabled_checks_condition),
				'unreachable_and_disabled_passive'  => array_merge(array('check_type' => 1, 'has_been_checked' => 1, 'state' => 2), $disabled_checks_condition),
				'unreachable_and_unhandled'         => array_merge(array('has_been_checked' => 1, 'state' => 2, 'acknowledged' => 0, 'scheduled_downtime_depth' => 0), $active_checks_condition),
				'flapping'                          => array( 'is_flapping' => 1 ),
				'flapping_disabled'                 => array( 'flap_detection_enabled' => 0 ),
				'notifications_disabled'            => array( 'notifications_enabled' => 0 ),
				'eventhandler_disabled'             => array( 'event_handler_enabled' => 0 ),
				'active_checks_disabled_active'     => array( 'check_type' => 0, 'active_checks_enabled' => 0 ),
				'active_checks_disabled_passive'    => array( 'check_type' => 1, 'active_checks_enabled' => 0 ),
				'passive_checks_disabled'           => array( 'accept_passive_checks' => 0 ),
				'outages'                           => array( 'state' => 1, 'childs' => array( '!=' => '' ) ),
		);
		if (is_array($options) && isset($options['stats']) && is_array($options['stats'])) {
			$newstats = array();
			foreach( $options['stats'] as $statcol ) {
				$newstats[$statcol] = $stats[$statcol];
			}
			$stats = $newstats;
		}
		$data = $this->backend->getStats('hosts', $stats, $options);
		return (object) $data[0];
	}


	/* getServiceTotals */
	public function getServiceTotals($options = null) {
		if (config::get('checks.show_passive_as_active', '*')) {
			$active_checks_condition = array('-or' => array('active_checks_enabled' => 1, 'accept_passive_checks' => 1));
			$disabled_checks_condition = array('active_checks_enabled' => 0, 'accept_passive_checks' => 0);
		} else {
			$active_checks_condition = array('active_checks_enabled' => 1);
			$disabled_checks_condition = array('active_checks_enabled' => 0);
		}
		$stats = array(
				'total'                             => array( 'description' => array( '!=' => '' ) ),
				'total_active'                      => array( 'check_type' => 0 ),
				'total_passive'                     => array( 'check_type' => 1 ),
				'pending'                           => array( 'has_been_checked' => 0 ),
				'pending_and_disabled'              => array_merge(array('has_been_checked' => 0), $disabled_checks_condition),
				'pending_and_scheduled'             => array( 'has_been_checked' => 0, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
				'ok'                                => array( 'has_been_checked' => 1, 'state' => 0 ),
				'ok_and_scheduled'                  => array( 'has_been_checked' => 1, 'state' => 0, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
				'ok_and_disabled_active'            => array_merge(array('check_type' => 0, 'has_been_checked' => 1, 'state' => 0), $disabled_checks_condition),
				'ok_and_disabled_passive'           => array_merge(array('check_type' => 1, 'has_been_checked' => 1, 'state' => 0), $disabled_checks_condition),
				'warning'                           => array( 'has_been_checked' => 1, 'state' => 1 ),
				'warning_and_scheduled'             => array( 'has_been_checked' => 1, 'state' => 1, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
				'warning_and_disabled_active'       => array_merge(array('check_type' => 0, 'has_been_checked' => 1, 'state' => 1), $disabled_checks_condition),
				'warning_and_disabled_passive'      => array_merge(array('check_type' => 1, 'has_been_checked' => 1, 'state' => 1), $disabled_checks_condition),
				'warning_and_ack'                   => array( 'has_been_checked' => 1, 'state' => 1, 'acknowledged' => 1 ),
				'warning_on_down_host'              => array( 'has_been_checked' => 1, 'state' => 1, 'host_state' => array( '!=' => 0 ) ),
				'warning_and_unhandled'             => array_merge(array('has_been_checked' => 1, 'state' => 1, 'host_state' => 0, 'acknowledged' => 0, 'scheduled_downtime_depth' => 0), $active_checks_condition),
				'critical'                          => array( 'has_been_checked' => 1, 'state' => 2 ),
				'critical_and_scheduled'            => array( 'has_been_checked' => 1, 'state' => 2, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
				'critical_and_disabled_active'      => array_merge(array('check_type' => 0, 'has_been_checked' => 1, 'state' => 2), $disabled_checks_condition),
				'critical_and_disabled_passive'     => array_merge(array('check_type' => 1, 'has_been_checked' => 1, 'state' => 2), $disabled_checks_condition),
				'critical_and_ack'                  => array( 'has_been_checked' => 1, 'state' => 2, 'acknowledged' => 1 ),
				'critical_on_down_host'             => array( 'has_been_checked' => 1, 'state' => 2, 'host_state' => array( '!=' => 0 ) ),
				'critical_and_unhandled'            => array_merge(array('has_been_checked' => 1, 'state' => 2, 'host_state' => 0, 'acknowledged' => 0, 'scheduled_downtime_depth' => 0), $active_checks_condition),
				'unknown'                           => array( 'has_been_checked' => 1, 'state' => 3 ),
				'unknown_and_scheduled'             => array( 'has_been_checked' => 1, 'state' => 3, 'scheduled_downtime_depth' => array( '>' => 0 ) ),
				'unknown_and_disabled_active'       => array_merge(array('check_type' => 0, 'has_been_checked' => 1, 'state' => 3), $disabled_checks_condition),
				'unknown_and_disabled_passive'      => array_merge(array('check_type' => 1, 'has_been_checked' => 1, 'state' => 3), $disabled_checks_condition),
				'unknown_and_ack'                   => array( 'has_been_checked' => 1, 'state' => 3, 'acknowledged' => 1 ),
				'unknown_on_down_host'              => array( 'has_been_checked' => 1, 'state' => 3, 'host_state' => array( '!=' => 0 ) ),
				'unknown_and_unhandled'             => array_merge(array('has_been_checked' => 1, 'state' => 3, 'host_state' => 0, 'acknowledged' => 0, 'scheduled_downtime_depth' => 0), $active_checks_condition),
				'flapping'                          => array( 'is_flapping' => 1 ),
				'flapping_disabled'                 => array( 'flap_detection_enabled' => 0 ),
				'notifications_disabled'            => array( 'notifications_enabled' => 0 ),
				'eventhandler_disabled'             => array( 'event_handler_enabled' => 0 ),
				'active_checks_disabled_active'     => array( 'check_type' => 0, 'active_checks_enabled' => 0 ),
				'active_checks_disabled_passive'    => array( 'check_type' => 1, 'active_checks_enabled' => 0 ),
				'passive_checks_disabled'           => array( 'accept_passive_checks' => 0 ),
		);
		if (is_array($options) && isset($options['stats']) && is_array($options['stats'])) {
			$newstats = array();
			foreach( $options['stats'] as $statcol ) {
				$newstats[$statcol] = $stats[$statcol];
			}
			$stats = $newstats;
		}
		$data = $this->backend->getStats('services', $stats, $options);
		return (object) $data[0];
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

	public function getSchedulingQueue($last_program_start, $options = null) {
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

		$data   = $this->backend->getStats($type, $stats);
		$result = array_merge($result, $data[0]);

		/* add stats for active checks */
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

		$data   = $this->backend->getStats($type, $stats, array('filter' => array('has_been_checked' => 1, 'check_type' => 0)));
		$result = array_merge($result, $data[0]);
	
		/* add stats for passive checks */
		$stats = array(
				'passive_state_change_sum' => array( '-sum' => 'percent_state_change' ),
				'passive_state_change_min' => array( '-min' => 'percent_state_change' ),
				'passive_state_change_max' => array( '-max' => 'percent_state_change' ),
				'passive_state_change_avg' => array( '-avg' => 'percent_state_change' ),
		);
		$data   = $this->backend->getStats($type, $stats, array('filter' => array('has_been_checked' => 1, 'check_type' => 1)));
		$result = array_merge($result, $data[0]);
	
		return (object) $result;
	}
}

/**
 * Livetatus interaface.
 */
class LivestatusBackend {
	private $connection      = null;

	/* constructor */
	public function __construct() {
	}

	/* combineFilter */
	public static function combineFilter($operator, $filter) {

		if(!isset($operator) and $operator != '-or' and $operator != '-and') {
			throw new op5LivestatusException("unknown operator in combine_filter(): ".$operator);
		}

		if(!isset($filter)) {
			return "";
		}

		if(!is_array($filter)) {
			throw new op5LivestatusException("expected array in combine_filter(): ");
		}

		if(count($filter) == 0) {
			return "";
		}

		if(count($filter) == 1) {
			return($filter[0]);
		}

		return array($operator => $filter );
	}


	/********************************************************
	 * INTERNAL FUNCTIONS
	 *******************************************************/
	public function getTable($table, $options = null) {
		return $this->getStats($table, false, $options);
	}

	public function getStats($table, $stats, $options = null) {
		$query = "";
		if(isset($options['filter'])) {
			$query .= $this->getQueryFilter($options['filter'], false);
		}
		if(isset($options['extra_header'])) {
			$query .= trim( $options['extra_header'] ) . "\n";
		}
		if( isset( $options['extra_columns'] ) ) {
			if( !isset( $options['columns'] ) )
				$options['columns'] = array();
			$options['columns'] = array_unique( array_merge( $options['columns'], $options['extra_columns'] ) );
		}
		
		$columns = isset($options['columns'])?$options['columns']:array();

		$output_columns = $columns;
		if( $stats !== false ) {
			foreach($stats as $key => $filter) {
				if( is_string( $filter ) ) {
					$query .= "Stats: $filter\n";
				} else {
					$query .= $this->getQueryFilter($filter, true);
				}
				array_push($output_columns, $key);
			}
		}

		$this->prepare_pagination($options);
		list($objects,$count) = $this->query($table, $query, $columns, $options);
		$this->postprocess_pagination($options, $count);
		
		return $this->objects2Assoc($objects, $output_columns, isset($options['callbacks']) ? $options['callbacks'] : null);
	}

	private function prepare_pagination(&$options) {
		if(isset($options['paging'])) {
			$page = $options['paging'];
			$items_per_page = $page->input->get('items_per_page', config::get('pagination.default.items_per_page', '*'));
			$items_per_page = $page->input->get('custom_pagination_field', $items_per_page);
		}
		elseif(isset($options['paginggroup'])) {
			$page = $options['paginggroup'];
			$items_per_page = $page->input->get('items_per_page', config::get('pagination.group_items_per_page', '*'));
			$items_per_page = $page->input->get('custom_pagination_field', $items_per_page);
		} else {
			return;
		}
		$current_page = $page->input->get('page',1);
		if(!is_numeric($current_page)) { $current_page = 1; }

		/* Set parameters to query */
		$options['offset'] = ($current_page-1) * $items_per_page;
		$options['limit']  = $items_per_page;

		/* Store to postprocess method */
		$options['page_enabled']        = true;
		$options['page_items_per_page'] = $items_per_page;
		$options['page_current_page']   = $current_page;
		$options['page_obj']            = $page;
	}
	
	private function postprocess_pagination($options, $count) {
		if( !isset($options['page_enabled']) || !$options['page_enabled'] )
			return;

		$page           = $options['page_obj'];
		$items_per_page = $options['page_items_per_page'];
		$current_page   = $options['page_current_page'];
		
		$pagination = new Pagination(array(
				'total_items'     => $count,
				'items_per_page'  => $items_per_page,
		));
		$page->template->content->pagination     = $pagination;
		$page->template->content->total_items    = $count;
		$page->template->content->items_per_page = $items_per_page;
		$page->template->content->page           = $current_page;
	}

	private function query($table, $filter, $columns, $options) {
		$ls = op5livestatus::instance();

		if( isset( $options['order'] ) ) {
			$order_mappings = !empty($options['order_mappings'])?$options['order_mappings']:array();
			foreach( $options['order'] as $column => $direction ) {
				if( isset($order_mappings[$column]) ) {
					foreach( $order_mappings[$column] as $mapping ) {
						$column = $mapping;
						if( $column[0] == '!' ) {
							$column = substr($column,1);
							$direction = strtolower($direction)=='asc'?'desc':'asc';
						}
						$filter .= "Sort: $column $direction\n";
					}
				} else {
					$filter .= "Sort: $column $direction\n";
				}
			}
		}
		if( isset( $options['offset'] ) ) {
			$filter .= "Offset: ".$options['offset']."\n";
		}
		if( isset( $options['limit'] ) && $options['limit'] !== false ) {
			$filter .= "Limit: ".$options['limit']."\n";
		}
		
		list($columns, $objects, $count) = $ls->query($table, $filter, $columns);
		return array($objects,$count);
	}
	
	/* Public, just to make it testable */
	public function getQueryFilter($filter, $stats = false ) {
		if( empty( $filter ) ) {
			return "";
		}
		return $this->parseQueryFilterArray($stats, $filter, null, null, $stats?'And':null);
	}

	private function parseQueryFilterArray($stats = false, $filter = null, $op = null, $name = null, $listop = null) {
		if($filter === null) {
			return "";
		}
		/* TODO: implement proper escaping */

		/* remove empty elements */
		while(is_array($filter) and isset($filter[0]) and $filter[0] === '') {
			array_shift($filter);
		}

		$query = "";
		if($this->is_assoc($filter)) {
			$iter = 0;
			foreach($filter as $key => $val) {
				$iter++;
				switch($key) {
					case '-or':  $query .= $this->parseQueryFilterArray($stats, $val, $op, $name, 'Or');
					break;
					case '-and': $query .= $this->parseQueryFilterArray($stats, $val, $op, $name, 'And');
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
					case '!~~':  $query .= $this->parseQueryFilterArray($stats, $val, $key, $name, 'And');
					break;
					case '-sum':
					case '-avg':
					case '-min':
					case '-max': $query .= $this->parseQueryFilterArray($stats, $val, substr($key, 1), '');
					break;
					default:     $query .= $this->parseQueryFilterArray($stats, $val, '=', $key, 'And');
					break;
				}
			}
			if($iter > 1 && $listop === null) {
				$listop = 'And';
			}
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
						$query .= $this->parseQueryFilterArray($stats, $val, $op, $name);
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

		if($filter === "") {
			return "";
		}

		throw new op5LivestatusException("broken filter");
	}

	private function is_assoc($array) {
		return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
	}

	private function objects2Assoc($objects, $columns, $callbacks = null) {
		$result = array();
		foreach($objects as $o) {
			$n = array();
			if (!is_array($columns)) {
				$n = $o[0];
			} else {
				$i = 0;
				foreach($columns as $c) {
					$n[$c] = $o[$i];
					$i++;
				}
				if($callbacks != null) {
					foreach($callbacks as $key => $cb) {
						$n[$key] = call_user_func($cb, $n);
					}
				}
			}
			array_push($result, $n);
		}
		return $result;
	}
}
