<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_problems_Widget extends widget_Base {
	protected $duplicatable = true;

	public function index()
	{
		$arguments = $this->get_arguments();
		# fetch widget view path
		$view_path = $this->view_path('view');

		$current_status = $this->get_current_status();

		# HOSTS DOWN / problems
		$problem = array();
		$i = 0;
		$outages = new Outages_Model();
		$outage_data = $outages->fetch_outage_data();

		if (!empty($outage_data)) {
			$problem[$i]['type'] = _('Network');
			$problem[$i]['status'] = _('Outages');
			$problem[$i]['url'] = 'outages/index/';
			$problem[$i]['title'] = count($outage_data).' '._('Network outages');
			$problem[$i]['no'] = 0;
			$problem[$i]['html_id'] = 'id_outages'.$this->model->instance_id;
			$i++;
		}

		if ($current_status->hosts_down_unacknowledged) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Down');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED);
			$problem[$i]['title'] = $current_status->hosts_down_unacknowledged.' '._('Unhandled problems');
			$problem[$i]['no'] = 0;
			$problem[$i]['html_id'] = 'id_host_down'.$this->model->instance_id;
			$i++;
		}

		if ($current_status->services_critical_unacknowledged) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Critical');
			$problem[$i]['url'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED);
			$problem[$i]['title'] = $current_status->services_critical_unacknowledged.' '._('Unhandled problems');
			$problem[$i]['no'] = $current_status->services_critical_host_problem;
			$problem[$i]['onhost'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.nagstat::SERVICE_CRITICAL;
			$problem[$i]['title2'] = $current_status->services_critical_host_problem.' '._('on problem hosts');
			$problem[$i]['html_id'] = 'id_service_critical'.$this->model->instance_id;
			$i++;
		}

		if ($current_status->hosts_unreachable_unacknowledged) {
			$problem[$i]['type'] = _('Host');
			$problem[$i]['status'] = _('Unreachable');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED);
			$problem[$i]['title'] = $current_status->hosts_unreachable_unacknowledged.' '._('Unhandled problems');
			$problem[$i]['html_id'] = 'id_host_unreachable'.$this->model->instance_id;
			$problem[$i]['no'] = 0;
			$i++;
		}

		if ($current_status->services_warning_unacknowledged) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Warning');
			$problem[$i]['url'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED);
			$problem[$i]['title'] = $current_status->services_warning_unacknowledged.' '._('Unhandled problems');
			$problem[$i]['no'] = $current_status->services_warning_host_problem;
			$problem[$i]['onhost'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.nagstat::SERVICE_WARNING;
			$problem[$i]['title2'] = $current_status->services_warning_host_problem.' '._('on problem hosts');
			$problem[$i]['html_id'] = 'id_service_warning'.$this->model->instance_id;
			$i++;
		}

		if ($current_status->services_unknown_unacknowledged) {
			$problem[$i]['type'] = _('Service');
			$problem[$i]['status'] = _('Unknown');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED);
			$problem[$i]['title'] = $current_status->services_unknown_unacknowledged.' '._('Unhandled problems');
			$problem[$i]['no'] = $current_status->services_unknown_host_problem;
			$problem[$i]['onhost'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE);
			$problem[$i]['title2'] = $current_status->services_unknown_host_problem.' '._('on problem hosts');
			$problem[$i]['html_id'] = 'id_service_unknown'.$this->model->instance_id;
			$i++;
		}

		$this->js = array('js/tac_problems', 'application/media/js/mColorPicker');
		$this->css = array('css/tac_problems');
		require($view_path);
	}
}
