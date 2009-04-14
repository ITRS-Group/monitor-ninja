<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Monitoring Features widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_monfeat_Widget extends widget_Core {

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

		# translation
		$title = $this->translate->_('Monitoring Features');
		$flap_detect_header_label = $this->translate->_('Flap Detection');
		$notifications_header_label = $this->translate->_('Notifications');
		$eventhandler_header_label = $this->translate->_('Event Handlers');
		$activechecks_header_label = $this->translate->_('Active Checks');
		$passivechecks_header_label = $this->translate->_('Passive Checks');
		$lable_enabled = $this->translate->_('Enabled');
		$lable_disabled = $this->translate->_('Disabled');
		$lable_flapping = $this->translate->_('Flapping');

		$lable_all_services = $this->translate->_('All Services');
		$lable_no_services = $this->translate->_('No Services');
		$lable_service_singular = $this->translate->_('Service');
		$lable_service_plural = $this->translate->_('Services');

		$lable_all_hosts = $this->translate->_('All Hosts');
		$lable_no_hosts = $this->translate->_('No Hosts');
		$lable_host_singular = $this->translate->_('Host');
		$lable_host_plural = $this->translate->_('Hosts');
		$na_str = $this->translate->_('N/A');

		# fetch global nagios config data
		# try with the database first but we may use the nagios.cfg file as fallback
		$status_res = ORM::factory('program_status')->find_all();
		if ($status_res->count() > 0) {
			$status = $status_res->current();
			$enable_notifications = $status->notifications_enabled;
			$enable_flap_detection = $status->flap_detection_enabled;
			$enable_event_handlers = $status->event_handlers_enabled;
			$execute_service_checks = $status->active_service_checks_enabled;
			$accept_passive_service_checks = $status->passive_service_checks_enabled;
		} else {
			$nagios_config = $current_status->parse_config_file('nagios.cfg');
			$enable_notifications = isset($nagios_config['enable_notifications']) ? $nagios_config['enable_notifications'] : false;
			$enable_flap_detection = isset($nagios_config['enable_flap_detection']) ? $nagios_config['enable_flap_detection'] : false;
			$enable_event_handlers = isset($nagios_config['enable_event_handlers']) ? $nagios_config['enable_event_handlers'] : false;
			$execute_service_checks = isset($nagios_config['execute_service_checks']) ? $nagios_config['execute_service_checks'] : false;
			$accept_passive_service_checks = isset($nagios_config['accept_passive_service_checks']) ? $nagios_config['accept_passive_service_checks'] : false;
		}

		$flap_disabled_services = $current_status->flap_disabled_services;
		$flapping_services = $current_status->flapping_services;
		$flap_disabled_hosts = $current_status->flap_disabled_hosts;
		$flapping_hosts = $current_status->flapping_hosts;

		$notification_disabled_services = $current_status->notification_disabled_services;
		$notification_disabled_hosts = $current_status->notification_disabled_hosts;

		$event_handler_disabled_services = $current_status->event_handler_disabled_services;
		$event_handler_disabled_hosts = $current_status->event_handler_disabled_hosts;

		$active_checks_disabled_services = $current_status->active_checks_disabled_services;
		$active_checks_disabled_hosts = $current_status->active_checks_disabled_hosts;

		$passive_checks_disabled_services = $current_status->passive_checks_disabled_services;
		$passive_checks_disabled_hosts = $current_status->passive_checks_disabled_hosts;

		$cmd_flap_link = html::anchor('cmd/command/'.($enable_flap_detection ? Cmd_Controller::CMD_DISABLE_FLAP_DETECTION : Cmd_Controller::CMD_ENABLE_FLAP_DETECTION), html::image($this->widget_full_path.'/images/tac'.($enable_flap_detection ? 'enabled' : 'disabled').'.png', array('alt' => $flap_detect_header_label.' '.($enable_flap_detection ? $lable_enabled : $lable_disabled), 'border' => 0)));
		$cmd_notification_link = html::anchor('cmd/command/'.($enable_notifications ? Cmd_Controller::CMD_DISABLE_NOTIFICATIONS : Cmd_Controller::CMD_ENABLE_NOTIFICATIONS), html::image($this->widget_full_path.'/images/tac'.($enable_notifications ? 'enabled' : 'disabled').'.png', array('alt' => $notifications_header_label.' '.($enable_notifications ? $lable_enabled : $lable_disabled), 'border' => 0)));
		$cmd_event_link = html::anchor('cmd/command/'.($enable_event_handlers ? Cmd_Controller::CMD_DISABLE_EVENT_HANDLERS : Cmd_Controller::CMD_ENABLE_EVENT_HANDLERS), html::image($this->widget_full_path.'/images/tac'.($enable_event_handlers ? 'enabled' : 'disabled').'.png', array('alt' => $eventhandler_header_label.' '.($enable_event_handlers ? $lable_enabled : $lable_disabled), 'border' => 0)));
		$cmd_activecheck_link = html::anchor('extinfo/', html::image($this->widget_full_path.'/images/tac'.($execute_service_checks ? 'enabled' : 'disabled').'.png', array('alt' => $activechecks_header_label.' '.($execute_service_checks ? $lable_enabled : $lable_disabled), 'border' => 0)));
		$cmd_passivecheck_link = html::anchor('extinfo/', html::image($this->widget_full_path.'/images/tac'.($accept_passive_service_checks ? 'enabled' : 'disabled').'.png', array('alt' => $passivechecks_header_label.' '.($accept_passive_service_checks ? $lable_enabled : $lable_disabled), 'border' => 0)));
		# <a href='extinfo.cgi?type=0'><img src='/monitor/images/tacenabled.png' border='0' alt='Active Checks Enabled' title='Active Checks Enabled'></a>


		# fetch widget content
		require_once($view_path);

		# call parent helper to assign all
		# variables to master controller
		return $this->fetch();
	}
}
