<?php

/**
 * A model for generating statistics from livestatus
 */
class Stats_Model extends Model {
	public $host_cols = false; /**< The list of legal host columns */
	public $service_cols = false; /**< The list of legal service columns */
	private $host_col_defs = false;
	private $service_col_defs = false;
	public function __construct() {
		if (config::get('checks.show_passive_as_active', '*')) {
			$active_checks_condition = "Stats: active_checks_enabled = 1\nStats: accept_passive_checks = 1\nStatsOr: 2";
			$disabled_checks_condition = "Stats: active_checks_enabled != 1\nStats: accept_passive_checks != 1\nStatsAnd: 2";
		} else {
			$active_checks_condition = "Stats: active_checks_enabled = 1";
			$disabled_checks_condition = "Stats: active_checks_enabled != 1";
		}

		$this->host_col_defs = array(
			'total_hosts' => 'Stats: state != 9999', // "any", as recommended by ls docs
			'flap_disabled_hosts' => 'Stats: flap_detection_enabled != 1',
			'flapping_hosts' => 'Stats: is_flapping = 1',
			'notification_disabled_hosts' => 'Stats: notifications_enabled != 1',
			'event_handler_disabled_hosts' => 'Stats: event_handler_enabled != 1',
			'active_checks_disabled_hosts' => $disabled_checks_condition,
			'passive_checks_disabled_hosts' => 'Stats: accept_passive_checks != 1',
			'hosts_up_disabled' => "Stats: state = 0\n$disabled_checks_condition\nStatsAnd: 2",
			'hosts_up_unacknowledged' => "Stats: state = 0\nStats: acknowledged != 1\nStatsAnd: 2",
			'hosts_up' => 'Stats: state = 0',
			'hosts_down_scheduled' => "Stats: state = 1\nStats: scheduled_downtime_depth > 0\nStatsAnd: 2",
			'hosts_down_acknowledged' => "Stats: state = 1\nStats: acknowledged = 1\nStatsAnd: 2",
			'hosts_down_disabled' => "Stats: state = 1\n$disabled_checks_condition\nStatsAnd: 2",
			'hosts_down_unacknowledged' => "Stats: state = 1\nStats: scheduled_downtime_depth = 0\nStats: acknowledged != 1\n$active_checks_condition\nStatsAnd: 4",
			'hosts_down' => 'Stats: state = 1',
			'hosts_unreachable_scheduled' => "Stats: state = 2\nStats: scheduled_downtime_depth > 0\nStatsAnd: 2",
			'hosts_unreachable_acknowledged' => "Stats: state = 2\nStats: acknowledged = 1\nStatsAnd: 2",
			'hosts_unreachable_disabled' => "Stats: state = 2\n$disabled_checks_condition\nStatsAnd: 2",
			'hosts_unreachable_unacknowledged' => "Stats: state = 2\nStats: scheduled_downtime_depth = 0\nStats: acknowledged != 1\n$active_checks_condition\nStatsAnd: 4",
			'hosts_unreachable' => "Stats: state = 2",
			'hosts_pending_disabled' => "Stats: has_been_checked = 0\n$disabled_checks_condition\nStatsAnd: 2",
			'hosts_pending' => 'Stats: has_been_checked = 0',
			'total_active_host_checks' => 'Stats: check_type = 0',
			'total_passive_host_checks' => 'Stats: check_type > 0',
			'min_host_latency' => 'Stats: min latency',
			'max_host_latency' => 'Stats: max latency',
			'total_host_latency' => 'Stats: sum latency',
			'avg_host_latency' => 'Stats: avg latency',
			'min_host_execution_time' => 'Stats: min execution_time',
			'max_host_execution_time' => 'Stats: max execution_time',
			'total_host_execution_time' => 'Stats: sum execution_time',
			'avg_host_execution_time' => 'Stats: avg execution_time',
		);
		$this->host_cols = array_keys($this->host_col_defs);

		$this->service_col_defs = array(
			'total_services' => 'Stats: state != 9999', // "any", as recommended by ls docs
			'flap_disabled_services' => 'Stats: flap_detection_enabled != 1',
			'flapping_services' => 'Stats: is_flapping = 1',
			'notification_disabled_services' => 'Stats: notifications_enabled != 1',
			'event_handler_disabled_svcs' => 'Stats: event_handler_enabled != 1',
			'active_checks_disabled_svcs' => $disabled_checks_condition,
			'passive_checks_disabled_svcs' => 'Stats: accept_passive_checks != 1',
			'services_ok_disabled' => "Stats: state = 0\n$disabled_checks_condition\nStatsAnd: 2",
			'services_ok_unacknowledged' => "Stats: state = 0\nStats: acknowledged != 1\nStatsAnd: 2",
			'services_ok' => 'Stats: state = 0',
			'services_warning_host_problem' => "Stats: state = 1\nStats: host_state > 0\nStats: service_scheduled_downtime_depth = 0\nStats: host_scheduled_downtime_depth = 0\nStatsAnd: 4",
			'services_warning_scheduled' => "Stats: state = 1\nStats: scheduled_downtime_depth > 0\nStats: host_scheduled_downtime_depth > 0\nStatsOr: 2\nStatsAnd: 2",
			'services_warning_acknowledged' => "Stats: state = 1\nStats: acknowledged = 1\nStatsAnd: 2",
			'services_warning_disabled' => "Stats: state = 1\n$disabled_checks_condition\nStatsAnd: 2",
			'services_warning_unacknowledged' => "Stats: state = 1\nStats: host_state != 1\nStats: host_state != 2\nStats: scheduled_downtime_depth = 0\nStats: host_scheduled_downtime_depth = 0\nStats: acknowledged != 1\n$active_checks_condition\nStatsAnd: 7",
			'services_warning' => 'Stats: state = 1',
			'services_critical_host_problem' => "Stats: state = 2\nStats: host_state > 0\nStats: service_scheduled_downtime_depth = 0\nStats: host_scheduled_downtime_depth = 0\nStatsAnd: 4",
			'services_critical_scheduled' => "Stats: state = 2\nStats: scheduled_downtime_depth > 0\nStats: host_scheduled_downtime_depth > 0\nStatsOr: 2\nStatsAnd: 2",
			'services_critical_acknowledged' => "Stats: state = 2\nStats: acknowledged = 1\nStatsAnd: 2",
			'services_critical_disabled' => "Stats: state = 2\n$disabled_checks_condition\nStatsAnd: 2",
			'services_critical_unacknowledged' => "Stats: state = 2\nStats: host_state != 1\nStats: host_state != 2\nStats: scheduled_downtime_depth = 0\nStats: host_scheduled_downtime_depth = 0\nStats: acknowledged != 1\n$active_checks_condition\nStatsAnd: 7",
			'services_critical' => 'Stats: state = 2',
			'services_unknown_host_problem' => "Stats: state = 3\nStats: host_state > 0\nStats: service_scheduled_downtime_depth = 0\nStats: host_scheduled_downtime_depth = 0\nStatsAnd: 4",
			'services_unknown_scheduled' => "Stats: state = 3\nStats: scheduled_downtime_depth > 0\nStats: host_scheduled_downtime_depth > 0\nStatsOr: 2\nStatsAnd: 2",
			'services_unknown_acknowledged' => "Stats: state = 3\nStats: acknowledged = 1\nStatsAnd: 2",
			'services_unknown_disabled' => "Stats: state = 3\n$disabled_checks_condition\nStatsAnd: 2",
			'services_unknown_unacknowledged' => "Stats: state = 3\nStats: host_state != 1\nStats: host_state != 2\nStats: scheduled_downtime_depth = 0\nStats: host_scheduled_downtime_depth = 0\nStats: acknowledged != 1\n$active_checks_condition\nStatsAnd: 7",
			'services_unknown' => 'Stats: state = 3',
			'services_pending_disabled' => "Stats: has_been_checked = 0\n$disabled_checks_condition\nStatsAnd: 2",
			'services_pending' => 'Stats: has_been_checked = 0',
			'total_active_service_checks' => 'Stats: check_type = 0',
			'total_passive_service_checks' => 'Stats: check_type > 0',
			'min_service_latency' => 'Stats: min latency',
			'max_service_latency' => 'Stats: max latency',
			'sum_service_latency' => 'Stats: sum latency',
			'avg_service_latency' => 'Stats: avg latency',
			'min_service_execution_time' => 'Stats: min execution_time',
			'max_service_execution_time' => 'Stats: max execution_time',
			'sum_service_execution_time' => 'Stats: sum execution_time',
			'avg_service_execution_time' => 'Stats: avg execution_time',
		);
		$this->service_cols = array_keys($this->service_col_defs);
	}

