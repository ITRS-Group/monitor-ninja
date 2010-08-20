<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Extinfo controller
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
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
	 * Default controller method
	 * Redirects to show_process_info() which
	 * is the equivalent of calling extinfo.cgi?type=0
	 */
	public function index()
	{
		url::redirect(Router::$controller.'/show_process_info');
	}

	/**
	 *
	 *
	 */
	public function details($type='host', $host=false, $service=false)
	{
		$type = urldecode($this->input->get('type', $type));
		$host = urldecode($this->input->get('host', $host));
		$service = urldecode($this->input->get('service', $service));

		$this->template->title = 'Monitoring » Extinfo';

		# load current status for host/service status totals
		$this->current = new Current_status_Model();

		$host = trim($host);
		$type = strtolower($type);
		if (empty($host)) {
			return false;
		}

		# is user authenticated to view details on current object?
		$auth = new Nagios_auth_Model();
		$is_authenticated = true;
		switch ($type) {
			case 'host':
				$auth_hosts = $auth->get_authorized_hosts();
				if (!array_key_exists($host, $auth->hosts_r)) {
					# user not allowed to view info on selected host
					$is_authenticated = false;
				}
				break;
			case 'service':
				$auth_services = $auth->get_authorized_services();
				if (!array_key_exists($host.';'.$service, $auth->services_r)) {
					# user not allowed to view info on selected service
					$is_authenticated = false;
				}
				break;
			case 'servicegroup': case 'hostgroup':
				return $this->group_details($type, $host);
				break;
		}
		if ($is_authenticated === false) {
			url::redirect('extinfo/unauthorized/'.$type);
		}

		$this->template->content = $this->add_view('extinfo/index');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->js_strings .= "var _pnp_web_path = '".Kohana::config('config.pnp4nagios_path')."';\n";
		$this->template->js_strings = $this->js_strings;
		$this->xtra_js[] = $this->add_path('extinfo/js/extinfo.js');
		$this->template->js_header->js = $this->xtra_js;

		# save us some typing
		$content = $this->template->content;
		$t = $this->translate;

		$result_data = Host_Model::object_status($host, $service);
		$result = $result_data->current();
		$host_link = false;
		$yes = $t->_('YES');
		$no = $t->_('NO');
		$content->label_notifies_to = $t->_('Notifies to');
		$content->label_contactgroup = $t->_('Contactgroup');
		$content->label_no_contactgroup = $t->_('No Contactgroup');
		$content->label_contacts = $t->_('Contacts');
		$content->label_no_contacts = $t->_('No Contacts');
		$content->lable_contact_name = $t->_('Contact Name');
		$content->lable_contact_alias = $t->_('Alias');
		$content->lable_contact_email = $t->_('Email');
		$content->lable_click_to_view = $t->_('Click to view contacts');
		$contactgroups_res = Contactgroup_Model::get_contactgroup($host, $service);
		$contacts = false;
		$contactgroups = false;
		if ($contactgroups_res !== false) {
			foreach ($contactgroups_res as $c_group) {
				$contactgroups[] = $c_group->contactgroup_name;
				$c_members = Contactgroup_Model::get_members($c_group->contactgroup_name);
				if ($c_members !== false) {
					foreach ($c_members as $member) {
						$contacts[$c_group->contactgroup_name][] = $member;
					}
				}
			}
		}
		$content->contactgroups = $contactgroups;
		$content->contacts = $contacts;
		$is_pending = false;
		$back_link = false;
		$content->parents = false;

		if ($type == 'host') {
			$group_info = Group_Model::get_groups_for_object($type, $result->id);
			$content->title = $this->translate->_('Host State Information');
			$content->no_group_lable = $t->_('No hostgroups');
			$check_compare_value = Current_status_Model::HOST_CHECK_ACTIVE;
			$last_notification = $result->last_host_notification;
			$content->lable_next_scheduled_check = $t->_('Next Scheduled Active Check');
			$content->lable_flapping = $t->_('Is This Host Flapping?');
			$obsessing = $result->obsess_over_host;
			$content->notes = $result->notes !='' ? nagstat::process_macros($result->notes, $result) : false;

			# check for parents
			$host_obj = new Host_Model();
			$parents = $host_obj->get_parents($host);
			$content->label_parents = $t->_('Parents');
			if (count($parents)) {
				$content->parents = $parents;
			}

			$back_link = '/extinfo/details/host/'.urlencode($host);
			if ($result->current_state == Current_status_Model::HOST_PENDING ) {
				$is_pending = true;
				$message_str = $t->_('This host has not yet been checked, so status information is not available.');
			}
		} else {
			$group_info = Group_Model::get_groups_for_object($type, $result->service_id);
			$content->title = $this->translate->_('Service State Information');
			$content->no_group_lable = $t->_('No servicegroups');
			$content->lable_next_scheduled_check = $t->_('Next Scheduled Check');
			$host_link = html::anchor('extinfo/details/host/'.urlencode($host), html::specialchars($host));
			$back_link = '/extinfo/details/service/'.urlencode($host).'?service='.urlencode($service);
			$check_compare_value = Current_status_Model::SERVICE_CHECK_ACTIVE;
			$last_notification = $result->last_notification;
			$content->lable_flapping = $t->_('Is This Service Flapping?');
			$obsessing = $result->obsess_over_service;
			$content->notes = $result->service_notes !='' ? nagstat::process_macros($result->service_notes, $result) : false;
			if ($result->current_state == Current_status_Model::SERVICE_PENDING ) {
				$is_pending = true;
				$message_str = $t->_('This service has not yet been checked, so status information is not available.');
			}
		}

		$content->label_notes = $t->_('Notes');
		$content->notes_url = $result->notes_url !='' ? nagstat::process_macros($result->notes_url, $result) : false;
		$content->label_notes_url = $t->_('Extra Notes');
		$content->action_url = $result->action_url !='' ? nagstat::process_macros($result->action_url, $result) : false;
		$content->label_action_url = $t->_('Extra Actions');

		$xaction = array();
		if (nacoma::link()===true) {
			$label = $t->_('Configure');
			$url = url::site() . "configuration/configure/$type/" . urlencode($host);
			if ($type === 'service') {
				$url .= '?service=' . urlencode($service);
				$alt = $t->_('Configure this service using Nacoma');
			} else {
				$alt = $t->_('Configure this host using Nacoma');
			}

			$xaction[$label] =
				array('url' => $url,
					  'img' => url::base(false) . $this->img_path('icons/16x16/nacoma.png'),
					  'alt' => $alt
					);
		}

		if (Kohana::config('config.pnp4nagios_path') !== false && pnp::has_graph($host, urlencode($service))) {
			$label = $t->_('Show performance graph');
			$url = url::site() . 'pnp/?host=' . urlencode($host);
			if ($type ===  'service') {
				$url .= '&srv=' . urlencode($service);
			} else {
				$url .= '&srv=_HOST_';
			}
			$xaction[$label] = array
				('url' => $url,
				 'img' => url::base(false) . $this->img_path('icons/16x16/pnp.png'),
				 'alt' => $label,
				 'img_class' => 'pnp_graph_icon'
				 );
		}
		$content->extra_action_links = $xaction;

		$groups = false;
		foreach ($group_info as $group_row) {
			$groups[] = html::anchor(sprintf("status/%sgroup/%s", $type, urlencode($group_row->{$type.'group_name'})),
				html::specialchars($group_row->{$type.'group_name'}));
		}

		if ($is_pending) {
			$content->pending_msg = $message_str;
		}
		$content->lable_type = $type == 'host' ? $t->_('Host') : $t->_('Service');
		$content->type = $type;
		$content->back_link = $back_link;
		$content->date_format_str = 'Y-m-d H:i:s';
		$content->host_link = $host_link;
		$content->lable_member_of = $t->_('Member of');
		$content->lable_for = $t->_('for');
		$content->lable_on_host = $t->_('On Host');
		$content->main_object = $type=='host' ? $host : $service;
		$content->host = $host;
		$content->lable_current_status = $t->_('Current Status');
		$content->lable_status_information = $t->_('Status Information');
		$content->current_status_str = $this->current->status_text($result->current_state, $type);
		$content->duration = $result->duration;
		$content->groups = $groups;
		$content->host_address = $result->address;
		$content->icon_image = $result->icon_image;
		$content->icon_image_alt = $result->icon_image_alt;
		$content->status_info = $result->output.'<br />'.str_replace('\n', '<br />', nl2br($result->long_output));
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
		$content->last_notification = $last_notification!=0 ? date(nagstat::date_format(), $last_notification) : $na_str;
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
			$last_update_ago_str = '('.implode(' ', $last_update_ago) . ' ' . $ago . ')';
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

		# check if nagios is running, will affect wich template to use
		$status = Program_status_Model::get_all();
		$is_running = empty($status) || count($status)==0 ? false : $status->current()->is_running;
		if (empty($status) || !$is_running) {
			$this->template->content->commands = $this->add_view('extinfo/not_running');
			$this->template->content->commands->info_message = sprintf($t->_('It appears as though %s is not running, so commands are temporarily unavailable...'), Kohana::config('config.product_name'));
			$this->template->content->commands->info_message_extra = sprintf($t->_('Click %s to view %s process information'), html::anchor('extinfo/show_process_info', html::specialchars($t->_('here'))), Kohana::config('config.product_name'));
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
			$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_CHECK') : nagioscmd::command_id('DISABLE_SVC_CHECK');
			$commands->link_active_checks = $this->command_link($cmd, $host, $service, $commands->lable_active_checks);
			$force_reschedule = 'true';
		} else {
			$commands->lable_active_checks = $type == 'host' ? $t->_('Enable Active Checks Of This Host') : $t->_('Enable Active Checks Of This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_CHECK') : nagioscmd::command_id('ENABLE_SVC_CHECK');
			$commands->link_active_checks = $this->command_link($cmd, $host, $service, $commands->lable_active_checks);
			$force_reschedule = 'false';
		}

		$commands->lable_reschedule_check = $type == 'host' ? $t->_('Re-schedule Next Host Check') : $t->_('Re-schedule Next Service Check');
		$commands->lable_link_reschedule_check = $type == 'host' ? $t->_('Re-schedule the next check of this host') : $t->_('Re-schedule the next check of this service');
		$cmd = $type == 'host' ? nagioscmd::command_id('SCHEDULE_HOST_CHECK') : nagioscmd::command_id('SCHEDULE_SVC_CHECK');
		$commands->link_reschedule_check = $this->command_link($cmd, $host, $service, $commands->lable_link_reschedule_check);

		if ($result->passive_checks_enabled) {
			$commands->lable_submit_passive_checks = $type == 'host' ? $t->_('Submit Passive Check Result For This Host') : $t->_('Submit Passive Check Result For This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('PROCESS_HOST_CHECK_RESULT') : nagioscmd::command_id('PROCESS_SERVICE_CHECK_RESULT');
			$commands->link_submit_passive_check = $this->command_link($cmd, $host, $service, $commands->lable_submit_passive_checks);

			$commands->lable_stop_start_passive_checks = $type == 'host' ? $t->_('Stop Accepting Passive Checks For This Host') : $t->_('Stop Accepting Passive Checks For This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_PASSIVE_HOST_CHECKS') : nagioscmd::command_id('DISABLE_PASSIVE_SVC_CHECKS');
			$commands->link_stop_start_passive_check = $this->command_link($cmd, $host, $service, $commands->lable_stop_start_passive_checks);
		} else {
			$commands->lable_stop_start_passive_checks = $type == 'host' ? $t->_('Start Accepting Passive Checks For This Host') : $t->_('Start Accepting Passive Checks For This Host');
			$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_PASSIVE_HOST_CHECKS') : nagioscmd::command_id('ENABLE_PASSIVE_SVC_CHECKS');
			$commands->link_stop_start_passive_check = $this->command_link($cmd, $host, $service, $commands->lable_stop_start_passive_checks);
		}
		if ($obsessing) {
			$commands->lable_obsessing = $type == 'host' ? $t->_('Stop Obsessing Over This Host') : $t->_('Stop Obsessing Over This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('STOP_OBSESSING_OVER_HOST') : nagioscmd::command_id('STOP_OBSESSING_OVER_SVC');
			$commands->link_obsessing = $this->command_link($cmd, $host, $service, $commands->lable_obsessing);
		} else {
			$commands->lable_obsessing = $type == 'host' ? $t->_('Start Obsessing Over This Host') : $t->_('Start Obsessing Over This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('START_OBSESSING_OVER_HOST') : nagioscmd::command_id('START_OBSESSING_OVER_SVC');
			$commands->link_obsessing = $this->command_link($cmd, $host, $service, $commands->lable_obsessing);
		}

		# acknowledgements
		$commands->show_ackinfo = false;
		if ($type == 'host') {
			if ($result->current_state == Current_status_Model::HOST_DOWN || $result->current_state == Current_status_Model::HOST_UNREACHABLE) {
				$commands->show_ackinfo = true;
				# show acknowledge info
				if (!$result->problem_has_been_acknowledged) {
					$commands->lable_acknowledge_problem = $t->_('Acknowledge This Host Problem');
					$commands->link_acknowledge_problem = $this->command_link(nagioscmd::command_id('ACKNOWLEDGE_HOST_PROBLEM'),
						$host, false, $commands->lable_acknowledge_problem);
				} else {
					$commands->lable_acknowledge_problem = $t->_('Remove Problem Acknowledgement');
					$commands->link_acknowledge_problem = $this->command_link(nagioscmd::command_id('REMOVE_HOST_ACKNOWLEDGEMENT'),
						$host, false, $commands->lable_acknowledge_problem);
				}
			}
		} else {
			if (($result->current_state == Current_status_Model::SERVICE_WARNING || $result->current_state == Current_status_Model::SERVICE_UNKNOWN || $result->current_state == Current_status_Model::SERVICE_CRITICAL) && $result->state_type) {
				$commands->show_ackinfo = true;
				# show acknowledge info
				if (!$result->problem_has_been_acknowledged) {
					$commands->lable_acknowledge_problem = $t->_('Acknowledge This Service Problem');
					$commands->link_acknowledge_problem = $this->command_link(nagioscmd::command_id('ACKNOWLEDGE_SVC_PROBLEM'),
						$host, $service, $commands->lable_acknowledge_problem);
				} else {
					$commands->lable_acknowledge_problem = $t->_('Remove Problem Acknowledgement');
					$commands->link_acknowledge_problem = $this->command_link(nagioscmd::command_id('REMOVE_SVC_ACKNOWLEDGEMENT'),
						$host, $service, $commands->lable_acknowledge_problem);
				}
			}

		}

		# notifications
		if ($result->notifications_enabled) {
			$commands->lable_notifications = $type == 'host' ? $t->_('Disable Notifications For This Host') : $t->_('Disable Notifications For This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_NOTIFICATIONS') : nagioscmd::command_id('DISABLE_SVC_NOTIFICATIONS');
			$commands->link_notifications = $this->command_link($cmd, $host, $service, $commands->lable_notifications);
		} else {
			$commands->lable_notifications = $type == 'host' ? $t->_('Enable Notifications For This Host') : $t->_('Enable Notifications For This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_NOTIFICATIONS') : nagioscmd::command_id('ENABLE_SVC_NOTIFICATIONS');
			$commands->link_notifications = $this->command_link($cmd, $host, $service, $commands->lable_notifications);
		}
		$commands->lable_custom_notifications = $t->_('Send Custom Notification');
		$commands->lable_link_custom_notifications = $type == 'host' ? $t->_('Send custom host notification') : $t->_('Send custom service notification');
		$cmd = $type == 'host' ? nagioscmd::command_id('SEND_CUSTOM_HOST_NOTIFICATION') : nagioscmd::command_id('SEND_CUSTOM_SVC_NOTIFICATION');
		$commands->link_custom_notifications = $this->command_link($cmd, $host, $service, $commands->lable_link_custom_notifications);

		$commands->show_delay = false;
		if ($type == 'host') {
			if ($result->current_state != Current_status_Model::HOST_UP) {
				$commands->show_delay = true;
				$commands->lable_delay_notification = $t->_('Delay Next Host Notification');
				$commands->link_delay_notifications = $this->command_link(nagioscmd::command_id('DELAY_HOST_NOTIFICATION'),
				$host, false, $commands->lable_delay_notification);
			}
		} else {
			if ($result->notifications_enabled && $result->current_state != Current_status_Model::SERVICE_OK) {
				$commands->show_delay = true;
				$commands->lable_delay_notification = $t->_('Delay Next Service Notification');
				$commands->link_delay_notifications = $this->command_link(nagioscmd::command_id('DELAY_SVC_NOTIFICATION'),
				$host, $service, $commands->lable_delay_notification);
			}
		}
		$commands->lable_schedule_dt = $type == 'host' ? $t->_('Schedule Downtime For This Host') : $t->_('Schedule Downtime For This Service');
		$cmd = $type == 'host' ?  nagioscmd::command_id('SCHEDULE_HOST_DOWNTIME') : nagioscmd::command_id('SCHEDULE_SVC_DOWNTIME');
		$commands->link_schedule_dt = $this->command_link($cmd, $host, $service, $commands->lable_schedule_dt);

		if ($type == 'host') {
			$commands->lable_disable_service_notifications_on_host = $t->_('Disable Notifications For All Services On This Host');
			$commands->link_disable_service_notifications_on_host = $this->command_link(nagioscmd::command_id('DISABLE_HOST_SVC_NOTIFICATIONS'),
				$host, $service, $commands->lable_disable_service_notifications_on_host);

			$commands->lable_enable_service_notifications_on_host = $t->_('Enable Notifications For All Services On This Host');
			$commands->link_enable_service_notifications_on_host = $this->command_link(nagioscmd::command_id('ENABLE_HOST_SVC_NOTIFICATIONS'),
				$host, $service, $commands->lable_enable_service_notifications_on_host);

			$commands->lable_check_all_services = $t->_('Schedule A Check Of All Services On This Host');
			$commands->link_check_all_services = $this->command_link(nagioscmd::command_id('SCHEDULE_HOST_SVC_CHECKS'),
				$host, $service, $commands->lable_check_all_services);

			$commands->lable_disable_servicechecks = $t->_('Disable Checks Of All Services On This Host');
			$commands->link_disable_servicechecks = $this->command_link(nagioscmd::command_id('DISABLE_HOST_SVC_CHECKS'),
				$host, $service, $commands->lable_disable_servicechecks);

			$commands->lable_enable_servicechecks = $t->_('Enable Checks Of All Services On This Host');
			$commands->link_enable_servicechecks = $this->command_link(nagioscmd::command_id('ENABLE_HOST_SVC_CHECKS'),
				$host, $service, $commands->lable_enable_servicechecks);
		}


		if ($result->event_handler_enabled) {
			$commands->lable_enable_disable_event_handler = $type == 'host' ? $t->_('Disable Event Handler For This Host') : $t->_('Disable Event Handler For This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_EVENT_HANDLER') : nagioscmd::command_id('DISABLE_SVC_EVENT_HANDLER');
			$commands->link_enable_disable_event_handler = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_event_handler);
		} else {
			$commands->lable_enable_disable_event_handler = $type == 'host' ? $t->_('Enable Event Handler For This Host') : $t->_('Enable Event Handler For This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_EVENT_HANDLER') : nagioscmd::command_id('ENABLE_SVC_EVENT_HANDLER');
			$commands->link_enable_disable_event_handler = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_event_handler);
		}

		if ($result->flap_detection_enabled) {
			$commands->lable_enable_disable_flapdetection = $type == 'host' ? $t->_('Disable Flap Detection For This Host') : $t->_('Disable Flap Detection For This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_FLAP_DETECTION') : nagioscmd::command_id('DISABLE_SVC_FLAP_DETECTION');
			$commands->link_enable_disable_flapdetection = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_flapdetection);
		} else {
			$commands->lable_enable_disable_flapdetection = $type == 'host' ? $t->_('Enable Flap Detection For This Host') : $t->_('Enable Flap Detection For This Service');
			$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_FLAP_DETECTION') : nagioscmd::command_id('ENABLE_SVC_FLAP_DETECTION');
			$commands->link_enable_disable_flapdetection = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_flapdetection);
		}

		# create page links
		switch ($type) {
			case 'host':
				$label_host_status_details = $t->_('Status detail');
				$label_host_alert_history = $t->_('Alert history');
				$label_host_trends = $t->_('Trends');
				$label_host_histogram = $t->_('Alert histogram');
				$label_host_avail = $t->_('Availability report');
				$label_host_notifications = $t->_('Notifications');
				$label_view_for = $this->translate->_('for this host');
				$page_links = array(
					 $label_host_status_details => 'status/service/'.$host,
					 $label_host_alert_history => 'showlog/alert_history/'.$host,
					 $label_host_trends => 'trends/host/'.$host,
					 $label_host_histogram => 'histogram/host/'.$host,
					 $label_host_avail => 'reports/generate/?type=avail&host_name[]='.$host,
					 $label_host_notifications => '/notifications/host/'.$host
				);
				break;
			case 'service':
				$label_host_info = $t->_('Information for this Host');
				$label_host_detail = $t->_('Status detail for this Host');
				$label_service_alert_history = $t->_('Alert history');
				$label_service_trends = $t->_('Trends');
				$label_service_histogram = $t->_('Alert histogram');
				$label_service_avail = $t->_('Availability report');
				$label_service_notifications = $t->_('Notifications');
				$label_view_for = $this->translate->_('for this service');
				$page_links = array(
					$label_host_info => 'extinfo/details/host/'.$host,
					$label_host_detail => 'status/service/'.$host,
					$label_service_alert_history => 'showlog/alert_history/'.$host.'?service='.urlencode($service),
					$label_service_trends => 'trends/host/'.$host.'?service='.urlencode($service),
					$label_service_histogram => 'histogram/host/'.$host.'?service='.urlencode($service),
					$label_service_avail => 'reports/generate/?type=avail&service_description[]='.$host.';'.urlencode($service).'&report_type=services',
					$label_service_notifications => '/notifications/host/'.$host.'?service='.urlencode($service)
				);

				break;
		}
		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = $label_view_for;
		}


		# show comments for hosts and services
		if ($type == 'host' || $type == 'service')
			$comments = $this->_comments($host, $service, $type);
	}

	/**
	 * Private helper function to save us from typing
	 * the links to the cmd controller
	 *
	 */
	private function command_link($command_type=false, $host=false, $service=false, $lable='', $method='submit', $force=false)
	{
		$host = trim($host);

		$lable = trim($lable);
		$method = trim($method);
		if ($command_type===false || empty($lable) || empty($method)) {
			return false;
		}
		$lnk = "command/$method?cmd_typ=$command_type";
		# only print extra params when present
		if (!empty($host)) {
			$lnk .= '&host_name=' . urlencode($host);
		}
		if (!empty($service)) {
			$lnk .= '&service=' . urlencode($service);
		}
		if ($force === true) {
			$lnk .= '&force=true';
		}

		return html::anchor($lnk, html::specialchars($lable));
	}

	/**
	 * Show Nagios process info
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

		$this->template->title = $this->translate->_('Monitoring » Process info');

		# save us some typing
		$content = $this->template->content;
		$t = $this->translate;

		# check if nagios is running, will affect wich template to use
		$status = Program_status_Model::get_all();
		$is_running = empty($status) || count($status)==0 ? false : $status->current()->is_running;
		if (!$is_running) {
			$this->template->content->commands = $this->add_view('extinfo/not_running');
			$this->template->content->commands->info_message = sprintf($t->_('It appears as though %s is not running, so commands are temporarily unavailable...'), Kohana::config('config.product_name'));

			# check if nagios_check_command is defined in cgi.cfg
			$cgi_config = System_Model::parse_config_file('/cgi.cfg');
			$nagios_check_command = false;
			if (!empty($cgi_config)) {
				$nagios_check_command = isset($cgi_config['nagios_check_command']) ? $cgi_config['nagios_check_command'] : false;
			}
			$info_message = '';
			if (empty($nagios_check_command)) {
				$info_message = $t->_('Hint: It looks as though you have not defined a command for checking the process state by supplying a value for the <b>nagios_check_command</b> option in the CGI configuration file');
			}
			$this->template->content->commands->info_message_extra = $info_message;
		} else {
			$this->template->content->commands = $this->add_view('extinfo/nagios_commands');
		}

		# instance_name = NULL
		# instance_id = NULL

		$commands = $this->template->content->commands;


		# Lables to translate
		$na_str = $t->_('N/A');
		$yes = $t->_('YES');
		$no = $t->_('NO');
		$content->lable_program_version = $t->_('Program Version');
		$content->lable_program_start_time = $t->_('Program Start Time');
		$content->lable_total_run_time = $t->_('Total Running Time');
		$content->lable_last_external_cmd_check = $t->_('Last External Command Check');
		$content->lable_last_logfile_rotation = $t->_('Last Log File Rotation');
		$content->lable_pid = sprintf($t->_('%s PID'), Kohana::config('config.product_name'));
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

		$date_format_str = nagstat::date_format();

		# fetch program status from program_status_model
		# uses ORM
		$status_res = Program_status_Model::get_all();

		# --------------------------------------
		# Fetch program version from status.log
		# --------------------------------------
		# where is status.log on this system?
		$nagios_config = System_Model::parse_config_file('nagios.cfg');
		$status_file = $nagios_config['status_file'];

		# use grep + awk to find version
		exec("/bin/grep -m1 version= ".$status_file."|/bin/awk -F = {'print $2'}", $version_output, $result);

		# check return values
		if ($result==0 && !empty($version_output)) {
			$version = $version_output[0];
		} else {
			$version = $na_str;
		}

		# assign program version to template
		$this->template->content->program_version = $version;

		if (!empty($status_res) && count($status_res) > 0) {
			$status = $status_res->current();
			$content->program_start = date($date_format_str, $status->program_start);
			$run_time_str = time::to_string(time() - $status->program_start);
			$content->run_time = $run_time_str;

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

		# Assign commands variables
		$commands->title = $t->_('Process Commands');
		$commands->label_shutdown_nagios = sprintf($t->_('Shutdown the %s Process'), Kohana::config('config.product_name'));
		$commands->link_shutdown_nagios = $this->command_link(nagioscmd::command_id('SHUTDOWN_PROCESS'), false, false, $commands->label_shutdown_nagios);
		$commands->label_restart_nagios = sprintf($t->_('Restart the %s Process'), Kohana::config('config.product_name'));
		$commands->link_restart_nagios = $this->command_link(nagioscmd::command_id('RESTART_PROCESS'), false, false, $commands->label_restart_nagios);

		if ($content->notifications_enabled) {
			$commands->label_notifications = $t->_('Disable Notifications');
			$commands->link_notifications = $this->command_link(nagioscmd::command_id('DISABLE_NOTIFICATIONS'), false, false, $commands->label_notifications);
		} else {
			$commands->label_notifications = $t->_('Enable Notifications');
			$commands->link_notifications = $this->command_link(nagioscmd::command_id('ENABLE_NOTIFICATIONS'), false, false, $commands->label_notifications);
		}

		if ($content->execute_service_checks) {
			$commands->label_execute_service_checks = $t->_('Stop Executing Service Checks');
			$commands->link_execute_service_checks = $this->command_link(nagioscmd::command_id('STOP_EXECUTING_SVC_CHECKS'), false, false, $commands->label_execute_service_checks);
		} else {
			$commands->label_execute_service_checks = $t->_('Start Executing Service Checks');
			$commands->link_execute_service_checks = $this->command_link(nagioscmd::command_id('START_EXECUTING_SVC_CHECKS'), false, false, $commands->label_execute_service_checks);
		}

		if ($content->accept_passive_service_checks) {
			$commands->label_passive_service_checks = $t->_('Stop Accepting Passive Service Checks');
			$commands->link_passive_service_checks = $this->command_link(nagioscmd::command_id('STOP_ACCEPTING_PASSIVE_SVC_CHECKS'), false, false, $commands->label_passive_service_checks);
		} else {
			$commands->label_passive_service_checks = $t->_('Start Accepting Passive Service Checks');
			$commands->link_passive_service_checks = $this->command_link(nagioscmd::command_id('START_ACCEPTING_PASSIVE_SVC_CHECKS'), false, false, $commands->label_passive_service_checks);
		}

		if ($content->execute_host_checks) {
			$commands->label_execute_host_checks = $t->_('Stop Executing Host Checks');
			$commands->link_execute_host_checks = $this->command_link(nagioscmd::command_id('STOP_EXECUTING_HOST_CHECKS'), false, false, $commands->label_execute_host_checks);
		} else {
			$commands->label_execute_host_checks = $t->_('Start Executing Host Checks');
			$commands->link_execute_host_checks = $this->command_link(nagioscmd::command_id('START_EXECUTING_HOST_CHECKS'), false, false, $commands->label_execute_host_checks);
		}

		if ($content->accept_passive_host_checks) {
			$commands->label_accept_passive_host_checks = $t->_('Stop Accepting Passive Host Checks');
			$commands->link_accept_passive_host_checks = $this->command_link(nagioscmd::command_id('STOP_ACCEPTING_PASSIVE_HOST_CHECKS'), false, false, $commands->label_accept_passive_host_checks);
		} else {
			$commands->label_accept_passive_host_checks = $t->_('Start Accepting Passive Host Checks');
			$commands->link_accept_passive_host_checks = $this->command_link(nagioscmd::command_id('START_ACCEPTING_PASSIVE_HOST_CHECKS'), false, false, $commands->label_accept_passive_host_checks);
		}

		if ($content->enable_event_handlers) {
			$commands->label_enable_event_handlers = $t->_('Disable Event Handlers');
			$commands->link_enable_event_handlers = $this->command_link(nagioscmd::command_id('DISABLE_EVENT_HANDLERS'), false, false, $commands->label_enable_event_handlers);
		} else {
			$commands->label_enable_event_handlers = $t->_('Enable Event Handlers');
			$commands->link_enable_event_handlers = $this->command_link(nagioscmd::command_id('ENABLE_EVENT_HANDLERS'), false, false, $commands->label_enable_event_handlers);
		}

		if ($content->obsess_over_services) {
			$commands->label_obsess_over_services = $t->_('Stop Obsessing Over Services');
			$commands->link_obsess_over_services = $this->command_link(nagioscmd::command_id('STOP_OBSESSING_OVER_SVC_CHECKS'), false, false, $commands->label_obsess_over_services);
		} else {
			$commands->label_obsess_over_services = $t->_('Start Obsessing Over Services');
			$commands->link_obsess_over_services = $this->command_link(nagioscmd::command_id('START_OBSESSING_OVER_SVC_CHECKS'), false, false, $commands->label_obsess_over_services);
		}

		if ($content->obsess_over_hosts) {
			$commands->label_obsess_over_hosts = $t->_('Stop Obsessing Over Hosts');
			$commands->link_obsess_over_hosts = $this->command_link(nagioscmd::command_id('STOP_OBSESSING_OVER_HOST_CHECKS'), false, false, $commands->label_obsess_over_hosts);
		} else {
			$commands->label_obsess_over_hosts = $t->_('Start Obsessing Over Hosts');
			$commands->link_obsess_over_hosts = $this->command_link(nagioscmd::command_id('START_OBSESSING_OVER_HOST_CHECKS'), false, false, $commands->label_obsess_over_hosts);
		}

		if ($content->flap_detection_enabled) {
			$commands->label_flap_detection_enabled = $t->_('Disable Flap Detection');
			$commands->link_flap_detection_enabled = $this->command_link(nagioscmd::command_id('DISABLE_FLAP_DETECTION'), false, false, $commands->label_flap_detection_enabled);
		} else {
			$commands->label_flap_detection_enabled = $t->_('Enable Flap Detection');
			$commands->link_flap_detection_enabled = $this->command_link(nagioscmd::command_id('ENABLE_FLAP_DETECTION'), false, false, $commands->label_flap_detection_enabled);
		}

		if ($content->process_performance_data) {
			$commands->label_process_performance_data = $t->_('Disable Performance Data');
			$commands->link_process_performance_data = $this->command_link(nagioscmd::command_id('DISABLE_PERFORMANCE_DATA'), false, false, $commands->label_process_performance_data);
		} else {
			$commands->label_process_performance_data = $t->_('Enable Performance Data');
			$commands->link_process_performance_data = $this->command_link(nagioscmd::command_id('ENABLE_PERFORMANCE_DATA'), false, false, $commands->label_process_performance_data);
		}

	}

	/**
	 * Display message to user when they lack proper
	 * credentials to view info on an object
	 */
	public function unauthorized($type='host')
	{
		$type = trim(strtolower($type));
		$this->template->content = $this->add_view('unauthorized');
		$this->template->disable_refresh = true;

		$this->template->content->error_description = $this->translate->_('If you believe this is an error, check the HTTP server authentication requirements for accessing this page and check the authorization options in your CGI configuration file.');
		switch ($type) {
			case 'host':
				$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to view information for this host or it doesn\'t exist...');
				break;
			case 'hostgroup':
				$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to view information for this hostgroup or it doesn\'t exist...');
				break;
			case 'servicegroup':
				$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to view information for this servicegroup or it doesn\'t exist...');
				break;
			case 'service':
				$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to view information for this service or it doesn\'t exist...');
				break;
			default:
				$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to view process information...');
		}
	}

	/**
	*	Display extinfo for host- and servicegroups
	*
	*/
	public function group_details($grouptype='servicegroup', $group=false)
	{
		$grouptype = urldecode($this->input->get('grouptype', $grouptype));
		$group = urldecode($this->input->get('group', $group));
		$t = $this->translate;
		if (empty($group)) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = $t->_("Error: No Group Name Specified");
			return;
		}

		$this->template->title = $this->translate->_('Monitoring » Group detail');

		$group_info_res = $grouptype == 'servicegroup' ?
			Servicegroup_Model::get_by_field_value('servicegroup_name', $group) :
			Hostgroup_Model::get_by_field_value('hostgroup_name', $group);

		if ($group_info_res === false) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = sprintf($t->_("The requested %s ('%s') wasn't found"), $grouptype, $group);
			return;
		}

		# check if nagios is running, will affect wich template to use
		$status = Program_status_Model::get_all();
		if (empty($status) || !$status->current()->is_running) {
			$this->template->content = $this->add_view('extinfo/not_running');
			$this->template->content->info_message = sprintf($t->_('It appears as though %s is not running, so commands are temporarily unavailable...'), Kohana::config('config.product_name'));
			$this->template->content->info_message_extra = sprintf($t->_('Click %s to view %s process information'), html::anchor('extinfo/show_process_info', html::specialchars($t->_('here'))), Kohana::config('config.product_name'));
			return;
		} else {
			$this->template->content = $this->add_view('extinfo/groups');
		}

		$content = $this->template->content;

		$content->label_grouptype = $grouptype=='servicegroup' ? $t->_('Servicegroup') : $t->_('Hostgroup');
		$content->group_alias = $group_info_res->alias;
		$content->groupname = $group;
		$content->grouptype = $grouptype;
		$content->label_commands = $t->_('Commands');
		$content->label_schedule_downtime_hosts = $t->_('Schedule downtime for all hosts in this');
		$content->cmd_schedule_downtime_hosts = nagioscmd::command_id('SCHEDULE_'.strtoupper($grouptype).'_HOST_DOWNTIME');
		$content->label_schedule_downtime_services = $t->_('Schedule downtime for all services in this');
		$content->cmd_schedule_downtime_services = nagioscmd::command_id('SCHEDULE_'.strtoupper($grouptype).'_SVC_DOWNTIME');
		$content->label_enable = $t->_('Enable');
		$content->label_disable = $t->_('Disable');
		$content->label_notifications_hosts = $t->_('Notifications For All Hosts In This');
		$content->cmd_enable_notifications_hosts = nagioscmd::command_id('ENABLE_'.strtoupper($grouptype).'_HOST_NOTIFICATIONS');
		$content->cmd_disable_notifications_hosts = nagioscmd::command_id('DISABLE_'.strtoupper($grouptype).'_HOST_NOTIFICATIONS');
		$content->label_notifications_services = $t->_('Notifications For All Services In This');
		$content->cmd_disable_notifications_services = nagioscmd::command_id('DISABLE_'.strtoupper($grouptype).'_SVC_NOTIFICATIONS');
		$content->cmd_enable_notifications_services = nagioscmd::command_id('ENABLE_'.strtoupper($grouptype).'_SVC_NOTIFICATIONS');
		$content->label_active_checks = $t->_('Active Checks Of All Services');
		$content->cmd_disable_active_checks = nagioscmd::command_id('DISABLE_'.strtoupper($grouptype).'_SVC_CHECKS');
		$content->cmd_enable_active_checks = nagioscmd::command_id('ENABLE_'.strtoupper($grouptype).'_SVC_CHECKS');

		$content->notes_url = $group_info_res->notes_url !='' ? nagstat::process_macros($group_info_res->notes_url, $group_info_res) : false;
		$content->label_notes_url = $t->_('Extra Notes');
		$content->action_url =$group_info_res->action_url !='' ? nagstat::process_macros($group_info_res->action_url, $group_info_res) : false;
		$content->label_action_url = $t->_('Extra Actions');

		switch ($grouptype) {
			case 'servicegroup':
				$label_status_details = $t->_('Status detail');
				$label_group_status_overview = $t->_('Status overview');
				$label_group_status_grid = $t->_('Status grid');
				$label_avail = $t->_('Availability');
				$label_view_for = $t->_('for this servicegroup');
				$page_links = array(
					$label_status_details => 'status/service/'.$group.'?group_type='.$grouptype,
					$label_group_status_overview => 'status/'.$grouptype.'/'.$group,
					$label_group_status_grid => 'status/'.$grouptype.'_grid/'.$group,
					$label_avail => 'reports/generate/?type=avail&report_type='.$grouptype.'s&'.$grouptype.'[]='.$group
				);
				break;
			case 'hostgroup':
				$label_status_details = $t->_('Status detail');
				$label_group_status_overview = $t->_('Status overview');
				$label_group_status_grid = $t->_('Status grid');
				$label_avail = $t->_('Availability');
				$label_view_for = $t->_('for this hostgroup');
				$page_links = array(
					$label_status_details => 'status/service/'.$group.'?group_type='.$grouptype,
					$label_group_status_overview => 'status/'.$grouptype.'/'.$group,
					$label_group_status_grid => 'status/'.$grouptype.'_grid/'.$group,
					$label_avail => 'reports/generate/?type=avail&report_type='.$grouptype.'s&'.$grouptype.'[]='.$group
				);
				break;
		}
		if (isset($page_links)) {
			$content->page_links = $page_links;
			$content->label_view_for = $label_view_for;
		}

	}

	/**
	*	Print comments for host or service
	*/
	public function _comments($host=false, $service=false, $type=false, $all=false, $items_per_page=false)
	{
		$items_per_page = !empty($items_per_page) ? $items_per_page : Kohana::config('pagination.default.items_per_page');
		$host = trim($host);
		$service = trim($service);
		$type = trim($type);
		if (empty($all) && empty($host)) {
			return false;
		}

		if ($all === true) {
			$tot = Comment_Model::fetch_all_comments($host, $service, false, false, true);
		} else {
			$tot = 0;
		}

		//Setup pagination
		$pagination = new Pagination(
			array(
				'uri_segment' => 3,
				'total_items'=> $tot,
				'items_per_page' => $items_per_page
			)
		);
		$offset = $pagination->sql_offset;

		$comment_data = $all ? Comment_Model::fetch_all_comments($host, $service, $items_per_page, $offset) :Comment_Model::fetch_comments($host, $service, $items_per_page, $offset);

		$i = 0;

		if (!empty($comment_data)) {
			foreach ($comment_data as $row) {
				$comment[$i]['host_name'] = $row->host_name;
				if (isset($row->service_description))
					$comment[$i]['service_description'] = $row->service_description;
				$comment[$i]['entry_time'] = $row->entry_time;
				$comment[$i]['author_name'] = $row->author_name;
				$comment[$i]['entry_time'] = $row->entry_time;
				$comment[$i]['comment_id'] = $row->comment_id;
				$comment[$i]['persistent'] = $row->persistent;
				$comment[$i]['entry_type'] = $row->entry_type;
				$comment[$i]['expires'] = $row->expires;
				$comment[$i]['expire_time'] = $row->expire_time;
				$comment[$i]['comment'] = Comment_Model::fetch_all_comment_types($row->entry_type, $row->host_name, $row->service_description);
				$tmp = Comment_Model::fetch_all_comment_types($row->entry_type, $row->host_name, $row->service_description);
				foreach($tmp as $test) {
					$comment[$i]['comment'] = empty($test->comment_data) ? $row->comment_data : $test->comment_data;
				}
				$i++;
			}
		}
		else
			$comment = $comment_data;

		$this->template->content->comments = $this->add_view('extinfo/comments');
		$t = $this->translate;
		$comments = $this->template->content->comments;
		$comments->label_add_comment = $service ? $t->_('Add a new service comment') : $t->_('Add a new host comment');
		$comments->cmd_add_comment =
			$type=='host' ? nagioscmd::command_id('ADD_HOST_COMMENT')
			: nagioscmd::command_id('ADD_SVC_COMMENT');
		$comments->cmd_delete_all_comments =
			$type=='host' ? nagioscmd::command_id('DEL_ALL_HOST_COMMENTS')
			: nagioscmd::command_id('DEL_ALL_SVC_COMMENTS');
		$comments->label_delete_all_comments = $t->_('Delete all comments');
		$comments->host = $host;
		$comments->label_title = $type == 'host' ? $t->_('Host Comments') : $t->_('Service Comments');
		$comments->service = $service;
		$comments->label_host_name = $t->_('Host Name');
		$comments->label_service = $t->_('Service');
		$comments->label_entry_time = $t->_('Entry Time');
		$comments->label_author = $t->_('Author');
		$comments->label_comment = $t->_('Comment');
		$comments->label_comment_id = $t->_('Comment ID');
		$comments->label_persistent = $t->_('Persistent');
		$comments->label_type = $t->_('Type');
		$comments->label_expires = $t->_('Expires');
		$comments->label_actions = $t->_('Actions');

		$comments->data = $comment;//$comment_data;
		$nagios_config = System_Model::parse_config_file('nagios.cfg');
		$comments->label_yes = $t->_('YES');
		$comments->label_no = $t->_('NO');
		$comments->label_type_user = $t->_('User');
		$comments->label_type_downtime = $t->_('Scheduled Downtime');
		$comments->label_type_flapping = $t->_('Flap Detection');
		$comments->label_type_acknowledgement = $t->_('Acknowledgement');
		$comments->na_str = $t->_('N/A');
		$comments->label_delete = $t->_('Delete This Comment');
		$comments->cmd_delete_comment =
			$type=='host' ? nagioscmd::command_id('DEL_HOST_COMMENT')
			: nagioscmd::command_id('DEL_SVC_COMMENT');

		$comments->date_format_str = nagstat::date_format($nagios_config['date_format']);
		$comments->no_data = $all ? $t->_('No comments found') : sprintf($t->_('This %s has no comments associated with it'), $type);
		$comments->pagination = $pagination;
		$this->template->title = $this->translate->_(sprintf('Monitoring » %s information', ucfirst($type)));
		return $this->template->content->comments->render();
	}

	/**
	*	Show all comments for hosts and services
	*/
	public function show_comments()
	{
		$items_per_page = $this->input->get('items_per_page', Kohana::config('pagination.default.items_per_page'));
		$this->template->content = $this->add_view('extinfo/all_comments');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->template->content->host_comments = $this->_comments(true, false, 'host', true, $items_per_page);
		$this->template->content->service_comments = $this->_comments(true, true, 'service', true, $items_per_page);
		$this->template->title = $this->translate->_('Monitoring » All comments');
	}

	/**
	*	Show Program-Wide Performance Information
	*	(Performance Info)
	*/
	public function performance()
	{
		$this->template->content = $this->add_view('extinfo/performance');
		$this->template->title = $this->translate->_('Monitoring').' » '.$this->translate->_('Performance info');
		$this->template->js_header = $this->add_view('js_header');
		$content = $this->template->content;
		$service_model = new Service_Model();
		$host_model = new Host_Model();

		# Labels
		$content->title = $this->translate->_("Program-Wide Performance Information");
		$content->label_svc_actively_checked = $this->translate->_("Services Actively Checked");
		$content->label_time_frame = $this->translate->_("Time Frame");
		$content->label_services_checked = $this->translate->_("Services Checked");
		$content->label_minute = $this->translate->_("minute");
		$content->label_minutes = $this->translate->_("minutes");
		$content->label_hour = $this->translate->_("hour");
		$content->label_hours = $this->translate->_("hours");
		$content->label_since_program_start = $this->translate->_("Since program start");
		$content->label_metric = $this->translate->_("Metric");
		$content->label_min = $this->translate->_("Min.");
		$content->label_max = $this->translate->_("Max.");
		$content->label_average = $this->translate->_("Average");
		$content->label_check_execution_time = $this->translate->_("Check Execution Time");
		$content->label_sec = $this->translate->_("sec");
		$content->label_check_latency = $this->translate->_("Check Latency");
		$content->label_percent_state_change = $this->translate->_("Percent State Change");
		$content->label_svc_passively_checked = $this->translate->_("Services Passively Checked");
		$content->label_time_frame = $this->translate->_("Time Frame");
		$content->label_hosts_actively_checked = $this->translate->_("Hosts Actively Checked");
		$content->label_hosts_checked = $this->translate->_("Hosts Checked");
		$content->label_hosts_passively_checked = $this->translate->_("Hosts Passively Checked");
		$content->label_check_statistics = $this->translate->_("Check Statistics");
		$content->label_type = $this->translate->_("Type");
		$content->label_last_1_min = $this->translate->_("Last 1 Min");
		$content->label_last_5_min = $this->translate->_("Last 5 Min");
		$content->label_last_15_min = $this->translate->_("Last 15 Min");
		$content->label_active_scheduled_host_check = $this->translate->_("Active Scheduled Host Checks");
		$content->label_active_ondemand_host_check = $this->translate->_("Active On-Demand Host Checks");
		$content->label_parallel_host_check = $this->translate->_("Parallel Host Checks");
		$content->label_serial_host_check = $this->translate->_("Serial Host Checks");
		$content->label_cached_host_check = $this->translate->_("Cached Host Checks");
		$content->label_passive_host_check = $this->translate->_("Passive Host Checks");
		$content->label_active_scheduled_service_check = $this->translate->_("Active Scheduled Service Checks");
		$content->label_active_ondemand_service_check = $this->translate->_("Active On-Demand Service Checks");
		$content->label_cached_service_check = $this->translate->_("Cached Service Checks");
		$content->label_passive_service_check = $this->translate->_("Passive Service Checks");
		$content->label_external_commands = $this->translate->_("External Commands");
		$content->label_buffer_usage = $this->translate->_("Buffer Usage");
		$content->label_in_use = $this->translate->_("In Use");
		$content->label_max_used = $this->translate->_("Max Used");
		$content->label_total_available = $this->translate->_("Total Available");

		# Values
		$service_model->get_performance_data();
		$host_model->get_performance_data();

		# active service checks
		$content->svc_active_1min = $service_model->active_service_checks_1min;
		$content->svc_active_5min = $service_model->active_service_checks_5min;
		$content->svc_active_15min = $service_model->active_service_checks_15min;
		$content->svc_active_1hour = $service_model->active_service_checks_1hour;
		$content->svc_active_start = $service_model->active_service_checks_start;
		$content->svc_active_ever = $service_model->active_service_checks_ever;

		# active service checks, percentages
		$content->svc_active_1min_perc = $service_model->total_active_service_checks > 0 ?
			 number_format(($service_model->active_service_checks_1min*100)/$service_model->total_active_service_checks, 1) : '0.0';
		$content->svc_active_5min_perc = $service_model->total_active_service_checks > 0 ?
			 number_format(($service_model->active_service_checks_5min*100)/$service_model->total_active_service_checks, 1) : '0.0';
		$content->svc_active_15min_perc = $service_model->total_active_service_checks > 0 ?
			 number_format(($service_model->active_service_checks_15min*100)/$service_model->total_active_service_checks, 1) : '0.0';
		$content->svc_active_1hour_perc = $service_model->total_active_service_checks > 0 ?
			 number_format(($service_model->active_service_checks_1hour*100)/$service_model->total_active_service_checks, 1) : '0.0';
		$content->svc_active_start_perc = $service_model->total_active_service_checks > 0 ?
			 number_format(($service_model->active_service_checks_start*100)/$service_model->total_active_service_checks, 1) : '0.0';
		#$content->svc_active_ever_perc = $service_model->total_active_service_checks > 0 ?
		#	 number_format(($service_model->active_service_checks_ever*100)/$service_model->total_active_service_checks, 1) : '0.0';

		# passive service checks
		$content->svc_passive_1min = $service_model->passive_service_checks_1min;
		$content->svc_passive_5min = $service_model->passive_service_checks_5min;
		$content->svc_passive_15min = $service_model->passive_service_checks_15min;
		$content->svc_passive_1hour = $service_model->passive_service_checks_1hour;
		$content->svc_passive_start = $service_model->passive_service_checks_start;
		$content->svc_passive_ever = $service_model->passive_service_checks_ever;

		# passive service checks, percentages
		$content->svc_passive_1min_perc = $service_model->total_passive_service_checks > 0 ?
			number_format(($service_model->passive_service_checks_1min*100)/$service_model->total_passive_service_checks, 1) : '0.0';
		$content->svc_passive_5min_perc =  $service_model->total_passive_service_checks > 0 ?
			number_format(($service_model->passive_service_checks_5min*100)/$service_model->total_passive_service_checks, 1) : '0.0';
		$content->svc_passive_15min_perc = $service_model->total_passive_service_checks > 0 ?
			number_format(($service_model->passive_service_checks_15min*100)/$service_model->total_passive_service_checks, 1) : '0.0';
		$content->svc_passive_1hour_perc = $service_model->total_passive_service_checks > 0 ?
			number_format(($service_model->passive_service_checks_1hour*100)/$service_model->total_passive_service_checks, 1) : '0.0';
		$content->svc_passive_start_perc = $service_model->total_passive_service_checks > 0 ?
			number_format(($service_model->passive_service_checks_start*100)/$service_model->total_passive_service_checks, 1) : '0.0';
		$content->svc_passive_ever_perc = $service_model->total_passive_service_checks > 0 ?
			number_format(($service_model->passive_service_checks_ever*100)/$service_model->total_passive_service_checks, 1) : '0.0';

		# service execution time
		$content->min_service_execution_time = number_format($service_model->min_service_execution_time, 2);
		$content->max_service_execution_time = number_format($service_model->max_service_execution_time, 2);
		$content->svc_average_execution_time = $service_model->total_active_service_checks > 0 ?
			number_format(($service_model->total_service_execution_time/$service_model->total_active_service_checks), 3) : 0;

		# service latency
		$content->min_service_latency = number_format($service_model->min_service_latency, 2);
		$content->max_service_latency = number_format($service_model->max_service_latency, 2);
		$content->average_service_latency = $service_model->total_active_service_checks > 0 ?
			number_format($service_model->total_service_latency/$service_model->total_active_service_checks, 3) : 0;

		# service state change - active
		$content->min_service_percent_change_a = number_format($service_model->min_service_percent_change_a, 2);
		$content->max_service_percent_change_a = number_format($service_model->max_service_percent_change_a, 2);
		$content->average_service_percent_change = $service_model->total_active_service_checks > 0 ?
			number_format($service_model->total_service_percent_change_a/$service_model->total_active_service_checks, 3) : '0.00';

		# service state change - passive
		$content->min_service_percent_change_b = number_format($service_model->min_service_percent_change_b, 2);
		$content->max_service_percent_change_b = number_format($service_model->max_service_percent_change_b, 2);
		$content->average_service_percent_change = $service_model->total_passive_service_checks > 0 ?
			number_format($service_model->total_service_percent_change_b/$service_model->total_passive_service_checks, 3) : '0.00';

		# active host checks
		$content->hst_active_1min = $host_model->active_host_checks_1min;
		$content->hst_active_5min = $host_model->active_host_checks_5min;
		$content->hst_active_15min = $host_model->active_host_checks_15min;
		$content->hst_active_1hour = $host_model->active_host_checks_1hour;
		$content->hst_active_start = $host_model->active_host_checks_start;
		$content->hst_active_ever = $host_model->active_host_checks_ever;

		# active host checks, percentages
		$content->hst_active_1min_perc = $host_model->total_active_host_checks > 0 ?
			number_format(($host_model->active_host_checks_1min*100)/$host_model->total_active_host_checks, 1) : '0.0';
		$content->hst_active_5min_perc = $host_model->total_active_host_checks > 0 ?
			number_format(($host_model->active_host_checks_5min*100)/$host_model->total_active_host_checks, 1) : '0.0';
		$content->hst_active_15min_perc = $host_model->total_active_host_checks > 0 ?
			number_format(($host_model->active_host_checks_15min*100)/$host_model->total_active_host_checks, 1) : '0.0';
		$content->hst_active_1hour_perc = $host_model->total_active_host_checks > 0 ?
			number_format(($host_model->active_host_checks_1hour*100)/$host_model->total_active_host_checks, 1) : '0.0';
		$content->hst_active_start_perc = $host_model->total_active_host_checks > 0 ?
			number_format(($host_model->active_host_checks_start*100)/$host_model->total_active_host_checks, 1) : '0.0';
		$content->hst_active_ever_perc = $host_model->total_active_host_checks > 0 ?
			number_format(($host_model->active_host_checks_ever*100)/$host_model->total_active_host_checks, 1) : '0.0';

		# passive host checks
		$content->hst_passive_1min = $host_model->passive_host_checks_1min;
		$content->hst_passive_5min = $host_model->passive_host_checks_5min;
		$content->hst_passive_15min = $host_model->passive_host_checks_15min;
		$content->hst_passive_1hour = $host_model->passive_host_checks_1hour;
		$content->hst_passive_start = $host_model->passive_host_checks_start;
		$content->hst_passive_ever = $host_model->passive_host_checks_ever;

		# passive host checks, percentages
		$content->hst_passive_1min_perc = $host_model->total_passive_host_checks > 0 ?
			number_format(($host_model->passive_host_checks_1min*100)/$host_model->total_passive_host_checks, 1) : '0.0';
		$content->hst_passive_5min_perc = $host_model->total_passive_host_checks > 0 ?
			number_format(($host_model->passive_host_checks_5min*100)/$host_model->total_passive_host_checks, 1) : '0.0';
		$content->hst_passive_15min_perc = $host_model->total_passive_host_checks > 0 ?
			number_format(($host_model->passive_host_checks_15min*100)/$host_model->total_passive_host_checks, 1) : '0.0';
		$content->hst_passive_1hour_perc = $host_model->total_passive_host_checks > 0 ?
			number_format(($host_model->passive_host_checks_1hour*100)/$host_model->total_passive_host_checks, 1) : '0.0';
		$content->hst_passive_start_perc = $host_model->total_passive_host_checks > 0 ?
			number_format(($host_model->passive_host_checks_start*100)/$host_model->total_passive_host_checks, 1) : '0.0';
		$content->hst_passive_ever_perc = $host_model->total_passive_host_checks > 0 ?
			number_format(($host_model->passive_host_checks_ever*100)/$host_model->total_passive_host_checks, 1) : '0.0';

		# host execution time
		$content->min_host_execution_time = number_format($host_model->min_host_execution_time, 2);
		$content->max_host_execution_time = number_format($host_model->max_host_execution_time, 2);
		$content->average_host_execution_time = $host_model->total_active_host_checks > 0 ?
			number_format(($host_model->total_host_execution_time/$host_model->total_active_host_checks), 3) : 0;

		# host latency
		$content->min_host_latency = number_format($host_model->min_host_latency, 2);
		$content->max_host_latency = number_format($host_model->max_host_latency, 2);
		$content->average_host_latency = $host_model->total_active_host_checks > 0 ?
			number_format($host_model->total_host_latency/$host_model->total_active_host_checks, 3) : 0;

		# host state change - active
		$content->min_host_percent_change_a = number_format($host_model->min_host_percent_change_a, 2);
		$content->max_host_percent_change_a = number_format($host_model->max_host_percent_change_a, 2);
		$content->average_host_percent_change = $host_model->total_active_host_checks > 0 ?
			number_format($host_model->total_host_percent_change_a/$host_model->total_active_host_checks, 3) : '0.00';

		# host state change - passive
		$content->min_host_percent_change_b = number_format($host_model->min_host_percent_change_b, 2);
		$content->max_host_percent_change_b = number_format($host_model->max_host_percent_change_b, 2);
		$content->average_host_percent_change = $host_model->total_passive_host_checks > 0 ?
			number_format($host_model->total_host_percent_change_b/$host_model->total_passive_host_checks, 3) : '0.00';

		$stats_key = 'programstatus';
		$check_stats = System_Model::get_status_info('status.log', $stats_key);
		if ($check_stats !== false && isset($check_stats[$stats_key])) {
			$stats = $check_stats[$stats_key];
			$content->active_scheduled_host_check_stats = System_Model::extract_stat_key('active_scheduled_host_check_stats', $stats);
			$content->active_ondemand_host_check_stats = System_Model::extract_stat_key('active_ondemand_host_check_stats', $stats);
			$content->parallel_host_check_stats = System_Model::extract_stat_key('parallel_host_check_stats', $stats);
			$content->serial_host_check_stats = System_Model::extract_stat_key('serial_host_check_stats', $stats);
			$content->cached_host_check_stats = System_Model::extract_stat_key('cached_host_check_stats', $stats);
			$content->passive_host_check_stats = System_Model::extract_stat_key('passive_host_check_stats', $stats);
			$content->active_scheduled_service_check_stats = System_Model::extract_stat_key('active_scheduled_service_check_stats', $stats);
			$content->active_ondemand_service_check_stats = System_Model::extract_stat_key('active_ondemand_service_check_stats', $stats);
			$content->cached_service_check_stats = System_Model::extract_stat_key('cached_service_check_stats', $stats);
			$content->passive_service_check_stats = System_Model::extract_stat_key('passive_service_check_stats', $stats);
			$content->external_command_stats = System_Model::extract_stat_key('external_command_stats', $stats);
			$content->total_external_command_buffer_slots = System_Model::extract_stat_key('total_external_command_buffer_slots', $stats);
			$content->used_external_command_buffer_slots = System_Model::extract_stat_key('used_external_command_buffer_slots', $stats);
			$content->high_external_command_buffer_slots = System_Model::extract_stat_key('high_external_command_buffer_slots', $stats);
		}
	}

	/**
	*	Show scheduling queue
	*/
	public function scheduling_queue($sort_field='next_check', $sort_order='ASC')
	{
		$items_per_page = urldecode($this->input->get('items_per_page', Kohana::config('pagination.default.items_per_page'))); # @@@FIXME: should be configurable from GUI
		$back_link = '/extinfo/scheduling_queue/';

		$sq_model = new Scheduling_queue_Model($items_per_page, true, true);
		$sq_model->sort_order = urldecode($this->input->get('sort_order', $sort_order));
		$sq_model->sort_field = urldecode($this->input->get('sort_field', $sort_field));

		$result = $sq_model->show_scheduling_queue();

		$pagination = new Pagination(
			array(
				'total_items'=> $sq_model->count_queue(),
				'items_per_page' => $items_per_page
			)
		);

		$sq_model->offset = $pagination->sql_offset;

		$header_links = array(
			array(
				'title' => $this->translate->_('Host'),
				'url_asc' => Router::$controller.'/'.Router::$method.'?sort_order=ASC&sort_field=host_name',
				'url_desc' => Router::$controller.'/'.Router::$method.'?sort_order=DESC&sort_field=host_name',
			),
			array(
				'title' => $this->translate->_('Service'),
				'url_asc' => Router::$controller.'/'.Router::$method.'?sort_order=ASC&sort_field=service_description',
				'url_desc' => Router::$controller.'/'.Router::$method.'?sort_order=DESC&sort_field=service_description',
			),
			array(
				'title' => $this->translate->_('Last check'),
				'url_asc' => Router::$controller.'/'.Router::$method.'?sort_order=ASC&sort_field=last_check',
				'url_desc' => Router::$controller.'/'.Router::$method.'?sorttype=DESC&sort_field=last_check',
			),
			array(
				'title' => $this->translate->_('Next check'),
				'url_asc' => Router::$controller.'/'.Router::$method.'?sort_order=ASC&sort_field=next_check',
				'url_desc' => Router::$controller.'/'.Router::$method.'?sort_order=DESC&sort_field=next_check',
			)
		);

		$this->template->title = $this->translate->_('Monitoring').' » '.$this->translate->_('Scheduling queue');
		$this->template->content = $this->add_view('extinfo/scheduling_queue');
		$this->template->content->data = $result;
		$this->template->content->back_link = $back_link;
		$this->template->content->header_links = $header_links;
		$this->template->content->pagination = isset($pagination) ? $pagination : false;
	}

	/**
	*	Print scheduled downtime
	*/
	public function scheduled_downtime($type='all')
	{
		# valid types
		$types = array(
			nagstat::HOST_DOWNTIME => 'host',
			nagstat::SERVICE_DOWNTIME  => 'service',
			nagstat::ANY_DOWNTIME => 'all'
			);
		$type = arr::search($_REQUEST, 'type', $type);
		$downtime_type = false;
		if (in_array($type, $types)) {
			$downtime_type = array_search($type, $types);
		} else {
			if (array_key_exists($type, $types)) {
				$downtime_type = $type;
			} else {
				$downtime_type = nagstat::ANY_DOWNTIME;
			}
		}

		$host_title_str = $this->translate->_('Scheduled Host Downtime');
		$service_title_str = $this->translate->_('Scheduled Service Downtime');
		$title = $this->translate->_('Scheduled Downtime');
		$type_str = false;
		$host_data = false;
		$service_data = false;

		switch ($downtime_type) {
			case nagstat::HOST_DOWNTIME:
				$type_str = $types[$downtime_type];
				$host_data = Downtime_Model::get_downtime_data($downtime_type, 'downtime_type DESC');
				break;
			case nagstat::SERVICE_DOWNTIME:
				$type_str = $types[$downtime_type];
				$service_data = Downtime_Model::get_downtime_data($downtime_type, 'downtime_type DESC');
				break;
			case nagstat::ANY_DOWNTIME:
				$host_data = Downtime_Model::get_downtime_data(nagstat::HOST_DOWNTIME, 'downtime_type DESC');
				$service_data = Downtime_Model::get_downtime_data(nagstat::SERVICE_DOWNTIME, 'downtime_type DESC');
				break;
		}
		$this->template->content = $this->add_view('extinfo/scheduled_downtime');
		$content = $this->template->content;

		# table header fields
		$content->label_host_name = $this->translate->_('Host Name');
		$content->label_service = $this->translate->_('Service');
		$content->label_entry_time = $this->translate->_('Entry Time');
		$content->label_author = $this->translate->_('Author');
		$content->label_comment = $this->translate->_('Comment');
		$content->label_start_time = $this->translate->_('Start Time');
		$content->label_end_time = $this->translate->_('End Time');
		$content->label_type = $this->translate->_('Type');
		$content->label_duration = $this->translate->_('Duration');
		$content->label_downtime_id = $this->translate->_('Downtime ID');
		$content->label_trigger_id = $this->translate->_('Trigger ID');
		$content->label_actions = $this->translate->_('Actions');

		$content->title = $title;
		$content->fixed = $this->translate->_('Fixed');
		$content->flexible = $this->translate->_('Flexible');
		$content->na_str = $this->translate->_('N/A');
		$content->host_link_text = $this->translate->_('Schedule host downtime');
		$content->service_link_text = $this->translate->_('Schedule service downtime');
		$content->link_titlestring = $this->translate->_('Delete/Cancel This Scheduled Downtime Entry');
		$content->date_format = nagstat::date_format();
		$content->host_data = $host_data;
		$content->host_title_str = $host_title_str;

		$content->service_data = $service_data;
		$content->service_title_str = $service_title_str;
		$this->template->title = $this->translate->_("Monitoring » Scheduled downtime");

	}
}
