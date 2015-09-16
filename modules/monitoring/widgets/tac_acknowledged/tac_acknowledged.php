<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @author     op5 AB
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

		if ($current_status->hst->down_and_ack) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Down');
			$problem[$i]['url'] = 'status/host/all/'.nagstat::HOST_DOWN.'/?hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED;
			$problem[$i]['title'] = $current_status->hst->down_and_ack.' '._('Acknowledged hosts');
			$i++;
		}

		if ($current_status->hst->unreachable_and_ack) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Unreachable');
			$problem[$i]['url'] = 'status/host/all/'.nagstat::HOST_UNREACHABLE.'/?hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED;
			$problem[$i]['title'] = $current_status->hst->unreachable_and_ack.' '._('Acknowledged hosts');
			$i++;
		}

		if ($current_status->svc->critical_and_ack) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Critical');
			$problem[$i]['url'] = 'status/service/all/'.(nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE|nagstat::HOST_PENDING).
				'/'.nagstat::SERVICE_CRITICAL.'/'.nagstat::SERVICE_STATE_ACKNOWLEDGED;
			$problem[$i]['title'] = $current_status->svc->critical_and_ack.' '._('Acknowledged services');
			$i++;
		}

		if ($current_status->svc->warning_and_ack) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Warning');
			$problem[$i]['url'] = 'status/service/all/'.(nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE|nagstat::HOST_PENDING).
				'/'.nagstat::SERVICE_WARNING.'/'.nagstat::SERVICE_STATE_ACKNOWLEDGED;
			$problem[$i]['title'] = $current_status->svc->warning_and_ack.' '._('Acknowledged services');
			$i++;
		}

		if ($current_status->svc->unknown_and_ack) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Unknown');
			$problem[$i]['url'] = 'status/service/all/'.(nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE|nagstat::HOST_PENDING).
				'/'.nagstat::SERVICE_UNKNOWN.'/'.nagstat::SERVICE_STATE_ACKNOWLEDGED;
			$problem[$i]['title'] = $current_status->svc->unknown_and_ack.' '._('Acknowledged services');
			$i++;
		}

		require($view_path);
	}

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'Acknowledged problems',
			'instanceable' => true
		));
	}
}