	/**
	 * Get statistical data from livestatus
	 * @param $table Name of table. Usually 'hosts' or 'services', but tables like 'hostsforgroup' also possible
	 * @param $cols array The columns to include. Must exist in either $this->service_cols or $this->host_cols, depending on the table
	 * @param $filter array Custom filters to add as filters to the query, meaning the returned statistics will only contain matches
	 * @param $xtra_columns array If set, the query will be run for each value of the column (so make sure to filter), and results will be returned for each
	 * @returns array of results, mapping the col (or xtra_column) to it's value, or false on error
	 */
	public function get_stats($table, $cols, $filter=false, $xtra_columns=false) {
		try {
			$defs = (strpos($table, 'service') === 0) ? $this->service_col_defs : $this->host_col_defs;
			$ls = Livestatus::instance();
			$query = "GET $table";
			if ($filter) {
				$query .= "\n".implode("\n",$filter);
			}
			foreach ($cols as $col) {
				$query .= "\n{$defs[$col]}";
			}
			if ($xtra_columns) {
				$query .= "\nColumns: ".implode(' ', $xtra_columns);
			}
			$res = $ls->query($query);
		} catch (LivestatusException $ex) {
			return false;
		}

		$ret = array();

		foreach($res as $k => $data) {
			reset($data);
			if ($xtra_columns) {
				foreach ($xtra_columns as $xtra_col) {
					$ret[$k][$xtra_col] = current($data);
					next($data);
				}
			}
			foreach ($cols as $col) {
				$ret[$k][$col] = current($data);
				next($data);
			}
		}
		return $ret;
	}
}
