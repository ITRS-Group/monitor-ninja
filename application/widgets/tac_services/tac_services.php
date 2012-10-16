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

		$service_filter =
			config::get('checks.show_passive_as_active')
			? ((nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED))
			: (nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED);

		$current_status = $this->get_current_status();

		# SERVICES CRITICAL
		$services_critical = array();
		if ($current_status->svc->critical_and_unhandled) {
			$services_critical['status/service/all/?hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.$service_filter] =
				$current_status->svc->critical_and_unhandled.' '._('Unhandled Problems');
		}

		if ($current_status->svc->critical_on_down_host) {
			$services_critical['status/service/all/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.nagstat::SERVICE_CRITICAL] = $current_status->svc->critical_on_down_host.' '._('on Problem Hosts');
		}

		if ($current_status->svc->critical_and_scheduled) {
			$services_critical['status/service/all/0/?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.nagstat::SERVICE_SCHEDULED_DOWNTIME] = $current_status->svc->critical_and_scheduled.' '._('Scheduled');
		}

		if ($current_status->svc->critical_and_ack) {
			$services_critical['status/service/all/0/?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.nagstat::SERVICE_STATE_ACKNOWLEDGED] = $current_status->svc->critical_and_ack.' '._('Acknowledged');
		}

		if ($current_status->svc->critical_and_disabled_active) {
			$services_critical['status/service/all/0/?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.nagstat::SERVICE_CHECKS_DISABLED ] = $current_status->svc->critical_and_disabled_active.' '._('Disabled');
		}


		# SERVICES WARNING
		$services_warning = array();
		# HOST_UP|HOST_PENDING
		# SERVICE_NO_SCHEDULED_DOWNTIME|SERVICE_STATE_UNACKNOWLEDGED|SERVICE_CHECKS_ENABLED
		if ($current_status->svc->warning_and_unhandled) {
			$services_warning['status/service/all/?hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.$service_filter] =
				$current_status->svc->warning_and_unhandled.' '._('Unhandled Problems');
		}

		if ($current_status->svc->warning_on_down_host) {
			$services_warning['status/service/all/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.nagstat::SERVICE_WARNING] = $current_status->svc->warning_on_down_host.' '._('on Problem Hosts');
		}

		if ($current_status->svc->warning_and_scheduled) {
			$services_warning['status/service/all/?servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.nagstat::SERVICE_SCHEDULED_DOWNTIME] = $current_status->svc->warning_and_scheduled.' '._('Scheduled');
		}

		if ($current_status->svc->warning_and_ack) {
			$services_warning['status/service/all/?servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.nagstat::SERVICE_STATE_ACKNOWLEDGED] = $current_status->svc->warning_and_ack.' '._('Acknowledged');
		}

		if ($current_status->svc->warning_and_disabled_active) {
			$services_warning['status/service/all/?servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.nagstat::SERVICE_CHECKS_DISABLED ] = $current_status->svc->warning_and_disabled_active.' '._('Disabled');
		}


		# SERVICES UNKNOWN
		$services_unknown = array();
		if ($current_status->svc->unknown_and_unhandled) {
			$services_unknown['status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&service_props='.$service_filter] =
				$current_status->svc->unknown_and_unhandled.' '._('Unhandled Problems');
		}

		if ($current_status->svc->unknown_on_down_host) {
			$services_unknown['status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE)] = $current_status->svc->unknown_on_down_host.' '._('on Problem Hosts');
		}

		if ($current_status->svc->unknown_and_scheduled) {
			$services_unknown['status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&service_props='.nagstat::SERVICE_SCHEDULED_DOWNTIME] = $current_status->svc->unknown_and_scheduled.' '._('Scheduled');
		}

		if ($current_status->svc->unknown_and_ack) {
			$services_unknown['status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&service_props='.nagstat::SERVICE_STATE_ACKNOWLEDGED] = $current_status->svc->unknown_and_ack.' '._('Acknowledged');
		}

		if ($current_status->svc->unknown_and_disabled_active) {
			$services_unknown['status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&service_props='.nagstat::SERVICE_CHECKS_DISABLED ] = $current_status->svc->unknown_and_disabled_active.' '._('Disabled');
		}


		# SERVICES OK DISABLED
		$services_ok_disabled = array();
		if ($current_status->svc->ok_and_disabled_active) {
			$services_ok_disabled['status/service/all/?servicestatustypes='.nagstat::SERVICE_OK.'&service_props='.nagstat::SERVICE_CHECKS_DISABLED] = $current_status->svc->ok_and_disabled_active.' '._('Disabled');
		}

		# SERVICES PENDING
		$services_pending = array();
		if ($current_status->svc->pending) {
			$services_pending['status/service/all/?servicestatustypes='.nagstat::SERVICE_PENDING] = $current_status->svc->pending.' '._('Pending');
		}

		require($view_path);
	}
}
