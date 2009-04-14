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

		$host = trim($host);
		$type = strtolower($type);
		$service = link::decode($service);
		if (empty($host)) {
			return false;
		}

		# is user authenticated to view details on current object?
		$auth = new Nagios_auth_Model();
		$is_authenticated = true;
		if ($type == 'host') {
			$auth_hosts = $auth->get_authorized_hosts();
			if (!array_key_exists($host, $auth->hosts_r)) {
				# user not allowed to view info on selected host
				$is_authenticated = false;
			}
		} else {
			$auth_services = $auth->get_authorized_services();
			if (!array_key_exists($host.';'.$service, $auth->services_r)) {
				# user not allowed to view info on selected service
				$is_authenticated = false;
			}
		}
		if ($is_authenticated === false) {
			url::redirect('extinfo/unauthorized/'.$type);
		}

		$this->template->content = $this->add_view('extinfo/index');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		# save us some typing
		$content = $this->template->content;
		$t = $this->translate;

		$result_data = $this->current->object_status($host, $service);
		$result = $result_data->current();
		$host_link = false;
		$yes = $t->_('YES');
		$no = $t->_('NO');

		if ($type == 'host') {
			$group_info = $this->current->get_groups_for_object($type, $result->id);
			$content->no_group_lable = $t->_('No hostgroups');
			$check_compare_value = Current_status_Model::HOST_CHECK_ACTIVE;
			$last_notification = $result->last_host_notification;
			$content->lable_next_scheduled_check = $t->_('Next Scheduled Active Check');
			$content->lable_flapping = $t->_('Is This Host Flapping?');
			$obsessing = $result->obsess_over_host;
		} else {
			$group_info = $this->current->get_groups_for_object($type, $result->service_id);
			$content->no_group_lable = $t->_('No servicegroups');
			$content->lable_next_scheduled_check = $t->_('Next Scheduled Check');
			$host_link = html::anchor('extinfo/details/host/'.$host, html::specialchars($host));
			$check_compare_value = Current_status_Model::SERVICE_CHECK_ACTIVE;
			$last_notification = $result->last_notification;
			$content->lable_flapping = $t->_('Is This Service Flapping?');
			$obsessing = $result->obsess_over_service;
		}

		$groups = false;
		foreach ($group_info as $group_row) {
			$groups[] = html::anchor(sprintf("status/%sgroup/%s", $type, $group_row->{$type.'group_name'}),
				html::specialchars($group_row->{$type.'group_name'}));
		}

		$content->lable_type = $type == 'host' ? $t->_('Host') : $t->_('Service');
		$content->type = $type;
		$content->date_format_str = 'Y-m-d H:i:s';
		$content->host_link = $host_link;
		$content->lable_member_of = $t->_('Member of');
		$content->lable_for = $t->_('for');
		$content->lable_on_host = $t->_('On Host');
		$content->main_object = $type=='host' ? $host : $service;
		$content->host = $host;
		$content->lable_current_status = $t->_('Current Status');
		$content->lable_status_information = $t->_('Status Information');
		$content->current_status_str = $this->current->translate_status($result->current_state, $type);
		$content->duration = $result->duration;
		$content->groups = $groups;
		$content->host_address = $result->address;
		$content->status_info = $result->plugin_output;
		$content->lable_perf_data = $t->_('Performance Data');
		$content->perf_data = $result->perf_data;
		$content->lable_current_attempt = $t->_('Current Attempt');
		$content->current_attempt = $result->current_attempt;
		$content->state_type = $result->state_type ? $t->_('HARD state') : $t->_('SOFT state');
		$content->main_object_alias = $type=='host' ? $result->alias : false;
		$content->max_attempts = $result->max_attempts;
		$content->last_update = $result->last_update;
		$content->last_check = $result->last_check;
		$content->lable_last_check = $t->_('Last Check Time');
		$content->lable_check_type = $t->_('Check Type');
		$content->lable_last_update = $t->_('Last Update');

		$str_active = $t->_('ACTIVE');
		$str_passive = $t->_('PASSIVE');
		$content->check_type = $result->check_type == $check_compare_value ? $str_active: $str_passive;
		$content->lable_check_latency_duration = $t->_('Check Latency / Duration');
		$na_str = $t->_('N/A');
		$content->na_str = $na_str;
		$content->check_latency =
		$result->check_type == $check_compare_value ? $result->latency : $na_str;
		$content->execution_time = $result->execution_time;
		$content->lable_seconds = $t->_('seconds');

		$content->next_check = (int)$result->next_check;
		$content->lable_last_state_change = $t->_('Last State Change');
		$content->last_state_change = (int)$result->last_state_change;
		$content->lable_last_notification = $t->_('Last Notification');
		$content->lable_n_a = $na_str;
		$content->last_notification = $last_notification!=0 ? $last_notification : $na_str;
		$content->lable_notifications = $t->_('notification');
		$content->current_notification_number = $result->current_notification_number;
		$lable_flapping_state_change = $t->_('state change');
		$content->percent_state_change_str = '';
		$is_flapping = $result->is_flapping;
		if (!$result->flap_detection_enabled) {
			$content->flap_value = $na_str;
		} else {
			$content->flap_value = $is_flapping ? $yes : $no;
			$content->percent_state_change_str = '('.number_format((int)$result->percent_state_change, 2).'% '.$lable_flapping_state_change.')';
		}
		$content->lable_in_scheduled_dt = $t->_('In Scheduled Downtime?');
		$content->scheduled_downtime_depth = $result->scheduled_downtime_depth ? $yes : $no;
		$last_update_ago_arr = date::timespan(time(), $result->last_update, 'days,hours,minutes,seconds');
		$ago = $t->_('ago');
		$last_update_ago = false;
		$last_update_ago_str = '';
		if (is_array($last_update_ago_arr) && !empty($last_update_ago_arr)) {
			foreach ($last_update_ago_arr as $key => $val) {
				$last_update_ago[] = $val.substr($key, 0, 1);
			}
			$last_update_ago_str = '( '.implode(' ', $last_update_ago) . ' ' . $ago . ')';
		}
		$content->last_update_ago = $last_update_ago_str !='' ? $last_update_ago_str : $na_str;
		$content->lable_active_checks = $t->_('Active Checks');
		$content->lable_passive_checks = $t->_('Passive Checks');
		$content->lable_obsessing = $t->_('Obsessing');
		$content->lable_notifications = $t->_('Notifications');
		$content->lable_event_handler = $t->_('Event Handler');
		$content->lable_flap_detection = $t->_('Flap Detection');
		$str_enabled = $t->_('ENABLED');
		$str_disabled = $t->_('DISABLED');
		$content->active_checks_enabled = $result->active_checks_enabled ? $str_enabled : $str_disabled;
		$content->passive_checks_enabled = $result->passive_checks_enabled ? $str_enabled : $str_disabled;
		$content->obsessing = $obsessing ? $str_enabled : $str_disabled;
		$content->notifications_enabled = $result->notifications_enabled ? $str_enabled : $str_disabled;
		$content->event_handler_enabled = $result->event_handler_enabled ? $str_enabled : $str_disabled;
		$content->flap_detection_enabled = $result->flap_detection_enabled ? $str_enabled : $str_disabled;

		# @@@FIXME Add commands and translations for servcies, below only hosts
		# @@@FIXME Add different icons depending on values below

		# check if nagios is running, will affect wich template to use
		$status = ORM::factory('program_status')->find_all();
		if (!$status->current()->is_running) {
			$this->template->content->commands = $this->add_view('extinfo/not_running');
			$this->template->content->commands->info_message = $t->_('It appears as though Nagios is not running, so commands are temporarily unavailable...');
			$this->template->content->commands->info_message_link = sprintf($t->_('Click %s to view Nagios process information'), html::anchor('extinfo/show_process_info', html::specialchars($t->_('here'))));
			return;
		} else {
			$this->template->content->commands = $this->add_view('extinfo/commands');
		}

		$commands = $this->template->content->commands;
		if ($type == 'host') {
			$commands->lable_command_title = $t->_('Host Commands');
		} else {
			$commands->lable_command_title = $t->_('Service Commands');
		}

		$commands->lable_host_map = $t->_('Locate Host On Map');
		$commands->type = $type;
		$commands->host = $host;

		if ($result->active_checks_enabled ) {
			$commands->lable_active_checks = $type == 'host' ? $t->_('Disable Active Checks Of This Host') : $t->_('Disable Active Checks Of This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_DISABLE_HOST_CHECK : Cmd_Controller::CMD_DISABLE_SVC_CHECK;
			$commands->link_active_checks = $this->command_link($cmd, $host, $service, $commands->lable_active_checks);
			$force_reschedule = 'true';
		} else {
			$commands->lable_active_checks = $type == 'host' ? $t->_('Enable Active Checks Of This Host') : $t->_('Enable Active Checks Of This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_ENABLE_HOST_CHECK : Cmd_Controller::CMD_ENABLE_SVC_CHECK;
			$commands->link_active_checks = $this->command_link($cmd, $host, $service, $commands->lable_active_checks);
			$force_reschedule = 'false';
		}

		$commands->lable_reschedule_check = $type == 'host' ? $t->_('Re-schedule Next Host Check') : $t->_('Re-schedule Next Service Check');
		$commands->lable_link_reschedule_check = $type == 'host' ? $t->_('Re-schedule the next check of this host') : $t->_('Re-schedule the next check of this service');
		$cmd = $type == 'host' ? Cmd_Controller::CMD_SCHEDULE_HOST_CHECK : Cmd_Controller::CMD_SCHEDULE_SVC_CHECK;
		$commands->link_reschedule_check = $this->command_link($cmd, $host, $service, $commands->lable_link_reschedule_check);

		if ($result->passive_checks_enabled) {
			$commands->lable_submit_passive_checks = $type == 'host' ? $t->_('Submit Passive Check Result For This Host') : $t->_('Submit Passive Check Result For This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_PROCESS_HOST_CHECK_RESULT : Cmd_Controller::CMD_PROCESS_SERVICE_CHECK_RESULT;
			$commands->link_submit_passive_check = $this->command_link($cmd, $host, $service, $commands->lable_submit_passive_checks);

			$commands->lable_stop_start_passive_checks = $type == 'host' ? $t->_('Stop Accepting Passive Checks For This Host') : $t->_('Stop Accepting Passive Checks For This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_DISABLE_PASSIVE_HOST_CHECKS : Cmd_Controller::CMD_DISABLE_PASSIVE_SVC_CHECKS;
			$commands->link_stop_start_passive_check = $this->command_link($cmd, $host, $service, $commands->lable_stop_start_passive_checks);
		} else {
			$commands->lable_stop_start_passive_checks = $type == 'host' ? $t->_('Start Accepting Passive Checks For This Host') : $t->_('Start Accepting Passive Checks For This Host');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_ENABLE_PASSIVE_HOST_CHECKS : Cmd_Controller::CMD_ENABLE_PASSIVE_SVC_CHECKS;
			$commands->link_stop_start_passive_check = $this->command_link($cmd, $host, $service, $commands->lable_stop_start_passive_checks);
		}
		if ($obsessing) {
			$commands->lable_obsessing = $type == 'host' ? $t->_('Stop Obsessing Over This Host') : $t->_('Stop Obsessing Over This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_STOP_OBSESSING_OVER_HOST : Cmd_Controller::CMD_STOP_OBSESSING_OVER_SVC;
			$commands->link_obsessing = $this->command_link($cmd, $host, $service, $commands->lable_obsessing);
		} else {
			$commands->lable_obsessing = $type == 'host' ? $t->_('Start Obsessing Over This Host') : $t->_('Start Obsessing Over This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_START_OBSESSING_OVER_HOST : Cmd_Controller::CMD_START_OBSESSING_OVER_SVC;
			$commands->link_obsessing = $this->command_link($cmd, $host, $service, $commands->lable_obsessing);
		}

		# acknowledgements
		$commands->show_ackinfo = false;
		if ($type == 'host') {
			if ($result->current_state == nagstat::HOST_DOWN || $result->current_state == nagstat::HOST_UNREACHABLE) {
				$commands->show_ackinfo = true;
				# show acknowledge info
				if (!$result->problem_has_been_acknowledged) {
					$commands->lable_acknowledge_problem = $t->_('Acknowledge This Host Problem');
					$commands->link_acknowledge_problem = $this->command_link(Cmd_Controller::CMD_ACKNOWLEDGE_HOST_PROBLEM,
						$host, false, $commands->lable_acknowledge_problem);
				} else {
					$commands->lable_acknowledge_problem = $t->_('Remove Problem Acknowledgement');
					$commands->link_acknowledge_problem = $this->command_link(Cmd_Controller::CMD_REMOVE_HOST_ACKNOWLEDGEMENT,
						$host, false, $commands->lable_acknowledge_problem);
				}
			}
		} else {
			if (($result->current_state == nagstat::SERVICE_WARNING || $result->current_state == nagstat::SERVICE_UNKNOWN || $result->current_state == nagstat::SERVICE_CRITICAL) && $result->state_type) {
				$commands->show_ackinfo = true;
				# show acknowledge info
				if (!$result->problem_has_been_acknowledged) {
					$commands->lable_acknowledge_problem = $t->_('Acknowledge This Service Problem');
					$commands->link_acknowledge_problem = $this->command_link(Cmd_Controller::CMD_ACKNOWLEDGE_SVC_PROBLEM,
						$host, $service, $commands->lable_acknowledge_problem);
				} else {
					$commands->lable_acknowledge_problem = $t->_('Remove Problem Acknowledgement');
					$commands->link_acknowledge_problem = $this->command_link(Cmd_Controller::CMD_REMOVE_SVC_ACKNOWLEDGEMENT,
						$host, $service, $commands->lable_acknowledge_problem);
				}
			}

		}

		# notifications
		if ($result->notifications_enabled) {
			$commands->lable_notifications = $type == 'host' ? $t->_('Disable Notifications For This Host') : $t->_('Disable Notifications For This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_DISABLE_HOST_NOTIFICATIONS : Cmd_Controller::CMD_DISABLE_SVC_NOTIFICATIONS;
			$commands->link_notifications = $this->command_link($cmd, $host, $service, $commands->lable_notifications);
		} else {
			$commands->lable_notifications = $type == 'host' ? $t->_('Enable Notifications For This Host') : $t->_('Enable Notifications For This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_ENABLE_HOST_NOTIFICATIONS : Cmd_Controller::CMD_ENABLE_SVC_NOTIFICATIONS;
			$commands->link_notifications = $this->command_link($cmd, $host, $service, $commands->lable_notifications);
		}
		$commands->lable_custom_notifications = $t->_('Send Custom Notification');
		$commands->lable_link_custom_notifications = $type == 'host' ? $t->_('Send custom host notification') : $t->_('Send custom service notification');
		$cmd = $type == 'host' ? Cmd_Controller::CMD_SEND_CUSTOM_HOST_NOTIFICATION : Cmd_Controller::CMD_SEND_CUSTOM_SVC_NOTIFICATION;
		$commands->link_custom_notifications = $this->command_link($cmd, $host, $service, $commands->lable_link_custom_notifications);

		$commands->show_delay = false;
		if ($type == 'host') {
			if ($result->current_state == nagstat::HOST_UP) {
				$commands->show_delay = true;
				$commands->lable_delay_notification = $t->_('Delay Next Host Notification');
				$commands->link_delay_notifications = $this->command_link(Cmd_Controller::CMD_DELAY_HOST_NOTIFICATION,
				$host, false, $commands->lable_delay_notification);
			}
		} else {
			if ($result->current_state == nagstat::SERVICE_OK) {
				$commands->show_delay = true;
				$commands->lable_delay_notification = $t->_('Delay Next Service Notification');
				$commands->link_delay_notifications = $this->command_link(Cmd_Controller::CMD_DELAY_SVC_NOTIFICATION,
				$host, $service, $commands->lable_delay_notification);
			}
		}
		$commands->lable_schedule_dt = $type == 'host' ? $t->_('Schedule Downtime For This Host') : $t->_('Schedule Downtime For This Service');
		$cmd = $type == 'host' ?  Cmd_Controller::CMD_SCHEDULE_HOST_DOWNTIME : Cmd_Controller::CMD_SCHEDULE_SVC_DOWNTIME;
		$commands->link_schedule_dt = $this->command_link($cmd, $host, $service, $commands->lable_schedule_dt);

		if ($type == 'host') {
			$commands->lable_disable_service_notifications_on_host = $t->_('Disable Notifications For All Services On This Host');
			$commands->link_disable_service_notifications_on_host = $this->command_link(Cmd_Controller::CMD_DISABLE_HOST_SVC_NOTIFICATIONS,
				$host, $service, $commands->lable_disable_service_notifications_on_host);

			$commands->lable_enable_service_notifications_on_host = $t->_('Enable Notifications For All Services On This Host');
			$commands->link_enable_service_notifications_on_host = $this->command_link(Cmd_Controller::CMD_ENABLE_HOST_SVC_NOTIFICATIONS,
				$host, $service, $commands->lable_enable_service_notifications_on_host);

			$commands->lable_check_all_services = $t->_('Schedule A Check Of All Services On This Host');
			$commands->link_check_all_services = $this->command_link(Cmd_Controller::CMD_SCHEDULE_HOST_SVC_CHECKS,
				$host, $service, $commands->lable_check_all_services);

			$commands->lable_disable_servicechecks = $t->_('Disable Checks Of All Services On This Host');
			$commands->link_disable_servicechecks = $this->command_link(Cmd_Controller::CMD_DISABLE_HOST_SVC_CHECKS,
				$host, $service, $commands->lable_disable_servicechecks);

			$commands->lable_enable_servicechecks = $t->_('Enable Checks Of All Services On This Host');
			$commands->link_enable_servicechecks = $this->command_link(Cmd_Controller::CMD_ENABLE_HOST_SVC_CHECKS,
				$host, $service, $commands->lable_enable_servicechecks);
		}


		if ($result->event_handler_enabled) {
			$commands->lable_enable_disable_event_handler = $type == 'host' ? $t->_('Disable Event Handler For This Host') : $t->_('Disable Event Handler For This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_DISABLE_HOST_EVENT_HANDLER : Cmd_Controller::CMD_DISABLE_SVC_EVENT_HANDLER;
			$commands->link_enable_disable_event_handler = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_event_handler);
		} else {
			$commands->lable_enable_disable_event_handler = $type == 'host' ? $t->_('Enable Event Handler For This Host') : $t->_('Enable Event Handler For This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_ENABLE_HOST_EVENT_HANDLER : Cmd_Controller::CMD_DISABLE_SVC_EVENT_HANDLER;
			$commands->link_enable_disable_event_handler = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_event_handler);
		}

		if ($result->flap_detection_enabled) {
			$commands->lable_enable_disable_flapdetection = $type == 'host' ? $t->_('Disable Flap Detection For This Host') : $t->_('Disable Flap Detection For This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_DISABLE_HOST_FLAP_DETECTION : Cmd_Controller::CMD_DISABLE_SVC_FLAP_DETECTION;
			$commands->link_enable_disable_flapdetection = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_flapdetection);
		} else {
			$commands->lable_enable_disable_flapdetection = $type == 'host' ? $t->_('Enable Flap Detection For This Host') : $t->_('Enable Flap Detection For This Service');
			$cmd = $type == 'host' ? Cmd_Controller::CMD_ENABLE_HOST_FLAP_DETECTION : Cmd_Controller::CMD_ENABLE_SVC_FLAP_DETECTION;
			$commands->link_enable_disable_flapdetection = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_flapdetection);
		}

	}

	/**
	*	@name	command_link
	*	@desc	Private helper function to save us from typing
	* 			the links to the cmd controller
	*
	*/
	private function command_link($command_type=false, $host=false, $service=false, $lable='', $method='command')
	{
		$host = trim($host);

		# encode service string if set
		$service = $service!==false ? link::encode($service) : false;

		$lable = trim($lable);
		$method = trim($method);
		if ($command_type===false || empty($host) || empty($lable) || empty($method)) {
			return false;
		}
		$link =	html::anchor('cmd/'.$method.'/'.$command_type.'/'.$host.'/'.$service,
			html::specialchars($lable));
		return $link;
	}

	/**
	*	@name show_process_info
	*	@desc
	*
	*/
	public function show_process_info()
	{
		$auth = new Nagios_auth_Model();
		if (!$auth->authorized_for_system_information) {
			url::redirect('extinfo/unauthorized/0');
		}
		$this->template->content = $this->add_view('extinfo/process_info');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		# instance_name = NULL
		# instance_id = NULL

		# save us some typing
		$content = $this->template->content;
		$t = $this->translate;

		# Lables to translate
		$na_str = $t->_('N/A');
		$yes = $t->_('YES');
		$no = $t->_('NO');
		$content->lable_program_version = $t->_('Program Version');
		$content->lable_program_start_time = $t->_('Program Start Time');
		$content->lable_total_run_time = $t->_('Total Running Time');
		$content->lable_last_external_cmd_check = $t->_('Last External Command Check');
		$content->lable_last_logfile_rotation = $t->_('Last Log File Rotation');
		$content->lable_pid = strstr(__FILE__, 'op5') ? $t->_('Monitor PID') : $t->_('Nagios PID');
		$content->lable_notifications_enabled = $t->_('Notifications Enabled?');
		$content->lable_service_checks = $t->_('Service Checks Being Executed?');
		$content->lable_service_checks_passive = $t->_('Passive Service Checks Being Accepted?');
		$content->lable_host_checks = $t->_('Host Checks Being Executed?');
		$content->lable_host_checks_passive = $t->_('Passive Host Checks Being Accepted?');
		$content->lable_event_handlers = $t->_('Event Handlers Enabled?');
		$content->lable_obsess_services = $t->_('Obsessing Over Services?');
		$content->lable_obsess_hosts = $t->_('Obsessing Over Hosts?');
		$content->lable_flap_enabled = $t->_('Flap Detection Enabled?');
		$content->lable_performance_data = $t->_('Performance Data Being Processed?');

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
		$status_res = ORM::factory('program_status')->find_all();

		# @@@FIXME how do we figure the program version out?
		$this->template->content->program_version = $na_str;

		if ($status_res->count() > 0) {
			$status = $status_res->current();
			$content->program_start = date($date_format_str, $status->program_start);
			$run_time_arr = date::timespan(time(), $status->program_start, 'days,hours,minutes,seconds');
			if (is_array($run_time_arr) && !empty($run_time_arr)) {
				foreach ($run_time_arr as $key => $val) {
					$run_time[] = $val.substr($key, 0, 1);
				}
				$run_time_str = implode(' ', $run_time);
			}
			$content->run_time = $run_time_str; # @@@FIXME - NOT translated (d, h, m, s)

			$content->last_command_check = $status->last_command_check;
			$content->last_log_rotation = $status->last_log_rotation;
			$content->nagios_pid = $status->pid;
			$content->notifications_enabled = $status->notifications_enabled;
			$content->execute_service_checks = $status->active_service_checks_enabled;
			$content->accept_passive_service_checks = $status->passive_service_checks_enabled;
			$content->execute_host_checks = $status->active_host_checks_enabled;
			$content->accept_passive_host_checks = $status->passive_service_checks_enabled;
			$content->enable_event_handlers = $status->event_handlers_enabled;
			$content->obsess_over_services = $status->obsess_over_services;
			$content->obsess_over_hosts = $status->obsess_over_hosts;
			$content->flap_detection_enabled = $status->flap_detection_enabled;
			$content->enable_failure_prediction = $status->failure_prediction_enabled;
			$content->process_performance_data = $status->process_performance_data;
		} else {
			# nothing found in program_status
			# @@@FIXME probably an error - handle this someway
			# fetch what we can find from nagios.cfg for now

			$content->notifications_enabled = isset($nagios_config['enable_notifications']) ? $nagios_config['enable_notifications'] : false;
			$content->flap_detection_enabled = isset($nagios_config['enable_flap_detection']) ? $nagios_config['enable_flap_detection'] : false;
			$content->enable_event_handlers = isset($nagios_config['enable_event_handlers']) ? $nagios_config['enable_event_handlers'] : false;
			$content->execute_service_checks = isset($nagios_config['execute_service_checks']) ? $nagios_config['execute_service_checks'] : false;
			$content->accept_passive_service_checks = isset($nagios_config['accept_passive_service_checks']) ? $nagios_config['accept_passive_service_checks'] : false;
			$content->obsess_over_services = isset($nagios_config['obsess_over_services']) ? $nagios_config['obsess_over_services'] : false;
			$content->execute_host_checks = isset($nagios_config['execute_host_checks']) ? $nagios_config['execute_host_checks'] : false;
			$content->accept_passive_host_checks = isset($nagios_config['accept_passive_host_checks']) ? $nagios_config['accept_passive_host_checks'] : false;
			$content->obsess_over_hosts = isset($nagios_config['obsess_over_hosts']) ? $nagios_config['obsess_over_hosts'] : false;
			$content->process_performance_data = isset($nagios_config['process_performance_data']) ? $nagios_config['process_performance_data'] : false;

			# set the following values to some default since we can't seem to determine
			# the correct value at the moment
			$content->enable_failure_prediction = false;
			$content->program_start = $na_str;
			$content->run_time = $na_str;
			$run_time = false;
			$content->last_command_check = $na_str;
			$content->last_log_rotation = $na_str;

			# are we runnig monitor or nagios?
			$process_name = strstr(__FILE__, 'op5') ? 'monitor' : 'nagios';
			$content->nagios_pid = exec("pidof ".$process_name."|awk {'print $1'}");
		}

		$content->notifications_class = $content->notifications_enabled ? 'notificationsENABLED' : 'notificationsDISABLED';
		$content->notifications_str = $content->notifications_enabled ? $yes : $no;
		$content->servicechecks_class = $content->execute_service_checks ? 'checksENABLED' : 'checksDISABLED';
		$content->servicechecks_str = $content->execute_service_checks ? $yes : $no;
		$content->passive_servicechecks_class = $content->accept_passive_service_checks ? 'checksENABLED' : 'checksDISABLED';
		$content->passive_servicechecks_str = $content->accept_passive_service_checks ? $yes : $no;
		$content->hostchecks_class = $content->execute_host_checks ? 'checksENABLED' : 'checksDISABLED';
		$content->hostchecks_str = $content->execute_host_checks ? $yes : $no;
		$content->passive_hostchecks_class = $content->accept_passive_host_checks ? 'checksENABLED' : 'checksDISABLED';
		$content->passive_hostchecks_str = $content->accept_passive_host_checks ? $yes : $no;
		$content->eventhandler_str = $content->enable_event_handlers ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->obsess_services_str = $content->obsess_over_services ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->obsess_hosts_str = $content->obsess_over_hosts ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->flap_detection_str = $content->flap_detection_enabled ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->performance_data_str = $content->process_performance_data ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
	}

	/**
	*	@name 	unauthorized
	*	@desc	Display message to user when they lack proper
	* 			credentials to view info on an object
	*/
	public function unauthorized($type='host')
	{
		$type = trim(strtolower($type));
		$this->template->content = $this->add_view('extinfo/unauthorized');

		$this->template->content->error_description = $this->translate->_('If you believe this is an error, check the HTTP server authentication requirements for accessing this page
			and check the authorization options in your CGI configuration file.');
		switch ($type) {
			case 'host':
				$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to view information for this host...');
				break;
			case 'service':
				$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to view information for this service...');
				break;
			default:
				$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to view process information...');
		}
	}

}