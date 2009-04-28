<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_problems_Widget extends widget_Core {
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

		# HOSTS DOWN / problems
		$problem = array();
		if ($current_status->hosts_down_unacknowledged) {
			$problem['status/host/all/'.nagstat::HOST_DOWN.'/'.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED)] =
				$current_status->hosts_down_unacknowledged.' '.$this->translate->_('Unhandled Problems');
		}

		if ($current_status->hosts_down_scheduled) {
			$problem['status/host/all/'.nagstat::HOST_DOWN.'/'.nagstat::HOST_SCHEDULED_DOWNTIME] = $current_status->hosts_down_scheduled.' '.$this->translate->_('Scheduled');
		}

		if ($current_status->hosts_down_acknowledged) {
			$problem['status/host/all/'.nagstat::HOST_DOWN.'/'.nagstat::HOST_STATE_ACKNOWLEDGED] = $current_status->hosts_down_acknowledged.' '.$this->translate->_('Acknowledged');
		}

		if ($current_status->hosts_down_disabled) {
			$problem['status/host/all/'.nagstat::HOST_DOWN.'/'.nagstat::HOST_CHECKS_DISABLED] = $current_status->hosts_down_disabled.' '.$this->translate->_('Disabled');
		}

		# HOSTS UNREACHABLE / problems

		if ($current_status->hosts_unreachable_unacknowledged) {
			$problem['status/host/all/'.nagstat::HOST_UNREACHABLE.'/'.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED)] =
				$current_status->hosts_unreachable_unacknowledged.' '.$this->translate->_('Unhandled Problems');
		}

		if ($current_status->hosts_unreachable_scheduled) {
			$problem['status/host/all/'.nagstat::HOST_UNREACHABLE.'/'.nagstat::HOST_SCHEDULED_DOWNTIME] = $current_status->hosts_unreachable_scheduled.' '.$this->translate->_('Scheduled');
		}

		if ($current_status->hosts_unreachable_acknowledged) {
			$problem['status/host/all/'.nagstat::HOST_UNREACHABLE.'/'.nagstat::HOST_STATE_ACKNOWLEDGED] = $current_status->hosts_unreachable_acknowledged.' '.$this->translate->_('Acknowledged');
		}

		if ($current_status->hosts_unreachable_disabled) {
			$problem['status/host/all/'.nagstat::HOST_UNREACHABLE.'/'.nagstat::HOST_CHECKS_DISABLED] = $current_status->hosts_unreachable_disabled.' '.$this->translate->_('Disabled');
		}

		# HOSTS UP DISABLED / problems

		if ($current_status->hosts_up_disabled) {
			$problem['status/host/all/'.nagstat::HOST_UP .'/'.nagstat::HOST_CHECKS_DISABLED] = $current_status->hosts_up_disabled.' '.$this->translate->_('Disabled');
		}

		# HOSTS PENDING DISABLED / problems

		if ($current_status->hosts_pending_disabled) {
			$problem['status/host/all/'.nagstat::HOST_PENDING  .'/'.nagstat::HOST_CHECKS_DISABLED] = $current_status->hosts_pending_disabled.' '.$this->translate->_('Disabled');
		}

		# fetch widget content
		require_once($view_path);

		# call parent helper to assign all
		# variables to master controller
		return $this->fetch();
	}
}

