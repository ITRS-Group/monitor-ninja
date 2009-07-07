<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for monitoring performance
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Monitoring_performance_Widget extends widget_Core {
	public function __construct()
	{
		parent::__construct();

		# needed to figure out path to widget
		$this->set_widget_name(__CLASS__, basename(__FILE__));
	}

	public function index($arguments=false, $master=false)
	{
		# required to enable us to assign the correct
		# variables to the calling controller
		$this->master_obj = $master;

		# fetch widget view path
		$view_path = $this->view_path('view');

		if (is_object($arguments[0])) {
			$current_status = $arguments[0];
			array_shift($arguments);
		} else {
			$current_status = new Current_status_Model();
			$current_status->host_status();
			$current_status->service_status();
		}

		$widget_id = $this->widgetname;
		$refresh_rate = 60;
		if (isset($arguments['refresh_interval'])) {
			$refresh_rate = $arguments['refresh_interval'];
		}

		$title = $this->translate->_('Monitoring Performance');
		if (isset($arguments['widget_title'])) {
			$title = $arguments['widget_title'];
		}

		# let view template know if wrapping div should be hidden or not
		$ajax_call = request::is_ajax() ? true : false;

		$label_service_check_execution_time = $this->translate->_('Service Check Execution Time');
		$label_service_check_latency = $this->translate->_('Service Check Latency');
		$label_sec = $this->translate->_('sec');
		$label_host_check_execution_time = $this->translate->_('Host Check Execution Time');
		$label_host_check_latency = $this->translate->_('Host Check Latency');
		$label_active_host_svc_check = $this->translate->_('# Active Host / Service Checks');
		$label_passive_host_svc_check = $this->translate->_('# Passive Host / Service Checks');

		$min_service_execution_time= number_format($current_status->min_service_execution_time, 2);
		$max_service_execution_time = number_format($current_status->max_service_execution_time, 2);
		$average_service_execution_time = $current_status->average_service_execution_time;
		$min_service_latency = number_format($current_status->min_service_latency, 2);
		$max_service_latency = number_format($current_status->max_service_latency, 2);
		$average_service_latency = number_format($current_status->average_service_latency, 2);

		$min_host_execution_time = number_format($current_status->min_host_execution_time, 2);
		$max_host_execution_time = number_format($current_status->max_host_execution_time, 2);
		$average_host_execution_time = number_format($current_status->average_host_execution_time, 2);
		$min_host_latency = number_format($current_status->min_host_latency, 2);
		$max_host_latency = number_format($current_status->max_host_latency, 2);
		$average_host_latency = number_format($current_status->average_host_latency, 2);

		$total_active_host_checks = $current_status->total_active_host_checks;
		$total_active_service_checks = $current_status->total_active_service_checks;
		$total_passive_host_checks = $current_status->total_passive_host_checks;
		$total_passive_service_checks = $current_status->total_passive_service_checks;

		# fetch widget content
		require_once($view_path);

		if(request::is_ajax()) {
			# output widget content
			echo json::encode( $this->output());
		} else {
			$this->js = array('/js/monitoring_performance');
			# call parent helper to assign all
			# variables to master controller
			return $this->fetch();
		}
	}
}