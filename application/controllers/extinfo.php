<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Extinfo controller
 *
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
	 * @param $type string = host
	 * @param $host boolean = false
	 * @param $service boolean = false
	 */
	public function details($type='host', $host=false, $service=false)
	{
		// If customers have non-utf8 service names, $this->input
		// will not contain a usefull name. Workaround.
		if (PHP_SAPI !== 'cli') {
			$type = getparams::get_raw_param('type', $type);
			$host = getparams::get_raw_param('host', $host);
			$service = getparams::get_raw_param('service', $service);
		} else {
			$type = $this->input->get('type', $type);
			$host = $this->input->get('host', $host);
			$service = $this->input->get('service', $service);
		}

		$this->template->title = 'Monitoring » Extinfo';

		# load current status for host/service status totals
		$this->current = new Current_status_Model();

		$host = trim($host);
		$type = strtolower($type);
		if (empty($host)) {
			return false;
		}

		# is user authenticated to view details on current object?
		$auth = Nagios_auth_Model::instance();
		$is_authenticated = true;
		switch ($type) {
			case 'host':
				if (!$auth->is_authorized_for_host($host))
					$is_authenticated = false;
				break;
			case 'service':
				if (!$auth->is_authorized_for_service($host, $service))
					$is_authenticated = false;
				break;
			case 'servicegroup': case 'hostgroup':
				return $this->group_details($type, $host);
		}
		if ($is_authenticated === false) {
			return url::redirect('extinfo/unauthorized/'.$type);
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

		$result_data = Host_Model::object_status($host, $service);
		$result = $result_data[0];
		switch($type) {
			case 'host':
				$content->custom_variables = Custom_variable_Model::get_for($type, $result->id);
				break;
			case 'service':
				$content->custom_variables = Custom_variable_Model::get_for($type, $result->service_id);
				break;
			default:
				$content->custom_variables = array();

		}
		$host_link = false;
		$yes = _('YES');
		$no = _('NO');
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
			$content->title = _('Host State Information');
			$content->no_group_lable = _('No hostgroups');
			$check_compare_value = Current_status_Model::HOST_CHECK_ACTIVE;
			$last_notification = $result->last_host_notification;
			$content->lable_next_scheduled_check = _('Next scheduled active check');
			$content->lable_flapping = _('Is this host flapping?');
			$obsessing = $result->obsess_over_host;
			$content->notes = $result->notes !='' ? nagstat::process_macros($result->notes, $result) : false;

			# check for parents
			$host_obj = new Host_Model();
			$parents = $host_obj->get_parents($host);
			if (count($parents)) {
				$content->parents = $parents;
			}

			$back_link = '/extinfo/details/?host='.urlencode($host);
			if ($result->current_state == Current_status_Model::HOST_PENDING ) {
				$is_pending = true;
				$message_str = _('This host has not yet been checked, so status information is not available.');
			}
		} else {
			$group_info = Group_Model::get_groups_for_object($type, $result->service_id);
			$content->title = _('Service State Information');
			$content->no_group_lable = _('No servicegroups');
			$content->lable_next_scheduled_check = _('Next scheduled check');
			$host_link = html::anchor('extinfo/details/?host='.urlencode($host), html::specialchars($host));
			$back_link = '/extinfo/details/service/?host='.urlencode($host).'&service='.urlencode($service);
			$check_compare_value = Current_status_Model::SERVICE_CHECK_ACTIVE;
			$last_notification = $result->last_notification;
			$content->lable_flapping = _('Is this service flapping?');
			$obsessing = $result->obsess_over_service;
			$content->notes = $result->service_notes !='' ? nagstat::process_macros($result->service_notes, $result) : false;
			if ($result->current_state == Current_status_Model::SERVICE_PENDING ) {
				$is_pending = true;
				$message_str = _('This service has not yet been checked, so status information is not available.');
			}
		}

		$content->notes_url = $result->notes_url !='' ? nagstat::process_macros($result->notes_url, $result) : false;
		$content->action_url = $result->action_url !='' ? nagstat::process_macros($result->action_url, $result) : false;

		$xaction = array();
		if (nacoma::link()===true) {
			$label = _('Configure');
			$url = url::site() . "configuration/configure/?type=$type&name=".urlencode($host);
			if ($type === 'service') {
				$url .= '&service='.urlencode($service);
				$alt = _('Configure this service using Nacoma');
			} else {
				$alt = _('Configure this host using Nacoma');
			}

			$xaction[$label] =
				array('url' => $url,
					  'img' => $this->img_path('icons/16x16/nacoma.png'),
					  'alt' => $alt
					);
		}

		if (Kohana::config('config.pnp4nagios_path') !== false && pnp::has_graph($host, urlencode($service))) {
			$label = _('Show performance graph');
			$url = url::site() . 'pnp/?host=' . urlencode($host);
			if ($type ===  'service') {
				$url .= '&srv=' . urlencode($service);
			} else {
				$url .= '&srv=_HOST_';
			}
			$xaction[$label] = array
				('url' => $url,
				 'img' => $this->img_path('icons/16x16/pnp.png'),
				 'alt' => $label,
				 'img_class' => 'pnp_graph_icon'
				 );
		}
		$content->extra_action_links = $xaction;

		$groups = false;
		if ($group_info !== false && count($group_info) > 0) {
			foreach ($group_info as $group_row) {
				$groups[] = html::anchor(sprintf("status/%sgroup/%s", $type, urlencode($group_row->{$type.'group_name'})),
					html::specialchars($group_row->{$type.'group_name'}));
			}
		}

		if ($is_pending) {
			$content->pending_msg = $message_str;
		}
		$content->lable_type = $type == 'host' ? _('Host') : _('Service');
		$content->type = $type;
		$content->back_link = $back_link;
		$content->date_format_str = nagstat::date_format();
		$content->host_link = $host_link;
		$content->lable_member_of = _('Member of');
		$content->lable_for = _('for');
		$content->lable_on_host = _('On host');
		$content->main_object = $type=='host' ? $host : $service;
		$content->host = $host;
		$content->lable_current_status = _('Current status');
		$content->lable_status_information = _('Status information');
		$content->current_status_str = $this->current->status_text($result->current_state, $type);
		$content->duration = $result->duration;
		$content->groups = $groups;
		$content->host_address = $result->address;
		$content->icon_image = $result->icon_image;
		$content->icon_image_alt = $result->icon_image_alt;
		$content->status_info = $result->output.'<br />'.str_replace('\n', '<br />', nl2br($result->long_output));
		$content->lable_perf_data = _('Performance data');
		$content->perf_data = $result->perf_data;
		$content->lable_current_attempt = _('Current attempt');
		$content->current_attempt = $result->current_attempt;
		$content->state_type = $result->state_type ? _('HARD state') : _('SOFT state');
		$content->main_object_alias = $type=='host' ? $result->alias : false;
		$content->max_attempts = $result->max_attempts;
		$content->last_update = $result->last_update;
		$content->last_check = $result->last_check;
		$content->lable_last_check = _('Last check time');
		$content->lable_check_type = _('Check type');
		$content->lable_last_update = _('Last update');

		$str_active = _('ACTIVE');
		$str_passive = _('PASSIVE');
		$content->check_type = $result->check_type == $check_compare_value ? $str_active: $str_passive;
		$content->lable_check_latency_duration = _('Check latency / duration');
		$na_str = _('N/A');
		$content->check_latency =
		$result->check_type == $check_compare_value ? $result->latency : $na_str;
		$content->execution_time = $result->execution_time;
		$content->lable_seconds = _('seconds');

		$content->next_check = (int)$result->next_check;
		$content->lable_last_state_change = _('Last state change');
		$content->last_state_change = (int)$result->last_state_change;
		$content->lable_last_notification = _('Last notification');
		$content->last_notification = $last_notification!=0 ? date(nagstat::date_format(), $last_notification) : $na_str;
		$content->lable_notifications = _('notification');
		$content->current_notification_number = $result->current_notification_number;
		$lable_flapping_state_change = _('state change');
		$content->percent_state_change_str = '';
		$is_flapping = $result->is_flapping;
		if (!$result->flap_detection_enabled) {
			$content->flap_value = $na_str;
		} else {
			$content->flap_value = $is_flapping ? $yes : $no;
			$content->percent_state_change_str = '('.number_format((int)$result->percent_state_change, 2).'% '.$lable_flapping_state_change.')';
		}
		$content->lable_in_scheduled_dt = _('In scheduled downtime?');
		$content->scheduled_downtime_depth = $result->scheduled_downtime_depth ? $yes : $no;
		$last_update_ago_arr = date::timespan(time(), $result->last_update, 'days,hours,minutes,seconds');
		$ago = _('ago');
		$last_update_ago = false;
		$last_update_ago_str = '';
		if (is_array($last_update_ago_arr) && !empty($last_update_ago_arr)) {
			foreach ($last_update_ago_arr as $key => $val) {
				$last_update_ago[] = $val.substr($key, 0, 1);
			}
			$last_update_ago_str = '('.implode(' ', $last_update_ago) . ' ' . $ago . ')';
		}
		$content->last_update_ago = $last_update_ago_str !='' ? $last_update_ago_str : $na_str;
		$content->lable_active_checks = _('Active checks');
		$content->lable_passive_checks = _('Passive checks');
		$content->lable_obsessing = _('Obsessing');
		$content->lable_notifications = _('Notifications');
		$content->lable_event_handler = _('Event handler');
		$content->lable_flap_detection = _('Flap detection');
		$str_enabled = _('ENABLED');
		$str_disabled = _('DISABLED');
		$content->active_checks_enabled = $result->active_checks_enabled ? $str_enabled : $str_disabled;
		$content->active_checks_enabled_val = $result->active_checks_enabled ? true : false;
		$content->passive_checks_enabled = $result->passive_checks_enabled ? $str_enabled : $str_disabled;
		$content->obsessing = $obsessing ? $str_enabled : $str_disabled;
		$content->notifications_enabled = $result->notifications_enabled ? $str_enabled : $str_disabled;
		$content->event_handler_enabled = $result->event_handler_enabled ? $str_enabled : $str_disabled;
		$content->flap_detection_enabled = $result->flap_detection_enabled ? $str_enabled : $str_disabled;

		# check if nagios is running, will affect wich template to use
		$status = Program_status_Model::get_local();
		$is_running = empty($status) || count($status)==0 ? false : $status->current()->is_running;
		if (empty($status) || !$is_running) {
			$this->template->content->commands = $this->add_view('extinfo/not_running');
			$this->template->content->commands->info_message = sprintf(_('It appears as though %s is not running, so commands are temporarily unavailable...'), Kohana::config('config.product_name'));
			$this->template->content->commands->info_message_extra = sprintf(_('Click %s to view %s process information'), html::anchor('extinfo/show_process_info', html::specialchars(_('here'))), Kohana::config('config.product_name'));
		} else {
			$this->template->content->commands = $this->add_view('extinfo/commands');
		}

		$commands = $this->template->content->commands;
		if ($type == 'host') {
			$commands->lable_command_title = _('Host Commands');
		} else {
			$commands->lable_command_title = _('Service Commands');
		}

		$commands->lable_host_map = _('Locate host on map');
		$commands->type = $type;
		$commands->host = $host;

		if ($result->active_checks_enabled ) {
			$commands->lable_active_checks = $type == 'host' ? _('Disable active checks of this host') : _('Disable active checks of this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_CHECK') : nagioscmd::command_id('DISABLE_SVC_CHECK');
			$commands->link_active_checks = $this->command_link($cmd, $host, $service, $commands->lable_active_checks);
			$force_reschedule = 'true';
		} else {
			$commands->lable_active_checks = $type == 'host' ? _('Enable active checks of this host') : _('Enable active checks of this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_CHECK') : nagioscmd::command_id('ENABLE_SVC_CHECK');
			$commands->link_active_checks = $this->command_link($cmd, $host, $service, $commands->lable_active_checks);
			$force_reschedule = 'false';
		}

		$commands->lable_reschedule_check = $type == 'host' ? _('Re-schedule next host check') : _('Re-schedule next service check');
		$commands->lable_link_reschedule_check = $type == 'host' ? _('Re-schedule the next check of this host') : _('Re-schedule the next check of this service');
		$cmd = $type == 'host' ? nagioscmd::command_id('SCHEDULE_HOST_CHECK') : nagioscmd::command_id('SCHEDULE_SVC_CHECK');
		$commands->link_reschedule_check = $this->command_link($cmd, $host, $service, $commands->lable_link_reschedule_check);

		if ($result->passive_checks_enabled) {
			$commands->lable_submit_passive_checks = $type == 'host' ? _('Submit passive check result for this host') : _('Submit passive check result for this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('PROCESS_HOST_CHECK_RESULT') : nagioscmd::command_id('PROCESS_SERVICE_CHECK_RESULT');
			$commands->link_submit_passive_check = $this->command_link($cmd, $host, $service, $commands->lable_submit_passive_checks);

			$commands->lable_stop_start_passive_checks = $type == 'host' ? _('Stop accepting passive checks for this host') : _('Stop accepting passive checks for this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_PASSIVE_HOST_CHECKS') : nagioscmd::command_id('DISABLE_PASSIVE_SVC_CHECKS');
			$commands->link_stop_start_passive_check = $this->command_link($cmd, $host, $service, $commands->lable_stop_start_passive_checks);
		} else {
			$commands->lable_stop_start_passive_checks = $type == 'host' ? _('Start accepting passive checks for this host') : _('Start accepting passive checks for this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_PASSIVE_HOST_CHECKS') : nagioscmd::command_id('ENABLE_PASSIVE_SVC_CHECKS');
			$commands->link_stop_start_passive_check = $this->command_link($cmd, $host, $service, $commands->lable_stop_start_passive_checks);
		}
		if ($obsessing) {
			$commands->lable_obsessing = $type == 'host' ? _('Stop obsessing over this host') : _('Stop obsessing over this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('STOP_OBSESSING_OVER_HOST') : nagioscmd::command_id('STOP_OBSESSING_OVER_SVC');
			$commands->link_obsessing = $this->command_link($cmd, $host, $service, $commands->lable_obsessing);
		} else {
			$commands->lable_obsessing = $type == 'host' ? _('Start obsessing over this host') : _('Start obsessing over this service');
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
					$commands->lable_acknowledge_problem = _('Acknowledge this host problem');
					$commands->link_acknowledge_problem = $this->command_link(nagioscmd::command_id('ACKNOWLEDGE_HOST_PROBLEM'),
						$host, false, $commands->lable_acknowledge_problem);
				} else {
					$commands->lable_acknowledge_problem = _('Remove problem acknowledgement');
					$commands->link_acknowledge_problem = $this->command_link(nagioscmd::command_id('REMOVE_HOST_ACKNOWLEDGEMENT'),
						$host, false, $commands->lable_acknowledge_problem);
				}
			}
		} else {
			if (($result->current_state == Current_status_Model::SERVICE_WARNING || $result->current_state == Current_status_Model::SERVICE_UNKNOWN || $result->current_state == Current_status_Model::SERVICE_CRITICAL) && $result->state_type) {
				$commands->show_ackinfo = true;
				# show acknowledge info
				if (!$result->problem_has_been_acknowledged) {
					$commands->lable_acknowledge_problem = _('Acknowledge this service problem');
					$commands->link_acknowledge_problem = $this->command_link(nagioscmd::command_id('ACKNOWLEDGE_SVC_PROBLEM'),
						$host, $service, $commands->lable_acknowledge_problem);
				} else {
					$commands->lable_acknowledge_problem = _('Remove problem acknowledgement');
					$commands->link_acknowledge_problem = $this->command_link(nagioscmd::command_id('REMOVE_SVC_ACKNOWLEDGEMENT'),
						$host, $service, $commands->lable_acknowledge_problem);
				}
			}

		}

		# notifications
		if ($result->notifications_enabled) {
			$commands->lable_notifications = $type == 'host' ? _('Disable notifications for this host') : _('Disable notifications for this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_NOTIFICATIONS') : nagioscmd::command_id('DISABLE_SVC_NOTIFICATIONS');
			$commands->link_notifications = $this->command_link($cmd, $host, $service, $commands->lable_notifications);
		} else {
			$commands->lable_notifications = $type == 'host' ? _('Enable notifications for this host') : _('Enable notifications for this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_NOTIFICATIONS') : nagioscmd::command_id('ENABLE_SVC_NOTIFICATIONS');
			$commands->link_notifications = $this->command_link($cmd, $host, $service, $commands->lable_notifications);
		}
		$commands->lable_custom_notifications = _('Send custom notification');
		$commands->lable_link_custom_notifications = $type == 'host' ? _('Send custom host notification') : _('Send custom service notification');
		$cmd = $type == 'host' ? nagioscmd::command_id('SEND_CUSTOM_HOST_NOTIFICATION') : nagioscmd::command_id('SEND_CUSTOM_SVC_NOTIFICATION');
		$commands->link_custom_notifications = $this->command_link($cmd, $host, $service, $commands->lable_link_custom_notifications);

		$commands->show_delay = false;
		if ($type == 'host') {
			if ($result->current_state != Current_status_Model::HOST_UP) {
				$commands->show_delay = true;
				$commands->lable_delay_notification = _('Delay next host notification');
				$commands->link_delay_notifications = $this->command_link(nagioscmd::command_id('DELAY_HOST_NOTIFICATION'),
				$host, false, $commands->lable_delay_notification);
			}
		} else {
			if ($result->notifications_enabled && $result->current_state != Current_status_Model::SERVICE_OK) {
				$commands->show_delay = true;
				$commands->lable_delay_notification = _('Delay next service notification');
				$commands->link_delay_notifications = $this->command_link(nagioscmd::command_id('DELAY_SVC_NOTIFICATION'),
				$host, $service, $commands->lable_delay_notification);
			}
		}
		$commands->lable_schedule_dt = $type == 'host' ? _('Schedule downtime for this host') : _('Schedule downtime for this service');
		$cmd = $type == 'host' ?  nagioscmd::command_id('SCHEDULE_HOST_DOWNTIME') : nagioscmd::command_id('SCHEDULE_SVC_DOWNTIME');
		$commands->link_schedule_dt = $this->command_link($cmd, $host, $service, $commands->lable_schedule_dt);

		if ($type == 'host') {
			$commands->lable_disable_service_notifications_on_host = _('Disable notifications for all services on this host');
			$commands->link_disable_service_notifications_on_host = $this->command_link(nagioscmd::command_id('DISABLE_HOST_SVC_NOTIFICATIONS'),
				$host, $service, $commands->lable_disable_service_notifications_on_host);

			$commands->lable_enable_service_notifications_on_host = _('Enable notifications for all services on this host');
			$commands->link_enable_service_notifications_on_host = $this->command_link(nagioscmd::command_id('ENABLE_HOST_SVC_NOTIFICATIONS'),
				$host, $service, $commands->lable_enable_service_notifications_on_host);

			$commands->lable_check_all_services = _('Schedule a check of all services on this host');
			$commands->link_check_all_services = $this->command_link(nagioscmd::command_id('SCHEDULE_HOST_SVC_CHECKS'),
				$host, $service, $commands->lable_check_all_services);

			$commands->lable_disable_servicechecks = _('Disable checks of all services on this host');
			$commands->link_disable_servicechecks = $this->command_link(nagioscmd::command_id('DISABLE_HOST_SVC_CHECKS'),
				$host, $service, $commands->lable_disable_servicechecks);

			$commands->lable_enable_servicechecks = _('Enable checks of all services on this host');
			$commands->link_enable_servicechecks = $this->command_link(nagioscmd::command_id('ENABLE_HOST_SVC_CHECKS'),
				$host, $service, $commands->lable_enable_servicechecks);
		}


		if ($result->event_handler_enabled) {
			$commands->lable_enable_disable_event_handler = $type == 'host' ? _('Disable event handler for this host') : _('Disable event handler for this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_EVENT_HANDLER') : nagioscmd::command_id('DISABLE_SVC_EVENT_HANDLER');
			$commands->link_enable_disable_event_handler = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_event_handler);
		} else {
			$commands->lable_enable_disable_event_handler = $type == 'host' ? _('Enable event handler for this host') : _('Enable event handler for this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_EVENT_HANDLER') : nagioscmd::command_id('ENABLE_SVC_EVENT_HANDLER');
			$commands->link_enable_disable_event_handler = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_event_handler);
		}

		if ($result->flap_detection_enabled) {
			$commands->lable_enable_disable_flapdetection = $type == 'host' ? _('Disable flap detection for this host') : _('Disable flap detection for this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_FLAP_DETECTION') : nagioscmd::command_id('DISABLE_SVC_FLAP_DETECTION');
			$commands->link_enable_disable_flapdetection = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_flapdetection);
		} else {
			$commands->lable_enable_disable_flapdetection = $type == 'host' ? _('Enable flap detection for this host') : _('Enable flap detection for this service');
			$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_FLAP_DETECTION') : nagioscmd::command_id('ENABLE_SVC_FLAP_DETECTION');
			$commands->link_enable_disable_flapdetection = $this->command_link($cmd, $host, $service, $commands->lable_enable_disable_flapdetection);
		}

		# create page links
		switch ($type) {
			case 'host':
				$label_view_for = _('for this host');
				$page_links = array(
					 _('Status detail') => 'status/service/?name='.urlencode($host),
					 _('Alert history') => 'showlog/alert_history/'.$host,
					 _('Trends') => 'trends/host/'.$host,
					 _('Alert histogram') => 'histogram/host/'.$host,
					 _('Availability report') => Kohana::config('reports.reports_link').'/generate/?type=avail&host_name[]='.$host,
					 _('Notifications') => '/notifications/host/'.$host
				);
				break;
			case 'service':
				$label_view_for = _('for this service');
				$page_links = array(
					_('Information for this host') => 'extinfo/details/host/'.$host,
					_('Status detail for this host') => 'status/service/'.$host,
					_('Alert history') => 'showlog/alert_history/'.$host.'?service='.urlencode($service),
					_('Trends') => 'trends/host/'.$host.'?service='.urlencode($service),
					_('Alert histogram') => 'histogram/host/'.$host.'?service='.urlencode($service),
					_('Availability report') => Kohana::config('reports.reports_link').'/generate/?type=avail&service_description[]='.$host.';'.urlencode($service).'&report_type=services',
					_('Notifications') => '/notifications/host/'.$host.'?service='.urlencode($service)
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
		$auth = Nagios_auth_Model::instance();
		if (!$auth->authorized_for_system_information) {
			url::redirect('extinfo/unauthorized/0');
		}

		$this->template->content = $this->add_view('extinfo/process_info');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->template->title = _('Monitoring » Process info');

		# save us some typing
		$content = $this->template->content;

		# check if nagios is running, will affect wich template to use
		$status = Program_status_Model::get_local();
		$is_running = empty($status) || count($status)==0 ? false : $status->current()->is_running;
		if (!$is_running) {
			$this->template->content->commands = $this->add_view('extinfo/not_running');
			$this->template->content->commands->info_message = sprintf(_('It appears as though %s is not running, so commands are temporarily unavailable...'), Kohana::config('config.product_name'));

			# check if nagios_check_command is defined in cgi.cfg
			$cgi_config = System_Model::parse_config_file('/cgi.cfg');
			$nagios_check_command = false;
			if (!empty($cgi_config)) {
				$nagios_check_command = isset($cgi_config['nagios_check_command']) ? $cgi_config['nagios_check_command'] : false;
			}
			$info_message = '';
			if (empty($nagios_check_command)) {
				$info_message = _('Hint: It looks as though you have not defined a command for checking the process state by supplying a value for the <b>nagios_check_command</b> option in the CGI configuration file');
			}
			$this->template->content->commands->info_message_extra = $info_message;
		} else {
			$this->template->content->commands = $this->add_view('extinfo/nagios_commands');
		}

		# instance_name = NULL
		# instance_id = NULL

		$commands = $this->template->content->commands;


		# Lables to translate
		$na_str = _('N/A');
		$yes = _('YES');
		$no = _('NO');
		$content->lable_pid = sprintf(_('%s PID'), Kohana::config('config.product_name'));

		$date_format_str = nagstat::date_format();
		$content->date_format_str = $date_format_str;

		# fetch program status from program_status_model
		# uses ORM
		$status_res = Program_status_Model::get_local();

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
		$content->eventhandler_class = $content->enable_event_handlers ? 'checksENABLED' : 'checksDISABLED';
		$content->eventhandler_str = $content->enable_event_handlers ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->obsess_services_class = $content->obsess_over_services ? 'checksENABLED' : 'checksDISABLED';
		$content->obsess_services_str = $content->obsess_over_services ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->obsess_host_class = $content->obsess_over_hosts ? 'checksENABLED' : 'checksDISABLED';
		$content->obsess_hosts_str = $content->obsess_over_hosts ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->flap_detection_class = $content->flap_detection_enabled ? 'checksENABLED' : 'checksDISABLED';
		$content->flap_detection_str = $content->flap_detection_enabled ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->performance_data_class = $content->process_performance_data ? 'checksENABLED' : 'checksDISABLED';
		$content->performance_data_str = $content->process_performance_data ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));

		# Assign commands variables
		$commands->title = _('Process Commands');
		$commands->label_shutdown_nagios = sprintf(_('Shutdown the %s process'), Kohana::config('config.product_name'));
		$commands->link_shutdown_nagios = $this->command_link(nagioscmd::command_id('SHUTDOWN_PROCESS'), false, false, $commands->label_shutdown_nagios);
		$commands->label_restart_nagios = sprintf(_('Restart the %s process'), Kohana::config('config.product_name'));
		$commands->link_restart_nagios = $this->command_link(nagioscmd::command_id('RESTART_PROCESS'), false, false, $commands->label_restart_nagios);

		if ($content->notifications_enabled) {
			$commands->label_notifications = _('Disable notifications');
			$commands->link_notifications = $this->command_link(nagioscmd::command_id('DISABLE_NOTIFICATIONS'), false, false, $commands->label_notifications);
		} else {
			$commands->label_notifications = _('Enable notifications');
			$commands->link_notifications = $this->command_link(nagioscmd::command_id('ENABLE_NOTIFICATIONS'), false, false, $commands->label_notifications);
		}

		if ($content->execute_service_checks) {
			$commands->label_execute_service_checks = _('Stop executing service checks');
			$commands->link_execute_service_checks = $this->command_link(nagioscmd::command_id('STOP_EXECUTING_SVC_CHECKS'), false, false, $commands->label_execute_service_checks);
		} else {
			$commands->label_execute_service_checks = _('Start executing service checks');
			$commands->link_execute_service_checks = $this->command_link(nagioscmd::command_id('START_EXECUTING_SVC_CHECKS'), false, false, $commands->label_execute_service_checks);
		}

		if ($content->accept_passive_service_checks) {
			$commands->label_passive_service_checks = _('Stop accepting passive service checks');
			$commands->link_passive_service_checks = $this->command_link(nagioscmd::command_id('STOP_ACCEPTING_PASSIVE_SVC_CHECKS'), false, false, $commands->label_passive_service_checks);
		} else {
			$commands->label_passive_service_checks = _('Start accepting passive service checks');
			$commands->link_passive_service_checks = $this->command_link(nagioscmd::command_id('START_ACCEPTING_PASSIVE_SVC_CHECKS'), false, false, $commands->label_passive_service_checks);
		}

		if ($content->execute_host_checks) {
			$commands->label_execute_host_checks = _('Stop executing host checks');
			$commands->link_execute_host_checks = $this->command_link(nagioscmd::command_id('STOP_EXECUTING_HOST_CHECKS'), false, false, $commands->label_execute_host_checks);
		} else {
			$commands->label_execute_host_checks = _('Start executing host checks');
			$commands->link_execute_host_checks = $this->command_link(nagioscmd::command_id('START_EXECUTING_HOST_CHECKS'), false, false, $commands->label_execute_host_checks);
		}

		if ($content->accept_passive_host_checks) {
			$commands->label_accept_passive_host_checks = _('Stop accepting passive host checks');
			$commands->link_accept_passive_host_checks = $this->command_link(nagioscmd::command_id('STOP_ACCEPTING_PASSIVE_HOST_CHECKS'), false, false, $commands->label_accept_passive_host_checks);
		} else {
			$commands->label_accept_passive_host_checks = _('Start accepting passive host checks');
			$commands->link_accept_passive_host_checks = $this->command_link(nagioscmd::command_id('START_ACCEPTING_PASSIVE_HOST_CHECKS'), false, false, $commands->label_accept_passive_host_checks);
		}

		if ($content->enable_event_handlers) {
			$commands->label_enable_event_handlers = _('Disable event handlers');
			$commands->link_enable_event_handlers = $this->command_link(nagioscmd::command_id('DISABLE_EVENT_HANDLERS'), false, false, $commands->label_enable_event_handlers);
		} else {
			$commands->label_enable_event_handlers = _('Enable event handlers');
			$commands->link_enable_event_handlers = $this->command_link(nagioscmd::command_id('ENABLE_EVENT_HANDLERS'), false, false, $commands->label_enable_event_handlers);
		}

		if ($content->obsess_over_services) {
			$commands->label_obsess_over_services = _('Stop obsessing over services');
			$commands->link_obsess_over_services = $this->command_link(nagioscmd::command_id('STOP_OBSESSING_OVER_SVC_CHECKS'), false, false, $commands->label_obsess_over_services);
		} else {
			$commands->label_obsess_over_services = _('Start obsessing over services');
			$commands->link_obsess_over_services = $this->command_link(nagioscmd::command_id('START_OBSESSING_OVER_SVC_CHECKS'), false, false, $commands->label_obsess_over_services);
		}

		if ($content->obsess_over_hosts) {
			$commands->label_obsess_over_hosts = _('Stop obsessing over hosts');
			$commands->link_obsess_over_hosts = $this->command_link(nagioscmd::command_id('STOP_OBSESSING_OVER_HOST_CHECKS'), false, false, $commands->label_obsess_over_hosts);
		} else {
			$commands->label_obsess_over_hosts = _('Start obsessing over hosts');
			$commands->link_obsess_over_hosts = $this->command_link(nagioscmd::command_id('START_OBSESSING_OVER_HOST_CHECKS'), false, false, $commands->label_obsess_over_hosts);
		}

		if ($content->flap_detection_enabled) {
			$commands->label_flap_detection_enabled = _('Disable flap detection');
			$commands->link_flap_detection_enabled = $this->command_link(nagioscmd::command_id('DISABLE_FLAP_DETECTION'), false, false, $commands->label_flap_detection_enabled);
		} else {
			$commands->label_flap_detection_enabled = _('Enable flap detection');
			$commands->link_flap_detection_enabled = $this->command_link(nagioscmd::command_id('ENABLE_FLAP_DETECTION'), false, false, $commands->label_flap_detection_enabled);
		}

		if ($content->process_performance_data) {
			$commands->label_process_performance_data = _('Disable performance data');
			$commands->link_process_performance_data = $this->command_link(nagioscmd::command_id('DISABLE_PERFORMANCE_DATA'), false, false, $commands->label_process_performance_data);
		} else {
			$commands->label_process_performance_data = _('Enable performance data');
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

		$this->template->content->error_description = _('If you believe this is an error, check the HTTP server authentication requirements for accessing this page and check the authorization options in your CGI configuration file.');
		switch ($type) {
			case 'host':
				$this->template->content->error_message = _('It appears as though you do not have permission to view information for this host or it doesn\'t exist...');
				break;
			case 'hostgroup':
				$this->template->content->error_message = _('It appears as though you do not have permission to view information for this hostgroup or it doesn\'t exist...');
				break;
			case 'servicegroup':
				$this->template->content->error_message = _('It appears as though you do not have permission to view information for this servicegroup or it doesn\'t exist...');
				break;
			case 'service':
				$this->template->content->error_message = _('It appears as though you do not have permission to view information for this service or it doesn\'t exist...');
				break;
			default:
				$this->template->content->error_message = _('It appears as though you do not have permission to view process information...');
		}
	}

	/**
	*	Display extinfo for host- and servicegroups
	*
	*/
	public function group_details($grouptype='servicegroup', $group=false)
	{
		$grouptype = $this->input->get('grouptype', $grouptype);
		$group = $this->input->get('group', $group);
		if (empty($group)) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = _("Error: No group name specified");
			return;
		}

		$this->template->title = _('Monitoring » Group detail');

		$authorized = false;
		switch ($grouptype) {
			case 'hostgroup':
				$authorized = Hostgroup_Model::check_group_access($group);
				break;
			case 'servicegroup':
				$authorized = Servicegroup_Model::check_group_access($group);
				break;
		}

		if (!$authorized) {
			url::redirect('extinfo/unauthorized/'.$grouptype);
		}

		$group_info_res = $grouptype == 'servicegroup' ?
			Servicegroup_Model::get($group) :
			Hostgroup_Model::get($group);

		if ($group_info_res === false) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = sprintf(_("The requested %s ('%s') wasn't found"), $grouptype, $group);
			return;
		} else {
			$group_info_res = $group_info_res->current();
		}

		# check if nagios is running, will affect wich template to use
		$status = Program_status_Model::get_local();
		if (empty($status) || !$status->current()->is_running) {
			$this->template->content = $this->add_view('extinfo/not_running');
			$this->template->content->info_message = sprintf(_('It appears as though %s is not running, so commands are temporarily unavailable...'), Kohana::config('config.product_name'));
			$this->template->content->info_message_extra = sprintf(_('Click %s to view %s process information'), html::anchor('extinfo/show_process_info', html::specialchars(_('here'))), Kohana::config('config.product_name'));
			return;
		} else {
			$this->template->content = $this->add_view('extinfo/groups');
		}

		$content = $this->template->content;

		$content->label_grouptype = $grouptype=='servicegroup' ? _('servicegroup') : _('hostgroup');
		$content->group_alias = $group_info_res->alias;
		$content->groupname = $group;
		$content->grouptype = $grouptype;
		$content->cmd_schedule_downtime_hosts = nagioscmd::command_id('SCHEDULE_'.strtoupper($grouptype).'_HOST_DOWNTIME');
		$content->cmd_schedule_downtime_services = nagioscmd::command_id('SCHEDULE_'.strtoupper($grouptype).'_SVC_DOWNTIME');
		$content->cmd_enable_notifications_hosts = nagioscmd::command_id('ENABLE_'.strtoupper($grouptype).'_HOST_NOTIFICATIONS');
		$content->cmd_disable_notifications_hosts = nagioscmd::command_id('DISABLE_'.strtoupper($grouptype).'_HOST_NOTIFICATIONS');
		$content->cmd_disable_notifications_services = nagioscmd::command_id('DISABLE_'.strtoupper($grouptype).'_SVC_NOTIFICATIONS');
		$content->cmd_enable_notifications_services = nagioscmd::command_id('ENABLE_'.strtoupper($grouptype).'_SVC_NOTIFICATIONS');
		$content->cmd_disable_active_svc_checks = nagioscmd::command_id('DISABLE_'.strtoupper($grouptype).'_SVC_CHECKS');
		$content->cmd_enable_active_svc_checks = nagioscmd::command_id('ENABLE_'.strtoupper($grouptype).'_SVC_CHECKS');

		$content->cmd_disable_active_host_checks = nagioscmd::command_id('DISABLE_'.strtoupper($grouptype).'_HOST_CHECKS');
		$content->cmd_enable_active_host_checks = nagioscmd::command_id('ENABLE_'.strtoupper($grouptype).'_HOST_CHECKS');

		$content->notes_url = $group_info_res->notes_url !='' ? nagstat::process_macros($group_info_res->notes_url, $group_info_res) : false;
		$content->action_url =$group_info_res->action_url !='' ? nagstat::process_macros($group_info_res->action_url, $group_info_res) : false;
		$content->notes = $group_info_res->notes !='' ? nagstat::process_macros($group_info_res->notes, $group_info_res) : false;

		switch ($grouptype) {
			case 'servicegroup':
				$label_view_for = _('for this servicegroup');
				$page_links = array(
					_('Status detail') => 'status/service/'.$group.'?group_type='.$grouptype,
					_('Status overview') => 'status/'.$grouptype.'/'.$group,
					_('Status grid') => 'status/'.$grouptype.'_grid/'.$group,
					_('Availability') => Kohana::config('reports.reports_link').'/generate/?type=avail&report_type='.$grouptype.'s&'.$grouptype.'[]='.$group,
					_('Alert history') => 'showlog/alert_history?'.$grouptype.'='.$group
				);
				break;
			case 'hostgroup':
				$label_view_for = _('for this hostgroup');
				$page_links = array(
					_('Status detail') => 'status/service/'.$group.'?group_type='.$grouptype,
					_('Status overview') => 'status/'.$grouptype.'/'.$group,
					_('Status grid') => 'status/'.$grouptype.'_grid/'.$group,
					_('Availability') => Kohana::config('reports.reports_link').'/generate/?type=avail&report_type='.$grouptype.'s&'.$grouptype.'[]='.$group,
					_('Alert history') => 'showlog/alert_history?'.$grouptype.'='.$group
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
		$items_per_page = !empty($items_per_page) ? $items_per_page : config::get('pagination.default.items_per_page', '*');
		$host = trim($host);
		$service = trim($service);
		$type = trim($type);
		if (empty($all) && empty($host)) {
			return false;
		}

		$handling_deletes = false;
		$command_success = false;
		$command_result_msg = false;
		if (!empty($_POST) && (!empty($_POST['del_comment']) || !empty($_POST['del_downtime'])
			|| !empty($_POST['del_comment_host']) || !empty($_POST['del_comment_service']))) {
			$handling_deletes = true;
			$comment_cmd = false;
			$downtime_cmd = false;
			$nagios_commands = array();
			# bulk delete of comments?
			if (isset($_POST['del_submithost'])) {
				# host comments
				$comment_cmd = 'DEL_HOST_COMMENT';
				$downtime_cmd = 'DEL_HOST_DOWNTIME';
			} elseif (isset($_POST['del_submitservice'])) {
				# service comments
				$comment_cmd = 'DEL_SVC_COMMENT';
				$downtime_cmd = 'DEL_SVC_DOWNTIME';
			}


			if (isset($_POST['del_comment'])) {
				if (!Command_Controller::_is_authorized_for_command(array('cmd_typ' => $comment_cmd))) {
					url::redirect('command/unauthorized');
				}
				foreach ($_POST['del_comment'] as $param) {
					$nagios_commands = Command_Controller::_build_command($comment_cmd, array('comment_id' => $param), $nagios_commands);
				}
			}

			# delete host comments from search result
			if (isset($_POST['del_comment_host'])) {
				$comment_cmd = 'DEL_HOST_COMMENT';
				if (!Command_Controller::_is_authorized_for_command(array('cmd_typ' => $comment_cmd))) {
					url::redirect('command/unauthorized');
				}
				foreach ($_POST['del_comment_host'] as $param) {
					$nagios_commands = Command_Controller::_build_command($comment_cmd, array('comment_id' => $param), $nagios_commands);
				}
			}
			# delete service comments from search result
			if (isset($_POST['del_comment_service'])) {
				$comment_cmd = 'DEL_SVC_COMMENT';
				if (!Command_Controller::_is_authorized_for_command(array('cmd_typ' => $comment_cmd))) {
					url::redirect('command/unauthorized');
				}
				foreach ($_POST['del_comment_service'] as $param) {
					$nagios_commands = Command_Controller::_build_command($comment_cmd, array('comment_id' => $param), $nagios_commands);
				}
			}

			if (isset($_POST['del_downtime'])) {
				if (!Command_Controller::_is_authorized_for_command(array('cmd_typ' => $downtime_cmd))) {
					url::redirect('command/unauthorized');
				}
				foreach ($_POST['del_downtime'] as $param) {
					$nagios_commands = Command_Controller::_build_command($downtime_cmd, array('downtime_id' => $param), $nagios_commands);
				}
			}

			$nagios_base_path = Kohana::config('config.nagios_base_path');
			$pipe = $nagios_base_path."/var/rw/nagios.cmd";
			$nagconfig = System_Model::parse_config_file("nagios.cfg");
			if (isset($nagconfig['command_file'])) {
				$pipe = $nagconfig['command_file'];
			}

			while ($ncmd = array_pop($nagios_commands)) {
				$command_success = nagioscmd::submit_to_nagios($ncmd, $pipe);
			}

			if ($command_success === true) {
				# everything was ok
				$command_result_msg = sprintf(_('Your commands were successfully submitted to %s.'),
					Kohana::config('config.product_name'));
			} else {
				# errors encountered
				$command_result_msg = sprintf(_('There was an error submitting one or more of your commands to %s.'),
					Kohana::config('config.product_name'));
			}

			$redirect = arr::search($_REQUEST, 'redirect_page');
			if (empty($redirect)) {
				$_SESSION['command_result_msg'] = $command_result_msg;
				$_SESSION['command_success'] = $command_success;

				# reload controller to prevent it from trying to submit
				# the POST data on refresh
				url::redirect(Router::$controller.'/'.Router::$method);
			} else {
				url::redirect($redirect);
			}
		}

		$command_result_msg = $this->session->get('error_msg', $command_result_msg);
		$command_success = $this->session->get('error_msg', $command_success);

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
		$schedule_downtime_comments = $all ? Downtime_Model::fetch_all_comments($host, $service, $items_per_page, $offset) : Downtime_Model::fetch_comments($host, $service, $items_per_page, $offset);;

		$comment = false;
		$i = 0;

		$comment_type = 'comment';
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
				$comment[$i]['comment'] = $row->comment_data;
				$comment[$i]['comment_type'] = $comment_type;
				$i++;
			}
		}
		else {
			$comment = $comment_data;
		}

		$comment_type = 'downtime';
		if (!empty($schedule_downtime_comments) && count($schedule_downtime_comments) > 0) {
			foreach ($schedule_downtime_comments as $row) {
				if (empty($row->comment_data)) {
					continue;
				}
				$comment[$i]['host_name'] = $row->host_name;
				if (isset($row->service_description))
					$comment[$i]['service_description'] = $row->service_description;
				$comment[$i]['entry_time'] = $row->entry_time;
				$comment[$i]['author_name'] = $row->author_name;
				$comment[$i]['entry_time'] = $row->entry_time;
				$comment[$i]['comment_id'] = $row->downtime_id;
				$comment[$i]['persistent'] = false;
				$comment[$i]['entry_type'] = $row->downtime_type;
				$comment[$i]['expires'] = false;
				$comment[$i]['expire_time'] = false;
				$comment[$i]['comment'] = $row->comment_data;
				$comment[$i]['comment_type'] = $comment_type;
				$i++;
			}
		}

		if (!$all && is_array($comment)) {
			array_multisort($comment, SORT_ASC, SORT_REGULAR, $comment);
		}

		$filter_string = _('Enter text to filter');

		$this->js_strings .= "var _filter_label = '".$filter_string."';";
		$this->template->js_strings = $this->js_strings;

		$this->template->content->comments = $this->add_view('extinfo/comments');
		if (!is_array($this->xtra_js) || !in_array($this->add_path('extinfo/js/extinfo.js'), $this->xtra_js)) {
			$this->template->js_header = $this->add_view('js_header');
			$this->xtra_js[] = 'application/media/js/jquery.tablesorter.min.js';
			$this->xtra_js[] = $this->add_path('extinfo/js/extinfo.js');
			$this->template->js_header->js = $this->xtra_js;
		}

		$comments = $this->template->content->comments;
		$comments->filter_string = $filter_string;
		$comments->label_add_comment = $service ? _('Add a new service comment') : _('Add a new host comment');
		$comments->cmd_add_comment =
			$type=='host' ? nagioscmd::command_id('ADD_HOST_COMMENT')
			: nagioscmd::command_id('ADD_SVC_COMMENT');
		$comments->cmd_delete_all_comments =
			$type=='host' ? nagioscmd::command_id('DEL_ALL_HOST_COMMENTS')
			: nagioscmd::command_id('DEL_ALL_SVC_COMMENTS');
		$comments->host = $host;
		$comments->label_title = $type == 'host' ? _('Host Comments') : _('Service Comments');
		$comments->service = $service;

		$comments->data = $comment;
		$nagios_config = System_Model::parse_config_file('nagios.cfg');
		$comments->cmd_delete_comment =
			$type=='host' ? nagioscmd::command_id('DEL_HOST_COMMENT')
			: nagioscmd::command_id('DEL_SVC_COMMENT');
		$comments->cmd_delete_downtime =
			$type=='host' ? nagioscmd::command_id('DEL_HOST_DOWNTIME')
			: nagioscmd::command_id('DEL_SVC_DOWNTIME');

		$comments->date_format_str = nagstat::date_format($nagios_config['date_format']);
		$comments->no_data = $all ? _('No comments found') : sprintf(_('This %s has no comments associated with it'), $type);
		$comments->pagination = $pagination;
		$this->template->title = _(sprintf('Monitoring » %s information', ucfirst($type)));
		$comments->command_result = arr::search($_SESSION, 'command_result_msg');
		$comments->command_success = arr::search($_SESSION, 'command_success');
		unset($_SESSION['command_result_msg']);
		unset($_SESSION['command_success']);
		return $this->template->content->comments->render();
	}

	/**
	*	Show all comments for hosts and services
	*/
	public function show_comments()
	{
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.default.items_per_page', '*'));
		$this->template->content = $this->add_view('extinfo/all_comments');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->template->content->host_comments = $this->_comments(true, false, 'host', true, $items_per_page);
		$this->template->content->service_comments = $this->_comments(true, true, 'service', true, $items_per_page);
		$this->template->title = _('Monitoring » All comments');
	}

	/**
	*	Show Program-Wide Performance Information
	*	(Performance Info)
	*/
	public function performance()
	{
		$this->template->content = $this->add_view('extinfo/performance');
		$this->template->title = _('Monitoring').' » '._('Performance info');
		$this->template->js_header = $this->add_view('js_header');
		$content = $this->template->content;
		$service_model = new Service_Model();
		$host_model = new Host_Model();

		$content->title = _("Program-wide performance information");

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
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.default.items_per_page', '*'));
		$back_link = '/extinfo/scheduling_queue/';

		$sq_model = new Scheduling_queue_Model($items_per_page, true, true);
		$sq_model->sort_order = $this->input->get('sort_order', $sort_order);
		$sq_model->sort_field = $this->input->get('sort_field', $sort_field);

		$auth = Nagios_auth_Model::instance();
		if (!$auth->view_hosts_root) {
			url::redirect('extinfo/unauthorized/scheduling_queue');
		}

		$host_qry = false;
		$svc_qry = false;
		$search_active = false;
		if (arr::search($_REQUEST, 'host_name')) {
			$sq_model->set_host_search_term($_REQUEST['host_name']);
			$search_active = true;
		}
		if (arr::search($_REQUEST, 'service')) {
			$sq_model->set_service_search_term($_REQUEST['service']);
			$search_active = true;
		}

		$pagination = new Pagination(
			array(
				'total_items'=> $sq_model->count_queue(),
				'items_per_page' => $items_per_page
			)
		);

		$sq_model->offset = $pagination->sql_offset;
		$result = $sq_model->show_scheduling_queue($items_per_page, $pagination->sql_offset);

		$header_links = array(
			array(
				'title' => _('Host'),
				'url_asc' => Router::$controller.'/'.Router::$method.'?sort_order=ASC&amp;sort_field=host_name',
				'url_desc' => Router::$controller.'/'.Router::$method.'?sort_order=DESC&amp;sort_field=host_name',
			),
			array(
				'title' => _('Service'),
				'url_asc' => Router::$controller.'/'.Router::$method.'?sort_order=ASC&amp;sort_field=service_description',
				'url_desc' => Router::$controller.'/'.Router::$method.'?sort_order=DESC&amp;sort_field=service_description',
			),
			array(
				'title' => _('Last check'),
				'url_asc' => Router::$controller.'/'.Router::$method.'?sort_order=ASC&amp;sort_field=last_check',
				'url_desc' => Router::$controller.'/'.Router::$method.'?sort_order=DESC&amp;sort_field=last_check',
			),
			array(
				'title' => _('Next check'),
				'url_asc' => Router::$controller.'/'.Router::$method.'?sort_order=ASC&amp;sort_field=next_check',
				'url_desc' => Router::$controller.'/'.Router::$method.'?sort_order=DESC&amp;sort_field=next_check',
			)
		);

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = $this->add_path('extinfo/js/extinfo.js');
		$this->xtra_js[] = 'application/media/js/jquery.tablesorter.min.js';
		$filter_string = _('Enter text to filter');
		$this->js_strings .= "var _filter_label = '".$filter_string."';";
		$this->template->js_strings = $this->js_strings;

		$this->template->js_header->js = $this->xtra_js;

		$this->template->title = _('Monitoring').' » '._('Scheduling queue');
		$this->template->content = $this->add_view('extinfo/scheduling_queue');
		$this->template->content->data = $result;
		$this->template->content->search_active = $search_active;
		$this->template->content->filter_string = $filter_string;
		$this->template->content->back_link = $back_link;
		$this->template->content->header_links = $header_links;
		$this->template->content->pagination = isset($pagination) ? $pagination : false;
		$this->template->content->date_format_str = nagstat::date_format();
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

		$handling_commands = false;
		$command_success = false;
		$command_result_msg = false;
		if (!empty($_POST) && (!empty($_POST['del_host']) || !empty($_POST['del_service']))) {
			$handling_commands = true;
			$cmd = false;
			$nagios_commands = array();
			# bulk delete of comments?
			if (isset($_POST['del_submithost']) || isset($_POST['del_submithost_svc'])) {
				# host comments
				$cmd = 'DEL_HOST_DOWNTIME';
				foreach ($_POST['del_host'] as $param) {
					$nagios_commands = Command_Controller::_build_command($cmd, array('downtime_id' => $param), $nagios_commands);
				}

				if (!Command_Controller::_is_authorized_for_command(array('cmd_typ' => $cmd))) {
					url::redirect('command/unauthorized');
				}

				if (isset($_POST['del_submithost_svc']) && !empty($_POST['del_service'])) {
					# service comments
					$cmd = 'DEL_SVC_DOWNTIME';
					if (!Command_Controller::_is_authorized_for_command(array('cmd_typ' => $cmd))) {
						url::redirect('command/unauthorized');
					}
					foreach ($_POST['del_service'] as $param) {
						$nagios_commands = Command_Controller::_build_command($cmd, array('downtime_id' => $param), $nagios_commands);
					}
				}

			} elseif (isset($_POST['del_submitservice'])) {
				# service comments
				$cmd = 'DEL_SVC_DOWNTIME';
				foreach ($_POST['del_service'] as $param) {
					$nagios_commands = Command_Controller::_build_command($cmd, array('downtime_id' => $param), $nagios_commands);
				}

				if (!Command_Controller::_is_authorized_for_command(array('cmd_typ' => $cmd))) {
					url::redirect('command/unauthorized');
				}

			}

			$nagios_base_path = Kohana::config('config.nagios_base_path');
			$pipe = $nagios_base_path."/var/rw/nagios.cmd";
			$nagconfig = System_Model::parse_config_file("nagios.cfg");
			if (isset($nagconfig['command_file'])) {
				$pipe = $nagconfig['command_file'];
			}

			while ($ncmd = array_pop($nagios_commands)) {
				$command_success = nagioscmd::submit_to_nagios($ncmd, $pipe);
			}

			if ($command_success === true) {
				# everything was ok
				$command_result_msg = sprintf(_('Your commands were successfully submitted to %s.'),
					Kohana::config('config.product_name'));
			} else {
				# errors encountered
				$command_result_msg = sprintf(_('There was an error submitting one or more of your commands to %s.'),
					Kohana::config('config.product_name'));
			}

			$_SESSION['command_result_msg'] = $command_result_msg;
			$_SESSION['command_success'] = $command_success;

			# reload controller to prevent it from trying to submit
			# the POST data on refresh
			url::redirect(Router::$controller.'/'.Router::$method);
		}

		$command_result_msg = $this->session->get('error_msg', $command_result_msg);
		$command_success = $this->session->get('error_msg', $command_success);



		$host_title_str = _('Scheduled host downtime');
		$service_title_str = _('Scheduled service downtime');
		$title = _('Scheduled downtime');
		$type_str = false;
		$host_data = false;
		$service_data = false;

		switch ($downtime_type) {
			case nagstat::HOST_DOWNTIME:
				$type_str = $types[$downtime_type];
				$host_data = Downtime_Model::get_downtime_data($downtime_type, 'downtime_type DESC', true);
				break;
			case nagstat::SERVICE_DOWNTIME:
				$type_str = $types[$downtime_type];
				$service_data = Downtime_Model::get_downtime_data($downtime_type, 'downtime_type DESC', true);
				break;
			case nagstat::ANY_DOWNTIME:
				$host_data = Downtime_Model::get_downtime_data(nagstat::HOST_DOWNTIME, 'downtime_type DESC', true);
				$service_data = Downtime_Model::get_downtime_data(nagstat::SERVICE_DOWNTIME, 'downtime_type DESC', true);
				break;
		}
		$this->template->content = $this->add_view('extinfo/scheduled_downtime');
		$content = $this->template->content;

		$this->template->js_header = $this->add_view('js_header');
#		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_js[] = $this->add_path('extinfo/js/extinfo.js');
		$this->xtra_js[] = 'application/media/js/jquery.tablesorter.min.js';
		$filter_string = _('Enter text to filter');
		$this->js_strings .= "var _filter_label = '".$filter_string."';";
		$this->template->js_strings = $this->js_strings;

		$this->template->js_header->js = $this->xtra_js;

		$content->title = $title;
		$content->filter_string = $filter_string;
		$content->fixed = _('Fixed');
		$content->flexible = _('Flexible');
		$content->host_link_text = _('Schedule host downtime');
		$content->service_link_text = _('Schedule service downtime');
		$content->link_titlestring = _('Delete/cancel this scheduled downtime entry');
		$content->date_format = nagstat::date_format();
		$content->host_data = $host_data;
		$content->host_title_str = $host_title_str;

		$content->service_data = $service_data;
		$content->service_title_str = $service_title_str;
		$this->template->title = _("Monitoring » Scheduled downtime");
		$content->command_result = arr::search($_SESSION, 'command_result_msg');
		$content->command_success = arr::search($_SESSION, 'command_success');
		unset($_SESSION['command_result_msg']);
		unset($_SESSION['command_success']);


	}
}
