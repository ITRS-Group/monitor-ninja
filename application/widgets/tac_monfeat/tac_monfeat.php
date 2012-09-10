<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Monitoring Features widget for tactical overview
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_monfeat_Widget extends widget_Base {
	protected $duplicatable = true;

	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		$current_status = $this->get_current_status();

		$flap_detect_header_label = _('Flap Detection');
		$notifications_header_label = _('Notifications');
		$eventhandler_header_label = _('Event Handlers');
		$activechecks_header_label = _('Active Checks');
		$passivechecks_header_label = _('Passive Checks');
		$lable_enabled = _('Enabled');
		$lable_disabled = _('Disabled');
		$lable_flapping = _('Flapping');

		$lable_all_services = _('All Services');
		$lable_no_services = _('No Services');
		$lable_service_singular = _('Service');
		$lable_service_plural = _('Services');

		$lable_all_hosts = _('All Hosts');
		$lable_no_hosts = _('No Hosts');
		$lable_host_singular = _('Host');
		$lable_host_plural = _('Hosts');

		# fetch global nagios config data
		# try with the database first but we may use the nagios.cfg file as fallback
		$status_res = Program_status_Model::get_local();
		if (!empty($status_res) && count($status_res) > 0) {
			$status = $status_res->current();
			$enable_notifications = $status->notifications_enabled;
			$enable_flap_detection = $status->flap_detection_enabled;
			$enable_event_handlers = $status->event_handlers_enabled;
			$execute_service_checks = $status->active_service_checks_enabled;
			$accept_passive_service_checks = $status->passive_service_checks_enabled;
		} else {
			$nagios_config = System_Model::parse_config_file('nagios.cfg');
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

		$event_handler_disabled_svcs = $current_status->event_handler_disabled_svcs;
		$event_handler_disabled_hosts = $current_status->event_handler_disabled_hosts;

		$active_checks_disabled_svcs = $current_status->active_checks_disabled_svcs;
		$active_checks_disabled_hosts = $current_status->active_checks_disabled_hosts;

		$passive_checks_disabled_svcs = $current_status->passive_checks_disabled_svcs;
		$passive_checks_disabled_hosts = $current_status->passive_checks_disabled_hosts;

		$cmd_flap_status = ($enable_flap_detection ? 'enabled' : 'disabled').'_monfeat';
		$cmd_notification_status = ($enable_notifications ? 'enabled' : 'disabled').'_monfeat';
		$cmd_event_status = ($enable_event_handlers ? 'enabled' : 'disabled').'_monfeat';
		$cmd_activecheck_status = ($execute_service_checks ? 'enabled' : 'disabled').'_monfeat';
		$cmd_passivecheck_status = ($accept_passive_service_checks ? 'enabled' : 'disabled').'_monfeat';

		$cmd_flap_link = url::site('command/submit?cmd_typ='.($enable_flap_detection ? 'DIS' : 'EN').'ABLE_FLAP_DETECTION');
		$cmd_notification_link = url::site('command/submit?cmd_typ='.($enable_notifications ? 'DIS' : 'EN') . 'ABLE_NOTIFICATIONS');
		$cmd_event_link = url::site('command/submit?cmd_typ='.($enable_event_handlers ? 'DIS' : 'EN') . 'ABLE_EVENT_HANDLERS');
		$cmd_activecheck_link = url::site('extinfo/');
		$cmd_passivecheck_link = url::site('extinfo/');

		# fetch widget content
		require($view_path);
	}
}
