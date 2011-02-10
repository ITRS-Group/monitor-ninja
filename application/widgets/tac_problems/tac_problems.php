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
			$current_status = new Current_status_Model();
			$current_status->analyze_status_data();
		}
		$refresh_rate = 60;
		$widget_id = $this->widgetname;
		if (isset($arguments['refresh_interval'])) {
			$refresh_rate = $arguments['refresh_interval'];
		}
		$title = $this->translate->_('Unhandled problems');
		if (isset($arguments['widget_title'])) {
			$title = $arguments['widget_title'];
		}

		$col_outages = isset($arguments['col_outages']) ? $arguments['col_outages'] : '#ffffff';
		$col_host_down = isset($arguments['col_host_down']) ? $arguments['col_host_down'] : '#ffffff';
		$col_service_critical = isset($arguments['col_service_critical']) ? $arguments['col_service_critical'] : '#ffffff';
		$col_host_unreachable = isset($arguments['col_host_unreachable']) ? $arguments['col_host_unreachable'] : '#ffffff';
		$col_service_warning = isset($arguments['col_service_warning']) ? $arguments['col_service_warning'] : '#ffffff';
		$col_service_unknown = isset($arguments['col_service_unknown']) ? $arguments['col_service_unknown'] : '#ffffff';

		# HOSTS DOWN / problems
		$problem = array();
		$i = 0;
		$current_status->find_hosts_causing_outages();

		if (!empty($current_status->total_blocking_outages)) {
			$problem[$i]['type'] = $this->translate->_('Network');
			$problem[$i]['status'] = $this->translate->_('Outages');
			$problem[$i]['url'] = 'outages/index/';
			$problem[$i]['title'] = $current_status->total_blocking_outages.' '.$this->translate->_('Network outages');
			$problem[$i]['no'] = 0;
			$problem[$i]['html_id'] = 'id_outages';
			$problem[$i]['bgcolor'] = $col_outages;
			$i++;
		}

		if ($current_status->hosts_down_unacknowledged) {
			$problem[$i]['type'] = $this->translate->_('Host');
			$problem[$i]['status'] = $this->translate->_('Down');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|(config::get('checks.show_passive_as_active')?0:nagstat::HOST_CHECKS_ENABLED));
			$problem[$i]['title'] = $current_status->hosts_down_unacknowledged.' '.$this->translate->_('Unhandled problems');
			$problem[$i]['no'] = 0;
			$problem[$i]['html_id'] = 'id_host_down';
			$problem[$i]['bgcolor'] = $col_host_down;
			$i++;
		}

		if ($current_status->svcs_critical_unacknowledged) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Critical');
			$problem[$i]['url'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|(config::get('checks.show_passive_as_active')?0:nagstat::SERVICE_CHECKS_ENABLED));
			$problem[$i]['title'] = $current_status->svcs_critical_unacknowledged.' '.$this->translate->_('Unhandled problems');
			$problem[$i]['no'] = $current_status->services_critical_host_problem;
			$problem[$i]['onhost'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.nagstat::SERVICE_CRITICAL;
			$problem[$i]['title2'] = $current_status->services_critical_host_problem.' '.$this->translate->_('on problem hosts');
			$problem[$i]['html_id'] = 'id_service_critical';
			$problem[$i]['bgcolor'] = $col_service_critical;
			$i++;
		}

		if ($current_status->hosts_unreach_unacknowledged) {
			$problem[$i]['type'] = $this->translate->_('Host');
			$problem[$i]['status'] = $this->translate->_('Unreachable');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|(config::get('checks.show_passive_as_active')?0:nagstat::HOST_CHECKS_ENABLED));
			$problem[$i]['title'] = $current_status->hosts_unreach_unacknowledged.' '.$this->translate->_('Unhandled problems');
			$problem[$i]['html_id'] = 'id_host_unreachable';
			$problem[$i]['bgcolor'] = $col_host_unreachable;
			$problem[$i]['no'] = 0;
			$i++;
		}

		if ($current_status->svcs_warning_unacknowledged) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Warning');
			$problem[$i]['url'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|(config::get('checks.show_passive_as_active')?0:nagstat::SERVICE_CHECKS_ENABLED));
			$problem[$i]['title'] = $current_status->svcs_warning_unacknowledged.' '.$this->translate->_('Unhandled problems');
			$problem[$i]['no'] = $current_status->services_warning_host_problem;
			$problem[$i]['onhost'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.nagstat::SERVICE_WARNING;
			$problem[$i]['title2'] = $current_status->services_warning_host_problem.' '.$this->translate->_('on problem hosts');
			$problem[$i]['html_id'] = 'id_service_warning';
			$problem[$i]['bgcolor'] = $col_service_warning;
			$i++;
		}

		if ($current_status->svcs_unknown_unacknowledged) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Unknown');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|(config::get('checks.show_passive_as_active')?0:nagstat::SERVICE_CHECKS_ENABLED));
			$problem[$i]['title'] = $current_status->svcs_unknown_unacknowledged.' '.$this->translate->_('Unhandled problems');
			$problem[$i]['no'] = $current_status->services_unknown_host_problem;
			$problem[$i]['onhost'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE);
			$problem[$i]['title2'] = $current_status->services_unknown_host_problem.' '.$this->translate->_('on problem hosts');
			$problem[$i]['html_id'] = 'id_service_unknown';
			$problem[$i]['bgcolor'] = $col_service_unknown;
			$i++;
		}

		# let view template know if wrapping div should be hidden or not
		$ajax_call = request::is_ajax() ? true : false;

		# fetch widget content
		require_once($view_path);

		if(request::is_ajax()) {
			# output widget content
			echo json::encode( $this->output());
		} else {
			# add custom javascript to header
			$this->js = array('/js/tac_problems');
			$this->master_obj->xtra_js[] = 'application/media/js/mColorPicker.min.js';
			$this->css = array('/css/tac_problems.css.php');
			# call parent helper to assign all
			# variables to master controller
			return $this->fetch();
		}
	}
}