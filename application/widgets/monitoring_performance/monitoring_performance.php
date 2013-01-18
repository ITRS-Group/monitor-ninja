<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for monitoring performance
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Monitoring_performance_Widget extends widget_Base {
	protected $duplicatable = true;
	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		$label_service_check_execution_time = $this->translate->_('Service Check Execution Time');
		$label_service_check_latency = $this->translate->_('Service Check Latency');
		$label_sec = $this->translate->_('sec');
		$label_host_check_execution_time = $this->translate->_('Host Check Execution Time');
		$label_host_check_latency = $this->translate->_('Host Check Latency');
		$label_active_host_svc_check = $this->translate->_('# Active Host / Service Checks');
		$label_passive_host_svc_check = $this->translate->_('# Passive Host / Service Checks');

		$current_status = $this->get_current_status();

		$min_service_execution_time = $current_status->min_service_execution_time;
		$max_service_execution_time = $current_status->max_service_execution_time;
		$average_service_execution_time = $current_status->average_service_execution_time;
		$min_service_latency = $current_status->min_service_latency;
		$max_service_latency = $current_status->max_service_latency;
		$average_service_latency = $current_status->average_service_latency;

		$min_host_execution_time = $current_status->min_host_execution_time;
		$max_host_execution_time = $current_status->max_host_execution_time;
		$average_host_execution_time = $current_status->average_host_execution_time;
		$min_host_latency = $current_status->min_host_latency;
		$max_host_latency = $current_status->max_host_latency;
		$average_host_latency = $current_status->average_host_latency;

		$total_active_host_checks = $current_status->total_active_host_checks;
		$total_active_service_checks = $current_status->total_active_service_checks;
		$total_passive_host_checks = $current_status->total_passive_host_checks;
		$total_passive_service_checks = $current_status->total_passive_service_checks;

		require($view_path);
	}
}
