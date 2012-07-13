<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Total Status widget
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Status_totals_Widget extends widget_Base {
	protected $movable=false;
	protected $removable=false;
	protected $closeconfirm=false;
	protected $editable=false;

	private $host = 'all';
	private $hoststatus = false;
	private $servicestatus = false;
	private $grouptype = false;

	public function __construct($model) {
		$model->friendly_name = '<span>Host Status Totals</span><span style="margin-left: 145px">Service Status Totals</span>';
		parent::__construct($model);
		$this->hoststatus = nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE|nagstat::HOST_PENDING;
	}

	public function set_host($host) {
		$this->host = $host;
	}

	public function set_hoststatus($hoststatus) {
		$this->hoststatus = $hoststatus;
	}

	public function set_servicestatus($servicestatus) {
		$this->servicestatus = $servicestatus;
	}

	public function set_grouptype($grouptype) {
		$this->grouptype = str_replace('group', '', $grouptype);
	}

	public function index()
	{
		$hoststatus = isset($_GET['hoststatustypes']) ? $_GET['hoststatustypes'] : $this->hoststatus;
		$servicestatus = isset($_GET['servicestatustypes']) ? $_GET['servicestatustypes'] : $this->servicestatus;

		# fetch widget view path
		$view_path = $this->view_path('view');

		$current_status = $this->get_current_status();
		$host_cols = array(
			'total_hosts',
			'hosts_up',
			'hosts_down',
			'hosts_unreachable',
			'hosts_pending',
			'hosts_problem'
		);
		$svc_cols = array(
			'total_services',
			'services_ok',
			'services_warning',
			'services_critical',
			'services_unknown',
			'services_pending',
			'services_problem'
		);

		$stats = new Stats_Model();
		if (empty($this->grouptype) || $this->host == 'all' || !$this->host) {
			$hosts = $stats->get_stats('hosts', $host_cols);
			$services = $stats->get_stats('services', $svc_cols);
		} else if ($this->grouptype == 'host') {
			$hosts = $stats->get_stats('hostsbygroup', $host_cols, array('Filter: hostgroup_name = '.$this->host));
			$services = $stats->get_stats('servicesbyhostgroup', $svc_cols, array('Filter: hostgroup_name = '.$this->host));
		} else {
			$services = $stats->get_stats('servicesbygroup', $svc_cols, array('Filter: servicegroup_name = '.$this->host));
			$ls = Livestatus::instance();
			foreach ($services as $service) {
				$group = $service['servicegroup_name'];
				$ret[$group] = $service;
				$host_names = $ls->query("GET servicesbygroup
Columns: host_name
Filter: servicegroup_name = $group");
				$this_match = array();
				foreach ($host_names as $host) {
					$this_match[] = "Filter: host_name = {$host[0]}";
				}
				$this_match[] = 'Or: '.count($host_names);
			}
			$hosts = $stats->get_stats('hosts', $host_cols, $this_match);
		}
		$hosts = $hosts[0];
		$services = $services[0];

		$grouptype = !empty($this->grouptype) ? $this->grouptype.'group' : false;

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
		$target_method = 'host';

		$grouptype_arg = $grouptype ? 'group_type='.$grouptype : '';
		$host_header = array(
			array('url' => 'status/'.$target_method.'/'.$this->host.'/?hoststatustypes='.nagstat::HOST_UP.'&'.$grouptype_arg, 'lable' => $hosts['hosts_up'], 'status' => $label_up, 'status_id' => nagstat::HOST_UP),
			array('url' => 'status/'.$target_method.'/'.$this->host.'/?hoststatustypes='.nagstat::HOST_DOWN.'&'.$grouptype_arg, 'lable' => $hosts['hosts_down'], 'status' => $label_down, 'status_id' => nagstat::HOST_DOWN),
			array('url' => 'status/'.$target_method.'/'.$this->host.'/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&'.$grouptype_arg, 'lable' => $hosts['hosts_unreachable'], 'status' => $label_unreachable, 'status_id' => nagstat::HOST_UNREACHABLE),
			array('url' => 'status/'.$target_method.'/'.$this->host.'/?hoststatustypes='.nagstat::HOST_PENDING.'&'.$grouptype_arg, 'lable' => $hosts['hosts_pending'], 'status' => $label_pending, 'status_id' => nagstat::HOST_PENDING)
		);

		$svc_label_ok = $this->translate->_('Ok');
		$svc_label_warning = $this->translate->_('Warning');
		$svc_label_unknown = $this->translate->_('Unknown');
		$svc_label_critical	= $this->translate->_('Critical');
		$svc_label_pending = $this->translate->_('Pending');

		$service_header = array(
			array('url' => 'status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.nagstat::SERVICE_OK.'&'.$grouptype_arg, 'lable' => $services['services_ok'], 'status' => $svc_label_ok, 'status_id' => nagstat::SERVICE_OK),
			array('url' => 'status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.nagstat::SERVICE_WARNING.'&'.$grouptype_arg, 'lable' => $services['services_warning'], 'status' => $svc_label_warning, 'status_id' => nagstat::SERVICE_WARNING),
			array('url' => 'status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&'.$grouptype_arg, 'lable' => $services['services_unknown'], 'status' => $svc_label_unknown, 'status_id' => nagstat::SERVICE_UNKNOWN),
			array('url' => 'status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&'.$grouptype_arg, 'lable' => $services['services_critical'], 'status' => $svc_label_critical, 'status_id' => nagstat::SERVICE_CRITICAL),
			array('url' => 'status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.nagstat::SERVICE_PENDING.'&'.$grouptype_arg, 'lable' => $services['services_pending'], 'status' => $svc_label_pending, 'status_id' => nagstat::SERVICE_PENDING)
		);

		$this->js = array('js/status_totals');
		require($view_path);
	}
}
