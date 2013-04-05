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
		//$this->current = new Current_status_Model();

		$host = trim($host);
		$service = trim($service);
		$hostgroup = trim($hostgroup);
		$servicegroup = trim($servicegroup);

		$ls = Livestatus::instance();
		if(!empty($host) && empty($service)) {
			$set = HostPool_Model::all()->reduce_by('name', $host, '=');
		}
		else if(!empty($host) && !empty($service)) {
			$set = ServicePool_Model::all()
				->reduce_by('host.name', $host, '=')
				->reduce_by('description', $service, '=');
			$type = 'service';
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
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min';
		
		$this->xtra_css[] = 'application/media/css/jquery.fancybox';
		
		
		// Widgets
		$this->template->content->widgets = array();
		$this->xtra_js[] = $this->add_path('/js/widgets.js');

		# save us some typing
		$content = $this->template->content;

		if (count($set) != 1) {
			return url::redirect('extinfo/unauthorized/'.$type);
		}
		$it = $set->it(false, array(), 1, 0);
		$object = $it->current();

		$content->object = $object;
		
		$username = Auth::instance()->get_user()->username;
		
		$setting = array(
			'query'=>$set->get_comments()->get_query(),
			'columns'=>'all, -host_state, -host_name, -service_state, -service_description'
			);
		$model = new Ninja_widget_Model(array(
			'page' => Router::$controller,
			'name' => 'listview',
			'widget' => 'listview',
			'username' => $username,
			'friendly_name' => 'Comments',
			'setting' => $setting
		));
	
		$widget = widget::get($model, $this);
		widget::set_resources($widget, $this);
	
		$widget->set_fixed($set->get_comments()->get_query());
		$widget->extra_data_attributes['text-if-empty'] = _("No comments yet");
	
		$this->template->content->comments = $widget->render();

		if ($object->get_scheduled_downtime_depth()) {
			$setting = array(
				'query'=>$set->get_downtimes()->get_query(),
				'columns'=>'all, -host_state, -host_name, -service_state, -service_description'
				);
			$model = new Ninja_widget_Model(array(
				'page' => Router::$controller,
				'name' => 'listview',
				'widget' => 'listview',
				'username' => $username,
				'friendly_name' => 'Downtimes',
				'setting' => $setting
			));

			$widget = widget::get($model, $this);
			widget::set_resources($widget, $this);
		
			$widget->set_fixed($set->get_downtimes()->get_query());

			$this->template->content->downtimes = $widget->render();
		}
		
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;


		if (Command_Controller::_is_authorized_for_command(array('host_name' => $host, 'service' => $service)) === true) {
			$this->template->content->commands = $this->add_view('extinfo/commands');
			$this->template->content->commands->set = $set;
		} else {
			$this->template->content->commands = $this->add_view('extinfo/not_running');
			$this->template->content->commands->info_message = _("You're not authorized to run commands");
		}


		# create page links
		switch ($type) {
			case 'host':
				$page_links = array(
					 _('Status detail') => listview::link('services',array('host.name'=>$host)),
					 _('Alert history') => 'alert_history/generate?host_name[]='.urlencode($host),
					 _('Alert histogram') => 'histogram/generate?host_name[]='.urlencode($host),
					 _('Availability report') => 'avail/generate/?host_name[]='.urlencode($host),
					 _('Notifications') => listview::link('notifications',array('host_name'=>$host))
				);
				break;
			case 'service':
				$page_links = array(
					_('Information for this host') => 'extinfo/details/host/'.urlencode($host),
					_('Status detail for this host') => listview::link('services',array('host.name'=>$host)),
					_('Alert history') => 'alert_history/generate?service_description[]='.$host.';'.urlencode($service),
					_('Alert histogram') => 'histogram/generate?service_description[]='.$host.';'.urlencode($service),
					_('Availability report') => 'avail/generate/?service_description[]='.$host.';'.urlencode($service).'&report_type=services',
					_('Notifications') => listview::link('notifications',array('host_name'=>$host, 'service_description'=>$service))
				);

				break;
		}
		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = _("for this $type");
		}
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
		$this->template->content->commands = $this->add_view('extinfo/nagios_commands');
		$commands = $this->template->content->commands;


		# Lables to translate
		$na_str = _('N/A');
		$yes = _('YES');
		$no = _('NO');

		$date_format_str = nagstat::date_format();
		$content->date_format_str = $date_format_str;

		# fetch program status from program_status_model
		# uses ORM
		$status = Current_status_Model::instance()->program_status();

		$content->program_status = $status;
		$content->run_time = time::to_string(time() - $status->program_start);
		$content->program_start = date($date_format_str, $status->program_start);
		$content->last_log_rotation = $status->last_log_rotation ? date($date_format_str, $status->last_log_rotation) : 'never';

		$content->notifications_str = $status->enable_notifications ? $yes : $no;
		$content->servicechecks_str = $status->execute_service_checks ? $yes : $no;
		$content->passive_servicechecks_str = $status->accept_passive_service_checks ? $yes : $no;
		$content->hostchecks_str = $status->execute_host_checks ? $yes : $no;
		$content->passive_hostchecks_str = $status->accept_passive_host_checks ? $yes : $no;
		$content->eventhandler_str = $status->enable_event_handlers ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->obsess_services_str = $status->obsess_over_services ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->obsess_hosts_str = $status->obsess_over_hosts ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
		$content->flap_detection_str = $status->enable_flap_detection ? ucfirst(strtolower($yes)) : ucfirst(strtolower($no));
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
	*   Display extinfo for host- and servicegroups
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
					_('Availability') => 'avail/generate/?report_type='.$grouptype.'s&'.$grouptype.'[]='.$group,
					_('Alert history') => 'alert_history/generate?'.$grouptype.'[]='.$group
				);
				break;
			case 'hostgroup':
				$label_view_for = _('for this hostgroup');
				$page_links = array(
					_('Status detail') => 'status/service/'.$group.'?group_type='.$grouptype,
					_('Status overview') => 'status/'.$grouptype.'/'.$group,
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
	*   Show Program-Wide Performance Information
	*   (Performance Info)
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
	*   Show scheduling queue
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
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';
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
