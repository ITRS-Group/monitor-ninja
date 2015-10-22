<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for monitoring performance
 *
 * @author     op5 AB
 */
class Monitoring_performance_Widget extends widget_Base {
	protected $duplicatable = true;

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'Monitoring performance',
			'instanceable' => true
		));
	}

	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		$label_passive_host_svc_check = _('# Passive Host / Service Checks');
		$service_check_execution_time = _('N/A');
		$service_check_latency = _('N/A');
		$host_check_execution_time = _('N/A');
		$host_check_latency = _('N/A');

		$total_active_host_checks = _('N/A');
		$total_active_service_checks = _('N/A');

		$total_passive_host_checks = _('N/A');
		$total_passive_service_checks = _('N/A');
		try {
			$current_status = Current_status_Model::instance();
			$current_status->analyze_status_data();
			$min_service_execution_time= number_format($current_status->svc_perf->execution_time_min, 2);
			$max_service_execution_time = number_format($current_status->svc_perf->execution_time_max, 2);
			$average_service_execution_time = number_format($current_status->svc_perf->execution_time_avg, 3);
			$min_service_latency = number_format($current_status->svc_perf->latency_min, 2);
			$max_service_latency = number_format($current_status->svc_perf->latency_max, 2);
			$average_service_latency = number_format($current_status->svc_perf->latency_avg, 3);

			$min_host_execution_time = number_format($current_status->hst_perf->execution_time_min, 2);
			$max_host_execution_time = number_format($current_status->hst_perf->execution_time_max, 2);
			$average_host_execution_time = number_format($current_status->hst_perf->execution_time_avg, 3);
			$min_host_latency = number_format($current_status->hst_perf->latency_min, 2);
			$max_host_latency = number_format($current_status->hst_perf->latency_max, 2);
			$average_host_latency = number_format($current_status->hst_perf->latency_avg, 3);

			$total_active_host_checks = $current_status->hst->total_active;
			$total_active_service_checks = $current_status->svc->total_active;
			$total_passive_host_checks = $current_status->hst->total_passive;
			$total_passive_service_checks = $current_status->svc->total_passive;


			$service_check_execution_time = $min_service_execution_time.' / '.$max_service_execution_time.' / '.$average_service_execution_time.' '._('sec');
			$service_check_latency = $min_service_latency.' / '.$max_service_latency.' / '.$average_service_latency.' '._('sec');
			$host_check_execution_time = $min_host_execution_time.' / '.$max_host_execution_time.' / '.$average_host_execution_time.' '._('sec');
			$host_check_latency = $min_host_latency.' / '.$max_host_latency.' / '.$average_host_latency.' '._('sec');
		}
		catch (op5LivestatusException $ex) {
		}

		require($view_path);
	}
}
