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
		$model->friendly_name = 'Host Status Totals | Service Status Totals';
		parent::__construct($model);
		$this->hoststatus = nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE|nagstat::HOST_PENDING;
		$this->add_css_class('right');
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

		$stats = new Stats_Model();
		if (empty($this->grouptype) || $this->host == 'all' || !$this->host) {
			$hosts    = $stats->get_stats('host_totals');
			$services = $stats->get_stats('service_totals');
		} else if ($this->grouptype == 'host') {
			$hosts    = $stats->get_stats('host_totals',    array('filter' => array(      'groups' => array('>=' => $this->host))));
			$services = $stats->get_stats('service_totals', array('filter' => array( 'host_groups' => array('>=' => $this->host))));
		} else {
			$hosts    = false; // Not possible in livestatus. Livestatus actually doesn't support hostsbyservicegroup
			$services = $stats->get_stats('service_totals', array('filter' => array(      'groups' => array('>=' => $this->host))));
		}

		$grouptype = !empty($this->grouptype) ? $this->grouptype.'group' : false;

		# assign variables for our view
		$label_all_problems = _('All Problems');
		$label_all_types = _('All Types');
		$label_all_host_problems = _('Problems in Total');
		$label_all_host_types = _('Types in Total');
		$label_all_service_problems = _('Problems in Total');
		$label_all_service_types = _('Types in Total');

		$host_title = _('Host Status Totals');
		$service_title = _('Service Status Totals');
		$target_method = 'host';

		$grouptype_arg = $grouptype ? 'group_type='.$grouptype : '';
		$host_header = $hosts===false ? false : array(
			array('url' => 'status/'.$target_method.'/'.$this->host.'/?hoststatustypes='.nagstat::HOST_UP.'&'.$grouptype_arg, 'lable' => $hosts->up, 'status' => _('Up'), 'status_id' => nagstat::HOST_UP),
			array('url' => 'status/'.$target_method.'/'.$this->host.'/?hoststatustypes='.nagstat::HOST_DOWN.'&'.$grouptype_arg, 'lable' => $hosts->down, 'status' => _('Down'), 'status_id' => nagstat::HOST_DOWN),
			array('url' => 'status/'.$target_method.'/'.$this->host.'/?hoststatustypes='.nagstat::HOST_UNREACHABLE.'&'.$grouptype_arg, 'lable' => $hosts->unreachable, 'status' => _('Unreachable'), 'status_id' => nagstat::HOST_UNREACHABLE),
			array('url' => 'status/'.$target_method.'/'.$this->host.'/?hoststatustypes='.nagstat::HOST_PENDING.'&'.$grouptype_arg, 'lable' => $hosts->pending, 'status' => _('Pending'), 'status_id' => nagstat::HOST_PENDING)
		);

		$service_header = $services===false ? false : array(
			array('url' => 'status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.nagstat::SERVICE_OK.'&'.$grouptype_arg, 'lable' => $services->ok, 'status' => _('Ok'), 'status_id' => nagstat::SERVICE_OK),
			array('url' => 'status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.nagstat::SERVICE_WARNING.'&'.$grouptype_arg, 'lable' => $services->warning, 'status' => _('Warning'), 'status_id' => nagstat::SERVICE_WARNING),
			array('url' => 'status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&'.$grouptype_arg, 'lable' => $services->unknown, 'status' => _('Unknown'), 'status_id' => nagstat::SERVICE_UNKNOWN),
			array('url' => 'status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&'.$grouptype_arg, 'lable' => $services->critical, 'status' => _('Critical'), 'status_id' => nagstat::SERVICE_CRITICAL),
			array('url' => 'status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.nagstat::SERVICE_PENDING.'&'.$grouptype_arg, 'lable' => $services->pending, 'status' => _('Pending'), 'status_id' => nagstat::SERVICE_PENDING)
		);

		require($view_path);
	}
}
