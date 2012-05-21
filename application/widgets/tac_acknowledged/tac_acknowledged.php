<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_acknowledged_Widget extends widget_Base {
	protected $duplicatable = true;
	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		$current_status = $this->get_current_status();

		# HOSTS DOWN / problems
		$problem = array();
		$i = 0;

		if ($current_status->hosts_down_acknowledged) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Down');
			$problem[$i]['url'] = 'status/host/all/'.nagstat::HOST_DOWN.'/?hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED;
			$problem[$i]['title'] = $current_status->hosts_down_acknowledged.' '._('Acknowledged hosts');
			$i++;
		}

		if ($current_status->hosts_unreachable_acknowledged) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Unreachable');
			$problem[$i]['url'] = 'status/host/all/'.nagstat::HOST_UNREACHABLE.'/?hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED;
			$problem[$i]['title'] = $current_status->hosts_unreachable_acknowledged.' '._('Acknowledged hosts');
			$i++;
		}

		if ($current_status->services_critical_acknowledged) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Critical');
			$problem[$i]['url'] = 'status/service/all/'.(nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE|nagstat::HOST_PENDING).
				'/'.nagstat::SERVICE_CRITICAL.'/'.nagstat::SERVICE_STATE_ACKNOWLEDGED;
			$problem[$i]['title'] = $current_status->services_critical_acknowledged.' '._('Acknowledged services');
			$i++;
		}

		if ($current_status->services_warning_acknowledged) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Warning');
			$problem[$i]['url'] = 'status/service/all/'.(nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE|nagstat::HOST_PENDING).
				'/'.nagstat::SERVICE_WARNING.'/'.nagstat::SERVICE_STATE_ACKNOWLEDGED;
			$problem[$i]['title'] = $current_status->services_warning_acknowledged.' '._('Acknowledged services');
			$i++;
		}

		if ($current_status->services_unknown_acknowledged) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Unknown');
			$problem[$i]['url'] = 'status/service/all/'.(nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE|nagstat::HOST_PENDING).
				'/'.nagstat::SERVICE_UNKNOWN.'/'.nagstat::SERVICE_STATE_ACKNOWLEDGED;
			$problem[$i]['title'] = $current_status->services_unknown_acknowledged.' '._('Acknowledged services');
			$i++;
		}

		require($view_path);
	}
}
