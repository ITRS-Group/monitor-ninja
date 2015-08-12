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
class Extinfo_Controller extends Ninja_Controller {
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
	 * Show a single object that is not a group, such as a host
	 *
	 * @param $type string = host
	 * @param $host boolean = false
	 * @param $service boolean = false
	 */
	public function details($type='host', $host=false, $service=false)
	{
		$host = $this->input->get('host', $host);
		$service = $this->input->get('service', $service);
		$hostgroup = $this->input->get('hostgroup', false);
		$servicegroup = $this->input->get('servicegroup', false);

		$this->template->title = 'Monitoring » Extinfo';

		# load current status for host/service status totals
		//$this->current = new Current_status_Model();

		$host = trim($host);
		$service = trim($service);
		$hostgroup = trim($hostgroup);
		$servicegroup = trim($servicegroup);

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

		$this->_verify_access($set->mayi_resource().':read.extinfo');

		$this->template->content = $this->add_view('extinfo/index');
		$this->template->js_strings = $this->js_strings;
		$this->template->js[] = $this->add_path('extinfo/js/extinfo.js');

		// Widgets
		$this->template->content->widgets = array();
		$this->template->js[] = $this->add_path('/js/widgets.js');

		# save us some typing
		$content = $this->template->content;

		if (count($set) != 1) {
			return url::redirect('extinfo/unauthorized/'.$type);
		}
		$it = $set->it(false, array(), 1, 0);
		$object = $it->current();

		$content->object = $object;

		$username = Auth::instance()->get_user()->username;

		/* Comment widget */
		if($object->get_comments_count() > 0) {
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

			$widget->set_fixed();
			$widget->extra_data_attributes['text-if-empty'] = _("No comments yet");

			$this->template->content->widgets[] = $widget;
		}
		/* End of comment widget */

		/* Downtimes widget */
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

			$widget->set_fixed();

			$this->template->content->widgets[] = $widget;
		}
		/* End of downtimes widget */

		/* Services widget */
		if($set->get_table() == 'hosts') {
			$setting = array(
				'query'=>$set->get_services()->get_query(),
				'columns'=>'all, -host_state, -host_name, -host_actions',
				'limit' => 100
				);
			$model = new Ninja_widget_Model(array(
				'page' => Router::$controller,
				'name' => 'listview',
				'widget' => 'listview',
				'username' => $username,
				'friendly_name' => 'Services',
				'setting' => $setting
			));

			$widget = widget::get($model, $this);
			widget::set_resources($widget, $this);

			$widget->set_fixed();
			$widget->extra_data_attributes['text-if-empty'] = _("No comments yet");

			$this->template->content->widgets[] = $widget;
		}
		/* End of services widget */

		$this->template->inline_js = $this->inline_js;

		$this->template->content->commands = $this->add_view('extinfo/commands');
		$this->template->content->commands->set = $set;

		$this->template->toolbar = new Toolbar_Controller();
		$toolbar = &$this->template->toolbar;

