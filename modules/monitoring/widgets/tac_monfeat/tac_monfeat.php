<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Monitoring Features widget for tactical overview
 *
 * @author     op5 AB
 */
class Tac_monfeat_Widget extends widget_Base {
	protected $duplicatable = true;

	public function index () {

		$view_path = $this->view_path('view');
		$linkprovider = LinkProvider::factory();

		$status = (object) array();
		$host_status = (object) array();
		$service_status = (object) array();

		try {
			$current_status = Current_status_Model::instance();
			$current_status->analyze_status_data();
			$host_status = $current_status->hst;
			$service_status = $current_status->svc;
			$status = StatusPool_Model::status();
		} catch (op5LivestatusException $ex) {
			$error = _("Could not connect to Livestatus");
			require($view_path);
			return;
		}

		$cmd_flap_link = $linkprovider->get_url('cmd', 'index', array(
			"command" => ($status->get_enable_flap_detection() ? 'stop' : 'start').'_flap_detection',
			"table" => "status",
			"object" => ""
		));

		$cmd_notification_link = $linkprovider->get_url('cmd', 'index', array(
			"command" => ($status->get_enable_notifications() ? 'disable' : 'enable').'_notifications',
			"table" => "status",
			"object" => ""
		));

		$cmd_event_link = $linkprovider->get_url('cmd', 'index', array(
			"command" => ($status->get_enable_event_handlers() ? 'disable' : 'enable').'_event_handlers',
			"table" => "status",
			"object" => ""
		));

		$cmd_check_service_link = $linkprovider->get_url('cmd', 'index', array(
			"command" => ($status->get_execute_service_checks() ? 'disable' : 'enable').'_service_checks',
			"table" => "status",
			"object" => ""
		));

		$cmd_check_host_link = $linkprovider->get_url('cmd', 'index', array(
			"command" => ($status->get_execute_host_checks() ? 'disable' : 'enable').'_host_checks',
			"table" => "status",
			"object" => ""
		));

		$cmd_passive_service_link = $linkprovider->get_url('cmd', 'index', array(
			"command" => ($status->get_accept_passive_service_checks() ? 'disable' : 'enable').'_service_passive_checks',
			"table" => "status",
			"object" => ""
		));

		$cmd_passive_host_link = $linkprovider->get_url('cmd', 'index', array(
			"command" => ($status->get_accept_passive_host_checks() ? 'disable' : 'enable').'_host_passive_checks',
			"table" => "status",
			"object" => ""
		));

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
