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
			$host = getparams::get_raw_param('host', $host);
			$service = getparams::get_raw_param('service', $service);
			$hostgroup = getparams::get_raw_param('hostgroup', false);
			$servicegroup = getparams::get_raw_param('servicegroup', false);
		} else {
			$host = $this->input->get('host', $host);
			$service = $this->input->get('service', $service);
			$hostgroup = $this->input->get('hostgroup', false);
			$servicegroup = $this->input->get('servicegroup', false);
		}

		$this->template->title = 'Monitoring » Extinfo';

		# load current status for host/service status totals
		$this->current = new Current_status_Model();

		$host = trim($host);
		$service = trim($service);
		$hostgroup = trim($hostgroup);
		$servicegroup = trim($servicegroup);

		$ls = Livestatus::instance();
		if(!empty($host) && empty($service)) {
			$type='host';
			$result_data = $ls->getHosts(array('filter' => array('name' => $host), 'extra_columns' => array('contact_groups')));
		}
		else if(!empty($host) && !empty($service)) {
			$type='service';
			$result_data = $ls->getServices(array('filter' => array('host_name' => $host, 'description' => $service), 'extra_columns' => array('contact_groups')));
		}
		else if(!empty($hostgroup)) {
			return $this->group_details('hostgroup', $hostgroup);
		}
		else if(!empty($servicegroup)) {
			return $this->group_details('servicegroup', $servicegroup);
		}
		else {
			return false;
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
		
		if (count($result_data) === 0) {
			return url::redirect('extinfo/unauthorized/'.$type);
		}
		$result = (object)$result_data[0];
		
		/* TODO: implement */
		switch($type) {
		/*
			case 'host':
				$content->custom_variables = Custom_variable_Model::get_for($type, $result->id);
				break;
			case 'service':
				$content->custom_variables = Custom_variable_Model::get_for($type, $result->service_id);
				break;
		*/
			default:
				$content->custom_variables = array();

		}
		$host_link = false;
		$yes = _('YES');
		$no = _('NO');
		
		$content->contactgroups = isset($result->contact_groups)?$result->contact_groups:false;
		$is_pending = false;
		$back_link = false;
		$content->parents = false;

		if ($type == 'host') {
			#$group_info = Group_Model::get_groups_for_object($type, $result->id);
			$content->title = _('Host State Information');
			$content->no_group_lable = _('No hostgroups');
			$check_compare_value = Current_status_Model::HOST_CHECK_ACTIVE;
			$last_notification = $result->last_notification;
			$content->lable_next_scheduled_check = _('Next scheduled active check');
			$content->lable_flapping = _('Is this host flapping?');
			$obsessing = $result->obsess;
			$content->notes = $result->notes !='' ? nagstat::process_macros($result->notes, $result) : false;

			# check for parents
			#$host_obj = new Host_Model();
			#$parents = $host_obj->get_parents($host);
			#if (count($parents)) {
		#		$content->parents = $parents;
		#	}

			$back_link = '/extinfo/details/?host='.urlencode($host);
			if ($result->state == Current_status_Model::HOST_PENDING ) {
				$is_pending = true;
				$message_str = _('This host has not yet been checked, so status information is not available.');
			}
		} else {
			#$group_info = Group_Model::get_groups_for_object($type, $result->service_id);
			$content->title = _('Service State Information');
			$content->no_group_lable = _('No servicegroups');
			$content->lable_next_scheduled_check = _('Next scheduled check');
			$host_link = html::anchor('extinfo/details/?host='.urlencode($host), html::specialchars($host));
			$back_link = '/extinfo/details/service/?host='.urlencode($host).'&service='.urlencode($service);
			$check_compare_value = Current_status_Model::SERVICE_CHECK_ACTIVE;
			$last_notification = $result->last_notification;
			$content->lable_flapping = _('Is this service flapping?');
			$obsessing = $result->obsess;
			$content->notes = $result->notes_expanded;
			if ($result->state == Current_status_Model::SERVICE_PENDING ) {
				$is_pending = true;
				$message_str = _('This service has not yet been checked, so status information is not available.');
			}
		}

		$content->notes_url = $result->notes_url_expanded;
		$content->action_url = $result->action_url_expanded;

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

		if($result->pnpgraph_present) {
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
		if(count($result->groups) > 0) {
			foreach ($result->groups as $group) {
				$groups[] = html::anchor(sprintf("status/%sgroup/%s", $type, urlencode($group)),
					html::specialchars($group));
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
		$content->current_status_str = $this->current->status_text($result->state, $result->has_been_checked, $type);
		$content->duration = $result->duration;
		$content->groups = $groups;
		$content->host_address = $type == 'host' ? $result->address : $result->host_address;
		$content->icon_image = $result->icon_image;
		$content->icon_image_alt = $result->icon_image_alt;
		// "Why the str_replace, it looks stupid?" Well, because nagios (livestatus?) stores data with newlines replaced with a backslash and an 'n'.
		// "So why the nl2br, then, huh?" Uhm, it was there when I found it...
		$content->status_info = htmlspecialchars($result->plugin_output).'<br />'.str_replace('\n', '<br />', nl2br(htmlspecialchars($result->long_plugin_output)));
		$content->lable_perf_data = _('Performance data');
		$content->perf_data = $result->perf_data;
		$content->lable_current_attempt = _('Current attempt');
		$content->current_attempt = $result->current_attempt;
		$content->state_type = $result->state_type ? _('HARD state') : _('SOFT state');
		$content->main_object_alias = $type=='host' ? $result->alias : false;
		$content->max_attempts = $result->max_check_attempts;
		$content->last_update = time();
		$content->last_check = $result->last_check;
		$content->lable_last_check = _('Last check time');
		$content->lable_check_type = _('Check type');

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
		$content->passive_checks_enabled = $result->accept_passive_checks ? $str_enabled : $str_disabled;
		$content->obsessing = $obsessing ? $str_enabled : $str_disabled;
		$content->notifications_enabled = $result->notifications_enabled ? $str_enabled : $str_disabled;
		$content->event_handler_enabled = $result->event_handler_enabled ? $str_enabled : $str_disabled;
		$content->flap_detection_enabled = $result->flap_detection_enabled ? $str_enabled : $str_disabled;

		if (Command_Controller::_is_authorized_for_command(array('host_name' => $host, 'service' => $service)) === true) {
			$this->template->content->commands = $this->add_view('extinfo/commands');
		} else {
			$this->template->content->commands = $this->add_view('extinfo/not_running');
			$this->template->content->commands->info_message = _("You're not authorized to run commands");
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

		if ($result->accept_passive_checks) {
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
			if ($result->state == Current_status_Model::HOST_DOWN || $result->state == Current_status_Model::HOST_UNREACHABLE) {
				$commands->show_ackinfo = true;
				# show acknowledge info
				if (!$result->acknowledged) {
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
			if (($result->state == Current_status_Model::SERVICE_WARNING || $result->state == Current_status_Model::SERVICE_UNKNOWN || $result->state == Current_status_Model::SERVICE_CRITICAL) && $result->state_type) {
				$commands->show_ackinfo = true;
				# show acknowledge info
				if (!$result->acknowledged) {
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
			if ($result->state != Current_status_Model::HOST_UP) {
				$commands->show_delay = true;
				$commands->lable_delay_notification = _('Delay next host notification');
				$commands->link_delay_notifications = $this->command_link(nagioscmd::command_id('DELAY_HOST_NOTIFICATION'),
				$host, false, $commands->lable_delay_notification);
			}
		} else {
			if ($result->notifications_enabled && $result->state != Current_status_Model::SERVICE_OK) {
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
					 _('Alert history') => 'alert_history/generate?host_name[]='.$host,
					 _('Alert histogram') => 'histogram/generate?host_name[]='.$host,
					 _('Availability report') => 'avail/generate/?host_name[]='.$host,
					 _('Notifications') => '/notifications/host/'.$host
				);
				break;
			case 'service':
				$label_view_for = _('for this service');
				$page_links = array(
					_('Information for this host') => 'extinfo/details/host/'.$host,
					_('Status detail for this host') => 'status/service/'.$host,
					_('Alert history') => 'alert_history/generate?service_description[]='.$host.';'.urlencode($service),
					_('Alert histogram') => 'histogram/generate?service_description[]='.$host.';'.urlencode($service),
					_('Availability report') => 'avail/generate/?service_description[]='.$host.';'.urlencode($service).'&report_type=services',
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
			$this->_comments($host, $service);
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
		if (!Auth::instance()->authorized_for('system_information')) {
			url::redirect('extinfo/unauthorized/0');
		}

		$this->template->content = $this->add_view('extinfo/process_info');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->template->title = _('Monitoring » Process info');

		# save us some typing
		$content = $this->template->content;

		# check if nagios is running, will affect wich template to use
/*
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
*/
			$this->template->content->commands = $this->add_view('extinfo/nagios_commands');
/*
		}
*/

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
		$status = Current_status_Model::instance()->program_status();

		$content->program_status = $status;
		$content->run_time = time::to_string(time() - $status->program_start);
		$content->program_start = date($date_format_str, $status->program_start);
		$content->last_log_rotation = $status->last_log_rotation ? date($date_format_str, $status->last_log_rotation) : 'never';

		$content->notifications_class = $status->enable_notifications ? 'notificationsENABLED' : 'notificationsDISABLED';
		$content->notifications_str = $status->enable_notifications ? $yes : $no;
		$content->servicechecks_class = $status->execute_service_checks ? 'checksENABLED' : 'checksDISABLED';
		$content->servicechecks_str = $status->execute_service_checks ? $yes : $no;
		$content->passive_servicechecks_class = $status->accept_passive_service_checks ? 'checksENABLED' : 'checksDISABLED';
		$content->passive_servicechecks_str = $status->accept_passive_service_checks ? $yes : $no;
		$content->hostchecks_class = $status->execute_host_checks ? 'checksENABLED' : 'checksDISABLED';
		$content->hostchecks_str = $status->execute_host_checks ? $yes : $no;
		$content->passive_hostchecks_class = $status->accept_passive_host_checks ? 'checksENABLED' : 'checksDISABLED';
		$content->passive_hostchecks_str = $status->accept_passive_host_checks ? $yes : $no;
		$content->eventhandler_class = $status->enable_event_handlers ? 'checksENABLED' : 'checksDISABLED';
		$content->eventhandler_str = $status->enable_event_handlers ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->obsess_services_class = $status->obsess_over_services ? 'checksENABLED' : 'checksDISABLED';
		$content->obsess_services_str = $status->obsess_over_services ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->obsess_host_class = $status->obsess_over_hosts ? 'checksENABLED' : 'checksDISABLED';
		$content->obsess_hosts_str = $status->obsess_over_hosts ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->flap_detection_class = $status->enable_flap_detection ? 'checksENABLED' : 'checksDISABLED';
		$content->flap_detection_str = $status->enable_flap_detection ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->performance_data_class = $status->process_performance_data ? 'checksENABLED' : 'checksDISABLED';
		$content->performance_data_str = $status->process_performance_data ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));

		# Assign commands variables
		$commands->title = _('Process Commands');
		$commands->label_shutdown_nagios = sprintf(_('Shutdown the %s process'), Kohana::config('config.product_name'));
		$commands->link_shutdown_nagios = $this->command_link(nagioscmd::command_id('SHUTDOWN_PROCESS'), false, false, $commands->label_shutdown_nagios);
		$commands->label_restart_nagios = sprintf(_('Restart the %s process'), Kohana::config('config.product_name'));
		$commands->link_restart_nagios = $this->command_link(nagioscmd::command_id('RESTART_PROCESS'), false, false, $commands->label_restart_nagios);

		if ($status->enable_notifications) {
			$commands->label_notifications = _('Disable notifications');
			$commands->link_notifications = $this->command_link(nagioscmd::command_id('DISABLE_NOTIFICATIONS'), false, false, $commands->label_notifications);
		} else {
			$commands->label_notifications = _('Enable notifications');
			$commands->link_notifications = $this->command_link(nagioscmd::command_id('ENABLE_NOTIFICATIONS'), false, false, $commands->label_notifications);
		}

		if ($status->execute_service_checks) {
			$commands->label_execute_service_checks = _('Stop executing service checks');
			$commands->link_execute_service_checks = $this->command_link(nagioscmd::command_id('STOP_EXECUTING_SVC_CHECKS'), false, false, $commands->label_execute_service_checks);
		} else {
			$commands->label_execute_service_checks = _('Start executing service checks');
			$commands->link_execute_service_checks = $this->command_link(nagioscmd::command_id('START_EXECUTING_SVC_CHECKS'), false, false, $commands->label_execute_service_checks);
		}

		if ($status->accept_passive_service_checks) {
			$commands->label_passive_service_checks = _('Stop accepting passive service checks');
			$commands->link_passive_service_checks = $this->command_link(nagioscmd::command_id('STOP_ACCEPTING_PASSIVE_SVC_CHECKS'), false, false, $commands->label_passive_service_checks);
		} else {
			$commands->label_passive_service_checks = _('Start accepting passive service checks');
			$commands->link_passive_service_checks = $this->command_link(nagioscmd::command_id('START_ACCEPTING_PASSIVE_SVC_CHECKS'), false, false, $commands->label_passive_service_checks);
		}

		if ($status->execute_host_checks) {
			$commands->label_execute_host_checks = _('Stop executing host checks');
			$commands->link_execute_host_checks = $this->command_link(nagioscmd::command_id('STOP_EXECUTING_HOST_CHECKS'), false, false, $commands->label_execute_host_checks);
		} else {
			$commands->label_execute_host_checks = _('Start executing host checks');
			$commands->link_execute_host_checks = $this->command_link(nagioscmd::command_id('START_EXECUTING_HOST_CHECKS'), false, false, $commands->label_execute_host_checks);
		}

		if ($status->accept_passive_host_checks) {
			$commands->label_accept_passive_host_checks = _('Stop accepting passive host checks');
			$commands->link_accept_passive_host_checks = $this->command_link(nagioscmd::command_id('STOP_ACCEPTING_PASSIVE_HOST_CHECKS'), false, false, $commands->label_accept_passive_host_checks);
		} else {
			$commands->label_accept_passive_host_checks = _('Start accepting passive host checks');
			$commands->link_accept_passive_host_checks = $this->command_link(nagioscmd::command_id('START_ACCEPTING_PASSIVE_HOST_CHECKS'), false, false, $commands->label_accept_passive_host_checks);
		}

		if ($status->enable_event_handlers) {
			$commands->label_enable_event_handlers = _('Disable event handlers');
			$commands->link_enable_event_handlers = $this->command_link(nagioscmd::command_id('DISABLE_EVENT_HANDLERS'), false, false, $commands->label_enable_event_handlers);
		} else {
			$commands->label_enable_event_handlers = _('Enable event handlers');
			$commands->link_enable_event_handlers = $this->command_link(nagioscmd::command_id('ENABLE_EVENT_HANDLERS'), false, false, $commands->label_enable_event_handlers);
		}

		if ($status->obsess_over_services) {
			$commands->label_obsess_over_services = _('Stop obsessing over services');
			$commands->link_obsess_over_services = $this->command_link(nagioscmd::command_id('STOP_OBSESSING_OVER_SVC_CHECKS'), false, false, $commands->label_obsess_over_services);
		} else {
			$commands->label_obsess_over_services = _('Start obsessing over services');
			$commands->link_obsess_over_services = $this->command_link(nagioscmd::command_id('START_OBSESSING_OVER_SVC_CHECKS'), false, false, $commands->label_obsess_over_services);
		}

		if ($status->obsess_over_hosts) {
			$commands->label_obsess_over_hosts = _('Stop obsessing over hosts');
			$commands->link_obsess_over_hosts = $this->command_link(nagioscmd::command_id('STOP_OBSESSING_OVER_HOST_CHECKS'), false, false, $commands->label_obsess_over_hosts);
		} else {
			$commands->label_obsess_over_hosts = _('Start obsessing over hosts');
			$commands->link_obsess_over_hosts = $this->command_link(nagioscmd::command_id('START_OBSESSING_OVER_HOST_CHECKS'), false, false, $commands->label_obsess_over_hosts);
		}

		if ($status->enable_flap_detection) {
			$commands->label_flap_detection_enabled = _('Disable flap detection');
			$commands->link_flap_detection_enabled = $this->command_link(nagioscmd::command_id('DISABLE_FLAP_DETECTION'), false, false, $commands->label_flap_detection_enabled);
		} else {
			$commands->label_flap_detection_enabled = _('Enable flap detection');
			$commands->link_flap_detection_enabled = $this->command_link(nagioscmd::command_id('ENABLE_FLAP_DETECTION'), false, false, $commands->label_flap_detection_enabled);
		}

		if ($status->process_performance_data) {
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

		$this->template->content->error_description = _('If you believe this is an error, check the authorization requirements for accessing this page and your given authorization points.');
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
		

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->js_strings .= "var _pnp_web_path = '".Kohana::config('config.pnp4nagios_path')."';\n";
		$this->template->js_strings = $this->js_strings;
		$this->xtra_js[] = $this->add_path('extinfo/js/extinfo.js');
		$this->template->js_header->js = $this->xtra_js;

		$this->template->title = _('Monitoring » Group detail');

		$ls = Livestatus::instance();
		
		$group_info_res = $grouptype == 'servicegroup' ?
			$ls->getServicegroups(array('filter' => array('name' => $group))) :
			$ls->getHostgroups(array('filter' => array('name' => $group)));
		
		if ($group_info_res === false || count($group_info_res)==0) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = sprintf(_("The requested %s ('%s') wasn't found"), $grouptype, $group);
			return;
		} else {
			$group_info_res = (object)$group_info_res[0];
		}

		# check if nagios is running, will affect wich template to use
/*		$status = $ls->getProcessInfo();
		if (empty($status) || !$status->is_running) {
			$this->template->content = $this->add_view('extinfo/not_running');
			$this->template->content->info_message = sprintf(_('It appears as though %s is not running, so commands are temporarily unavailable...'), Kohana::config('config.product_name'));
			$this->template->content->info_message_extra = sprintf(_('Click %s to view %s process information'), html::anchor('extinfo/show_process_info', html::specialchars(_('here'))), Kohana::config('config.product_name'));
			return;
		}
*/
		$this->template->content = $this->add_view('extinfo/groups');
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
//					_('Status grid') => 'status/'.$grouptype.'_grid/'.$group,
					_('Availability') => 'avail/generate/?report_type='.$grouptype.'s&'.$grouptype.'[]='.$group,
					_('Alert history') => 'alert_history/generate?'.$grouptype.'[]='.$group
				);
				break;
			case 'hostgroup':
				$label_view_for = _('for this hostgroup');
				$page_links = array(
					_('Status detail') => 'status/service/'.$group.'?group_type='.$grouptype,
					_('Status overview') => 'status/'.$grouptype.'/'.$group,
//					_('Status grid') => 'status/'.$grouptype.'_grid/'.$group,
					_('Availability') => 'avail/generate/?report_type='.$grouptype.'s&'.$grouptype.'[]='.$group,
					_('Alert history') => 'alert_history/generate??'.$grouptype.'[]='.$group
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
	private function _comments($host=false, $service=false, $all=false, $items_per_page=false)
	{
		$items_per_page = !empty($items_per_page) ? $items_per_page : config::get('pagination.default.items_per_page', '*');
		$type = $service ? 'service' : 'host';
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
			$tot = Comment_Model::count_comments_by_user($host, $service);
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

		$comment_data = $all ? Comment_Model::fetch_comments_by_user($service != false, $items_per_page, $offset) : Comment_Model::fetch_comments_by_object($host, $service, $items_per_page, $offset);
		$schedule_downtime_comments = $all ? Downtime_Model::fetch_comments_by_user($service != false, $items_per_page, $offset) : Downtime_Model::fetch_comments_by_object($host, $service, $items_per_page, $offset);

		$comment = false;
		$i = 0;

		$comment_type = 'comment';
		foreach ($comment_data as $row) {
			$comment[$i] = $row;
			$comment[$i]['comment_type'] = $comment_type;
			$i++;
		}

		$comment_type = 'downtime';
		foreach ($schedule_downtime_comments as $row) {
//			if (empty($row->comment_data)) {
//				continue;
//			}
			$comment[$i] = $row;
			$comment[$i]['comment_type'] = $comment_type;
			$i++;
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
		$comments->label_title = $type == 'host' ? _('Host Comments') : _('Service Comments');
		if (!$all) {
			$comments->host = $host;
			$comments->service = $service;
		}
		$comments->type = $type;

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
		$this->template->content->host_comments = $this->_comments(true, false, true, $items_per_page);
		$this->template->content->service_comments = $this->_comments(true, true, true, $items_per_page);
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

		$content->title = _("Program-wide performance information");

		# Values
		$program_status = Current_status_Model::instance()->program_status();
		$ls = Livestatus::instance();
		$hoststats    = $ls->getHostPerformance($program_status->program_start);
		$servicestats = $ls->getServicePerformance($program_status->program_start);

		$content->program_status = $program_status;

		# active service checks
		$content->svc_active_1min  = $servicestats->active_1_sum;
		$content->svc_active_5min  = $servicestats->active_5_sum;
		$content->svc_active_15min = $servicestats->active_15_sum;
		$content->svc_active_1hour = $servicestats->active_60_sum;
		$content->svc_active_start = $servicestats->active_all_sum;
		$content->svc_active_ever  = $servicestats->active_sum;

		# active service checks, percentages
		$content->svc_active_1min_perc = $servicestats->active_sum > 0 ?
			 number_format(($servicestats->active_1_sum*100)/$servicestats->active_sum, 1) : '0.0';
		$content->svc_active_5min_perc = $servicestats->active_sum > 0 ?
			 number_format(($servicestats->active_5_sum*100)/$servicestats->active_sum, 1) : '0.0';
		$content->svc_active_15min_perc = $servicestats->active_sum > 0 ?
			 number_format(($servicestats->active_15_sum*100)/$servicestats->active_sum, 1) : '0.0';
		$content->svc_active_1hour_perc = $servicestats->active_sum > 0 ?
			 number_format(($servicestats->active_60_sum*100)/$servicestats->active_sum, 1) : '0.0';
		$content->svc_active_start_perc = $servicestats->active_sum > 0 ?
			 number_format(($servicestats->active_all_sum*100)/$servicestats->active_sum, 1) : '0.0';

		# passive service checks
		$content->svc_passive_1min = $servicestats->passive_1_sum;
		$content->svc_passive_5min = $servicestats->passive_5_sum;
		$content->svc_passive_15min = $servicestats->passive_15_sum;
		$content->svc_passive_1hour = $servicestats->passive_60_sum;
		$content->svc_passive_start = $servicestats->passive_all_sum;
		$content->svc_passive_ever = $servicestats->passive_sum;

		# passive service checks, percentages
		$content->svc_passive_1min_perc = $servicestats->passive_sum > 0 ?
			number_format(($servicestats->passive_1_sum*100)/$servicestats->passive_sum, 1) : '0.0';
		$content->svc_passive_5min_perc =  $servicestats->passive_sum > 0 ?
			number_format(($servicestats->passive_5_sum*100)/$servicestats->passive_sum, 1) : '0.0';
		$content->svc_passive_15min_perc = $servicestats->passive_sum > 0 ?
			number_format(($servicestats->passive_15_sum*100)/$servicestats->passive_sum, 1) : '0.0';
		$content->svc_passive_1hour_perc = $servicestats->passive_sum > 0 ?
			number_format(($servicestats->passive_60_sum*100)/$servicestats->passive_sum, 1) : '0.0';
		$content->svc_passive_start_perc = $servicestats->passive_sum > 0 ?
			number_format(($servicestats->passive_all_sum*100)/$servicestats->passive_sum, 1) : '0.0';

		# service execution time
		$content->min_service_execution_time = number_format($servicestats->execution_time_min, 2);
		$content->max_service_execution_time = number_format($servicestats->execution_time_max, 2);
		$content->svc_average_execution_time = number_format($servicestats->execution_time_avg, 3);

		# service latency
		$content->min_service_latency = number_format($servicestats->latency_min, 2);
		$content->max_service_latency = number_format($servicestats->latency_max, 2);
		$content->average_service_latency = number_format($servicestats->latency_avg, 3);

		# service state change - active
		$content->min_service_percent_change_a = number_format($servicestats->active_state_change_min, 2);
		$content->max_service_percent_change_a = number_format($servicestats->active_state_change_max, 2);
		$content->average_service_percent_change = number_format($servicestats->active_state_change_avg, 3);

		# service state change - passive
		$content->min_service_percent_change_b = number_format($servicestats->passive_state_change_min, 2);
		$content->max_service_percent_change_b = number_format($servicestats->passive_state_change_max, 2);
		$content->average_service_percent_change = number_format($servicestats->passive_state_change_avg, 3);

		# active host checks
		$content->hst_active_1min = $hoststats->active_1_sum;
		$content->hst_active_5min = $hoststats->active_5_sum;
		$content->hst_active_15min = $hoststats->active_15_sum;
		$content->hst_active_1hour = $hoststats->active_60_sum;
		$content->hst_active_start = $hoststats->active_all_sum;
		$content->hst_active_ever = $hoststats->active_sum;

		# active host checks, percentages
		$content->hst_active_1min_perc = $hoststats->active_sum > 0 ?
			number_format(($hoststats->active_1_sum*100)/$hoststats->active_sum, 1) : '0.0';
		$content->hst_active_5min_perc = $hoststats->active_sum > 0 ?
			number_format(($hoststats->active_5_sum*100)/$hoststats->active_sum, 1) : '0.0';
		$content->hst_active_15min_perc = $hoststats->active_sum > 0 ?
			number_format(($hoststats->active_15_sum*100)/$hoststats->active_sum, 1) : '0.0';
		$content->hst_active_1hour_perc = $hoststats->active_sum > 0 ?
			number_format(($hoststats->active_60_sum*100)/$hoststats->active_sum, 1) : '0.0';
		$content->hst_active_start_perc = $hoststats->active_sum > 0 ?
			number_format(($hoststats->active_all_sum*100)/$hoststats->active_sum, 1) : '0.0';

		# passive host checks
		$content->hst_passive_1min = $hoststats->passive_1_sum;
		$content->hst_passive_5min = $hoststats->passive_5_sum;
		$content->hst_passive_15min = $hoststats->passive_15_sum;
		$content->hst_passive_1hour = $hoststats->passive_60_sum;
		$content->hst_passive_start = $hoststats->passive_all_sum;
		$content->hst_passive_ever = $hoststats->passive_sum;

		# passive host checks, percentages
		$content->hst_passive_1min_perc = $hoststats->passive_sum > 0 ?
			number_format(($hoststats->passive_1_sum*100)/$hoststats->passive_sum, 1) : '0.0';
		$content->hst_passive_5min_perc = $hoststats->passive_sum > 0 ?
			number_format(($hoststats->passive_5_sum*100)/$hoststats->passive_sum, 1) : '0.0';
		$content->hst_passive_15min_perc = $hoststats->passive_sum > 0 ?
			number_format(($hoststats->passive_15_sum*100)/$hoststats->passive_sum, 1) : '0.0';
		$content->hst_passive_1hour_perc = $hoststats->passive_sum > 0 ?
			number_format(($hoststats->passive_60_sum*100)/$hoststats->passive_sum, 1) : '0.0';
		$content->hst_passive_start_perc = $hoststats->passive_sum > 0 ?
			number_format(($hoststats->passive_all_sum*100)/$hoststats->passive_sum, 1) : '0.0';

		# host execution time
		$content->min_host_execution_time = number_format($hoststats->execution_time_min, 2);
		$content->max_host_execution_time = number_format($hoststats->execution_time_max, 2);
		$content->average_host_execution_time = number_format($hoststats->execution_time_avg, 3);

		# host latency
		$content->min_host_latency = number_format($hoststats->latency_min, 2);
		$content->max_host_latency = number_format($hoststats->latency_max, 2);
		$content->average_host_latency = number_format($hoststats->latency_avg, 3);

		# host state change - active
		$content->min_host_percent_change_a = number_format($hoststats->active_state_change_min, 2);
		$content->max_host_percent_change_a = number_format($hoststats->active_state_change_max, 2);
		$content->average_host_percent_change = number_format($hoststats->active_state_change_avg, 3);

		# host state change - passive
		$content->min_host_percent_change_b = number_format($hoststats->passive_state_change_min, 2);
		$content->max_host_percent_change_b = number_format($hoststats->passive_state_change_max, 2);
		$content->average_host_percent_change = number_format($hoststats->passive_state_change_avg, 3);
	}

	/**
	*	Show scheduling queue
	*/
	public function scheduling_queue()
	{
		$back_link = '/extinfo/scheduling_queue/';
		
		$host = $this->input->get('host');
		$service = $this->input->get('service');
		$sq_model = new Scheduling_queue_Model();

		$items_per_page = $this->input->get('items_per_page', config::get('pagination.default.items_per_page', '*'));
		$pagination = new CountlessPagination(array('style' => 'digg-pageless', 'items_per_page' => $items_per_page));
		
		$sq_model->set_range(
				$pagination->items_per_page,
				($pagination->current_page-1)*$pagination->items_per_page
				);
		
		if (!Auth::instance()->authorized_for('host_view_all')) {
			url::redirect('extinfo/unauthorized/scheduling_queue');
		}

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = $this->add_path('extinfo/js/extinfo.js');
		$this->xtra_js[] = 'application/media/js/jquery.tablesorter.min.js';
		$this->js_strings .= "var _filter_label = '"._('Enter text to filter')."';";
		$this->template->js_strings = $this->js_strings;

		$this->template->js_header->js = $this->xtra_js;
		$this->session->set('back_extinfo',$back_link);

		$this->template->title = _('Monitoring').' » '._('Scheduling queue');
		$this->template->content = $this->add_view('extinfo/scheduling_queue');
		$this->template->content->pagination = $pagination;
		$this->template->content->data = $sq_model->show_scheduling_queue($service, $host);
		$this->template->content->host_search = $host;
		$this->template->content->service_search = $service;
		$this->template->content->header_links = array(
			'host_name' => _('Host'),
			'description' => _('Service'),
			'last_check' => _('Last check'),
			'next_check' => _('Next check')
		);
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
				$host_data = Downtime_Model::get_downtime_data($downtime_type, array('type' => 'DESC'), true);
				break;
			case nagstat::SERVICE_DOWNTIME:
				$type_str = $types[$downtime_type];
				$service_data = Downtime_Model::get_downtime_data($downtime_type, array('type' => 'DESC'), true);
				break;
			case nagstat::ANY_DOWNTIME:
				$host_data = Downtime_Model::get_downtime_data(nagstat::HOST_DOWNTIME, array('type' => 'DESC'), true);
				$service_data = Downtime_Model::get_downtime_data(nagstat::SERVICE_DOWNTIME, array('type' => 'DESC'), true);
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
