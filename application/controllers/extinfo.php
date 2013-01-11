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
		return url::redirect(Router::$controller.'/show_process_info');
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
		

		$content->custom_variables = array();
		switch($type) {
			case 'host':
				if($result->custom_variable_names) {
					$content->custom_variables = array_combine($result->custom_variable_names, $result->custom_variable_values);
				}
				break;
			case 'service':
				if($result->custom_variable_names) {
					$content->custom_variables = array_combine($result->custom_variable_names, $result->custom_variable_values);
				}
				break;

		}
		$host_link = false;

		$content->contactgroups = false;
		if(isset($result->contact_groups)) {
			
			$filter = array();
			foreach($result->contact_groups as $grp) {
				$filter[] = 'Filter: name = '.str_replace("\n","",$grp);
			}
			$filter[] = "Or: ".count($filter);
			$filter = implode("\n",$filter);
			
			$groups = $ls->getContactgroups(
				array(
					'extra_header' => $filter,
					'extra_columns' => array('members')
				)
			);
			

			
			$complete_groups = false;
			foreach($groups as $group) {
				$filter = array();
				foreach($group['members'] as $grp) {
					$filter[] = 'Filter: name = '.str_replace("\n","",$grp);
				}
				$filter[] = "Or: ".count($filter);
				$filter = implode("\n",$filter);
				$complete_groups[$group['name']] = $ls->getContacts(
					array(
						'extra_header' => $filter,
						'columns' => array('name', 'alias', 'email', 'pager')
					)
				);
			}
			$content->contactgroups = $complete_groups;
		}
		$is_pending = false;
		$back_link = false;
		$content->parents = false;

		if ($type == 'host') {
			$content->title = _('Host State Information');
			$content->no_group_lable = _('No hostgroups');
			$check_compare_value = Current_status_Model::HOST_CHECK_ACTIVE;
			$last_notification = $result->last_notification;
			$content->lable_next_scheduled_check = _('Next scheduled active check');
			$content->lable_flapping = _('Is this host flapping?');
			$obsessing = $result->obsess;

			$content->parents = $result->parents;

			$back_link = '/extinfo/details/?host='.urlencode($host);
			if ($result->state == Current_status_Model::HOST_PENDING ) {
				$is_pending = true;
				$message_str = _('This host has not yet been checked, so status information is not available.');
			}
		} else {
			$content->title = _('Service State Information');
			$content->no_group_lable = _('No servicegroups');
			$content->lable_next_scheduled_check = _('Next scheduled check');
			$host_link = html::anchor('extinfo/details/?host='.urlencode($host), html::specialchars($host));
			$back_link = '/extinfo/details/service/?host='.urlencode($host).'&service='.urlencode($service);
			$check_compare_value = Current_status_Model::SERVICE_CHECK_ACTIVE;
			$last_notification = $result->last_notification;
			$content->lable_flapping = _('Is this service flapping?');
			$obsessing = $result->obsess;
			if ($result->state == Current_status_Model::SERVICE_PENDING ) {
				$is_pending = true;
				$message_str = _('This service has not yet been checked, so status information is not available.');
			}
		}

		$content->notes      = $result->notes      !='' ? nagstat::process_macros($result->notes,      $result, $type) : false;
		$content->notes_url  = $result->notes_url  !='' ? nagstat::process_macros($result->notes_url,  $result, $type) : false;
		$content->action_url = $result->action_url !='' ? nagstat::process_macros($result->action_url, $result, $type) : false;

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
		$content->main_object = $type=='host' ? $host : $service;
		$content->host = $host;
		$content->current_status_str = $this->current->status_text($result->state, $result->has_been_checked, $type);
		$content->duration = $result->duration;
		$content->groups = $groups;
		$content->host_address = $type == 'host' ? $result->address : $result->host_address;
		$content->icon_image = $result->icon_image;
		$content->icon_image_alt = $result->icon_image_alt;
		// "Why the str_replace, it looks stupid?" Well, because nagios (livestatus?) stores data with newlines replaced with a backslash and an 'n'.
		// "So why the nl2br, then, huh?" Uhm, it was there when I found it...
		$content->status_info = security::xss_clean($result->plugin_output).'<br />'.str_replace('\n', '<br />', nl2br(security::xss_clean($result->long_plugin_output)));
		$content->perf_data = $result->perf_data;
		$content->current_attempt = $result->current_attempt;
		$content->state_type = $result->state_type ? _('HARD state') : _('SOFT state');
		$content->main_object_alias = $type=='host' ? $result->alias : false;
		$content->max_attempts = $result->max_check_attempts;
		$content->last_update = time();
		$content->last_check = $result->last_check;

		$content->check_type = $result->check_type == $check_compare_value ? _('ACTIVE'): _('PASSIVE');
		$na_str = _('N/A');
		$content->check_latency = $check_compare_value ? $result->latency : $na_str;
		$content->execution_time = $result->execution_time;

		$content->next_check = (int)$result->next_check;
		$content->last_state_change = (int)$result->last_state_change;
		$content->last_notification = $last_notification!=0 ? date(nagstat::date_format(), $last_notification) : $na_str;
		$content->current_notification_number = $result->current_notification_number;
		$lable_flapping_state_change = _('state change');
		$content->percent_state_change_str = '';
		$is_flapping = $result->is_flapping;
		$yes = _('YES');
		$no = _('NO');
		if (!$result->flap_detection_enabled) {
			$content->flap_value = $na_str;
		} else {
			$content->flap_value = $is_flapping ? $yes : $no;
			$content->percent_state_change_str = '('.number_format((int)$result->percent_state_change, 2).'% '.$lable_flapping_state_change.')';
		}
		$content->scheduled_downtime_depth = $result->scheduled_downtime_depth ? $yes : $no;
		$str_enabled = _('ENABLED');
		$str_disabled = _('DISABLED');
		$content->active_checks_enabled = $result->active_checks_enabled ? $str_enabled : $str_disabled;
		$content->active_checks_enabled_val = (boolean) $result->active_checks_enabled;
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

		$commands->type = $type;
		$commands->host = $host;
		$commands->service = $service;
		$commands->result = $result;

		# create page links
		switch ($type) {
			case 'host':
				$page_links = array(
					 _('Status detail') => 'status/service/?host='.urlencode($host),
					 _('Alert history') => 'alert_history/generate?host_name[]='.$host,
					 _('Alert histogram') => 'histogram/generate?host_name[]='.$host,
					 _('Availability report') => 'avail/generate/?host_name[]='.$host,
					 _('Notifications') => '/notifications/host/'.$host
				);
				break;
			case 'service':
				$page_links = array(
					_('Information for this host') => 'extinfo/details/host/'.$host,
					_('Status detail for this host') => 'status/service/?host='.$host,
					_('Alert history') => 'alert_history/generate?service_description[]='.$host.';'.urlencode($service),
					_('Alert histogram') => 'histogram/generate?service_description[]='.$host.';'.urlencode($service),
					_('Availability report') => 'avail/generate/?service_description[]='.$host.';'.urlencode($service).'&report_type=services',
					_('Notifications') => '/notifications/host/'.$host.'?service='.urlencode($service)
				);

				break;
		}
		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = _("for this $type");
		}


		# show comments for hosts and services
		if ($type == 'host' || $type == 'service')
			$this->_comments($host, $service);
	}

	/**
	 * Show Nagios process info
	 */
	public function show_process_info()
	{
		if (!Auth::instance()->authorized_for('system_information')) {
			return url::redirect('extinfo/unauthorized/0');
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
		$commands->link_shutdown_nagios = nagioscmd::command_link(nagioscmd::command_id('SHUTDOWN_PROCESS'), false, false, $commands->label_shutdown_nagios);
		$commands->label_restart_nagios = sprintf(_('Restart the %s process'), Kohana::config('config.product_name'));
		$commands->link_restart_nagios = nagioscmd::command_link(nagioscmd::command_id('RESTART_PROCESS'), false, false, $commands->label_restart_nagios);

		if ($status->enable_notifications) {
			$commands->label_notifications = _('Disable notifications');
			$commands->link_notifications = nagioscmd::command_link(nagioscmd::command_id('DISABLE_NOTIFICATIONS'), false, false, $commands->label_notifications);
		} else {
			$commands->label_notifications = _('Enable notifications');
			$commands->link_notifications = nagioscmd::command_link(nagioscmd::command_id('ENABLE_NOTIFICATIONS'), false, false, $commands->label_notifications);
		}

		if ($status->execute_service_checks) {
			$commands->label_execute_service_checks = _('Stop executing service checks');
			$commands->link_execute_service_checks = nagioscmd::command_link(nagioscmd::command_id('STOP_EXECUTING_SVC_CHECKS'), false, false, $commands->label_execute_service_checks);
		} else {
			$commands->label_execute_service_checks = _('Start executing service checks');
			$commands->link_execute_service_checks = nagioscmd::command_link(nagioscmd::command_id('START_EXECUTING_SVC_CHECKS'), false, false, $commands->label_execute_service_checks);
		}

		if ($status->accept_passive_service_checks) {
			$commands->label_passive_service_checks = _('Stop accepting passive service checks');
			$commands->link_passive_service_checks = nagioscmd::command_link(nagioscmd::command_id('STOP_ACCEPTING_PASSIVE_SVC_CHECKS'), false, false, $commands->label_passive_service_checks);
		} else {
			$commands->label_passive_service_checks = _('Start accepting passive service checks');
			$commands->link_passive_service_checks = nagioscmd::command_link(nagioscmd::command_id('START_ACCEPTING_PASSIVE_SVC_CHECKS'), false, false, $commands->label_passive_service_checks);
		}

		if ($status->execute_host_checks) {
			$commands->label_execute_host_checks = _('Stop executing host checks');
			$commands->link_execute_host_checks = nagioscmd::command_link(nagioscmd::command_id('STOP_EXECUTING_HOST_CHECKS'), false, false, $commands->label_execute_host_checks);
		} else {
			$commands->label_execute_host_checks = _('Start executing host checks');
			$commands->link_execute_host_checks = nagioscmd::command_link(nagioscmd::command_id('START_EXECUTING_HOST_CHECKS'), false, false, $commands->label_execute_host_checks);
		}

		if ($status->accept_passive_host_checks) {
			$commands->label_accept_passive_host_checks = _('Stop accepting passive host checks');
			$commands->link_accept_passive_host_checks = nagioscmd::command_link(nagioscmd::command_id('STOP_ACCEPTING_PASSIVE_HOST_CHECKS'), false, false, $commands->label_accept_passive_host_checks);
		} else {
			$commands->label_accept_passive_host_checks = _('Start accepting passive host checks');
			$commands->link_accept_passive_host_checks = nagioscmd::command_link(nagioscmd::command_id('START_ACCEPTING_PASSIVE_HOST_CHECKS'), false, false, $commands->label_accept_passive_host_checks);
		}

		if ($status->enable_event_handlers) {
			$commands->label_enable_event_handlers = _('Disable event handlers');
			$commands->link_enable_event_handlers = nagioscmd::command_link(nagioscmd::command_id('DISABLE_EVENT_HANDLERS'), false, false, $commands->label_enable_event_handlers);
		} else {
			$commands->label_enable_event_handlers = _('Enable event handlers');
			$commands->link_enable_event_handlers = nagioscmd::command_link(nagioscmd::command_id('ENABLE_EVENT_HANDLERS'), false, false, $commands->label_enable_event_handlers);
		}

		if ($status->obsess_over_services) {
			$commands->label_obsess_over_services = _('Stop obsessing over services');
			$commands->link_obsess_over_services = nagioscmd::command_link(nagioscmd::command_id('STOP_OBSESSING_OVER_SVC_CHECKS'), false, false, $commands->label_obsess_over_services);
		} else {
			$commands->label_obsess_over_services = _('Start obsessing over services');
			$commands->link_obsess_over_services = nagioscmd::command_link(nagioscmd::command_id('START_OBSESSING_OVER_SVC_CHECKS'), false, false, $commands->label_obsess_over_services);
		}

		if ($status->obsess_over_hosts) {
			$commands->label_obsess_over_hosts = _('Stop obsessing over hosts');
			$commands->link_obsess_over_hosts = nagioscmd::command_link(nagioscmd::command_id('STOP_OBSESSING_OVER_HOST_CHECKS'), false, false, $commands->label_obsess_over_hosts);
		} else {
			$commands->label_obsess_over_hosts = _('Start obsessing over hosts');
			$commands->link_obsess_over_hosts = nagioscmd::command_link(nagioscmd::command_id('START_OBSESSING_OVER_HOST_CHECKS'), false, false, $commands->label_obsess_over_hosts);
		}

		if ($status->enable_flap_detection) {
			$commands->label_flap_detection_enabled = _('Disable flap detection');
			$commands->link_flap_detection_enabled = nagioscmd::command_link(nagioscmd::command_id('DISABLE_FLAP_DETECTION'), false, false, $commands->label_flap_detection_enabled);
		} else {
			$commands->label_flap_detection_enabled = _('Enable flap detection');
			$commands->link_flap_detection_enabled = nagioscmd::command_link(nagioscmd::command_id('ENABLE_FLAP_DETECTION'), false, false, $commands->label_flap_detection_enabled);
		}

		if ($status->process_performance_data) {
			$commands->label_process_performance_data = _('Disable performance data');
			$commands->link_process_performance_data = nagioscmd::command_link(nagioscmd::command_id('DISABLE_PERFORMANCE_DATA'), false, false, $commands->label_process_performance_data);
		} else {
			$commands->label_process_performance_data = _('Enable performance data');
			$commands->link_process_performance_data = nagioscmd::command_link(nagioscmd::command_id('ENABLE_PERFORMANCE_DATA'), false, false, $commands->label_process_performance_data);
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

		$content->notes_url = $group_info_res->notes_url !='' ? nagstat::process_macros($group_info_res->notes_url, $group_info_res, $grouptype) : false;
		$content->action_url =$group_info_res->action_url !='' ? nagstat::process_macros($group_info_res->action_url, $group_info_res, $grouptype) : false;
		$content->notes = $group_info_res->notes !='' ? nagstat::process_macros($group_info_res->notes, $group_info_res, $grouptype) : false;

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
					return url::redirect('command/unauthorized');
				}
				foreach ($_POST['del_comment'] as $param) {
					$nagios_commands = Command_Controller::_build_command($comment_cmd, array('comment_id' => $param), $nagios_commands);
				}
			}

			# delete host comments from search result
			if (isset($_POST['del_comment_host'])) {
				$comment_cmd = 'DEL_HOST_COMMENT';
				if (!Command_Controller::_is_authorized_for_command(array('cmd_typ' => $comment_cmd))) {
					return url::redirect('command/unauthorized');
				}
				foreach ($_POST['del_comment_host'] as $param) {
					$nagios_commands = Command_Controller::_build_command($comment_cmd, array('comment_id' => $param), $nagios_commands);
				}
			}
			# delete service comments from search result
			if (isset($_POST['del_comment_service'])) {
				$comment_cmd = 'DEL_SVC_COMMENT';
				if (!Command_Controller::_is_authorized_for_command(array('cmd_typ' => $comment_cmd))) {
					return url::redirect('command/unauthorized');
				}
				foreach ($_POST['del_comment_service'] as $param) {
					$nagios_commands = Command_Controller::_build_command($comment_cmd, array('comment_id' => $param), $nagios_commands);
				}
			}

			if (isset($_POST['del_downtime'])) {
				if (!Command_Controller::_is_authorized_for_command(array('cmd_typ' => $downtime_cmd))) {
					return url::redirect('command/unauthorized');
				}
				foreach ($_POST['del_downtime'] as $param) {
					$nagios_commands = Command_Controller::_build_command($downtime_cmd, array('downtime_id' => $param), $nagios_commands);
				}
			}

			$pipe = System_Model::get_pipe();

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
				return url::redirect(Router::$controller.'/'.Router::$method);
			} else {
				return url::redirect($redirect);
			}
		}

		$command_result_msg = $this->session->get('error_msg', $command_result_msg);
		$command_success = $this->session->get('error_msg', $command_success);

		if ($all === true) {
			$tot = Old_Comment_Model::count_comments_by_user($host, $service);
		} else {
			$tot = 0;
		}

		//Setup pagination
		$items_per_page = $this->input->get('custom_pagination_field', config::get('pagination.default.items_per_page', '*'));
		$pagination = new Pagination(
			array(
				'uri_segment' => 3,
				'total_items'=> $tot,
				'items_per_page' => $items_per_page
			)
		);
		$offset = $pagination->sql_offset;

		$comment_data = $all ? Old_Comment_Model::fetch_comments_by_user($service != false, $items_per_page, $offset) : Old_Comment_Model::fetch_comments_by_object($host, $service, $items_per_page, $offset);
		$schedule_downtime_comments = $all ? Old_Downtime_Model::fetch_comments_by_user($service != false, $items_per_page, $offset) : Old_Downtime_Model::fetch_comments_by_object($host, $service, $items_per_page, $offset);

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
		$pagination = new CountlessPagination(array('items_per_page' => $items_per_page));
		
		$sq_model->set_range(
				$pagination->items_per_page,
				($pagination->current_page-1)*$pagination->items_per_page
				);
		
		if (!Auth::instance()->authorized_for('host_view_all')) {
			return url::redirect('extinfo/unauthorized/scheduling_queue');
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
}
