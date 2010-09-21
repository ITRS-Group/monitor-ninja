<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Total Status widget
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Status_totals_Widget extends widget_Core {

	public function __construct()
	{
		parent::__construct();

		# needed to figure out path to widget
		$this->set_widget_name(__CLASS__, basename(__FILE__));
		$this->hoststatus = false;
		$this->servicestatus = false;
	}

	public function index($arguments=false, $master=false)
	{
		$hoststatus = isset($_GET['hoststatustypes']) ? $_GET['hoststatustypes'] : $this->hoststatus;
		$servicestatus = isset($_GET['servicestatustypes']) ? $_GET['servicestatustypes'] : $this->servicestatus;

		# required to enable us to assign the correct
		# variables to the calling controller
		$this->master_obj = $master;

		# fetch widget view path
		$view_path = $this->view_path('view');

		$tjobba = '--';
		if (is_object($arguments[0])) {
			$current_status = $arguments[0];
			array_shift($arguments);
			$tjobba = implode(',', $arguments);
			$_SESSION['grouptype'] = isset($arguments[3]) ? str_replace('group', '', $arguments[3]) : false;
			$_SESSION['name'] = isset($arguments[0]) ? $arguments[0] : 'all';
		}

		if (isset($_SESSION['grouptype']) && !empty($_SESSION['grouptype']) && $_SESSION['name'] != 'all') {
			$host_data = Group_Model::state_breakdown($_SESSION['grouptype'], 'host', $_SESSION['name']);
			$service_data = Group_Model::state_breakdown($_SESSION['grouptype'], 'service', $_SESSION['name']);
			$apa = "using session";
		} else {
			$apa = "NO SESS";
			$current_status = new Current_status_Model();
			$current_status->host_status();
			$current_status->service_status();
		}

		$grouptype = !empty($_SESSION['grouptype']) ? $_SESSION['grouptype'].'group' : false;

		# assign variables for our view
		$label_up = $this->translate->_('Up');
		$label_down = $this->translate->_('Down');
		$label_unreachable = $this->translate->_('Unreachable');
		$label_pending = $this->translate->_('Pending');
		$label_all_problems = $this->translate->_('All Problems');
		$label_all_types = $this->translate->_('All Types');
		$label_all_host_problems = $this->translate->_('Problems in Total');
		$label_all_host_types = $this->translate->_('Types in Total');
		$label_all_service_problems = $this->translate->_('Problems in Total');
		$label_all_service_types = $this->translate->_('Types in Total');

		$host_title = $this->translate->_('Host Status Totals');
		$service_title = $this->translate->_('Service Status Totals');
		$host = isset($arguments[0]) ? $arguments[0] : 'all';
		$host_state = isset($arguments[1]) && !empty($arguments[1]) ? $arguments[1] : (nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE|nagstat::HOST_PENDING);
		$service_state = isset($arguments[2]) ? $arguments[2] : false;
		$target_method = 'host';

		$total_up = 0;
		$total_down = 0;
		$total_unreachable = 0;
		$total_pending = 0;
		$total_hosts = 0;
		$total_problems = 0;

		$svc_total_ok = 0;
		$svc_total_warning = 0;
		$svc_total_unknown = 0;
		$svc_total_critical = 0;
		$svc_total_pending = 0;
		$svc_total_services = 0;
		$svc_total_problems = 0;

		# host data
		if (isset($host_data) && count($host_data) && !empty($host_data)) {
			foreach ($host_data as $data) {
				switch ($data->current_state) {
					case Current_status_Model::HOST_UP:
						$total_up = $data->cnt;
						break;
					case Current_status_Model::HOST_DOWN:
						$total_down = $data->cnt;
						break;
					case Current_status_Model::HOST_UNREACHABLE:
						$total_unreachable = $data->cnt;
						break;
					case Current_status_Model::HOST_PENDING:
						$total_pending = $data->cnt;
						break;
				}
				$total_hosts += $data->cnt;
			}
			$total_problems = $total_down + $total_unreachable;

			if (isset($service_data) && count($service_data) && !empty($service_data)) {
				foreach ($service_data as $data) {
					switch ($data->current_state) {
						case Current_status_Model::SERVICE_OK:
							$svc_total_ok = $data->cnt;
							break;
						case Current_status_Model::SERVICE_WARNING:
							$svc_total_warning = $data->cnt;
							break;
						case Current_status_Model::SERVICE_UNKNOWN:
							$svc_total_unknown = $data->cnt;
							break;
						case Current_status_Model::SERVICE_CRITICAL:
							$svc_total_critical = $data->cnt;
							break;
						case Current_status_Model::SERVICE_PENDING:
							$svc_total_pending = $data->cnt;
							break;
					}
					$svc_total_services += $data->cnt;
				}
				$svc_total_problems = $svc_total_unknown + $svc_total_warning + $svc_total_critical;
			}
		} else {
			$total_up = $current_status->hosts_up;
			$total_down = $current_status->hosts_down;
			$total_unreachable = $current_status->hosts_unreachable;
			$total_pending = $current_status->hosts_pending;
			$total_hosts = $current_status->total_hosts;
			$total_problems = $current_status->hosts_down + $current_status->hosts_unreachable;

			# service data
			$svc_total_ok = $current_status->services_ok;
			$svc_total_warning = $current_status->services_warning;
			$svc_total_unknown = $current_status->services_unknown;
			$svc_total_critical = $current_status->services_critical;
			$svc_total_pending = $current_status->services_pending;
			$svc_total_services = $current_status->total_services;
			$svc_total_problems = $svc_total_unknown + $svc_total_warning + $svc_total_critical;
		}

		$grouptype_arg = $grouptype ? 'group_type='.$grouptype : '';
		$host_header = array(
			array('url' => 'status/'.$target_method.'/'.$host.'/?hoststatustypes='.nagstat::HOST_UP.'&'.$grouptype_arg, 'lable' => $total_up, 'status' => $label_up, 'status_id' => nagstat::HOST_UP),
			array('url' => 'status/'.$target_method.'/'.$host.'/?hoststatustypes='.nagstat::HOST_DOWN.'&'.$grouptype_arg, 'lable' => $total_down, 'status' => $label_down, 'status_id' => nagstat::HOST_DOWN),
			array('url' => 'status/'.$target_method.'/'.$host.'/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&'.$grouptype_arg, 'lable' => $total_unreachable, 'status' => $label_unreachable, 'status_id' => nagstat::HOST_UNREACHABLE),
			array('url' => 'status/'.$target_method.'/'.$host.'/?hoststatustypes='.nagstat::HOST_PENDING.'&'.$grouptype_arg, 'lable' => $total_pending, 'status' => $label_pending, 'status_id' => nagstat::HOST_PENDING)
		);

		$svc_label_ok = $this->translate->_('Ok');
		$svc_label_warning = $this->translate->_('Warning');
		$svc_label_unknown = $this->translate->_('Unknown');
		$svc_label_critical	= $this->translate->_('Critical');
		$svc_label_pending = $this->translate->_('Pending');

		$service_header = array(
			array('url' => 'status/service/'.$host.'/?hoststatustypes='.$host_state.'&servicestatustypes='.nagstat::SERVICE_OK.'&'.$grouptype_arg, 'lable' => $svc_total_ok, 'status' => $svc_label_ok, 'status_id' => nagstat::SERVICE_OK),
			array('url' => 'status/service/'.$host.'/?hoststatustypes='.$host_state.'&servicestatustypes='.nagstat::SERVICE_WARNING.'&'.$grouptype_arg, 'lable' => $svc_total_warning, 'status' => $svc_label_warning, 'status_id' => nagstat::SERVICE_WARNING),
			array('url' => 'status/service/'.$host.'/?hoststatustypes='.$host_state.'&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&'.$grouptype_arg, 'lable' => $svc_total_unknown, 'status' => $svc_label_unknown, 'status_id' => nagstat::SERVICE_UNKNOWN),
			array('url' => 'status/service/'.$host.'/?hoststatustypes='.$host_state.'&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&'.$grouptype_arg, 'lable' => $svc_total_critical, 'status' => $svc_label_critical, 'status_id' => nagstat::SERVICE_CRITICAL),
			array('url' => 'status/service/'.$host.'/?hoststatustypes='.$host_state.'&servicestatustypes='.nagstat::SERVICE_PENDING.'&'.$grouptype_arg, 'lable' => $svc_total_pending, 'status' => $svc_label_pending, 'status_id' => nagstat::SERVICE_PENDING)
		);

		# let view template know if wrapping div should be hidden or not
		$ajax_call = request::is_ajax() || (isset($arguments['is_ajax']) && $arguments['is_ajax'] === true) ? true : false;

		$widget_id =  $this->widgetname;
		$refresh_rate = 60;
		if (isset($arguments['refresh_interval'])) {
			$refresh_rate = $arguments['refresh_interval'];
		}

		require_once($view_path);

		if(request::is_ajax()) {
			# output widget content
			echo json::encode( $this->output());
		} else {
			# set required extra resources
			$this->css = array('/css/status_totals');
			$this->js = array('/js/status_totals');

			# call parent helper to assign all
			# variables to master controller
			return $this->fetch();
		}

	}
}
