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
		$host = trim($this->input->get('host', $host));
		$service = trim($this->input->get('service', $service));
		$hostgroup = trim($this->input->get('hostgroup', false));
		$servicegroup = trim($this->input->get('servicegroup', false));


		if(!empty($host) && empty($service)) {
			$set = HostPool_Model::all()->reduce_by('name', $host, '=');
		} else if(!empty($host) && !empty($service)) {
			$set = ServicePool_Model::all()
				->reduce_by('host.name', $host, '=')
				->reduce_by('description', $service, '=');
			$type = 'service';
		} else if(!empty($hostgroup)) {
			return $this->group_details('hostgroup', $hostgroup);
		} else if(!empty($servicegroup)) {
			return $this->group_details('servicegroup', $servicegroup);
		} else return; /* @TODO handle this more gracefully */

		$this->_verify_access($set->mayi_resource().':read.extinfo');
		$this->template->title = 'Monitoring » Extinfo';

		$this->template->css[] = $this->add_path('extinfo/css/extinfo.css');
		$this->template->css[] = $this->add_path('extinfo/css/c3.css');

		$this->template->js[] = 'modules/monitoring/views/extinfo/js/d3.js';
		$this->template->js[] = 'modules/monitoring/views/extinfo/js/c3.js';

		$this->template->content = $this->add_view('extinfo/index');
		$this->template->js_strings = $this->js_strings;
		$this->template->js[] = 'modules/monitoring/views/extinfo/js/extinfo.js';

		$this->template->content->widgets = array();

		# save us some typing
		$content = $this->template->content;

		if (count($set) != 1) {
			return url::redirect('extinfo/unauthorized/'.$type);
		}

		$it = $set->it(false, array(), 1, 0);
		$object = $it->current();

		$content->object = $object;

		$username = Auth::instance()->get_user()->get_username();

		$contact_set = ContactPool_Model::none();
		$contact_all = ContactPool_Model::all();

		foreach ($object->get_contacts() as $contact) {
			$contact_set = $contact_set->union(
				$contact_all->reduce_by('name', $contact, '=')
			);
		}

		/* Contacts widget */
		$model = new Ninja_widget_Model(array(
			'page' => Router::$controller,
			'name' => 'listview',
			'widget' => 'listview',
			'username' => $username,
			'friendly_name' => 'Contacts',
			'setting' => array(
				'query'=> $contact_set->get_query(),
				'columns'=>'all'
			)
		));

		$widget = widget::get($model, $this);
		widget::set_resources($widget, $this);

		$widget->set_fixed();
		$widget->extra_data_attributes['text-if-empty'] = _("No contacts available");

		$this->template->content->widgets[_('Contacts')] = $widget;
		/* End of contacts widget */

		/* Comment widget */
		if($object->get_comments_count() > 0) {

			$model = new Ninja_widget_Model(array(
				'page' => Router::$controller,
				'name' => 'listview',
				'widget' => 'listview',
				'username' => $username,
				'friendly_name' => 'Comments',
				'setting' => array(
					'query'=>$set->get_comments()->get_query(),
					'columns'=>'all, -host_state, -host_name, -service_state, -service_description'
				)
			));

			$widget = widget::get($model, $this);
			widget::set_resources($widget, $this);

			$widget->set_fixed();
			$widget->extra_data_attributes['text-if-empty'] = _("No comments yet");

			$this->template->content->widgets[_('Comments')] = $widget;
		}
		/* End of comment widget */

		$this->template->inline_js = $this->inline_js;

		$this->template->toolbar = new Toolbar_Controller();
		$toolbar = &$this->template->toolbar;
		$lp = LinkProvider::factory();

		$reports = new Menu_Model();
		$reports->set('Options');

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

				$reports->set('Options.Report.Availability', $lp->get_url(
					'avail', 'generate', array(
						"report_type" => "hosts",
						"objects[]" => $object->get_key()
					)
				));

				$reports->set('Options.Report.Alert history', $lp->get_url(
					'alert_history', 'generate', array(
						'report_type' => 'hosts',
						'objects' => array($object->get_key())
					)
				));

				$reports->set('Options.Report.Event log', $lp->get_url(
					'showlog', 'showlog', array(
						"hide_initial" => "1",
						"hide_process" => "1",
						"hide_logrotation" => "1",
						"hide_commands" => "1",
						"host_state_options[d]" => "1",
						"host_state_options[u]" => "1",
						"host_state_options[r]" => "1",
						"host[]" => $object->get_key()
					)
				));

				$reports->set('Options.Report.Histogram', $lp->get_url(
					'histogram', 'generate', array(
						"report_type" => "hosts",
						"objects[]" => $object->get_key()
					)
				));

				break;

			case 'service':

				$toolbar->title = "Service";
				$toolbar->subtitle = html::specialchars($object->get_description());

				$reports->set('Options.Report.Availability', $lp->get_url(
					'avail', 'generate', array(
						"report_type" => "services",
						"objects[]" => $object->get_key()
					)
				));

				$reports->set('Options.Report.Alert history', $lp->get_url(
					'alert_history', 'generate', array(
						'report_type' => 'services',
						'objects' => array($object->get_key())
					)
				));

				$reports->set('Options.Report.Event log', $lp->get_url(
					'showlog', 'showlog', array(
						"hide_initial" => "1",
						"hide_process" => "1",
						"hide_logrotation" => "1",
						"hide_commands" => "1",
						"service_state_options[w]" => "1",
						"service_state_options[u]" => "1",
						"service_state_options[c]" => "1",
						"service_state_options[r]" => "1",
						"service[]" => $object->get_key()
					)
				));

				$reports->set('Options.Report.Histogram', $lp->get_url(
					'histogram', 'generate', array(
						"report_type" => "services",
						"objects[]" => $object->get_key()
					)
				));

				break;

		}

		$commands = $object->list_commands();
		$command_categories = array();

		foreach($commands as $cmd => $cmdinfo) {
			if ($cmdinfo['category'] === 'Operations') continue;
			if($cmdinfo['enabled']) {
				if(!isset($command_categories[$cmdinfo['category']]))
					$command_categories[$cmdinfo['category']] = array();
				$command_categories[$cmdinfo['category']][$cmd] = $cmdinfo;
			}
		}

		foreach($command_categories as $category => $category_commands) {
			foreach($category_commands as $cmd => $cmdinfo) {
				$reports->set("Options.$category." . $cmdinfo['name'],
					$lp->get_url('cmd', null,
array(
						'command' => $cmd,
						'table' => $object->get_table(),
						'object' => $object->get_key()
					))
				);
			}
		}

		$toolbar->menu($reports);
		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = _("for this $type");
		}

	}

	private function enabled_icon($enabled) {
		return '<span class="icon-16 x16-'.($enabled?'enabled':'disabled').'"></span>'.($enabled?'Enabled':'Disabled');
	}

	/**
	 * Show Nagios process info
	 */
	public function show_process_info()
	{
		$resource = StatusPool_Model::all()->mayi_resource();
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

		$date_format_str = date::date_format();
		$content->date_format_str = $date_format_str;

		# fetch program status from program_status_model; uses ORM
		$status = StatusPool_Model::status();
		if (!$status) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = _("Error: No monitoring features status information available");
			return;
		}

		$content->object = $status;
		$content->info[] = array(
			"title" => "Program version",
			"value" => $status->get_program_version()
		);

		$content->info[] = array( "title" => "Program Starttime", "value" => date($date_format_str, $status->get_program_start()) );
		$content->info[] = array( "title" => "Running Time", "value" => time::to_string(time() - $status->get_program_start() ) );
		$content->info[] = array( "title" => "Last logfile rotation", "value" => $status->get_last_log_rotation() ? date($date_format_str, $status->get_last_log_rotation()) : 'never' );
		$content->info[] = array( "title" => "Nagios PID", "value" => $status->get_nagios_pid() );

		$content->info[] = array(
			"title" => _("Notifications enabled?"),
			"value" => $this->enabled_icon($status->get_enable_notifications())
		);

		$content->info[] = array(
			"title" => _("Service checks being executed?"),
			"value" => $this->enabled_icon($status->get_execute_service_checks())
		);

		$content->info[] = array(
			"title" => _("Passive service checks being accepted?"),
			"value" => $this->enabled_icon($status->get_accept_passive_service_checks())
		);

		$content->info[] = array(
			"title" => _("Host checks being executed?"),
			"value" => $this->enabled_icon($status->get_execute_host_checks())
		);

		$content->info[] = array(
			"title" => _("Passive host checks being accepted?"),
			"value" => $this->enabled_icon($status->get_accept_passive_host_checks())
		);

		$content->info[] = array(
			"title" => _("Event handlers enabled?"),
			"value" => $this->enabled_icon($status->get_enable_event_handlers())
		);

		$content->info[] = array(
			"title" => _("Obsessing over services?"),
			"value" => $this->enabled_icon($status->get_obsess_over_services())
		);

		$content->info[] = array(
			"title" => _('Obsessing over hosts?'),
			"value" => $this->enabled_icon($status->get_obsess_over_hosts())
		);

		$content->info[] = array(
			"title" => _('Flap detection enabled?'),
			"value" => $this->enabled_icon($status->get_enable_flap_detection())
		);

		$content->info[] = array(
			"title" => _('Performance data being processed?'),
			"value" => $this->enabled_icon($status->get_process_performance_data())
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
		$this->template->js[] = 'modules/monitoring/views/extinfo/js/extinfo.js';

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
		$content->commands->object = $object;

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
		$performance = new Performance_Model();

		$this->template->content = $this->add_view('extinfo/performance');
		$this->template->title = _('Monitoring').' » '._('Performance info');
		$this->template->toolbar = new Toolbar_Controller( _("Performance Information"), _("Program-wide") );
		$this->template->content->performance = $performance;

	}

	/**
	*   Show scheduling queue
	*/
	public function scheduling_queue($host_filter = '', $service_filter = '')
	{

		$host_filter = $this->input->get('host', $host_filter);
		$service_filter = $this->input->get('service', $service_filter);

		$service_set = ServicePool_Model::all()
			->reduce_by('description', $service_filter, '~~')
			->reduce_by('host.name', $host_filter, '~~');
		$host_set = HostPool_Model::all()
			->reduce_by('name', $host_filter, '~~');

		$this->_verify_access($service_set->mayi_resource().':read.scheduling_queue');
		$this->_verify_access($host_set->mayi_resource().':read.scheduling_queue');

		$unfiltered_total = $service_set->count() + $host_set->count();

		$service_set = $service_set->reduce_by('check_source', '^Merlin', '!~~');
		$host_set = $host_set->reduce_by('check_source', '^Merlin', '!~~');

		$filtered_total = $service_set->count() + $host_set->count();
		$is_remote_filtered = ($filtered_total < $unfiltered_total);

		$service_it = $service_set->it(false, array("next_check"));
		$host_it = $host_set->it(false, array("next_check"));

		$raw = array();

		foreach ($service_it as $service) {
			$raw[] = $service;
		}

		foreach ($host_it as $host) {
			$raw[] = $host;
		}

		usort($raw, function ($a, $b) {
			if ($a->get_next_check() > $b->get_next_check()) return 1;
			elseif ($a->get_next_check() > $b->get_next_check()) return -1;
			else return 0;
		});

		$total = count($raw);
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.default.items_per_page', '*'));
		$pagination = new CountlessPagination(array('items_per_page' => $items_per_page, 'total_items' => $total));

		if ($total <= $pagination->items_per_page * $pagination->current_page) {
			$pagination->hide_next = true;
		}

		$raw = array_slice($raw, ($pagination->current_page - 1) * $pagination->items_per_page, $pagination->items_per_page);

		$this->template->css[] = $this->add_path('extinfo/css/scheduling_queue.css');
		$this->template->title = _('Monitoring').' » '._('Scheduling queue');
		$this->template->content = $this->add_view('extinfo/scheduling_queue');
		$this->template->content->data = $raw;
		$this->template->content->is_remote_filtered = $is_remote_filtered;

		$this->template->content->host_filter = $host_filter;
		$this->template->content->service_filter = $service_filter;
		$this->template->content->columns = array(
			'host_name' => _('Host'),
			'description' => _('Service'),
			'last_check' => _('Last check'),
			'next_check' => _('Next check')
		);

		$this->template->toolbar = new Toolbar_Controller( "Scheduling Queue" );

		$form = '<form action="scheduling_queue" method="get">';
		$form .= _('Search for');
		$form .= '<label> ' . _('Host') . ': <input type="text" name="host" value="' . $host_filter . '" /></label>';
		$form .= '<label> ' . _('Service') . ': <input type="text" name="service" value="' . $service_filter . '" /></label>';
		$form .= '<input type="submit" value="' . _('Search') . '" /></form>';

		$this->template->toolbar->info($form);
		$this->template->toolbar->info($pagination->render());

		if ($host_filter || $service_filter) {
			$this->template->toolbar->info(' <span>' .
				' ' . _("Do you want to") .
				' <a href="'. Kohana::config('config.site_domain') . 'index.php/' . Router::$controller . '/' . Router::$method . '">' .
				_("reset the search filter?") . '</a></span>');
		}

	}
}
