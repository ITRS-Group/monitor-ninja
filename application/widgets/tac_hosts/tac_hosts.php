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

		# alter filter depending on config.show_passive_as_active value in config/checks.php
		$host_fiiter =
			config::get('checks.show_passive_as_active')
			? (nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED)
			: (nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED);

		if ($current_status->hosts_down_unacknowledged) {
			$hosts_down['status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.$host_fiiter] =
				$current_status->hosts_down_unacknowledged.' '.$this->translate->_('Unhandled Problems');
		}

		if ($current_status->hosts_down_scheduled) {
			$hosts_down['status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME] = $current_status->hosts_down_scheduled.' '.$this->translate->_('Scheduled');
		}

		if ($current_status->hosts_down_acknowledged) {
			$hosts_down['status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED] = $current_status->hosts_down_acknowledged.' '.$this->translate->_('Acknowledged');
		}

		if ($current_status->hosts_down_disabled) {
			$hosts_down['status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_CHECKS_DISABLED] = $current_status->hosts_down_disabled.' '.$this->translate->_('Disabled');
		}

		# HOSTS UNREACHABLE
		$hosts_unreachable = array();

		if ($current_status->hosts_unreachable_unacknowledged) {
			$hosts_unreachable['status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.$host_fiiter] =
				$current_status->hosts_unreachable_unacknowledged.' '.$this->translate->_('Unhandled Problems');
		}

		if ($current_status->hosts_unreachable_scheduled) {
			$hosts_unreachable['status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME] = $current_status->hosts_unreachable_scheduled.' '.$this->translate->_('Scheduled');
		}

		if ($current_status->hosts_unreachable_acknowledged) {
			$hosts_unreachable['status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED] = $current_status->hosts_unreachable_acknowledged.' '.$this->translate->_('Acknowledged');
		}

		if ($current_status->hosts_unreachable_disabled) {
			$hosts_unreachable['status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_CHECKS_DISABLED] = $current_status->hosts_unreachable_disabled.' '.$this->translate->_('Disabled');
		}


		# HOSTS UP DISABLED
		$hosts_up_disabled = array();
		if ($current_status->hosts_up_disabled) {
			$hosts_up_disabled['status/host/all/?hoststatustypes='.nagstat::HOST_UP .'&hostprops='.nagstat::HOST_CHECKS_DISABLED] = $current_status->hosts_up_disabled.' '.$this->translate->_('Disabled');
		}

		# HOSTS PENDING
		$hosts_pending = array();
		if ($current_status->hosts_pending) {
			$hosts_pending['status/host/all/?hoststatustypes='.nagstat::HOST_PENDING] = $current_status->hosts_pending.' '.$this->translate->_('Pending');
		}

		require($view_path);
	}
}

