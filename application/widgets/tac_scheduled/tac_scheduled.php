<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
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

		if ($current_status->hosts_down_scheduled) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Down');
			$problem[$i]['url'] = 'status/host/all/'.nagstat::HOST_DOWN.'/'.nagstat::HOST_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->hosts_down_scheduled.' '._('Scheduled hosts');
			$i++;
		}

		if ($current_status->hosts_unreachable_scheduled) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Unreachable');
			$problem[$i]['url'] = 'status/host/all/'.nagstat::HOST_UNREACHABLE.'/'.nagstat::HOST_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->hosts_unreachable_scheduled.' '._('Scheduled hosts');
			$i++;
		}

		if ($current_status->hosts_up_scheduled) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Up');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::HOST_UP.'/'.nagstat::HOST_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->hosts_up_scheduled.' '._('Scheduled hosts');
			$i++;
		}

		if ($current_status->services_critical_scheduled) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Critical');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::SERVICE_CRITICAL.'/'.nagstat::SERVICE_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->services_critical_scheduled.' '._('Scheduled services');
			$i++;
		}

		if ($current_status->services_warning_scheduled) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Warning');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::SERVICE_WARNING.'/'.nagstat::SERVICE_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->services_warning_scheduled.' '._('Scheduled services');
			$i++;
		}

		if ($current_status->services_unknown_scheduled) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Unknown');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::SERVICE_UNKNOWN.'/'.nagstat::SERVICE_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->services_unknown_scheduled.' '._('Scheduled services');
			$i++;
		}

		require($view_path);
	}
}