		# create page links
		switch ($type) {
			case 'host':

				$toolbar->title = "Host";
				$toolbar->subtitle = "";

				if ($object->get_icon_image()) {

					$attributes = array(
						'alt' => $object->get_icon_image_alt(),
						'title' => $object->get_icon_image_alt(),
						'style' => 'width: 16px; vertical-align: middle; display: inline-block; margin-right: 4px'
					);

					$logos_path = Kohana::config('config.logos_path');
					$logos_path.= substr($logos_path, -1) == '/' ? '' : '/';
					$toolbar->subtitle = html::image($logos_path.$object->get_icon_image(), $attributes);

				}

				$toolbar->subtitle .= html::specialchars($object->get_name()) . " (" . html::specialchars($object->get_alias()) . ")";

				$toolbar->info(html::anchor(listview::link('services',array('host.name'=>$host)) , _('Status detail')));
				$toolbar->info(html::anchor('alert_history/generate?report_type=hosts&amp;objects[]='.urlencode($host) , _('Alert history')));
				$toolbar->info(html::anchor('showlog/showlog?hide_initial=1&amp;hide_process=1&amp;hide_logrotation=1&amp;hide_commands=1&amp;host_state_options[d]=1&amp;host_state_options[u]=1&amp;host_state_options[r]=1&amp;host[]='.urlencode($host) , _('Event log')));
				$toolbar->info(html::anchor('histogram/generate?report_type=hosts&amp;objects[]='.urlencode($host) , _('Alert histogram')));
				$toolbar->info(html::anchor('avail/generate/?report_type=hosts&amp;objects[]='.urlencode($host) , _('Availability report')));
				$toolbar->info(html::anchor(listview::link('notifications',array('host_name'=>$host)) , _('Notifications')));

				break;
			case 'service':

				$toolbar->title = "Service";
				$toolbar->subtitle = html::specialchars($object->get_description());

				$toolbar->info(html::anchor('extinfo/details?host='.urlencode($host) , _('Information for host')));
				$toolbar->info(html::anchor(listview::link('services',array('host.name'=>$host)) , _('Status detail for host')));
				$toolbar->info(html::anchor('alert_history/generate?report_type=services&amp;objects[]='.$host.';'.urlencode($service) , _('Alert history')));
				$toolbar->info(html::anchor('showlog/showlog?hide_initial=1&amp;hide_process=1&amp;hide_logrotation=1&amp;hide_commands=1&amp;service_state_options[w]=1&amp;service_state_options[u]=1&amp;service_state_options[c]=1&amp;service_state_options[r]=1&amp;service[]='.urlencode($host).';'.urlencode($service), _('Event log')));
				$toolbar->info(html::anchor('histogram/generate?report_type=services&amp;objects[]='.$host.';'.urlencode($service) , _('Alert histogram')));
				$toolbar->info(html::anchor('avail/generate/?report_type=services&amp;objects[]='.$host.';'.urlencode($service).'&report_type=services' , _('Availability report')));
				$toolbar->info(html::anchor(listview::link('notifications',array('host_name'=>$host, 'service_description'=>$service)) , _('Notifications')));

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
		$resource = ObjectPool_Model::pool('status')->all()->mayi_resource();
		$this->_verify_access($resource.':read.extinfo');

		$this->template->content = $this->add_view('extinfo/process_info');

		$this->template->toolbar = new Toolbar_Controller( _("Process Information") );
		$this->template->title = _('Monitoring » Process info');

		# save us some typing
		$content = $this->template->content;
		$content->info = array();

		# Lables to translate
		$na_str = _('N/A');
		$yes = _('YES');
		$no = _('NO');

		$date_format_str = nagstat::date_format();
		$content->date_format_str = $date_format_str;

		# fetch program status from program_status_model; uses ORM
		$status = Current_status_Model::instance()->program_status();
		$content->program_status = $status;

		$content->info[] = array(
			"title" => "Program version",
			"value" => $status->program_version,
			"command" => array(
				nagioscmd::command_ajax_button(nagioscmd::command_id('SHUTDOWN_PROCESS'), sprintf(_('Shutdown the %s process'), Kohana::config('config.product_name'))),
				nagioscmd::command_ajax_button(nagioscmd::command_id('RESTART_PROCESS'), sprintf(_('Restart the %s process'), Kohana::config('config.product_name')))
			)
		);

		$content->info[] = array( "title" => "Program Starttime", "value" => date($date_format_str, $status->program_start) );
		$content->info[] = array( "title" => "Running Time", "value" => time::to_string(time() - $status->program_start ) );
		$content->info[] = array( "title" => "Last logfile rotation", "value" => $status->last_log_rotation ? date($date_format_str, $status->last_log_rotation) : 'never' );
		$content->info[] = array( "title" => "Nagios PID", "value" => $status->nagios_pid );

		$content->info[] = array(
			"title" => _("Notifications enabled?"),
			"command" => nagioscmd::command_ajax_button(
				($status->enable_notifications) ? nagioscmd::command_id('DISABLE_NOTIFICATIONS') : nagioscmd::command_id('ENABLE_NOTIFICATIONS'),
				($status->enable_notifications) ? _('Disable notifications') : _('Enable notifications'),
				false, $status->enable_notifications
			)
		);

		$content->info[] = array(
			"title" => _("Service checks being executed?"),
			"command" => nagioscmd::command_ajax_button(
				($status->execute_service_checks) ? nagioscmd::command_id('STOP_EXECUTING_SVC_CHECKS') : nagioscmd::command_id('START_EXECUTING_SVC_CHECKS'),
				($status->execute_service_checks) ? _('Stop executing service checks') : _('Start executing service checks'),
				false, $status->execute_service_checks
			)
		);

		$content->info[] = array(
			"title" => _("Passive service checks being accepted?"),
			"command" => nagioscmd::command_ajax_button(
				($status->accept_passive_service_checks) ? nagioscmd::command_id('STOP_ACCEPTING_PASSIVE_SVC_CHECKS') : nagioscmd::command_id('START_ACCEPTING_PASSIVE_SVC_CHECKS'),
				($status->accept_passive_service_checks) ? _('Stop accepting passive service checks') : _('Start accepting passive service checks'),
				false, $status->accept_passive_service_checks
			)
		);

		$content->info[] = array(
			"title" => _("Host checks being executed?"),
			"command" => nagioscmd::command_ajax_button(
				($status->execute_host_checks) ? nagioscmd::command_id('STOP_EXECUTING_HOST_CHECKS') : nagioscmd::command_id('START_EXECUTING_HOST_CHECKS'),
				($status->execute_host_checks) ? _('Stop executing host checks') : _('Start executing host checks'),
				false, $status->execute_host_checks
			)
		);

		$content->info[] = array(
			"title" => _("Passive host checks being accepted?"),
			"command" => nagioscmd::command_ajax_button(
				($status->accept_passive_host_checks) ? nagioscmd::command_id('STOP_ACCEPTING_PASSIVE_HOST_CHECKS') : nagioscmd::command_id('START_ACCEPTING_PASSIVE_HOST_CHECKS'),
				($status->accept_passive_host_checks) ? _('Stop accepting passive host checks') : _('Start accepting passive host checks'),
				false, $status->accept_passive_host_checks
			)
		);

		$content->info[] = array(
			"title" => _("Event handlers enabled?"),
			"command" => nagioscmd::command_ajax_button(
				($status->enable_event_handlers) ? nagioscmd::command_id('DISABLE_EVENT_HANDLERS') : nagioscmd::command_id('ENABLE_EVENT_HANDLERS'),
				($status->enable_event_handlers) ? _('Disable event handlers') : _('Enable event handlers'),
				false, $status->enable_event_handlers
			)
		);

		$content->info[] = array(
			"title" => _("Obsessing over services?"),
			"command" => nagioscmd::command_ajax_button(
				($status->obsess_over_services) ? nagioscmd::command_id('STOP_OBSESSING_OVER_SVC_CHECKS') : nagioscmd::command_id('START_OBSESSING_OVER_SVC_CHECKS'),
				($status->obsess_over_services) ? _('Stop obsessing over services') : _('Start obsessing over services'),
				false, $status->obsess_over_services
			)
		);

		$content->info[] = array(
			"title" => _('Obsessing over hosts?'),
			"command" => nagioscmd::command_ajax_button(
				($status->obsess_over_hosts) ? nagioscmd::command_id('STOP_OBSESSING_OVER_HOST_CHECKS') : nagioscmd::command_id('START_OBSESSING_OVER_HOST_CHECKS'),
				($status->obsess_over_hosts) ? _('Stop obsessing over hosts') : _('Start obsessing over hosts'),
				false, $status->obsess_over_hosts
			)
		);

		$content->info[] = array(
			"title" => _('Flap detection enabled?'),
			"command" => nagioscmd::command_ajax_button(
				($status->enable_flap_detection) ? nagioscmd::command_id('DISABLE_FLAP_DETECTION') : nagioscmd::command_id('ENABLE_FLAP_DETECTION'),
				($status->enable_flap_detection) ? _('Disable flap detection') : _('Enable flap detection'),
				false, $status->enable_flap_detection
			)
		);

		$content->info[] = array(
			"title" => _('Performance data being processed?'),
			"command" => nagioscmd::command_ajax_button(
				($status->process_performance_data) ? nagioscmd::command_id('DISABLE_PERFORMANCE_DATA') : nagioscmd::command_id('ENABLE_PERFORMANCE_DATA'),
				($status->process_performance_data) ? _('Disable performance data') : _('Enable performance data'),
				false, $status->process_performance_data
			)
		);

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
	 * Show a single object that is a group, such as a servicegroup
	 */
	public function group_details($grouptype='servicegroup', $group=false)
	{
		$grouptype = $this->input->get('grouptype', $grouptype);
		$group = $this->input->get('group', $group);

		if(!in_array($grouptype, array('hostgroup', 'servicegroup'), true)) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = _("Error: Incorrect group type specified");
			return;
		}
		if (empty($group)) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = _("Error: No group name specified");
			return;
		}

