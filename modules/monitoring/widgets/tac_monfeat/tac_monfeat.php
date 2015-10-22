<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Monitoring Features widget for tactical overview
 *
 * @author     op5 AB
 */
class Tac_monfeat_Widget extends widget_Base {
	protected $duplicatable = true;

	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		try {
			$current_status = Current_status_Model::instance();
			$current_status->analyze_status_data();

			# fetch global nagios config data
			# try with the database first but we may use the nagios.cfg file as fallback
			$status = Current_status_Model::instance()->program_status();
			$enable_notifications = $status->enable_notifications;
			$enable_flap_detection = $status->enable_flap_detection;
			$enable_event_handlers = $status->enable_event_handlers;
			$execute_service_checks = $status->execute_service_checks;
			$accept_passive_service_checks = $status->accept_passive_service_checks;

			$flap_disabled_services = $current_status->svc->flapping_disabled;
			$flapping_services = $current_status->svc->flapping;
			$flap_disabled_hosts = $current_status->hst->flapping_disabled;
			$flapping_hosts = $current_status->hst->flapping;

			$notification_disabled_services = $current_status->svc->notifications_disabled;
			$notification_disabled_hosts = $current_status->hst->notifications_disabled;

			$event_handler_disabled_svcs = $current_status->svc->eventhandler_disabled;
			$event_handler_disabled_hosts = $current_status->svc->eventhandler_disabled;

			$active_checks_disabled_svcs = $current_status->svc->active_checks_disabled_active;
			$active_checks_disabled_hosts = $current_status->hst->active_checks_disabled_active;

			$passive_checks_disabled_svcs = $current_status->svc->passive_checks_disabled;
			$passive_checks_disabled_hosts = $current_status->hst->passive_checks_disabled;


		}
		catch ( op5LivestatusException $ex) {
			$enable_notifications = false;
			$enable_flap_detection = false;
			$enable_event_handlers = false;
			$execute_service_checks = false;
			$accept_passive_service_checks = false;
		}

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

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'Monitoring features',
			'instanceable' => true
		));
	}
}
