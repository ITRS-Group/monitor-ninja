<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_disabled_Widget extends widget_Core {
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
			$current_status = new Current_status_Model();
			$current_status->analyze_status_data();
		}

		$widget_id = $this->widgetname;
		$refresh_rate = 60;
		if (isset($arguments['refresh_interval'])) {
			$refresh_rate = $arguments['refresh_interval'];
		}

		$title = $this->translate->_('Disabled checks');
		if (isset($arguments['widget_title'])) {
			$title = $arguments['widget_title'];
		}

		# let view template know if wrapping div should be hidden or not
		$ajax_call = request::is_ajax() ? true : false;

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

		# fetch widget content
		require_once($view_path);

		if(request::is_ajax()) {
			# output widget content
			echo json::encode( $this->output());
		} else {
			$this->js = array('/js/tac_disabled');
			# call parent helper to assign all
			# variables to master controller
			return $this->fetch();
		}
	}
}