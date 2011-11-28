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

	public function options()
	{
		$options = parent::options();
		$cols = array(
			'col_outages' => 'Network Outages',
			'col_host_down' => 'Host Down',
			'col_host_unreachable' => 'Host Unreachable',
			'col_service_critical' => 'Service Critical',
			'col_service_warning' => 'Service Warning',
			'col_service_unknown' => 'Service Unknown');
		$args = array(
			'type' => 'color',
			'data-text' => 'hidden',
			'data-hex' => 'true',
			'style' => 'height:10px;width:10px;');
		foreach ($cols as $col => $label) {
			$opt = new option($this->model->name, $col, $label, 'input', $args, '#ffffff');
			$opt->should_render_js(false);
			$options[] = $opt;
		}
		return $options;
	}

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
		$outage_data = $outages->fetch_outage_data($current_status);

		if (!empty($outage_data)) {
			$problem[$i]['type'] = $this->translate->_('Network');
			$problem[$i]['status'] = $this->translate->_('Outages');
			$problem[$i]['url'] = 'outages/index/';
			$problem[$i]['title'] = count($outage_data).' '.$this->translate->_('Network outages');
			$problem[$i]['no'] = 0;
			$problem[$i]['html_id'] = 'id_outages'.$this->model->instance_id;
			$problem[$i]['bgcolor'] = $arguments['col_outages'];
			$i++;
		}

		if ($current_status->hosts_down_unacknowledged) {
			$problem[$i]['type'] = $this->translate->_('Host');
			$problem[$i]['status'] = $this->translate->_('Down');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED);
			$problem[$i]['title'] = $current_status->hosts_down_unacknowledged.' '.$this->translate->_('Unhandled problems');
			$problem[$i]['no'] = 0;
			$problem[$i]['html_id'] = 'id_host_down'.$this->model->instance_id;
			$problem[$i]['bgcolor'] = $arguments['col_host_down'];
			$i++;
		}

		if ($current_status->svcs_critical_unacknowledged) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Critical');
			$problem[$i]['url'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED);
			$problem[$i]['title'] = $current_status->svcs_critical_unacknowledged.' '.$this->translate->_('Unhandled problems');
			$problem[$i]['no'] = $current_status->services_critical_host_problem;
			$problem[$i]['onhost'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.nagstat::SERVICE_CRITICAL;
			$problem[$i]['title2'] = $current_status->services_critical_host_problem.' '.$this->translate->_('on problem hosts');
			$problem[$i]['html_id'] = 'id_service_critical'.$this->model->instance_id;
			$problem[$i]['bgcolor'] = $arguments['col_service_critical'];
			$i++;
		}

		if ($current_status->hosts_unreach_unacknowledged) {
			$problem[$i]['type'] = $this->translate->_('Host');
			$problem[$i]['status'] = $this->translate->_('Unreachable');
			$problem[$i]['url'] = 'status/host/all/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED);
			$problem[$i]['title'] = $current_status->hosts_unreach_unacknowledged.' '.$this->translate->_('Unhandled problems');
			$problem[$i]['html_id'] = 'id_host_unreachable'.$this->model->instance_id;
			$problem[$i]['bgcolor'] = $arguments['col_host_unreachable'];
			$problem[$i]['no'] = 0;
			$i++;
		}

		if ($current_status->svcs_warning_unacknowledged) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Warning');
			$problem[$i]['url'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&servicestatustypes='.nagstat::SERVICE_WARNING.'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED);
			$problem[$i]['title'] = $current_status->svcs_warning_unacknowledged.' '.$this->translate->_('Unhandled problems');
			$problem[$i]['no'] = $current_status->services_warning_host_problem;
			$problem[$i]['onhost'] = 'status/service/all/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.nagstat::SERVICE_WARNING;
			$problem[$i]['title2'] = $current_status->services_warning_host_problem.' '.$this->translate->_('on problem hosts');
			$problem[$i]['html_id'] = 'id_service_warning'.$this->model->instance_id;
			$problem[$i]['bgcolor'] = $arguments['col_service_warning'];
			$i++;
		}

		if ($current_status->svcs_unknown_unacknowledged) {
			$problem[$i]['type'] = $this->translate->_('Service');
			$problem[$i]['status'] = $this->translate->_('Unknown');
			$problem[$i]['url'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED);
			$problem[$i]['title'] = $current_status->svcs_unknown_unacknowledged.' '.$this->translate->_('Unhandled problems');
			$problem[$i]['no'] = $current_status->services_unknown_host_problem;
			$problem[$i]['onhost'] = 'status/service/all/?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE);
			$problem[$i]['title2'] = $current_status->services_unknown_host_problem.' '.$this->translate->_('on problem hosts');
			$problem[$i]['html_id'] = 'id_service_unknown'.$this->model->instance_id;
			$problem[$i]['bgcolor'] = $arguments['col_service_unknown'];
			$i++;
		}

		$this->js = array('js/tac_problems', 'application/media/js/mColorPicker.min');
		$this->css = array('css/tac_problems');
		require($view_path);
	}
}
