<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
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

		if ($current_status->hosts_up_disabled) {
			$problem[$i]['type'] = $this->translate->_('Host');
			$problem[$i]['status'] = $this->translate->_('Up');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_UP.'&hostprops='.nagstat::HOST_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->hosts_up_disabled.' '.$this->translate->_('Disabled hosts');
			$i++;
		}

		if ($current_status->hosts_down_disabled) {
			$problem[$i]['type'] = $this->translate->_('Host');
			$problem[$i]['status'] = $this->translate->_('Down');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->hosts_down_disabled.' '.$this->translate->_('Disabled hosts');
			$i++;
		}

		if ($current_status->hosts_unreachable_disabled) {
			$problem[$i]['type'] = $this->translate->_('Host');
			$problem[$i]['status'] = $this->translate->_('Unreachable');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->hosts_unreachable_disabled.' '.$this->translate->_('Disabled hosts');
			$i++;
		}

		if ($current_status->hosts_pending_disabled) {
			$problem[$i]['type'] = $this->translate->_('Host');
			$problem[$i]['status'] = $this->translate->_('Pending');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_PENDING.'&hostprops='.nagstat::HOST_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->hosts_pending_disabled.' '.$this->translate->_('Disabled hosts');
			$i++;
		}

		if ($current_status->services_ok_disabled) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('OK');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_OK.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->services_ok_disabled.' '.$this->translate->_('Disabled services');
			$i++;
		}

		if ($current_status->services_warning_disabled) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Warning');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->services_warning_disabled.' '.$this->translate->_('Disabled services');
			$i++;
		}

		if ($current_status->services_critical_disabled) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Critical');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->services_critical_disabled.' '.$this->translate->_('Disabled services');
			$i++;
		}

		if ($current_status->services_unknown_disabled) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Unknown');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->services_unknown_disabled.' '.$this->translate->_('Disabled services');
			$i++;
		}

		if ($current_status->services_pending_disabled) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Pending');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_PENDING.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED;
			$problem[$i]['title'] = $current_status->services_pending_disabled.' '.$this->translate->_('Disabled services');
			$i++;
		}

		require($view_path);
	}
}
