<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Extinfo controller
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Extinfo_Controller extends Authenticated_Controller {
	public $current = false;
	public $logos_path = '';

	public function __construct()
	{
		parent::__construct();

		$this->logos_path = Kohana::config('config.logos_path');
	}

	/**
	*	Default controller method
	*	Redirects to show_process_info() which
	*	is the equivalent of calling extinfo.cgi?type=0
	*/
	public function index()
	{
		url::redirect(Router::$controller.'/show_process_info');
	}

	/**
	*	@name details
	*	@desc
	*
	*/
	public function details($type='host', $host=false, $service=false)
	{
		# load current status for host/service status totals
		$this->current = new Current_status_Model();

		$host =link::decode($host);
		$service = link::decode($service);
		if (empty($host)) {
			return false;
		}

		$this->template->content = $this->add_view('extinfo/index');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$result = $this->current->object_status($host, $service);
		$host_link = false;
		$yes = $this->translate->_('YES');
		$no = $this->translate->_('NO');

		if ($type == 'host') {
			$group_info = $this->current->get_groups_for_object($type, $result->current()->id);
			$this->template->content->no_group_lable = $this->translate->_('No hostgroups');
			$check_compare_value = Current_status_Model::HOST_CHECK_ACTIVE;
			$last_notification = $result->current()->last_host_notification;
			$this->template->content->lable_next_scheduled_check = $this->translate->_('Next Scheduled Active Check');
			$this->template->content->lable_flapping = $this->translate->_('Is This Host Flapping?');
			$obsessing = $result->current()->obsess_over_host;
		} else {
			$group_info = $this->current->get_groups_for_object($type, $result->current()->service_id);
			$this->template->content->no_group_lable = $this->translate->_('No servicegroups');
			$this->template->content->lable_next_scheduled_check = $this->translate->_('Next Scheduled Check');
			$host_link = html::anchor('extinfo/details/host/'.link::encode($host), html::specialchars($host));
			$check_compare_value = Current_status_Model::SERVICE_CHECK_ACTIVE;
			$last_notification = $result->current()->last_notification;
			$this->template->content->lable_flapping = $this->translate->_('Is This Service Flapping?');
			$obsessing = $result->current()->obsess_over_service;
		}

		$groups = false;
		foreach ($group_info as $group_row) {
			$groups[] = html::anchor(sprintf("status/%sgroup/%s", $type, link::encode($group_row->{$type.'group_name'})),
				html::specialchars($group_row->{$type.'group_name'}));
		}


		$this->template->content->lable_type = $type == 'host' ? $this->translate->_('Host') : $this->translate->_('Service');
		$this->template->content->type = $type;
		$this->template->content->date_format_str = 'Y-m-d H:i:s';
		$this->template->content->host_link = $host_link;
		$this->template->content->lable_member_of = $this->translate->_('Member of');
		$this->template->content->lable_for = $this->translate->_('for');
		$this->template->content->lable_on_host = $this->translate->_('On Host');
		$this->template->content->main_object = $type=='host' ? $host : $service;
		$this->template->content->host = $host;
		$this->template->content->lable_current_status = $this->translate->_('Current Status');
		$this->template->content->lable_status_information = $this->translate->_('Status Information');
		$this->template->content->current_status_str = $this->current->translate_status($result->current()->current_state, $type);
		$this->template->content->duration = $result->current()->duration;
		$this->template->content->groups = $groups;
		$this->template->content->host_address = $result->current()->address;
		$this->template->content->status_info = $result->current()->plugin_output;
		$this->template->content->lable_perf_data = $this->translate->_('Performance Data');
		$this->template->content->perf_data = $result->current()->perf_data;
		$this->template->content->lable_current_attempt = $this->translate->_('Current Attempt');
		$this->template->content->current_attempt = $result->current()->current_attempt;
		$this->template->content->state_type = $result->current()->state_type ? $this->translate->_('HARD state') : $this->translate->_('SOFT state');
		$this->template->content->main_object_alias = $type=='host' ? $result->current()->alias : false;
		$this->template->content->max_attempts = $result->current()->max_attempts;
		$this->template->content->last_update = $result->current()->last_update;
		$this->template->content->last_check = $result->current()->last_check;
		$this->template->content->lable_last_check = $this->translate->_('Last Check Time');
		$this->template->content->lable_check_type = $this->translate->_('Check Type');
		$this->template->content->lable_last_update = $this->translate->_('Last Update');

		$str_active = $this->translate->_('ACTIVE');
		$str_passive = $this->translate->_('PASSIVE');
		$this->template->content->check_type = $result->current()->check_type == $check_compare_value ? $str_active: $str_passive;
		$this->template->content->lable_check_latency_duration = $this->translate->_('Check Latency / Duration');
		$na_str = $this->translate->_('N/A');
		$this->template->content->na_str = $na_str;
		$this->template->content->check_latency =
		$result->current()->check_type == $check_compare_value ? $result->current()->latency : $na_str;
		$this->template->content->execution_time = $result->current()->execution_time;
		$this->template->content->lable_seconds = $this->translate->_('seconds');

		$this->template->content->next_check = (int)$result->current()->next_check;
		$this->template->content->lable_last_state_change = $this->translate->_('Last State Change');
		$this->template->content->last_state_change = (int)$result->current()->last_state_change;
		$this->template->content->lable_last_notification = $this->translate->_('Last Notification');
		$this->template->content->lable_n_a = $na_str;
		$this->template->content->last_notification = $last_notification!=0 ? $last_notification : $na_str;
		$this->template->content->lable_notifications = $this->translate->_('notification');
		$this->template->content->current_notification_number = $result->current()->current_notification_number;
		$lable_flapping_state_change = $this->translate->_('state change');
		$this->template->content->percent_state_change_str = '';
		$is_flapping = $result->current()->is_flapping;
		if (!$result->current()->flap_detection_enabled) {
			$this->template->content->flap_value = $na_str;
		} else {
			$this->template->content->flap_value = $is_flapping ? $yes : $no;
			$this->template->content->percent_state_change_str = '('.number_format((int)$result->current()->percent_state_change, 2).'% '.$lable_flapping_state_change.')';
		}
		$this->template->content->lable_in_scheduled_dt = $this->translate->_('In Scheduled Downtime?');
		$this->template->content->scheduled_downtime_depth = $result->current()->scheduled_downtime_depth ? $yes : $no;
		$last_update_ago_arr = date::timespan(time(), $result->current()->last_update, 'days,hours,minutes,seconds');
		$ago = $this->translate->_('ago');
		$last_update_ago = false;
		$last_update_ago_str = '';
		if (is_array($last_update_ago_arr) && !empty($last_update_ago_arr)) {
			foreach ($last_update_ago_arr as $key => $val) {
				$last_update_ago[] = $val.substr($key, 0, 1);
			}
			$last_update_ago_str = '( '.implode(' ', $last_update_ago) . ' ' . $ago . ')';
		}
		$this->template->content->last_update_ago = $last_update_ago_str !='' ? $last_update_ago_str : $na_str;
		$this->template->content->lable_active_checks = $this->translate->_('Active Checks');
		$this->template->content->lable_passive_checks = $this->translate->_('Passive Checks');
		$this->template->content->lable_obsessing = $this->translate->_('Obsessing');
		$this->template->content->lable_notifications = $this->translate->_('Notifications');
		$this->template->content->lable_event_handler = $this->translate->_('Event Handler');
		$this->template->content->lable_flap_detection = $this->translate->_('Flap Detection');
		$str_enabled = $this->translate->_('ENABLED');
		$str_disabled = $this->translate->_('DISABLED');
		$this->template->content->active_checks_enabled = $result->current()->active_checks_enabled ? $str_enabled : $str_disabled;
		$this->template->content->passive_checks_enabled = $result->current()->passive_checks_enabled ? $str_enabled : $str_disabled;
		$this->template->content->obsessing = $obsessing ? $str_enabled : $str_disabled;
		$this->template->content->notifications_enabled = $result->current()->notifications_enabled ? $str_enabled : $str_disabled;
		$this->template->content->event_handler_enabled = $result->current()->event_handler_enabled ? $str_enabled : $str_disabled;
		$this->template->content->flap_detection_enabled = $result->current()->flap_detection_enabled ? $str_enabled : $str_disabled;
	}

	/**
	*	@name show_process_info
	*	@desc
	*
	*/
	public function show_process_info()
	{
		$this->template->content = $this->add_view('extinfo/process_info');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		# instance_name = NULL
		# instance_id = NULL

		# Lables to translate
		$na_str = $this->translate->_('N/A');
		$yes = $this->translate->_('YES');
		$no = $this->translate->_('NO');
		$this->template->content->lable_program_version = $this->translate->_('Program Version');
		$this->template->content->lable_program_start_time = $this->translate->_('Program Start Time');
		$this->template->content->lable_total_run_time = $this->translate->_('Total Running Time');
		$this->template->content->lable_last_external_cmd_check = $this->translate->_('Last External Command Check');
		$this->template->content->lable_last_logfile_rotation = $this->translate->_('Last Log File Rotation');
		$this->template->content->lable_pid = strstr(__FILE__, 'op5') ? $this->translate->_('Monitor PID') : $this->translate->_('Nagios PID');
		$this->template->content->lable_notifications_enabled = $this->translate->_('Notifications Enabled?');
		$this->template->content->lable_service_checks = $this->translate->_('Service Checks Being Executed?');
		$this->template->content->lable_service_checks_passive = $this->translate->_('Passive Service Checks Being Accepted?');
		$this->template->content->lable_host_checks = $this->translate->_('Host Checks Being Executed?');
		$this->template->content->lable_host_checks_passive = $this->translate->_('Passive Host Checks Being Accepted?');
		$this->template->content->lable_event_handlers = $this->translate->_('Event Handlers Enabled?');
		$this->template->content->lable_obsess_services = $this->translate->_('Obsessing Over Services?');
		$this->template->content->lable_obsess_hosts = $this->translate->_('Obsessing Over Hosts?');
		$this->template->content->lable_flap_enabled = $this->translate->_('Flap Detection Enabled?');
		$this->template->content->lable_performance_data = $this->translate->_('Performance Data Being Processed?');

		# parse nagios.cfg to figure out date format
		$current_status = new Current_status_Model;
		$nagios_config = $current_status->parse_config_file('nagios.cfg');

		# @@@FIXME setting date format should be done somewhere global
		# DATE FORMAT OPTION
		#       us              (MM-DD-YYYY HH:MM:SS)
		#       euro            (DD-MM-YYYY HH:MM:SS)
		#       iso8601         (YYYY-MM-DD HH:MM:SS)
		#       strict-iso8601  (YYYY-MM-DDTHH:MM:SS)

		$date_format_str = nagstat::date_format($nagios_config['date_format']);

		# fetch program status from program_status_model
		# uses ORM
		$status = ORM::factory('program_status')->find_all();

		# @@@FIXME how do we figure the program version out?
		$this->template->content->program_version = $na_str;

		if ($status->count() > 0) {
			$this->template->content->program_start = date($date_format_str, $status->program_start);
			$this->template->content->run_time = date::timespan(time(), $result->current()->$status->program_start, 'days,hours,minutes,seconds');
			$this->template->content->last_command_check = $status->last_command_check;
			$this->template->content->last_log_rotation = $status->last_log_rotation;
			$this->template->content->nagios_pid = $status->pid;
			$this->template->content->notifications_enabled = $status->notifications_enabled;
			$this->template->content->execute_service_checks = $status->active_service_checks_enabled;
			$this->template->content->accept_passive_service_checks = $status->passive_service_checks_enabled;
			$this->template->content->execute_host_checks = $status->active_host_checks_enabled;
			$this->template->content->accept_passive_host_checks = $status->passive_service_checks_enabled;
			$this->template->content->enable_event_handlers = $status->event_handlers_enabled;
			$this->template->content->obsess_over_services = $status->obsess_over_services;
			$this->template->content->obsess_over_hosts = $status->obsess_over_hosts;
			$this->template->content->flap_detection_enabled = $status->flap_detection_enabled;
			$this->template->content->enable_failure_prediction = $status->failure_prediction_enabled;
			$this->template->content->process_performance_data = $status->process_performance_data;
		} else {
			# nothing found in program_status
			# @@@FIXME probably an error - handle this someway
			# fetch what we can find from nagios.cfg for now

			$this->template->content->notifications_enabled = isset($nagios_config['enable_notifications']) ? $nagios_config['enable_notifications'] : false;
			$this->template->content->flap_detection_enabled = isset($nagios_config['enable_flap_detection']) ? $nagios_config['enable_flap_detection'] : false;
			$this->template->content->enable_event_handlers = isset($nagios_config['enable_event_handlers']) ? $nagios_config['enable_event_handlers'] : false;
			$this->template->content->execute_service_checks = isset($nagios_config['execute_service_checks']) ? $nagios_config['execute_service_checks'] : false;
			$this->template->content->accept_passive_service_checks = isset($nagios_config['accept_passive_service_checks']) ? $nagios_config['accept_passive_service_checks'] : false;
			$this->template->content->obsess_over_services = isset($nagios_config['obsess_over_services']) ? $nagios_config['obsess_over_services'] : false;
			$this->template->content->execute_host_checks = isset($nagios_config['execute_host_checks']) ? $nagios_config['execute_host_checks'] : false;
			$this->template->content->accept_passive_host_checks = isset($nagios_config['accept_passive_host_checks']) ? $nagios_config['accept_passive_host_checks'] : false;
			$this->template->content->obsess_over_hosts = isset($nagios_config['obsess_over_hosts']) ? $nagios_config['obsess_over_hosts'] : false;
			$this->template->content->process_performance_data = isset($nagios_config['process_performance_data']) ? $nagios_config['process_performance_data'] : false;

			# set the following values to some default since we can't seem to determine
			# the correct value at the moment
			$this->template->content->enable_failure_prediction = false;
			$this->template->content->program_start = $na_str;
			$this->template->content->run_time = $na_str;
			$run_time = false;
			$this->template->content->last_command_check = $na_str;
			$this->template->content->last_log_rotation = $na_str;

			# are we runnig monitor or nagios?
			$process_name = strstr(__FILE__, 'op5') ? 'monitor' : 'nagios';
			$this->template->content->nagios_pid = exec("pidof ".$process_name."|awk {'print $1'}");
		}

		$this->template->content->notifications_class = $this->template->content->notifications_enabled ? 'notificationsENABLED' : 'notificationsDISABLED';
		$this->template->content->notifications_str = $this->template->content->notifications_enabled ? $yes : $no;
		$this->template->content->servicechecks_class = $this->template->content->execute_service_checks ? 'checksENABLED' : 'checksDISABLED';
		$this->template->content->servicechecks_str = $this->template->content->execute_service_checks ? $yes : $no;
		$this->template->content->passive_servicechecks_class = $this->template->content->accept_passive_service_checks ? 'checksENABLED' : 'checksDISABLED';
		$this->template->content->passive_servicechecks_str = $this->template->content->accept_passive_service_checks ? $yes : $no;
		$this->template->content->hostchecks_class = $this->template->content->execute_host_checks ? 'checksENABLED' : 'checksDISABLED';
		$this->template->content->hostchecks_str = $this->template->content->execute_host_checks ? $yes : $no;
		$this->template->content->passive_hostchecks_class = $this->template->content->accept_passive_host_checks ? 'checksENABLED' : 'checksDISABLED';
		$this->template->content->passive_hostchecks_str = $this->template->content->accept_passive_host_checks ? $yes : $no;
		$this->template->content->eventhandler_str = $this->template->content->enable_event_handlers ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$this->template->content->obsess_services_str = $this->template->content->obsess_over_services ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$this->template->content->obsess_hosts_str = $this->template->content->obsess_over_hosts ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$this->template->content->flap_detection_str = $this->template->content->flap_detection_enabled ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$this->template->content->performance_data_str = $this->template->content->process_performance_data ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
	}
}