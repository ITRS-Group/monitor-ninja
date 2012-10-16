<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_hosts_Widget extends widget_Base {
	protected $duplicatable = true;
	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		$default_links = array(
			'down' => 'status/host/all/'.nagstat::HOST_DOWN,
			'unreachable' => 'status/host/all/'.nagstat::HOST_UNREACHABLE,
			'up' => 'status/host/all/'.nagstat::HOST_UP,
			'pending' => 'status/host/all/'.nagstat::HOST_PENDING
		);

		$current_status = $this->get_current_status();

		# HOSTS DOWN
		$hosts_down = array();

		$host_filter =
			config::get('checks.show_passive_as_active', '*')
			? (nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED)
			: (nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED);

		if ($current_status->hst->down_and_unhandled) {
			$hosts_down['status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.$host_filter] =
				$current_status->hst->down_and_unhandled.' '._('Unhandled Problems');
		}

		if ($current_status->hst->down_and_scheduled) {
			$hosts_down['status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME] = $current_status->hst->down_and_scheduled.' '._('Scheduled');
		}

		if ($current_status->hst->down_and_ack) {
			$hosts_down['status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED] = $current_status->hst->down_and_ack.' '._('Acknowledged');
		}

		if ($current_status->hst->down_and_disabled_active) {
			$hosts_down['status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_CHECKS_DISABLED] = $current_status->hst->down_and_disabled_active.' '._('Disabled');
		}

		# HOSTS UNREACHABLE
		$hosts_unreachable = array();

		if ($current_status->hst->unreachable_and_unhandled) {
			$hosts_unreachable['status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.$host_filter] =
				$current_status->hst->unreachable_and_unhandled.' '._('Unhandled Problems');
		}

		if ($current_status->hst->unreachable_and_scheduled) {
			$hosts_unreachable['status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME] = $current_status->hst->unreachable_and_scheduled.' '._('Scheduled');
		}

		if ($current_status->hst->unreachable_and_ack) {
			$hosts_unreachable['status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED] = $current_status->hst->unreachable_and_ack.' '._('Acknowledged');
		}

		if ($current_status->hst->unreachable_and_disabled_active) {
			$hosts_unreachable['status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_CHECKS_DISABLED] = $current_status->hst->unreachable_and_disabled_active.' '._('Disabled');
		}


		# HOSTS UP DISABLED
		$hosts_up_disabled = array();
		if ($current_status->hst->up_and_disabled_active) {
			$hosts_up_disabled['status/host/all/?hoststatustypes='.nagstat::HOST_UP .'&hostprops='.nagstat::HOST_CHECKS_DISABLED] = $current_status->hst->up_and_disabled.' '._('Disabled');
		}

		# HOSTS PENDING
		$hosts_pending = array();
		if ($current_status->hst->pending) {
			$hosts_pending['status/host/all/?hoststatustypes='.nagstat::HOST_PENDING] = $current_status->hst->pending.' '._('Pending');
		}

		require($view_path);
	}
}
