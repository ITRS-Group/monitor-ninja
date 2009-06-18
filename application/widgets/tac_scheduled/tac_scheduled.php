<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_scheduled_Widget extends widget_Core {
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
		}

		$widget_id = $this->widgetname;
		$refresh_rate = 60;
		if (isset($arguments['refresh_interval'])) {
			$refresh_rate = $arguments['refresh_interval'];
		}

		$title = $this->translate->_('Scheduled downtime');
		if (isset($arguments['widget_title'])) {
			$title = $arguments['widget_title'];
		}

		# let view template know if wrapping div should be hidden or not
		$ajax_call = request::is_ajax() ? true : false;

		# HOSTS DOWN / problems
		$problem = array();
		$i = 0;

		if ($current_status->hosts_down_scheduled) {
			$problem[$i]['type'] = $this->translate->_('Host');
			$problem[$i]['status'] = $this->translate->_('Down');
			$problem[$i]['url'] = 'status/host/all/'.nagstat::HOST_DOWN.'/'.nagstat::HOST_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->hosts_down_scheduled.' '.$this->translate->_('Scheduled hosts');
			$i++;
		}

		if ($current_status->hosts_unreachable_scheduled) {
			$problem[$i]['type'] = $this->translate->_('Host');
			$problem[$i]['status'] = $this->translate->_('Unreachable');
			$problem[$i]['url'] = 'status/host/all/'.nagstat::HOST_UNREACHABLE.'/'.nagstat::HOST_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->hosts_unreachable_scheduled.' '.$this->translate->_('Scheduled hosts');
			$i++;
		}

		if ($current_status->services_critical_scheduled) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Critical');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::SERVICE_CRITICAL.'/'.nagstat::SERVICE_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->services_critical_scheduled.' '.$this->translate->_('Scheduled services');
			$i++;
		}

		if ($current_status->services_warning_scheduled) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Warning');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::SERVICE_WARNING.'/'.nagstat::SERVICE_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->services_warning_scheduled.' '.$this->translate->_('Scheduled services');
			$i++;
		}

		if ($current_status->services_unknown_scheduled) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Unknown');
			$problem[$i]['url'] = 'status/service/all/0/'.nagstat::SERVICE_UNKNOWN.'/'.nagstat::SERVICE_SCHEDULED_DOWNTIME;
			$problem[$i]['title'] = $current_status->services_unknown_scheduled.' '.$this->translate->_('Scheduled services');
			$i++;
		}

		# fetch widget content
		require_once($view_path);
		if(request::is_ajax()) {
			# output widget content
			echo json::encode( $this->output());
		} else {

			# set required extra resources
			$this->js = array('/js/tac_scheduled');

			# call parent helper to assign all
			# variables to master controller
			return $this->fetch();
		}
	}
}