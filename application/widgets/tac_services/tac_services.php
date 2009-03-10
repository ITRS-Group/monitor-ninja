<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Services widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_services_Widget extends widget_Core {
	const HOST_PENDING = 1;
	const HOST_UP = 2;
	const HOST_DOWN	= 4;
	const HOST_UNREACHABLE = 8;
#	const HOST_STATE_ACKNOWLEDGED = 4;
#	const HOST_STATE_UNACKNOWLEDGED = 8;
#	const HOST_SCHEDULED_DOWNTIME = 1;
#	const HOST_NO_SCHEDULED_DOWNTIME = 2;
#	const HOST_CHECKS_DISABLED = 16;
#	const HOST_CHECKS_ENABLED = 32;
	const SERVICE_PENDING = 1;
	const SERVICE_OK = 2;
	const SERVICE_WARNING = 4;
	const SERVICE_UNKNOWN = 8;
	const SERVICE_CRITICAL = 16;
	const SERVICE_SCHEDULED_DOWNTIME = 1;
	const SERVICE_NO_SCHEDULED_DOWNTIME	= 2;
	const SERVICE_STATE_ACKNOWLEDGED = 4;
	const SERVICE_STATE_UNACKNOWLEDGED = 8;
	const SERVICE_CHECKS_DISABLED = 16;
	const SERVICE_CHECKS_ENABLED = 32;

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
		$title = $this->translate->_('Services');

		$header_links = array(
			'status/index/all/detail/'.self::SERVICE_CRITICAL => $current_status->services_critical.' '.$this->translate->_('Critical'),
			'status/index/all/detail/'.self::SERVICE_WARNING  => $current_status->services_warning.' '.$this->translate->_('Warning'),
			'status/index/all/detail/'.self::SERVICE_UNKNOWN  => $current_status->services_unknown.' '.$this->translate->_('Unknown'),
			'status/index/all/detail/'.self::SERVICE_OK  => $current_status->services_ok.' '.$this->translate->_('Ok'),
			'status/index/all/detail/'.self::SERVICE_PENDING   => $current_status->services_pending.' '.$this->translate->_('Pending')
		);

		# SERVICES CRITICAL
		$services_critical = array();
		if ($current_status->services_critical_unacknowledged) {
			$services_critical['status/index/all/detail/'.self::SERVICE_CRITICAL.'/'.(self::HOST_UP|self::HOST_PENDING).'/'.(self::SERVICE_NO_SCHEDULED_DOWNTIME|self::SERVICE_STATE_UNACKNOWLEDGED|self::SERVICE_CHECKS_ENABLED)] =
				$current_status->services_critical_unacknowledged.' '.$this->translate->_('Unhandled Problems');
		}

		if ($current_status->services_critical_host_problem) {
			$services_critical['status/index/all/detail/'.self::SERVICE_CRITICAL.'/'.(self::HOST_DOWN|self::HOST_UNREACHABLE)] = $current_status->services_critical_host_problem.' '.$this->translate->_('on Problem Hosts');
		}

		if ($current_status->services_critical_scheduled) {
			$services_critical['status/index/all/detail/'.self::SERVICE_CRITICAL.'/'.self::SERVICE_SCHEDULED_DOWNTIME] = $current_status->services_critical_scheduled.' '.$this->translate->_('Scheduled');
		}

		if ($current_status->services_critical_acknowledged) {
			$services_critical['status/index/all/detail/'.self::SERVICE_CRITICAL.'/'.self::SERVICE_STATE_ACKNOWLEDGED] = $current_status->services_critical_acknowledged.' '.$this->translate->_('Acknowledged');
		}

		if ($current_status->services_critical_disabled) {
			$services_critical['status/index/all/detail/'.self::SERVICE_CRITICAL.'/'.self::SERVICE_CHECKS_DISABLED ] = $current_status->services_critical_disabled.' '.$this->translate->_('Disabled');
		}


		# SERVICES WARNING
		$services_warning = array();
		# HOST_UP|HOST_PENDING
		# SERVICE_NO_SCHEDULED_DOWNTIME|SERVICE_STATE_UNACKNOWLEDGED|SERVICE_CHECKS_ENABLED
		if ($current_status->services_warning_unacknowledged) {
			$services_warning['status/index/all/detail/'.self::SERVICE_WARNING.'/'.(self::HOST_UP|self::HOST_PENDING).'/'.(self::SERVICE_NO_SCHEDULED_DOWNTIME|self::SERVICE_STATE_UNACKNOWLEDGED|self::SERVICE_CHECKS_ENABLED)] =
				$current_status->services_warning_unacknowledged.' '.$this->translate->_('Unhandled Problems');
		}

		if ($current_status->services_warning_host_problem) {
			$services_warning['status/index/all/detail/'.self::SERVICE_WARNING.'/'.(self::HOST_DOWN|self::HOST_UNREACHABLE)] = $current_status->services_warning_host_problem.' '.$this->translate->_('on Problem Hosts');
		}

		if ($current_status->services_warning_scheduled) {
			$services_critical['status/index/all/detail/'.self::SERVICE_WARNING.'/'.self::SERVICE_SCHEDULED_DOWNTIME] = $current_status->services_warning_scheduled.' '.$this->translate->_('Scheduled');
		}

		if ($current_status->services_warning_acknowledged) {
			$services_warning['status/index/all/detail/'.self::SERVICE_WARNING.'/'.self::SERVICE_STATE_ACKNOWLEDGED] = $current_status->services_warning_acknowledged.' '.$this->translate->_('Acknowledged');
		}

		if ($current_status->services_warning_disabled) {
			$services_warning['status/index/all/detail/'.self::SERVICE_WARNING.'/'.self::SERVICE_CHECKS_DISABLED ] = $current_status->services_warning_disabled.' '.$this->translate->_('Disabled');
		}


		# SERVICES UNKNOWN
		$services_unknown = array();
		if ($current_status->services_unknown_unacknowledged) {
			$services_unknown['status/index/all/hostdetail/'.self::SERVICE_UNKNOWN.'/'.(self::HOST_UP|self::HOST_PENDING).'/'.(self::SERVICE_NO_SCHEDULED_DOWNTIME|self::SERVICE_STATE_UNACKNOWLEDGED|self::SERVICE_CHECKS_ENABLED)] =
				$current_status->services_unknown_unacknowledged.' '.$this->translate->_('Unhandled Problems');
		}

		if ($current_status->services_unknown_host_problem) {
			$services_unknown['status/index/all/detail/'.self::SERVICE_UNKNOWN.'/'.(self::HOST_DOWN|self::HOST_UNREACHABLE)] = $current_status->services_unknown_host_problem.' '.$this->translate->_('on Problem Hosts');
		}

		if ($current_status->services_unknown_scheduled) {
			$services_unknown['status/index/all/detail/'.self::SERVICE_UNKNOWN.'/'.self::SERVICE_SCHEDULED_DOWNTIME] = $current_status->services_unknown_scheduled.' '.$this->translate->_('Scheduled');
		}

		if ($current_status->services_unknown_acknowledged) {
			$services_unknown['status/index/all/detail/'.self::SERVICE_UNKNOWN.'/'.self::SERVICE_STATE_ACKNOWLEDGED] = $current_status->services_unknown_acknowledged.' '.$this->translate->_('Acknowledged');
		}

		if ($current_status->services_unknown_disabled) {
			$services_unknown['status/index/all/detail/'.self::SERVICE_UNKNOWN.'/'.self::SERVICE_CHECKS_DISABLED ] = $current_status->services_unknown_disabled.' '.$this->translate->_('Disabled');
		}


		# SERVICES OK DISABLED
		$services_ok_disabled = array();
		if ($current_status->services_ok_disabled) {
			$services_ok_disabled['status/index/all/detail/'.self::SERVICE_OK.'/'.self::SERVICE_CHECKS_DISABLED] = $current_status->services_ok_disabled.' '.$this->translate->_('Disabled');
		}

		# SERVICES PENDING DISABLED
		$services_pending_disabled = array();
		if ($current_status->services_pending_disabled) {
			$services_pending_disabled['status/index/all/detail/'.self::SERVICE_PENDING .'/'.self::SERVICE_CHECKS_DISABLED] = $current_status->services_pending_disabled.' '.$this->translate->_('Disabled');
		}

		# fetch widget content
		require_once($view_path);

		# call parent helper to assign all
		# variables to master controller
		return $this->fetch();
	}
}

