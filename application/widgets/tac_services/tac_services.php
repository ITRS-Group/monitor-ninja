<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Services widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_services_Widget extends widget_Base {
	protected $duplicatable = true;

	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		$default_links = array(
			'critical' => 'status/service/all/?servicestatustypes='.nagstat::SERVICE_CRITICAL,
			'warning' => 'status/service/all/?servicestatustypes='.nagstat::SERVICE_WARNING,
			'unknown' => 'status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN,
			'ok' => 'status/service/all/?servicestatustypes='.nagstat::SERVICE_OK,
			'pending' => 'status/service/all/?servicestatustypes='.nagstat::SERVICE_PENDING
		);

		# alter filter depending on config.show_passive_as_active value in config/checks.php
		$service_fiiter =
			config::get('checks.show_passive_as_active')
			? ((nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED))
			: (nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED);

		$current_status = $this->get_current_status();

		# SERVICES CRITICAL
		$services_critical = array();
		if ($current_status->services_critical_unacknowledged) {
			$services_critical['status/service/all/?hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.$service_fiiter] =
				$current_status->services_critical_unacknowledged.' '._('Unhandled Problems');
		}

		if ($current_status->services_critical_host_problem) {
			$services_critical['status/service/all/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.nagstat::SERVICE_CRITICAL] = $current_status->services_critical_host_problem.' '._('on Problem Hosts');
		}

		if ($current_status->services_critical_scheduled) {
			$services_critical['status/service/all/0/?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.nagstat::SERVICE_SCHEDULED_DOWNTIME] = $current_status->services_critical_scheduled.' '._('Scheduled');
		}

		if ($current_status->services_critical_acknowledged) {
			$services_critical['status/service/all/0/?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.nagstat::SERVICE_STATE_ACKNOWLEDGED] = $current_status->services_critical_acknowledged.' '._('Acknowledged');
		}

		if ($current_status->services_critical_disabled) {
			$services_critical['status/service/all/0/?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.nagstat::SERVICE_CHECKS_DISABLED ] = $current_status->services_critical_disabled.' '._('Disabled');
		}


		# SERVICES WARNING
		$services_warning = array();
		# HOST_UP|HOST_PENDING
		# SERVICE_NO_SCHEDULED_DOWNTIME|SERVICE_STATE_UNACKNOWLEDGED|SERVICE_CHECKS_ENABLED
		if ($current_status->services_warning_unacknowledged) {
			$services_warning['status/service/all/?hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.$service_fiiter] =
				$current_status->services_warning_unacknowledged.' '._('Unhandled Problems');
		}

		if ($current_status->services_warning_host_problem) {
			$services_warning['status/service/all/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.nagstat::SERVICE_WARNING] = $current_status->services_warning_host_problem.' '._('on Problem Hosts');
		}

		if ($current_status->services_warning_scheduled) {
			$services_warning['status/service/all/?servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.nagstat::SERVICE_SCHEDULED_DOWNTIME] = $current_status->services_warning_scheduled.' '._('Scheduled');
		}

		if ($current_status->services_warning_acknowledged) {
			$services_warning['status/service/all/?servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.nagstat::SERVICE_STATE_ACKNOWLEDGED] = $current_status->services_warning_acknowledged.' '._('Acknowledged');
		}

		if ($current_status->services_warning_disabled) {
			$services_warning['status/service/all/?servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.nagstat::SERVICE_CHECKS_DISABLED ] = $current_status->services_warning_disabled.' '._('Disabled');
		}


		# SERVICES UNKNOWN
		$services_unknown = array();
		if ($current_status->services_unknown_unacknowledged) {
			$services_unknown['status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&service_props='.$service_fiiter] =
				$current_status->services_unknown_unacknowledged.' '._('Unhandled Problems');
		}

		if ($current_status->services_unknown_host_problem) {
			$services_unknown['status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE)] = $current_status->services_unknown_host_problem.' '._('on Problem Hosts');
		}

		if ($current_status->services_unknown_scheduled) {
			$services_unknown['status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&service_props='.nagstat::SERVICE_SCHEDULED_DOWNTIME] = $current_status->services_unknown_scheduled.' '._('Scheduled');
		}

		if ($current_status->services_unknown_acknowledged) {
			$services_unknown['status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&service_props='.nagstat::SERVICE_STATE_ACKNOWLEDGED] = $current_status->services_unknown_acknowledged.' '._('Acknowledged');
		}

		if ($current_status->services_unknown_disabled) {
			$services_unknown['status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&service_props='.nagstat::SERVICE_CHECKS_DISABLED ] = $current_status->services_unknown_disabled.' '._('Disabled');
		}


		# SERVICES OK DISABLED
		$services_ok_disabled = array();
		if ($current_status->services_ok_disabled) {
			$services_ok_disabled['status/service/all/?servicestatustypes='.nagstat::SERVICE_OK.'&service_props='.nagstat::SERVICE_CHECKS_DISABLED] = $current_status->services_ok_disabled.' '._('Disabled');
		}

		# SERVICES PENDING
		$services_pending = array();
		if ($current_status->services_pending) {
			$services_pending['status/service/all/?servicestatustypes='.nagstat::SERVICE_PENDING] = $current_status->services_pending.' '._('Pending');
		}

		require($view_path);
	}
}

