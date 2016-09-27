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
		$this->template->disable_refresh = true;

		$host = trim($this->input->get('host', $host));
		$service = trim($this->input->get('service', $service));
		$hostgroup = trim($this->input->get('hostgroup', false));
		$servicegroup = trim($this->input->get('servicegroup', false));

		if(!empty($host) && empty($service)) {
			$set = HostPool_Model::all()->reduce_by('name', $host, '=');
			$this->template->content = $this->add_view('extinfo/index');
		} else if(!empty($host) && !empty($service)) {
			$set = ServicePool_Model::all()
				->reduce_by('host.name', $host, '=')
				->reduce_by('description', $service, '=');
			$this->template->content = $this->add_view('extinfo/index');
			$type = 'service';
		} else if(!empty($hostgroup)) {
			$set = HostGroupPool_Model::all()->reduce_by('name', $hostgroup, '=');
			$this->template->content = $this->add_view('extinfo/groups');
			$type = 'hostgroup';
		} else if(!empty($servicegroup)) {
			$set = ServiceGroupPool_Model::all()->reduce_by('name', $servicegroup, '=');
			$this->template->content = $this->add_view('extinfo/groups');
			$type = 'servicegroup';
		} else return; /* @TODO handle this more gracefully */

		$this->_verify_access($set->mayi_resource().':read.extinfo');
		$this->template->title = 'Monitoring » Extinfo';

		$this->template->css[] = $this->add_path('extinfo/css/extinfo.css');

		$this->template->js_strings = $this->js_strings;

		$this->template->content->widgets = array();
		$this->template->content->type = $type;

		# save us some typing
		$content = $this->template->content;

		if (count($set) != 1) {
			Event::run('system.403');
			return;
		}

		$it = $set->it(false, array(), 1, 0);
		$object = $it->current();

		$content->object = $object;

		$username = Auth::instance()->get_user()->get_username();


		if ($host || $service) {

			/* Contacts widget */

			$contact_set = ContactPool_Model::none();
			$contact_all = ContactPool_Model::all();

			foreach ($object->get_contacts() as $contact) {
				$contact_set = $contact_set->union(
					$contact_all->reduce_by('name', $contact, '=')
				);
			}

			if (count($contact_set) > 0) {
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
			}

			/* End of contacts widget */
		}

		if ($host || $service) {
			$contactgroup_set = ContactgroupPool_Model::none();
			$contactgroup_all = ContactgroupPool_Model::all();

			foreach ($object->get_contact_groups() as $contactgroup) {
				$contactgroup_set = $contactgroup_set->union(
					$contactgroup_all->reduce_by('name', $contactgroup, '=')
				);
			}

			if (count($contactgroup_set) > 0) {
				$model = new Ninja_widget_Model(array(
					'page' => Router::$controller,
					'name' => 'listview',
					'widget' => 'listview',
					'username' => $username,
					'friendly_name' => 'Contactgroups',
					'setting' => array(
						'query'=> $contactgroup_set->get_query(),
						'columns'=>'all'
					)
				));

				$widget = widget::get($model, $this);
				widget::set_resources($widget, $this);

				$widget->set_fixed();
				$widget->extra_data_attributes['text-if-empty'] = _("No contactgroups available");

				$this->template->content->widgets[_('Contactgroups')] = $widget;
			}
		}

		if ($host || $service) {
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
		}

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
						'objects[]' => $object->get_key()
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
						'objects[]' => $object->get_key()
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
			case 'hostgroup':
				$model = new Ninja_widget_Model(array(
					'page' => Router::$controller,
					'name' => 'listview',
					'widget' => 'listview',
					'username' => $username,
					'friendly_name' => 'Hosts in this group',
					'setting' => array(
						'query'=> HostPool_Model::all()->reduce_by('groups', $object->get_name(), '>=')->get_query(),
						'columns'=>'all'
					)
				));

				$widget = widget::get($model, $this);
				widget::set_resources($widget, $this);

				$widget->set_fixed();
				$widget->extra_data_attributes['text-if-empty'] = _("No comments yet");
				$this->template->content->widgets['Hosts in this group'] = $widget;

				break;
			case 'servicegroup':
				$model = new Ninja_widget_Model(array(
					'page' => Router::$controller,
					'name' => 'listview',
					'widget' => 'listview',
					'username' => $username,
					'friendly_name' => 'Service in this group',
					'setting' => array(
						'query'=> ServicePool_Model::all()->reduce_by('groups', $object->get_name(), '>=')->get_query(),
						'columns'=>'all'
					)
				));

				$widget = widget::get($model, $this);
				widget::set_resources($widget, $this);

				$widget->set_fixed();
				$widget->extra_data_attributes['text-if-empty'] = _("No comments yet");
				$this->template->content->widgets['Services in this group'] = $widget;

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
					)), null, false, array(
						'class' => 'command-ajax-link'
					)
				);
			}
		}

		$custom_commands = $object->list_custom_commands();
		foreach ($custom_commands as $commandname => $command) {
			$reports->set("Options.Custom commands." . $commandname,
				$lp->get_url('command', 'exec_custom_command', array(
					'command' => $commandname,
					'table' => $object->get_table(),
					'key' => $object->get_key()
				))
			);
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

		$menu = new Menu_Model();
		$menu->set('Options');
		$commands = $status->list_commands();
		$command_categories = array();

		foreach($commands as $cmd => $cmdinfo) {
			if($cmdinfo['enabled']) {
				if(!isset($command_categories[$cmdinfo['category']]))
					$command_categories[$cmdinfo['category']] = array();
				$command_categories[$cmdinfo['category']][$cmd] = $cmdinfo;
			}
		}

		$lp = LinkProvider::factory();
		foreach($command_categories as $category => $category_commands) {
			foreach($category_commands as $cmd => $cmdinfo) {
				$menu->set("Options.$category." . $cmdinfo['name'],
					$lp->get_url('cmd', null,
					array(
						'command' => $cmd,
						'table' => $status->get_table(),
						'object' => $status->get_key()
					)), null, false, array(
						'class' => 'command-ajax-link'
					)
				);
			}
		}

		$this->template->toolbar->menu($menu);
		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = _("for this $type");
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
	 * Show scheduling queue
	 *
	 * @param $host_filter string
	 * @param $service_filter string
	 */
	public function scheduling_queue($host_filter = '', $service_filter = '')
	{

		$host_filter = $this->input->get('host', $host_filter);
		$service_filter = $this->input->get('service', $service_filter);
		$linkprovider = LinkProvider::factory();

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
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.default.items_per_page'));
		$pagination = new CountlessPagination(array('items_per_page' => $items_per_page, 'total_items' => $total));

		if ($total <= $pagination->items_per_page * $pagination->current_page) {
			$pagination->hide_next = true;
		}

		$raw = array_slice($raw, ($pagination->current_page - 1) * $pagination->items_per_page, $pagination->items_per_page);

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
		$form .= _('Search for ');
		$form .= '<label> ' . _('Host') . ': <input type="text" name="host" value="' . $host_filter . '" /></label>';
		$form .= '<label> ' . _('Service') . ': <input type="text" name="service" value="' . $service_filter . '" /></label>';
		$form .= '<input type="submit" value="' . _('Search') . '" /></form>';

		$this->template->toolbar->info($form);
		$this->template->toolbar->info($pagination->render());

		if ($host_filter || $service_filter) {
			$this->template->toolbar->info(
				' <span> Do you want to <a href="' .
				$linkprovider->get_url(Router::$controller, Router::$method) .
				'">reset the search filter?</a></span> '
			);
		}

	}
}
