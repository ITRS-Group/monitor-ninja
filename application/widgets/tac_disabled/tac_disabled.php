<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @author     op5 AB
 */
class Tac_disabled_Widget extends widget_Base {
	protected $duplicatable = true;
	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		$current_status = $this->get_current_status();

		# HOSTS DOWN / problems
		$problem = array();
		$i = 0;

		if ($current_status->hst->up_and_disabled_active) {
/* TODO: check passive setting */
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Up');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_UP.'&hostprops='.nagstat::HOST_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->hst->up_and_disabled_active.' '._('Disabled hosts');
			$i++;
		}

		if ($current_status->hst->down_and_disabled_active) {
/* TODO: check passive setting */
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Down');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->hst->down_and_disabled_active.' '._('Disabled hosts');
			$i++;
		}

		if ($current_status->hst->unreachable_and_disabled_active) {
/* TODO: check passive setting */
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Unreachable');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->hst->unreachable_and_disabled_active.' '._('Disabled hosts');
			$i++;
		}

		if ($current_status->hst->pending_and_disabled) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Pending');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_PENDING.'&hostprops='.nagstat::HOST_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->hst->pending_and_disabled.' '._('Disabled hosts');
			$i++;
		}

		if ($current_status->svc->ok_and_disabled_active) {
/* TODO: check passive setting */
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('OK');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_OK.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->svc->ok_and_disabled_active.' '._('Disabled services');
			$i++;
		}

		if ($current_status->svc->warning_and_disabled_active) {
/* TODO: check passive setting */
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Warning');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->svc->warning_and_disabled_active.' '._('Disabled services');
			$i++;
		}

		if ($current_status->svc->critical_and_disabled_active) {
/* TODO: check passive setting */
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Critical');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->svc->critical_and_disabled_active.' '._('Disabled services');
			$i++;
		}

		if ($current_status->svc->unknown_and_disabled_active) {
/* TODO: check passive setting */
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Unknown');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->svc->unknown_and_disabled_active.' '._('Disabled services');
			$i++;
		}

		if ($current_status->svc->pending_and_disabled) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Pending');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_PENDING.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->svc->pending_and_disabled.' '._('Disabled services');
			$i++;
		}

		require($view_path);
	}
}