		$set = ObjectPool_Model::pool($grouptype.'s')
			->all()
			->reduce_by('name', $group, '=');

		/* @var $set ServiceGroupSet_Model */
		$this->_verify_access($set->mayi_resource().':read.extinfo');

		if (count($set) != 1) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = sprintf(_("The requested %s ('%s') wasn't found"), $grouptype, $group);
			return;
		}

		$this->js_strings .= "var _pnp_web_path = '".Kohana::config('config.pnp4nagios_path')."';\n";
		$this->template->js_strings = $this->js_strings;
		$this->template->js[] = $this->add_path('extinfo/js/extinfo.js');

		$this->template->title = _('Monitoring » Group detail');

		$ls = Livestatus::instance();

		$group_info_res = $grouptype == 'servicegroup' ?
			$ls->getServicegroups(array('filter' => array('name' => $group))) :
			$ls->getHostgroups(array('filter' => array('name' => $group)));

		if ($group_info_res === false || count($group_info_res)==0) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = sprintf(_("The requested %s ('%s') wasn't found"), $grouptype, $group);
			return;
		}
		$group_info_res = (object)$group_info_res[0];
		$this->template->content = $this->add_view('extinfo/groups');
		$content = $this->template->content;
		$object = $set->it(false)->current();
		$content->object = $object;

		$content->label_grouptype = $grouptype=='servicegroup' ? _('servicegroup') : _('hostgroup');
		$content->group_alias = $group_info_res->alias;
		$content->groupname = $group;
		$content->commands = $this->add_view('extinfo/commands');
		$content->commands->set = $set;

		$content->notes_url = $group_info_res->notes_url !='' ? nagstat::process_macros($group_info_res->notes_url, $group_info_res, $grouptype) : false;
		$content->action_url =$group_info_res->action_url !='' ? nagstat::process_macros($group_info_res->action_url, $group_info_res, $grouptype) : false;
		$content->notes = $group_info_res->notes !='' ? nagstat::process_macros($group_info_res->notes, $group_info_res, $grouptype) : false;

		$this->template->toolbar = new Toolbar_Controller( );
		$toolbar = &$this->template->toolbar;

		switch ($grouptype) {
			case 'servicegroup':

				$toolbar->title = "Servicegroup";
				$toolbar->subtitle = security::xss_clean( $content->group_alias );

				$label_view_for = _('for this servicegroup');
				$toolbar->info( html::anchor( 'status/service/'.$group.'?group_type='.$grouptype , _('Status detail') ) );
				$toolbar->info( html::anchor( 'status/'.$grouptype.'/'.$group , _('Status overview') ) );
				$toolbar->info( html::anchor( 'avail/generate/?report_type='.$grouptype.'s&'.$grouptype.'[]='.$group , _('Availability') ) );
				$toolbar->info( html::anchor( 'alert_history/generate?'.$grouptype.'[]='.$group , _('Alert history') ) );

				break;
			case 'hostgroup':

				$toolbar->title = "Hostgroup";
				$toolbar->subtitle = security::xss_clean( $content->group_alias );

				$label_view_for = _('for this hostgroup');
				$toolbar->info( html::anchor( 'status/service/'.$group.'?group_type='.$grouptype , _('Status detail') ) );
				$toolbar->info( html::anchor( 'status/'.$grouptype.'/'.$group , _('Status overview') ) );
				$toolbar->info( html::anchor( 'avail/generate/?report_type='.$grouptype.'s&'.$grouptype.'[]='.$group , _('Availability') ) );
				$toolbar->info( html::anchor( 'alert_history/generate?'.$grouptype.'[]='.$group , _('Alert history') ) );

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
		$this->_verify_access('ninja.performance:read.extinfo');

		$this->template->content = $this->add_view('extinfo/performance');
		$this->template->title = _('Monitoring').' » '._('Performance info');
		$content = $this->template->content;

		$this->template->toolbar = new Toolbar_Controller( _("Performance Information"), _("Program-wide") );

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
		$resource = ObjectPool_Model::pool('hosts')->all()->mayi_resource();
		$this->_verify_access($resource.':read.scheduling_queue');
		$resource = ObjectPool_Model::pool('services')->all()->mayi_resource();
		$this->_verify_access($resource.':read.scheduling_queue');

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

		$this->template->js[] = $this->add_path('extinfo/js/extinfo.js');
		$this->template->js[] = 'application/media/js/jquery.tablesorter.min.js';
		$this->template->js_strings = $this->js_strings;

		$this->session->set('back_extinfo',$back_link);

		$this->template->title = _('Monitoring').' » '._('Scheduling queue');
		$this->template->content = $this->add_view('extinfo/scheduling_queue');
		$this->template->content->data = $sq_model->show_scheduling_queue($service, $host);

		if(!$this->template->content->data || count($this->template->content->data) < $items_per_page) {
			$pagination->hide_next = true;
		}

		$this->template->content->host_search = $host;
		$this->template->content->service_search = $service;
		$this->template->content->header_links = array(
			'host_name' => _('Host'),
			'description' => _('Service'),
			'last_check' => _('Last check'),
			'next_check' => _('Next check')
		);

		$this->template->content->date_format_str = nagstat::date_format();
		$this->template->toolbar = new Toolbar_Controller( "Scheduling Queue" );

		$form = '<form action="scheduling_queue" method="get">';
		$form .= _('Search for');
		$form .= '<label> ' . _('Host') . ': <input name="host" value="' . $host . '" /></label>';
		$form .= '<label> ' . _('Service') . ': <input name="service" value="' . $service . '" /></label>';
		$form .= '<input type="submit" value="' . _('Search') . '" /></form>';

		$this->template->toolbar->info( $form );
		if ( isset( $pagination ) ) {
			$this->template->toolbar->info( $pagination );
		}

		if ( $host || $service ) {
			$this->template->toolbar->info( ' <span>' .
				' ' . _("Do you want to") .
				' <a href="'. Kohana::config('config.site_domain') . 'index.php/' . Router::$controller . '/' . Router::$method . '">' .
				_("reset the search filter?") . '</a></span>'
			);
		}

	}
}
