<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @author     op5 AB
 */
class Tac_scheduled_Widget extends widget_Base {
	protected $duplicatable = true;

	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		# HOSTS DOWN / problems
		$problem = array();
		$i = 0;

		$current_status = $this->get_current_status();

		if ($current_status->hst->down_and_scheduled) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Down');
			$problem[$i]['url'] = 'status/host/all/'.nagstat::HOST_DOWN.'/'.nagstat::HOST_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->hst->down_and_scheduled.' '._('Scheduled hosts');
			$i++;
		}

		if ($current_status->hst->unreachable_and_scheduled) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Unreachable');
			$problem[$i]['url'] = 'status/host/all/'.nagstat::HOST_UNREACHABLE.'/'.nagstat::HOST_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->hst->unreachable_and_scheduled.' '._('Scheduled hosts');
			$i++;
		}

		if ($current_status->hst->up_and_scheduled) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Up');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::HOST_UP.'/'.nagstat::HOST_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->hst->up_and_scheduled.' '._('Scheduled hosts');
			$i++;
		}

		if ($current_status->svc->critical_and_scheduled) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Critical');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::SERVICE_CRITICAL.'/'.nagstat::SERVICE_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->svc->critical_and_scheduled.' '._('Scheduled services');
			$i++;
		}

		if ($current_status->svc->warning_and_scheduled) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Warning');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::SERVICE_WARNING.'/'.nagstat::SERVICE_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->svc->warning_and_scheduled.' '._('Scheduled services');
			$i++;
		}

		if ($current_status->svc->unknown_and_scheduled) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Unknown');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::SERVICE_UNKNOWN.'/'.nagstat::SERVICE_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->svc->unknown_and_scheduled.' '._('Scheduled services');
			$i++;
		}

		require($view_path);
	}
}
