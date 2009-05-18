<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_hosts_Widget extends widget_Core {
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
			# don't accept widget to call current_status
			# and re-generate all status data
			return false;
		}

		# assign variables for our view
		$title = $this->translate->_('Hosts');
		$header_links = array(
			'status/host/all/'.nagstat::HOST_DOWN => $current_status->hosts_down.' '.$this->translate->_('Down'),
			'status/host/all/'.nagstat::HOST_UNREACHABLE => $current_status->hosts_unreachable.' '.$this->translate->_('Unreachable'),
			'status/host/all/'.nagstat::HOST_UP => $current_status->hosts_up.' '.$this->translate->_('Up'),
			'status/host/all/'.nagstat::HOST_PENDING => $current_status->hosts_pending.' '.$this->translate->_('Pending')
		);

		# HOSTS DOWN
		$hosts_down = array();
		if ($current_status->hosts_down_unacknowledged) {
			$hosts_down['status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED)] =
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
			$hosts_unreachable['status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED)] =
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

		# HOSTS PENDING DISABLED
		$hosts_pending_disabled = array();
		if ($current_status->hosts_pending_disabled) {
			$hosts_pending_disabled['status/host/all/?hoststatustypes='.nagstat::HOST_PENDING  .'&hostprops='.nagstat::HOST_CHECKS_DISABLED] = $current_status->hosts_pending_disabled.' '.$this->translate->_('Disabled');
		}

		# fetch widget content
		require_once($view_path);

		# call parent helper to assign all
		# variables to master controller
		return $this->fetch();
	}
}

