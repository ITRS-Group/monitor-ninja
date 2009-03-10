<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_hosts_Widget extends widget_Core {
	const HOST_PENDING = 1;
	const HOST_UP = 2;
	const HOST_DOWN	= 4;
	const HOST_UNREACHABLE = 8;
	const HOST_STATE_ACKNOWLEDGED = 4;
	const HOST_STATE_UNACKNOWLEDGED = 8;
	const HOST_SCHEDULED_DOWNTIME = 1;
	const HOST_NO_SCHEDULED_DOWNTIME = 2;
	const HOST_CHECKS_DISABLED = 16;
	const HOST_CHECKS_ENABLED = 32;

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

		# assign variables for our view
		$header_links = array(
			'status/index/all/hostdetail/'.self::HOST_DOWN => $current_status->hosts_down.' Down',
			'status/index/all/hostdetail/'.self::HOST_UNREACHABLE => $current_status->hosts_unreachable.' Unreachable',
			'status/index/all/hostdetail/'.self::HOST_UP => $current_status->hosts_up.' Up',
			'status/index/all/hostdetail/'.self::HOST_PENDING => $current_status->hosts_pending.' Pending'
		);

		# HOSTS DOWN
		$hosts_down = array();
		if ($current_status->hosts_down_unacknowledged) {
			$hosts_down['status/index/all/hostdetail/'.self::HOST_DOWN.'/'.(self::HOST_NO_SCHEDULED_DOWNTIME|self::HOST_STATE_UNACKNOWLEDGED|self::HOST_CHECKS_ENABLED)] =
				$current_status->hosts_down_unacknowledged.' Unhandled Problems';
		}

		if ($current_status->hosts_down_scheduled) {
			$hosts_down['status/index/all/hostdetail/'.self::HOST_DOWN.'/'.self::HOST_SCHEDULED_DOWNTIME] = $current_status->hosts_down_scheduled.' Scheduled';
		}

		if ($current_status->hosts_down_acknowledged) {
			$hosts_down['status/index/all/hostdetail/'.self::HOST_DOWN.'/'.self::HOST_STATE_ACKNOWLEDGED] = $current_status->hosts_down_acknowledged.' Acknowledged';
		}

		if ($current_status->hosts_down_disabled) {
			$hosts_down['status/index/all/hostdetail/'.self::HOST_DOWN.'/'.self::HOST_CHECKS_DISABLED] = $current_status->hosts_down_disabled.' Disabled';
		}

		# HOSTS UNREACHABLE
		$hosts_unreachable = array();

		if ($current_status->hosts_unreachable_unacknowledged) {
			$hosts_unreachable['status/index/all/hostdetail/'.self::HOST_UNREACHABLE.'/'.(self::HOST_NO_SCHEDULED_DOWNTIME|self::HOST_STATE_UNACKNOWLEDGED|self::HOST_CHECKS_ENABLED)] =
				$current_status->hosts_unreachable_unacknowledged.' Unhandled Problems';
		}

		if ($current_status->hosts_unreachable_scheduled) {
			$hosts_unreachable['status/index/all/hostdetail/'.self::HOST_UNREACHABLE.'/'.self::HOST_SCHEDULED_DOWNTIME] = $current_status->hosts_unreachable_scheduled.' Scheduled';
		}

		if ($current_status->hosts_unreachable_acknowledged) {
			$hosts_unreachable['status/index/all/hostdetail/'.self::HOST_UNREACHABLE.'/'.self::HOST_STATE_ACKNOWLEDGED] = $current_status->hosts_unreachable_acknowledged.' Acknowledged';
		}

		if ($current_status->hosts_unreachable_disabled) {
			$hosts_unreachable['status/index/all/hostdetail/'.self::HOST_UNREACHABLE.'/'.self::HOST_CHECKS_DISABLED] = $current_status->hosts_unreachable_disabled.' Disabled';
		}


		# HOSTS UP DISABLED
		$hosts_up_disabled = array();
		if ($current_status->hosts_up_disabled) {
			$hosts_up_disabled['status/index/all/hostdetail/'.self::HOST_UP .'/'.self::HOST_CHECKS_DISABLED] = $current_status->hosts_up_disabled.' Disabled';
		}

		# HOSTS PENDING DISABLED
		$hosts_pending_disabled = array();
		if ($current_status->hosts_pending_disabled) {
			$hosts_pending_disabled['status/index/all/hostdetail/'.self::HOST_PENDING  .'/'.self::HOST_CHECKS_DISABLED] = $current_status->hosts_pending_disabled.' Disabled';
		}

		# fetch widget content
		require_once($view_path);

		# call parent helper to assign all
		# variables to master controller
		return $this->fetch();
	}
}

