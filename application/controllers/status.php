<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Status controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Status_Controller extends Authenticated_Controller {
	public $img_sort_up = false;
	public $img_sort_down = false;
	public $hoststatustypes = false;
	public $servicestatustypes = false;
	public $hostprops = false;
	public $serviceprops = false;
	public $cmd_ok = false;
	public $cmd_host_ok = false;
	public $cmd_svc_ok = false;

	public function __construct()
	{
		parent::__construct();

		# load current status for host/service status totals
		$this->xtra_js[] = $this->add_path('/js/widgets.js');

		# decide what kind of commands
		# that the current user is authorized for
		$contact = Contact_Model::get_contact();
		if (!empty($contact)) {
			$contact = $contact->current();
			$this->cmd_ok = $contact->can_submit_commands;
		}
		unset($contact);

		$auth = Nagios_auth_Model::instance();
		$this->cmd_host_ok = $auth->command_hosts_root;
		$this->cmd_svc_ok = $auth->command_services_root;
		unset($auth);

		# add context menu items (hidden in html body)
		$this->template->context_menu = $this->add_view('status/context_menu');
	}

	/**
	 * Equivalent to style=hostdetail
	 *
	 * @param $host
	 * @param $hoststatustypes
	 * @param $sort_order
	 * @param $sort_field
	 * @param $show_services
	 */
	public function host($host='all', $hoststatustypes=false, $sort_order='ASC', $sort_field='name', $show_services=false, $group_type=false, $serviceprops=false, $hostprops=false)
	{
		$host = $this->input->get('host', $host);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$sort_order = $this->input->get('sort_order', $sort_order);
		$sort_field = $this->input->get('sort_field', $sort_field);
		#$show_services = $this->input->get('show_services', $show_services);
		$group_type = $this->input->get('group_type', $group_type);
		#$serviceprops = $this->input->get('serviceprops', $serviceprops);
		$hostprops = $this->input->get('hostprops', $hostprops);
		$noheader = $this->input->get('noheader', false);
		$group_type = strtolower($group_type);

		$host = trim($host);

		$replace = array(
			1  => _('UP'),
			2  => _('Down'),
			4  => _('Unreachable'),
			6  => _('All problems'),
			64 => _('Pending')
		);

		$title = _('Monitoring » Host details').($hoststatustypes != false ? ' » '.$replace[$hoststatustypes] : '');
		$this->template->title = $title;

		$this->template->content = $this->add_view('status/host');
		list($hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter) = $this->classic_filter('host', $host, false, false, $hoststatustypes, $hostprops, false, $serviceprops);
		$this->template->content->noheader = $noheader;
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$widget = widget::get(Ninja_widget_Model::get(Router::$controller, 'status_totals'), $this);
		$widget->set_host($host);
		$widget->set_hoststatus($hoststatustypes);
		$this->template->content->widgets = array($widget->render());
		widget::set_resources($widget, $this);
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;

		# set sort images, used in header_links() below
		$this->img_sort_up = $this->img_path('icons/16x16/up.gif');
		$this->img_sort_down = $this->img_path('icons/16x16/down.gif');

		# assign specific header fields and values for current method
		$header_link_fields = array(
			array('title' => _('Status'),     	  'sort_field_db' => 'state', 		'sort_field_str' => 'host status'),
			array('title' => _('Host'),      	  'sort_field_db' => 'name', 		'sort_field_str' => 'host name'),
			array('title' => _('Last Check'),         'sort_field_db' => 'last_check', 	'sort_field_str' => 'last check time'),
			array('title' => _('Duration'),           'sort_field_db' => 'duration', 	'sort_field_str' => 'state duration'),
			array('title' => _('Status Information'), 'sort_field_db' => 'plugin_output',	'sort_field_str' => 'status information')
		);

		$show_display_name = config::get('config.show_display_name', '*');
		if ($show_display_name) {
			$header_link_fields[] = array('title' => _('Display Name'), 'sort_field_db' => 'display_name', 'sort_field_str' => 'display name');
		}
		$this->template->content->show_display_name = $show_display_name;

		$show_notes = config::get('config.show_notes', '*');
		if ($show_notes) {
			$header_link_fields[] = array('title' => _('Notes'), 'sort_field_db' => 'notes', 'sort_field_str' => 'notes');
		}
		$this->template->content->show_notes = $show_notes;

		# build header links array
		foreach ($header_link_fields as $fields) {
			if (sizeof($fields) > 1) {
				$header_links[] = $this->header_links(Router::$method, $host, $fields['title'], Router::$method, $fields['sort_field_db'], $fields['sort_field_str'], $hoststatustypes, false);
			} else {
				$header_links[] = $this->header_links(Router::$method, $host, $fields['title']);
			}
		}

		$this->template->content->header_links = $header_links;

		$shown = strtolower($host) == 'all' ? _('All Hosts') : _('Host')." '".$host."'";
		$sub_title = _('Host Status Details For').' '.$shown;
		$this->template->content->sub_title = $sub_title;
		$this->template->content->pending_output = _('Host check scheduled for %s');

		$ls         = Livestatus::instance();
		$result     = $ls->getHosts(array('filter' => $hostfilter, 'paging' => $this, 'order' => array($sort_field => $sort_order)));

		$this->template->content->date_format_str = nagstat::date_format();
		$this->template->content->result = $result;

		if (empty($group_type)) {
			if ($host == 'all') {
				$label_view_for = _('for all host groups');
				$page_links = array(
					 _('Service status detail') => Router::$controller.'/hostgroup/all?style=detail',
					 _('Status overview') => Router::$controller.'/hostgroup/all',
					 _('Status summary') => Router::$controller.'/hostgroup/all?style=summary',
					 _('Status grid') => Router::$controller.'/hostgroup_grid/all'
				);
			} else {
				$label_view_for = _('for this host');
				$page_links = array(
					 _('Alert history') => 'showlog/alert_history/'.$host,
					 _('Notifications') => 'notifications/host/'.$host,
					 _('Service status detail for all hosts') => Router::$controller.'/service/all'
				);
			}
		} else {
			if ($group_type == 'hostgroup') {
				$label_view_for = _('for this host group');
				$page_links = array(
					_('Host status detail') => Router::$controller.'/host/all',
					_('Service status detail') => Router::$controller.'/hostgroup/'.$host.'?style=detail',
					_('Status overview') => Router::$controller.'/'.$group_type.'/'.$host,
					_('Status summary') => Router::$controller.'/'.$group_type.'_summary/'.$host,
					_('Status grid') => Router::$controller.'/'.$group_type.'_grid/'.$host
				);

			} else {
				$label_view_for = _('for this service group');
				$page_links = array(
					_('Status overview') => Router::$controller.'/'.$group_type.'/'.$host,
					_('Status summary') => Router::$controller.'/'.$group_type.'/'.$host.'?style=summary',
					_('Service status grid') => Router::$controller.'/'.$group_type.'_grid/'.$host,
					_('Service status detail for all service groups') => Router::$controller.'/'.$group_type.'/all?style=detail'
				);
			}
		}

		# fetch all comments to be able to detect if we should display comment icon
		$host_comments = Comment_Model::count_all_comments_by_object();
		$this->template->content->host_comments = $host_comments;


		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = $label_view_for;
		}
	}

	/**
	 * List status details for hosts and services
	 *
	 * @param $name
	 * @param $servicestatustypes
	 * @param $hoststatustypes
	 * @param $sort_order
	 * @param $sort_field
	 * @param $group_type
	 */
	public function service($name='all', $hoststatustypes=false, $servicestatustypes=false, $service_props=false, $sort_order='ASC', $sort_field='host_name', $group_type=false, $hostprops=false)
	{
		$name = $this->input->get('name', $name);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$service_props = $this->input->get('serviceprops', $service_props);
		$service_props = $this->input->get('service_props', $service_props);
		$hostprops = $this->input->get('hostprops', $hostprops);
		$sort_order = $this->input->get('sort_order', $sort_order);
		$sort_field = $this->input->get('sort_field', $sort_field);
		$group_type = $this->input->get('group_type', $group_type);
		$noheader = $this->input->get('noheader', false);
		$group_type = strtolower($group_type);

		$name = trim($name);

		$srv_replace = array(
			1  => _('OK'),
			2  => _('Warning'),
			4  => _('Critical'),
			8  => _('Unknown'),
			14 => _('All problems'),
			64 => _('Pending'),
			65 => _('Non-problem services'),
			71 => _('All services'),
			78 => _('All problems')
		);

		$host_replace = array(
			1  => _('Host OK'),
			2  => _('Host down'),
			4  => _('Host unreachable'),
			6  => _('All host problems'),
			64 => _('Host pending'),
			65 => _('Non-problem hosts'),
			71 => _('All hosts'),
		);

		$title = _('Monitoring » Service details').
			($hoststatustypes != false ? ' » '.$host_replace[$hoststatustypes] : '').
			($servicestatustypes != false ? ' » '.$srv_replace[$servicestatustypes] : '');

		$this->template->title = $title;

		$this->template->content = $this->add_view('status/service');
		list($hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter) = $this->classic_filter('service', $name, false, false, $hoststatustypes, $hostprops, $servicestatustypes, $service_props);
		$this->template->content->noheader = $noheader;
		$this->template->content->group_type = $group_type;
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$widget = widget::get(Ninja_widget_Model::get(Router::$controller, 'status_totals'), $this);
		$this->template->content->widgets = array($widget->render());
		widget::set_resources($widget, $this);
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;

		$this->template->content->date_format_str = nagstat::date_format();

		# set sort images, used in header_links() below
		$this->img_sort_up = $this->img_path('icons/arrow-up.gif');
		$this->img_sort_down = $this->img_path('icons/arrow-down.gif');

		# assign specific header fields and values for current method
		$header_link_fields = array(
			array('title' => _('Host'), 		  'sort_field_db' => 'host_name', 	'sort_field_str' => 'host name'),
			array('title' => _('Status'), 		  'sort_field_db' => 'state', 		'sort_field_str' => 'service status'),
			array('title' => _('Service'), 	  	  'sort_field_db' => 'description', 	'sort_field_str' => 'service name'),
			array('title' => _('Last Check'), 	  'sort_field_db' => 'last_check', 	'sort_field_str' => 'last check time'),
			array('title' => _('Duration'), 	  'sort_field_db' => 'duration', 	'sort_field_str' => 'state duration'),
			array('title' => _('Attempt'), 		  'sort_field_db' => 'current_attempt', 'sort_field_str' => 'attempt'),
			array('title' => _('Status Information'), 'sort_field_db' => 'plugin_output', 	'sort_field_str' => 'status information')
		);

		$show_display_name = config::get('config.show_display_name', '*');
		if ($show_display_name) {
			$header_link_fields[] = array('title' => _('Display Name'), 'sort_field_db' => 'display_name', 'sort_field_str' => 'display name');
		}
		$this->template->content->show_display_name = $show_display_name;

		$show_notes = config::get('config.show_notes', '*');
		if ($show_notes) {
			$header_link_fields[] = array('title' => _('Notes'), 'sort_field_db' => 'notes', 'sort_field_str' => 'notes');
		}
		$this->template->content->show_notes = $show_notes;


		# build header links array
		foreach ($header_link_fields as $fields) {
			if (sizeof($fields) > 1) {
				$header_links[] = $this->header_links('service', $name, $fields['title'], Router::$method, $fields['sort_field_db'], $fields['sort_field_str'], $hoststatustypes, $servicestatustypes, $service_props,$hostprops);
			} else {
				$header_links[] = $this->header_links('service', $name, $fields['title']);
			}
		}

		$this->template->content->header_links = $header_links;

		$shown = strtolower($name) == 'all' ? _('All hosts') : _('Host')." '".$name."'";

		$ls         = Livestatus::instance();
		$result     = $ls->getServices(array('filter' => $servicefilter, 'paging' => $this, 'order' => array($sort_field => $sort_order)));

		$this->template->content->is_svc_details = false;

		if (!empty($group_type)) {
			$shown = $group_type == 'servicegroup' ? _('Service Group') : _('Host Group');
			$shown .= " '".$name."'";
			# convert 'servicegroup' to 'service' and 'hostgroup' to 'host'
			$grouptype = str_replace('group', '', $group_type);
			$hostlist = Group_Model::get_group_hoststatus($grouptype, $name, $hoststatustypes, $servicestatustypes);
			$group_hosts = false;
			if ($hostlist !== false)
				foreach ($hostlist as $host_info) {
					$group_hosts[] = $host_info->host_name;
				}

			# servicegroups should only show services in the group
			if ($group_type == 'servicegroup') {
				$result = Group_Model::get_group_info($grouptype, $name, $hoststatustypes, $servicestatustypes, $service_props, $hostprops);
				$tot = $result !== false ? count($result) : 0;
				unset($result);
				$pagination = new Pagination(
					array(
						'total_items'=> $tot,
						'items_per_page' => $items_per_page
					)
				);
				$limit = $pagination->sql_limit;
				$result = Group_Model::get_group_info($grouptype, $name, $hoststatustypes,
					$servicestatustypes, $service_props, $hostprops, $limit, $sort_field, $sort_order);
				$this->template->content->is_svc_details = true;
			} else {
				$host_model->num_per_page = false;
				$host_model->offset = false;
				$host_model->count = true;

				$host_model->set_host_list($group_hosts);
				$result_cnt = $host_model->get_host_status();

				$tot = $result_cnt !== false ? $result_cnt : 0;
				$pagination = new Pagination(
					array(
						'total_items'=> $tot,
						'items_per_page' => $items_per_page
					)
				);
				$offset = $pagination->sql_offset;
				$host_model->count = false;
				$host_model->num_per_page = $items_per_page;
				$host_model->offset = $offset;

				$host_model->set_host_list($group_hosts);
				$host_model->set_sort_field($sort_field);
				$host_model->set_sort_order($sort_order);

				$result = $host_model->get_host_status();
			}
		} else {
/* TODO: implement */
/*
			$host_model->num_per_page = false;
			$host_model->offset = false;
			$host_model->count = true;

			if (strstr($name, ',')) {
				$name = explode(',', $name);
			}

			$host_model->set_host_list($name);
			$result_cnt = $host_model->get_host_status();
			$tot = $result_cnt !== false ? $result_cnt : 0;
			$pagination = new Pagination(
				array(
					'total_items'=> $tot,
					'items_per_page' => $items_per_page
				)
			);
			$offset = $pagination->sql_offset;
			$host_model->count = false;
			$host_model->num_per_page = $items_per_page;
			$host_model->offset = $offset;

			$host_model->set_sort_field($sort_field);
			$host_model->set_sort_order($sort_order);

			$host_model->set_host_list($name);
			$result = $host_model->get_host_status();
*/
		}
		$sub_title = _('Service Status Details For').' '.$shown;
		$this->template->content->sub_title = $sub_title;

		$this->template->content->pending_output = _('Service check scheduled for %s');
		$this->template->content->result = $result;
		$this->template->content->style = 'detail';
		if (empty($group_type)) {
			if ($name == 'all') {
				$label_view_for = _('for all hosts');
				$page_links = array(
					 _('Alert history') => 'showlog/alert_history/',
					 _('Notifications') => 'notifications/host/'.$name,
					 _('Host status detail') => Router::$controller.'/host/all'
				);
			} else {
				$label_view_for = _('for this host');
				$page_links = array(
					 _('Alert history') => 'showlog/alert_history/'.$name,
					 _('Notifications') => 'notifications/host/'.$name,
					 _('Service status detail for all hosts') => Router::$controller.'/service/all',
				);
			}
		} else {
			if ($group_type == 'hostgroup') {
				if ($name == 'all') {
					$label_view_for = _('for all host groups');
					$page_links = array(
						_('Host status detail') => Router::$controller.'/host/all',
						_('Status overview') => Router::$controller.'/'.$group_type.'/all',
						_('Status summary') => Router::$controller.'/'.$group_type.'/all?style=summary',
						_('Status grid') => Router::$controller.'/'.$group_type.'_grid/all',
					);
				} else {
					$label_view_for = _('for this host group');
					$page_links = array(
						_('Service status detail for all host groups') => Router::$controller.'/'.$group_type.'/all?style=detail',
						_('Host status detail') => Router::$controller.'/host/'.$name.'?group_type='.$group_type,
						_('Status overview') => Router::$controller.'/'.$group_type.'/'.$name,
						_('Status summary') => Router::$controller.'/'.$group_type.'_summary/'.$name,
						_('Status grid') => Router::$controller.'/'.$group_type.'_grid/'.$name,
					);
				}
			} else {
				# servicegroup links
				if ($name == 'all') {
					$label_view_for = _('for all service groups');
					$page_links = array(
						_('Status overview') => Router::$controller.'/'.$group_type.'/all',
						_('Status summary') => Router::$controller.'/'.$group_type.'/all?style=summary',
						_('Service status grid') => Router::$controller.'/'.$group_type.'_grid/all'
					);
				} else {
					$label_view_for = _('for this service group');
					$page_links = array(
						_('Status overview') => Router::$controller.'/'.$group_type.'/'.$name,
						_('Status summary') => Router::$controller.'/'.$group_type.'/'.$name.'?style=summary',
						_('Service status grid') => Router::$controller.'/'.$group_type.'_grid/'.$name,
						_('Service status detail for all service groups') => Router::$controller.'/'.$group_type.'/all'
					);
				}
			}
		}

		# fetch all comments to be able to detect if we should display comment icon
		$host_comments = Comment_Model::count_all_comments_by_object();
		$this->template->content->host_comments = $host_comments;

		$svc_comments = Comment_Model::count_all_comments_by_object(true);
		$this->template->content->comments = $svc_comments;

		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = $label_view_for;
		}
	}

	/**
	*	Wrapper for Unhandled Problems link in menu
	* 	Equivalent to :
	* 		/status/service/all/?hoststatustypes=71&servicestatustypes=78&hostprops=10&service_props=10
	*/
	public function unhandled_problems()
	{
		return $this->service('all',
			(nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE),
			(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_PENDING),
			(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED), null, null, null,
			(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED));
	}

	/**
	*	Wrapper for Service problems link in menu
	* 	Equivalent to:
	* 		/status/service/all?servicestatustypes=14
	*/
	public function service_problems()
	{
		return $this->service('all', null, (nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN));
	}

	/**
	*	Wrapper for Host problems link in menu
	* 	Equivalent to:
	* 		/status/host/all/6
	*/
	public function host_problems()
	{
		return $this->host('all', (nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE));
	}

	/**
	 * Show servicegroup status, wrapper for group('service', ...)
	 * @param $group
	 * @param $hoststatustypes
	 * @param $servicestatustypes
	 * @param $style
	 *
	 */
	public function servicegroup($group='all', $hoststatustypes=false, $servicestatustypes=false, $style='overview', $serviceprops=false, $hostprops=false)
	{
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$serviceprops = $this->input->get('serviceprops', $serviceprops);
		$hostprops = $this->input->get('hostprops', $hostprops);
		$style = $this->input->get('style', $style);
		$grouptype = 'service';
		$this->template->title = 'Servicegroup';
		return $this->group($grouptype, $group, $hoststatustypes, $servicestatustypes, $style, $serviceprops, $hostprops);
	}

	/**
	 * Show hostgroup status, wrapper for group('host', ...)
	 * @param $group
	 * @param $hoststatustypes
	 * @param $servicestatustypes
	 * @param $style
	 *
	 */
	public function hostgroup($group='all', $hoststatustypes=false, $servicestatustypes=false, $style='overview', $serviceprops=false, $hostprops=false)
	{
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$serviceprops = $this->input->get('serviceprops', $serviceprops);
		$hostprops = $this->input->get('hostprops', $hostprops);
		$style = $this->input->get('style', $style);
		$grouptype = 'host';
		return $this->group($grouptype, $group, $hoststatustypes, $servicestatustypes, $style, $serviceprops, $hostprops);
	}

	/**
	 * Show status for host- or servicegroups
	 *
	 * @param $grouptype
	 * @param $group
	 * @param $hoststatustypes
	 * @param $servicestatustypes
	 * @param $style
	 */
	public function group($grouptype='service', $group='all', $hoststatustypes=false, $servicestatustypes=false, $style='overview', $serviceprops=false, $hostprops=false)
	{
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.group_items_per_page', '*'));
		$grouptype = $this->input->get('grouptype', $grouptype);
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$serviceprops = $this->input->get('serviceprops', $serviceprops);
		$hostprops = $this->input->get('hostprops', $hostprops);
		$style = $this->input->get('style', $style);
		$noheader = $this->input->get('noheader', false);
		$group = trim($group);
		$hoststatustypes = strtolower($hoststatustypes)==='false' ? false : $hoststatustypes;

		$this->hoststatustypes = $hoststatustypes;
		$this->hostprops = $hostprops;
		$this->servicestatustypes = $servicestatustypes;
		$this->serviceprops = $serviceprops;

		switch ($style) {
			case 'overview':
				$this->template->title = _('Monitoring » ').$grouptype._('group overview');
				$this->template->header = _('Monitoring » ').$grouptype._('group overview');
				$this->template->content = $this->add_view('status/group_overview');
				$this->template->content->noheader = $noheader;
				break;
			case 'detail': case 'details':
				$this->template->title = $grouptype._('group » Details');
				return $this->service($group, $hoststatustypes, $servicestatustypes, $serviceprops, false, false, $grouptype.'group', $hostprops);
			case 'summary':
				return $this->_group_summary($grouptype, $group, $hoststatustypes, $servicestatustypes, $serviceprops, $hostprops);
		}
		$group_details = false;
		$groupname_tmp = false;
		if ($group == 'all') {
			$auth = Nagios_auth_Model::instance();
			if ($grouptype == 'host') {
				$auth_groups = $auth->get_authorized_hostgroups();
			} else {
				$auth_groups = $auth->get_authorized_servicegroups();
			}
			$tot = count($auth_groups);
			$pagination = new Pagination(
				array(
					'total_items'=> $tot,
					'items_per_page' => $items_per_page
				)
			);
			$offset = $pagination->sql_offset;
			$this->template->content->pagination = $pagination;
			$group_details = $grouptype == 'service' ? Servicegroup_Model::get_all($items_per_page, $offset) : Hostgroup_Model::get_all($items_per_page, $offset);
		} else {
			$group_details = $grouptype == 'service' ?
				Servicegroup_Model::get($group) :
				Hostgroup_Model::get($group);
		}

		$this->template->content->group_details = $group_details;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$content = $this->template->content;

		if ($grouptype == 'service') {
			$content->lable_header = strtolower($group) == 'all' ? _("Service Overview For All Service Groups") : _("Service Overview For Service Group");
		} else {
			$content->lable_header = strtolower($group) == 'all' ? _("Service Overview For All Host Groups") : _("Service Overview For Host Group");
		}
		$content->lable_host = _('Host');
		$content->lable_status = _('Status');
		$content->lable_services = _('Services');
		$content->lable_actions = _('Actions');
		$content->grouptype = $grouptype;
		$content->hoststatustypes = $hoststatustypes;
		$content->servicestatustypes = $servicestatustypes;
		if (empty($group_details)) {
			$this->template->content->error_message = _("No data found");
		}

		$widget = widget::get(Ninja_widget_Model::get(Router::$controller, 'status_totals'), $this);
		$widget->set_host($group);
		$widget->set_hoststatus($hoststatustypes);
		$widget->set_servicestatus($servicestatustypes);
		$widget->set_grouptype($grouptype.'group');
		$this->template->content->widgets = array($widget->render());
		widget::set_resources($widget, $this);
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;

		if ($grouptype == 'host') {
			if ($group == 'all') {
				$label_view_for = _('for all host groups');
				$page_links = array(
					_('Service status detail') => Router::$controller.'/'.$grouptype.'group/all?style=detail',
					_('Host status detail') => Router::$controller.'/host/all',
					_('Status summary') => Router::$controller.'/'.$grouptype.'group/all?style=summary',
					_('Status grid') => Router::$controller.'/'.$grouptype.'group_grid/all'
				);
			} else {
				$label_view_for = _('for this host groups');
				$page_links = array(
					_('Status overview for all host groups') => Router::$controller.'/'.$grouptype.'group/all?style=summary',
					_('Service status detail') => Router::$controller.'/'.$grouptype.'group/'.$group.'?style=detail',
					_('Host status detail') => Router::$controller.'/host/'.$group.'?group_type='.$grouptype.'group',
					_('Status summary') => Router::$controller.'/'.$grouptype.'group/'.$group.'?style=summary',
					_('Service status grid') => Router::$controller.'/'.$grouptype.'group_grid/'.$group.'?style=summary'
				);
			}
		} else {
			if ($group == 'all') {
				$label_view_for = _('for all service groups');
				$page_links = array(
					_('Service status detail') => Router::$controller.'/'.$grouptype.'group/all?style=detail',
					_('Status summary') => Router::$controller.'/'.$grouptype.'group/all?style=summary',
					_('Service status grid') => Router::$controller.'/'.$grouptype.'group_grid/all'
				);
			} else {
				$label_view_for = _('for this service groups');
				$page_links = array(
					_('Status overview') => Router::$controller.'/'.$grouptype.'group/'.$group,
					_('Status summary') => Router::$controller.'/'.$grouptype.'group/'.$group.'?style=summary',
					_('Service status grid') => Router::$controller.'/'.$grouptype.'group_grid/'.$group,
					_('Service status detail for all service groups') => Router::$controller.'/'.$grouptype.'/all'
				);
			}
		}
		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = $label_view_for;
		}
	}

	/**
	 * Display servicegroup summary
	 */
	public function servicegroup_summary($group='all', $hoststatustypes=false, $servicestatustypes=false, $serviceprops=false, $hostprops=false)
	{
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$serviceprops = $this->input->get('serviceprops', $serviceprops);
		$hostprops = $this->input->get('hostprops', $hostprops);
		$this->template->title = _('Servicegroup » Summary');
		return $this->_group_summary('service', $group, $hoststatustypes, $servicestatustypes, $serviceprops, $hostprops);
	}

	public function hostgroup_summary($group='all', $hoststatustypes=false, $servicestatustypes=false, $serviceprops=false, $hostprops=false)
	{
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$serviceprops = $this->input->get('serviceprops', $serviceprops);
		$hostprops = $this->input->get('hostprops', $hostprops);
		return $this->_group_summary('host', $group, $hoststatustypes, $servicestatustypes, $serviceprops, $hostprops);
	}

	/**
	*	Create group summary page
	*/
	public function _group_summary($grouptype='service', $group='all', $hoststatustypes=false, $servicestatustypes=false, $serviceprops=false, $hostprops=false)
	{
		$grouptype = $this->input->get('grouptype', $grouptype);
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$serviceprops = $this->input->get('serviceprops', $serviceprops);
		$hostprops = $this->input->get('hostprops', $hostprops);
		$noheader = $this->input->get('noheader', false);
		$this->template->title = _('Monitoring » ').$grouptype._('group summary');

		$group = trim($group);
		$this->template->content = $this->add_view('status/group_summary');
		$this->template->content->noheader = $noheader;
		$content = $this->template->content;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$ls = Livestatus::instance();
		if($grouptype == 'host') {
			list($hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter) = $this->classic_filter('service', false, $group, false, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops);
		} else {
			list($hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter) = $this->classic_filter('service', false, false, $group, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops);
		}

		# get all host/service groups
		if( $grouptype == 'host' ) {
		    $groups = $ls->getHostgroups(array('filter' => $hostgroupfilter, 'paginggroup' => $this ) );
		}
		else {
		    $groups = $ls->getServicegroups(array('filter' => $servicegroupfilter, 'paginggroup' => $this ) );
		}

		# set defaults for all groups
		$all_groups = array();
		foreach($groups as &$g) {
			$g['hosts_total']                            = 0;
			$g['hosts_pending']                          = 0;
			$g['hosts_up']                               = 0;
			$g['hosts_down']                             = 0;
			$g['hosts_down_and_unhandled']               = 0;
			$g['hosts_down_and_scheduled']               = 0;
			$g['hosts_down_and_ack']                     = 0;
			$g['hosts_down_and_disabled_active']         = 0;
			$g['hosts_down_and_disabled_passive']        = 0;
			$g['hosts_unreachable']                      = 0;
			$g['hosts_unreachable_and_unhandled']        = 0;
			$g['hosts_unreachable_and_downtime']         = 0;
			$g['hosts_unreachable_and_ack']              = 0;
			$g['hosts_unreachable_and_disabled_active']  = 0;
			$g['hosts_unreachable_and_disabled_passive'] = 0;

			$g['services_total']                         = 0;
			$g['services_pending']                       = 0;
			$g['services_ok']                            = 0;
			$g['services_warning']                       = 0;
			$g['services_warning_and_unhandled']         = 0;
			$g['services_warning_and_scheduled']         = 0;
			$g['services_warning_on_down_host']          = 0;
			$g['services_warning_and_ack']               = 0;
			$g['services_warning_and_disabled_active']   = 0;
			$g['services_warning_and_disabled_passive']  = 0;
			$g['services_unknown']                       = 0;
			$g['services_unknown_and_unhandled']         = 0;
			$g['services_unknown_and_scheduled']         = 0;
			$g['services_unknown_on_down_host']          = 0;
			$g['services_unknown_and_ack']               = 0;
			$g['services_unknown_and_disabled_active']   = 0;
			$g['services_unknown_and_disabled_passive']  = 0;
			$g['services_critical']                      = 0;
			$g['services_critical_and_unhandled']        = 0;
			$g['services_critical_and_scheduled']        = 0;
			$g['services_critical_on_down_host']         = 0;
			$g['services_critical_and_ack']              = 0;
			$g['services_critical_and_disabled_active']  = 0;
			$g['services_critical_and_disabled_passive'] = 0;
			$all_groups[$g['name']]                      = $g;
		}

		if( $grouptype == 'host' ) {
			# we need the hosts data
			$host_data = $ls->getHosts(array( 'filter' => $hostfilter ) );
			foreach($host_data as &$host) {
				foreach($host['groups'] as $g) {
					if(!isset($all_groups[$g])) { continue; }
					$this->_summary_add_host_stats( "", $all_groups[$g], $host );
				}
			}
		}

		# create a hash of all services
		$services_data = $ls->getServices(array( 'filter' => $servicefilter ) );

		$groupsname = "host_groups";
		if( $grouptype == 'service' ) {
			$groupsname = "groups";
		}

		$host_already_added = array();
		$uniq_services      = array();
		foreach($services_data as &$service) {
			if(isset($uniq_services[$service['host_name']][$service['description']])) { continue; }
			$uniq_services[$service['host_name']][$service['description']] = 1;

			foreach($service[$groupsname] as &$g) {
				if(!isset($all_groups[$g])) { continue; }
				if( $grouptype == 'service' ) {
					if( !isset($host_already_added[$g][$service['host_name']] )) {
						$this->_summary_add_host_stats( "host_", $all_groups[$g], $service );
						$host_already_added[$g][$service['host_name']] = 1;
					}
				}

				$all_groups[$g]['services_total']++;

				if( $service['has_been_checked'] == 0 ) { $all_groups[$g]['services_pending']++; }
				elseif ( $service['state'] == 0 ) { $all_groups[$g]['services_ok']++; }
				elseif ( $service['state'] == 1 ) { $all_groups[$g]['services_warning']++; }
				elseif ( $service['state'] == 2 ) { $all_groups[$g]['services_critical']++; }
				elseif ( $service['state'] == 3 ) { $all_groups[$g]['services_unknown']++; }

				if( $service['state'] == 1 and $service['scheduled_downtime_depth'] > 0 ) { $all_groups[$g]['services_warning_and_scheduled']++; }
				if( $service['state'] == 1 and $service['acknowledged'] == 1 )            { $all_groups[$g]['services_warning_and_ack']++; }
				if( $service['state'] == 1 and $service['checks_enabled'] == 0 and $service['check_type'] == 0 ) { $all_groups[$g]['services_warning_and_disabled_active']++; }
				if( $service['state'] == 1 and $service['checks_enabled'] == 0 and $service['check_type'] == 1 ) { $all_groups[$g]['services_warning_and_disabled_passive']++; }
				if( $service['state'] == 1 and $service['host_state'] > 0 )               { $all_groups[$g]['services_warning_on_down_host']++; }
				elseif ( $service['state'] == 1 and $service['checks_enabled'] == 1 and $service['host_state'] == 0 and $service['acknowledged'] == 0 and $service['scheduled_downtime_depth'] == 0 ) { $all_groups[$g]['services_warning_and_unhandled']++; }

				if( $service['state'] == 2 and $service['scheduled_downtime_depth'] > 0 ) { $all_groups[$g]['services_critical_and_scheduled']++; }
				if( $service['state'] == 2 and $service['acknowledged'] == 1 )            { $all_groups[$g]['services_critical_and_ack']++; }
				if( $service['state'] == 2 and $service['checks_enabled'] == 0 and $service['check_type'] == 0 ) { $all_groups[$g]['services_critical_and_disabled_active']++; }
				if( $service['state'] == 2 and $service['checks_enabled'] == 0 and $service['check_type'] == 1 ) { $all_groups[$g]['services_critical_and_disabled_passive']++; }
				if( $service['state'] == 2 and $service['host_state'] > 0 )               { $all_groups[$g]['services_critical_on_down_host']++; }
				elseif ( $service['state'] == 2 and $service['checks_enabled'] == 1 and $service['host_state'] == 0 and $service['acknowledged'] == 0 and $service['scheduled_downtime_depth'] == 0 ) { $all_groups[$g]['services_critical_and_unhandled']++; }

				if( $service['state'] == 3 and $service['scheduled_downtime_depth'] > 0 ) { $all_groups[$g]['services_unknown_and_scheduled']++; }
				if( $service['state'] == 3 and $service['acknowledged'] == 1 )            { $all_groups[$g]['services_unknown_and_ack']++; }
				if( $service['state'] == 3 and $service['checks_enabled'] == 0 and $service['check_type'] == 0 ) { $all_groups[$g]['services_unknown_and_disabled_active']++; }
				if( $service['state'] == 3 and $service['checks_enabled'] == 0 and $service['check_type'] == 1 ) { $all_groups[$g]['services_unknown_and_disabled_passive']++; }
				if( $service['state'] == 3 and $service['host_state'] > 0 )               { $all_groups[$g]['services_unknown_on_down_host']++; }
				elseif ( $service['state'] == 3 and $service['checks_enabled'] == 1 and $service['host_state'] == 0 and $service['acknowledged'] == 0 and $service['scheduled_downtime_depth'] == 0 ) { $all_groups[$g]['services_unknown_and_unhandled']++; }
			}
		}

		foreach($all_groups as &$g) {
			# remove empty groups
			if( $g['hosts_total'] + $g['services_total'] == 0 ) {
				unset($all_groups[$g['name']]);
			}
		}

		if(!count($all_groups)) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = sprintf(_("The requested group ('%s') wasn't found"), $group);
			return;
		}
		$content->group_details = $all_groups;

		$widget = widget::get(Ninja_widget_Model::get(Router::$controller, 'status_totals'), $this);
		$widget->set_host($group);
		$widget->set_hoststatus($hoststatustypes);
		$widget->set_servicestatus($servicestatustypes);
		$widget->set_grouptype($grouptype);
		$this->template->content->widgets = array($widget->render());
		widget::set_resources($widget, $this);
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;

		if (strtolower($group) == 'all') {
			$content->lable_header = $grouptype == 'service' ? _('Status Summary For All Service Groups') : _('Status Summary For All Host Groups');
		} else {
			$label_header = $grouptype == 'service' ? _('Status Summary For Service Group ') : _('Status Summary For Host Group ');
			$content->lable_header = $label_header."'".$group."'";
		}

		$content->grouptype = $grouptype;
		$content->hoststatustypes = $hoststatustypes;
		$content->hostproperties = $hostprops;
		$content->servicestatustypes = $servicestatustypes;
		$content->serviceproperties = $serviceprops;

		if ($grouptype == 'host') {
			$content->label_group_name = _('Host Group');
			if ($group == 'all') {
				$label_view_for = _('For all host groups');
				$page_links = array(
					_('Service status detail') => Router::$controller.'/'.$grouptype.'group/all?style=detail',
					_('Host status detail') => Router::$controller.'/host/all?group_type='.$grouptype.'group',
					_('Status overview') => Router::$controller.'/'.$grouptype.'group/all',
					_('Status grid') => Router::$controller.'/'.$grouptype.'group_grid/all'
				);
			} else {
				$label_view_for = _('For this host groups');
				$page_links = array(
					_('Status summary for all host groups') => Router::$controller.'/'.$grouptype.'group/all?style=summary',
					_('Service status detail') => Router::$controller.'/'.$grouptype.'group/'.$group.'?style=detail',
					_('Host status detail') => Router::$controller.'/host/'.$group.'?group_type='.$grouptype.'group',
					_('Status overview') => Router::$controller.'/'.$grouptype.'group/'.$group,
					_('Status grid') => Router::$controller.'/'.$grouptype.'group_grid/'.$group
				);
			}

		} else {
			$content->label_group_name = _('Service Group');
			if ($group == 'all') {
				$label_view_for = _('For all service groups');
				$page_links = array(
					_('Service status detail') => Router::$controller.'/servicegroup/all?style=detail',
					_('Status overview') => Router::$controller.'/servicegroup/all',
					_('Service status grid') => Router::$controller.'/servicegroup_grid/all'
				);
			} else {
				$label_view_for = _('For this service group');
				$page_links = array(
						_('Service status detail') => Router::$controller.'/host/'.$group.'?group_type='.$grouptype.'group',
						_('Status overview') => Router::$controller.'/'.$grouptype.'group/'.$group,
						_('Service status grid') => Router::$controller.'/'.$grouptype.'group_grid/'.$group,
						_('Status summary for all service groups') => Router::$controller.'/'.$grouptype.'group/all?style=summary'
					);
			}
		}
		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = $label_view_for;
		}
	}

	/**
	*	Show a grid of hostgroup(s)
	* 	A wrapper for group_grid('host')
	*
	*/
	public function hostgroup_grid($group='all', $hoststatustypes=false, $servicestatustypes=false)
	{
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.group_items_per_page', '*'));
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$noheader = $this->input->get('noheader', false);

		$this->template->content = $this->add_view('status/hostgroup_grid');
		$this->template->content->noheader = $noheader;
		$content = $this->template->content;

		$this->hoststatustypes = $hoststatustypes;
		$this->servicestatustypes = $servicestatustypes;

		$this->template->title = _('Monitoring » ')._('servicegroup grid');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$widget = widget::get(Ninja_widget_Model::get(Router::$controller, 'status_totals'), $this);
		$widget->set_host($group);
		$widget->set_hoststatus($hoststatustypes);
		$widget->set_servicestatus($servicestatustypes);
		$widget->set_grouptype('host');
		$this->template->content->widgets = array($widget->render());
		widget::set_resources($widget, $this);
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;

		$content->label_host = _('Host');
		$content->label_services = _('Services');
		$content->label_actions = _('Actions');

		$ls = Livestatus::instance();
		list($hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter) = $this->classic_filter('host', false, $group, false, $hoststatustypes, false, $servicestatustypes, false);
		$groups   = $ls->getHostgroups(array('filter' => $hostgroupfilter, 'paginggroup' => $this ) );
		$hosts    = $ls->getHosts(array('filter' => $hostfilter));
		$services = $ls->getServices(array('filter' => $servicefilter));

		$groupshash = array();
		foreach($groups as &$gr) {
			$groupshash[$gr['name']] =& $gr;
		}

		$hostshash = array();
		foreach($hosts as &$host) {
			$hostshash[$host['name']] =& $host;
			foreach($host['groups'] as $g) {
				if(!isset($groupshash[$g]['hosts'])) { $groupshash[$g]['hosts'] = array(); }
				$groupshash[$g]['hosts'][$host['name']] =& $host;
			}
		}

		foreach($services as &$svc) {
			if(!isset($hostshash[$svc['host_name']]['services'])) { $hostshash[$svc['host_name']]['services'] = array(4 => array(), 0 => array(), 1 => array(), 2 => array(), 3 => array()); }
			if($svc['has_been_checked'] == 0) { $state = 4; } { $state = $svc['state']; }
			$hostshash[$svc['host_name']]['services'][$state][$svc['description']] =& $svc;
		}

		$content->group_details = $groups;

		if (strtolower($group) == 'all') {
			$content->label_header = _('Status Grid For All Host Groups');
		} else {
			# make sure we have the correct hostgroup
			$group_info_res = Hostgroup_Model::get($group);
			$label_header = _('Status Grid For Host Group ');
			$content->label_header = $label_header."'".$group."'";
		}

		$content->error_message = _('No hostgroup data found');
		$content->grouptype = 'host';
		$content->icon_path	= $this->img_path('icons/16x16/');
		$content->label_host_extinfo = _('View Extended Information For This Host');
		$content->label_service_status = _('View Service Details For This Host');
		$content->label_status_map = _('Locate Host On Map');
		$nacoma_link = false;
		/**
		 * Modify config/config.php to enable NACOMA
		 * and set the correct path in config/config.php,
		 * if installed, to use this
		 */
		if (nacoma::link()===true) {
			$content->label_nacoma = _('Configure this host using NACOMA (Nagios Configuration Manager)');
			$content->nacoma_path = Kohana::config('config.nacoma_path');
		}

		/**
		 * Enable PNP4Nagios integration
		 * Set correct path in config/config.php
		 */
		$pnp_link = false;
		if (Kohana::config('config.pnp4nagios_path')!==false) {
			$content->label_pnp = _('Show performance graph');
			$content->pnp_path = url::base(true) . 'pnp/?';
		}

		if ($group == 'all') {
			$label_host_status_details = _('Service status detail');
			$label_group_status_details = _('Host status detail');
			$label_group_status_overview = _('Status overview');
			$label_group_status_summary = _('Status summary');
			$label_view_for = _('for all host groups');
			$page_links	 = array(
				$label_host_status_details => Router::$controller.'/hostgroup/all?style=detail',
				$label_group_status_details => Router::$controller.'/host/all',
				$label_group_status_overview => Router::$controller.'/hostgroup/all',
				$label_group_status_summary => Router::$controller.'/hostgroup/all?style=summary'
			);
		} else {
			$label_host_status_grid = _('Status grid for all host groups');
			$label_group_service_status_details = _('Service status detail');
			$label_group_host_status_details = _('Host status detail');
			$label_group_status_overview = _('Status overview');
			$label_group_status_summary = _('Status summary');
			$label_view_for = _('for this host group');
			$page_links = array(
				$label_host_status_grid => Router::$controller.'/hostgroup_grid/all',
				$label_group_service_status_details => Router::$controller.'/hostgroup/'.$group.'?style=detail',
				$label_group_host_status_details => Router::$controller.'/host/'.$group.'?group_type=hostgroup',
				$label_group_status_overview => Router::$controller.'/hostgroup/'.$group,
				$label_group_status_summary => Router::$controller.'/hostgroup/'.$group.'?style=summary'
			);
		}
		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = $label_view_for;
		}
	}

	/**
	*	Show a grid of servicegroup(s)
	* 	A wrapper for group_grid('services')
	*
	*/
	public function servicegroup_grid($group='all', $hoststatustypes=false, $servicestatustypes=false)
	{
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.group_items_per_page', '*'));
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$noheader = $this->input->get('noheader', false);

		$this->template->content = $this->add_view('status/servicegroup_grid');
		$this->template->content->noheader = $noheader;
		$content = $this->template->content;

		$this->hoststatustypes = $hoststatustypes;
		$this->servicestatustypes = $servicestatustypes;

		$this->template->title = _('Monitoring » ')._('servicegroup grid');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$widget = widget::get(Ninja_widget_Model::get(Router::$controller, 'status_totals'), $this);
		$widget->set_host($group);
		$widget->set_hoststatus($hoststatustypes);
		$widget->set_servicestatus($servicestatustypes);
		$widget->set_grouptype('service');
		$this->template->content->widgets = array($widget->render());
		widget::set_resources($widget, $this);
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;

		$ls = Livestatus::instance();
		list($hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter) = $this->classic_filter('service', false, false, $group, $hoststatustypes, false, $servicestatustypes, false);
		$groups   = $ls->getServicegroups(array('filter' => $hostgroupfilter, 'paginggroup' => $this ) );
		$hosts    = $ls->getHosts(array('filter' => $hostfilter));
		$services = $ls->getServices(array('filter' => $servicefilter));

		$groupshash = array();
		foreach($groups as &$gr) {
			$groupshash[$gr['name']] =& $gr;
		}

		$hostshash = array();
		foreach($hosts as &$host) {
			$hostshash[$host['name']] =& $host;
		}

		foreach($services as &$svc) {
			foreach($svc['groups'] as $g) {
				if(!isset($groupshash[$g]['hosts'])) { $groupshash[$g]['hosts'] = array(); }
				$groupshash[$g]['hosts'][$svc['host_name']] =& $hostshash[$svc['host_name']];

				if(!isset($groupshash[$g]['services'][$svc['host_name']])) { $groupshash[$g]['services'][$svc['host_name']] = array(4 => array(), 0 => array(), 1 => array(), 2 => array(), 3 => array()); }
				if($svc['has_been_checked'] == 0) { $state = 4; } { $state = $svc['state']; }
				$groupshash[$g]['services'][$svc['host_name']][$state][$svc['description']] =& $svc;
			}
		}
		$content->group_details = $groups;

		if (strtolower($group) == 'all') {
			$content->label_header = _('Status Grid For All Service Groups');
		} else {
			# make sure we have the correct servicegroup
			$label_header = _('Status Grid For Service Group ');
			$content->label_header = $label_header."'".$group."'";
		}

		$content->error_message = _('No servicegroup data found');
		$content->grouptype = 'service';
		$content->icon_path	= $this->img_path('icons/16x16/');
		$nacoma_link = false;
		/**
		 * Modify config/config.php to enable NACOMA
		 * and set the correct path in config/config.php,
		 * if installed, to use this
		 */
		if (nacoma::link()===true) {
			$content->nacoma_path = Kohana::config('config.nacoma_path');
		}

		/**
		 * Enable PNP4Nagios integration
		 * Set correct path in config/config.php
		 */
		$pnp_link = false;
		if (Kohana::config('config.pnp4nagios_path')!==false) {
			$content->pnp_path = url::base(true) . 'pnp/?';
		}

		if ($group == 'all') {
			$label_view_for = _('for all service groups');
			$page_links = array(
				_('Service status detail') => Router::$controller.'/servicegroup/all?style=detail',
				_('Status overview') => Router::$controller.'/servicegroup/all',
				_('Status summary') => Router::$controller.'/servicegroup/all?style=summary'
			);
		} else {
			$label_view_for = _('for this service group');
			$page_links = array(
					_('Service status detail') => Router::$controller.'/host/'.$group.'?group_type=servicegroup',
					_('Status overview') => Router::$controller.'/servicegroup/'.$group,
					_('Status summary') => Router::$controller.'/servicegroup/'.$group.'?style=summary',
					_('Service status grid for all service groups') => Router::$controller.'/servicegroup_grid/all'
				);
		}
		if (isset($page_links)) {
			$this->template->content->page_links = $page_links;
			$this->template->content->label_view_for = $label_view_for;
		}
	}

	/**
	 * Create header links for status listing
	 */
	private function header_links(
			$type='host',
			$filter_object='all',
			$title=false,
			$method=false,
			$sort_field_db=false,
			$sort_field_str=false,
			$host_status=false,
			$service_status=false,
			$service_props=false,
			$host_props=false)
	{

		$type = trim($type);
		$filter_object = trim($filter_object);
		$title = trim($title);
		if (empty($type) || empty($title))  {
			return false;
		}
		$header = false;
		$lable_ascending = _('ascending');
		$lable_descending = _('descending');
		$lable_sort_by = _('Sort by');
		$lable_last = _('last');
		switch ($type) {
			case 'host': case 'host_problems':
				$header['title'] = $title;
				if (!empty($method) &&!empty($filter_object) && !empty($sort_field_db)) {
					$header['url_asc'] = Router::$controller.'/'.$method.'/'.$filter_object.'?hoststatustypes='.$host_status.'&sort_order='.nagstat::SORT_ASC.'&sort_field='.$sort_field_db;
					$header['alt_asc'] = $lable_sort_by.' '.$lable_last.' '.$sort_field_str.' ('.$lable_ascending.')';
					$header['img_asc'] = $this->img_sort_up;
					$header['url_desc'] = Router::$controller.'/'.$method.'/'.$filter_object.'?hoststatustypes='.$host_status.'&sort_order='.nagstat::SORT_DESC.'&sort_field='.$sort_field_db;
					$header['img_desc'] = $this->img_sort_down;
					$header['alt_desc'] = $lable_sort_by.' '.$sort_field_str.' ('.$lable_descending.')';
				}
				break;
			case 'service':
				$header['title'] = $title;
				if (!empty($method) &&!empty($filter_object) && !empty($sort_field_db)) {
					$header['url_asc'] = Router::$controller.'/'.$method.'/'.$filter_object.'?hoststatustypes='.$host_status.'&hostprops='.(int)$host_props.'&servicestatustypes='.$service_status.'&service_props='.(int)$service_props.'&sort_order='.nagstat::SORT_ASC.'&sort_field='.$sort_field_db;
					$header['img_asc'] = $this->img_sort_up;
					$header['alt_asc'] = $lable_sort_by.' '.$lable_last.' '.$sort_field_str.' ('.$lable_ascending.')';
					$header['url_desc'] = Router::$controller.'/'.$method.'/'.$filter_object.'?hoststatustypes='.$host_status.'&hostprops='.(int)$host_props.'&servicestatustypes='.$service_status.'&service_props='.(int)$service_props.'&sort_order='.nagstat::SORT_DESC.'&sort_field='.$sort_field_db;
					$header['img_desc'] = $this->img_sort_down;
					$header['alt_desc'] = $lable_sort_by.' '.$sort_field_str.' ('.$lable_descending.')';
				}
				break;
		}
		return $header;
	}

	/**
	*	shows service and host filters in use
	*/
	public function _show_filters($type, $host_statustype_filtername, $host_prop_filtername, $service_statustype_filtername, $service_prop_filtername)
	{
		$filters = $this->add_view('status/filters');
		$filters->header_title = _('Display Filters');
		$filters->lable_host_status_types = _('Host Status Types');
		$filters->lable_host_properties = _('Host Properties');
		$filters->lable_service_status_types = _('Service Status Types');
		$filters->lable_service_properties = _('Service Properties');
		$filters->host_status_type_val = $host_statustype_filtername;
		$filters->hostprop_val = $host_prop_filtername;
		$filters->service_status_type_val = $service_statustype_filtername;
		$filters->serviceprop_val = $service_prop_filtername;
		$filters->type = $type;
		return $filters;
	}

	/**
	* Translated helptexts for this controller
	*/
	public static function _helptexts($id)
	{
		# No helptexts defined yet - this is just an example
		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array(
			'edit' => _('@@@HELPTEXT:edit')
		);
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		}
		else
			echo sprintf(_("This helptext ('%s') is yet not translated"), $id);
	}


	private function classic_filter($type, $host = false, $hostgroup = false, $servicegroup = false, $hoststatustypes = false, $hostprops = false, $servicestatustypes = false, $serviceprops = false) {
		# classic search
		$errors       = 0;
		$host         = $this->input->get('host', $host);
		$hostgroup    = $this->input->get('hostgroup', $hostgroup);
		$servicegroup = $this->input->get('servicegroup', $servicegroup);

		$hoststatustypes    = $this->input->get('hoststatustypes', $hoststatustypes);
		$hostprops          = $this->input->get('hostprops', $hostprops);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$serviceprops       = $this->input->get('serviceprops', $serviceprops);

		$hostfilter = array();
		$hostgroupfilter = array();
		$servicefilter = array();
		$servicegroupfilter = array();
		if( $host != 'all' and $host != '' ) {
			# check for wildcards
			if( strpos( $host, '*' ) !== false ) {
				# convert wildcards into real regexp
				$searchhost = str_replace('.*', '*', $host);
				$searchhost = str_replace('*', '.*', $searchhost);
				/* TODO: validate regex */
				#$errors++ unless Livestatus::is_valid_regular_expression( $searchhost );
				$hostfilter[] 	 = array( 'name'      => array( '~~' => $searchhost ));
				$servicefilter[] = array( 'host_name' => array( '~~' => $searchhost ));
			} else {
				$hostfilter[]    = array( 'name'      => $host );
				$servicefilter[] = array( 'host_name' => $host );
			}
		}
		if ( $hostgroup != 'all' and $hostgroup != '' ) {
			$hostfilter[]       = array( 'groups'      => array( '>=' => $hostgroup ));
			$servicefilter[]    = array( 'host_groups' => array( '>=' => $hostgroup ));
			$hostgroupfilter[]  = array( 'name' => $hostgroup );
		}
		if ( $servicegroup != 'all' and $servicegroup != '' ) {
			$servicefilter[]       = array( 'groups' => array( '>=' => $servicegroup ) );
			$servicegroupfilter[]  = array( 'name' => $servicegroup );
		}

		$hostfilter         = Livestatus::combineFilter( '-and', $hostfilter );
		$hostgroupfilter    = Livestatus::combineFilter( '-or',  $hostgroupfilter );
		$servicefilter      = Livestatus::combineFilter( '-and', $servicefilter );
		$servicegroupfilter = Livestatus::combineFilter( '-or',  $servicegroupfilter );

		list( $show_filter_table, $hostfilter, $servicefilter, $host_statustype_filtername, $host_prop_filtername, $service_statustype_filtername, $service_prop_filtername, $host_statustype_filtervalue, $host_prop_filtervalue, $service_statustype_filtervalue, $service_prop_filtervalue )
			= $this->extend_filter( $hostfilter, $servicefilter, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops );

		if($show_filter_table) {
			$this->template->content->filters = $this->_show_filters($type, $host_statustype_filtername, $host_prop_filtername, $service_statustype_filtername, $service_prop_filtername);
		}

	    return (array( $hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter ));
	}


	private function extend_filter($hostfilter, $servicefilter, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops) {
	    $hostfilterlist    = array();
	    $servicefilterlist = array();

	    $hostfilter    && $hostfilterlist[]    = $hostfilter;
	    $servicefilter && $servicefilterlist[] = $servicefilter;

	    $show_filter_table = 0;

	    # host statustype filter (up,down,...)
	    list( $hoststatustypes, $host_statustype_filtername, $host_statustype_filter, $host_statustype_filter_service )
		= $this->get_host_statustype_filter($hoststatustypes);
	    $host_statustype_filter         && $hostfilterlist[]    = $host_statustype_filter;
	    $host_statustype_filter_service && $servicefilterlist[] = $host_statustype_filter_service;

	    $host_statustype_filter && $show_filter_table = 1;

	    # host props filter (downtime, acknowledged...)
	    list( $hostprops, $host_prop_filtername, $host_prop_filter, $host_prop_filter_service )
		= $this->get_host_prop_filter($hostprops);
	    $host_prop_filter         && $hostfilterlist[] =    $host_prop_filter;
	    $host_prop_filter_service && $servicefilterlist[] = $host_prop_filter_service;

	    $host_prop_filter && $show_filter_table = 1;

	    # service statustype filter (ok,warning,...)
	    list( $servicestatustypes, $service_statustype_filtername, $service_statustype_filter_service )
		= $this->get_service_statustype_filter($servicestatustypes);
	    $service_statustype_filter_service && $servicefilterlist[] = $service_statustype_filter_service;

	    $service_statustype_filter_service && $show_filter_table = 1;

	    # service props filter (downtime, acknowledged...)
	    list( $serviceprops, $service_prop_filtername, $service_prop_filter_service )
		= $this->get_service_prop_filter($serviceprops);
	    $service_prop_filter_service && $servicefilterlist[] = $service_prop_filter_service;

	    $service_prop_filter_service && $show_filter_table = 1;

	    $hostfilter    = Livestatus::combineFilter( '-and', $hostfilterlist );
	    $servicefilter = Livestatus::combineFilter( '-and', $servicefilterlist );

	    return array( $show_filter_table, $hostfilter, $servicefilter, $host_statustype_filtername, $host_prop_filtername, $service_statustype_filtername, $service_prop_filtername, $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops );
	}

	private function get_host_statustype_filter($number) {
	    $hoststatusfilter    = array();
	    $servicestatusfilter = array();

	    $hoststatusfiltername = 'All';
	    define('HOST_ALL', (nagstat::HOST_UP | nagstat::HOST_DOWN | nagstat::HOST_UNREACHABLE | nagstat::HOST_PENDING));
	    define('HOST_PROBLEM', ( nagstat::HOST_DOWN | nagstat::HOST_UNREACHABLE ));
	    if(!isset($number) or !is_numeric($number)) { return ( array(HOST_ALL, $hoststatusfiltername, "", "" )); }

	    if( $number and $number != HOST_ALL ) {
		$hoststatusfiltername_list = array();

		if( $number & nagstat::HOST_PENDING ) {    # 1 - pending
		    $hoststatusfilter[]    = array( 'has_been_checked'      => 0 );
		    $servicestatusfilter[] = array( 'host_has_been_checked' => 0 );
		    $hoststatusfiltername_list[] = 'Pending';
		}
		if( $number & nagstat::HOST_UP ) {    # 2 - up
		    $hoststatusfilter[]    = array( 'has_been_checked'      => 1, 'state'      => 0 );
		    $servicestatusfilter[] = array( 'host_has_been_checked' => 1, 'host_state' => 0 );
		    $hoststatusfiltername_list[] = 'Up';
		}
		if( $number & nagstat::HOST_DOWN ) {    # 4 - down
		    $hoststatusfilter[]    = array( 'has_been_checked'      => 1, 'state'      => 1 );
		    $servicestatusfilter[] = array( 'host_has_been_checked' => 1, 'host_state' => 1 );
		    $hoststatusfiltername_list[] = 'Down';
		}
		if( $number & nagstat::HOST_UNREACHABLE ) {    # 8 - unreachable
		    $hoststatusfilter[]    = array( 'has_been_checked'      => 1, 'state'      => 2 );
		    $servicestatusfilter[] = array( 'host_has_been_checked' => 1, 'host_state' => 2 );
		    $hoststatusfiltername_list[] = 'Unreachable';
		}
		$hoststatusfiltername = join( ' | ', $hoststatusfiltername_list );
		if($number == HOST_PROBLEM) { $hoststatusfiltername = 'All problems'; };
	    }

	    $hostfilter    = Livestatus::combineFilter( '-or', $hoststatusfilter );
	    $servicefilter = Livestatus::combineFilter( '-or', $servicestatusfilter );

	    return ( array($number, $hoststatusfiltername, $hostfilter, $servicefilter ));
	}


	private function get_host_prop_filter($number) {
	    $host_prop_filter = array();
	    $host_prop_filter_service = array();
	    $host_prop_filtername = 'Any';
	    if(!isset($number) or !is_numeric($number)) { return ( array( 0, $host_prop_filtername, "", "" )); }

	    if( $number > 0 ) {
		$host_prop_filtername_list = array();

		if( $number & nagstat::HOST_SCHEDULED_DOWNTIME ) {    # 1 - In Scheduled Downtime
		    $host_prop_filter[] =           array( 'scheduled_downtime_depth'      => array( '>' => 0 ));
		    $host_prop_filter_service[] =   array( 'host_scheduled_downtime_depth' => array( '>' => 0 ));
		    $host_prop_filtername_list[] = 'In Scheduled Downtime';
		}
		if( $number & nagstat::HOST_NO_SCHEDULED_DOWNTIME ) {    # 2 - Not In Scheduled Downtime
		    $host_prop_filter[] =           array( 'scheduled_downtime_depth'      => 0 );
		    $host_prop_filter_service[] =   array( 'host_scheduled_downtime_depth' => 0 );
		    $host_prop_filtername_list[] = 'Not In Scheduled Downtime';
		}
		if( $number & nagstat::HOST_STATE_ACKNOWLEDGED ) {    # 4 - Has Been Acknowledged
		    $host_prop_filter[] =           array( 'acknowledged'      => 1 );
		    $host_prop_filter_service[] =   array( 'host_acknowledged' => 1 );
		    $host_prop_filtername_list[] = 'Has Been Acknowledged';
		}
		if( $number & nagstat::HOST_STATE_UNACKNOWLEDGED ) {    # 8 - Has Not Been Acknowledged
		    $host_prop_filter[] =           array( 'acknowledged'      => 0 );
		    $host_prop_filter_service[] =   array( 'host_acknowledged' => 0 );
		    $host_prop_filtername_list[] = 'Has Not Been Acknowledged';
		}
		if( $number & nagstat::HOST_CHECKS_DISABLED ) {    # 16 - Checks Disabled
		    $host_prop_filter[] =           array( 'checks_enabled'      => 0 );
		    $host_prop_filter_service[] =   array( 'host_checks_enabled' => 0 );
		    $host_prop_filtername_list[] = 'Checks Disabled';
		}
		if( $number & nagstat::HOST_CHECKS_ENABLED ) {    # 32 - Checks Enabled
		    $host_prop_filter[] =           array( 'checks_enabled'      => 1 );
		    $host_prop_filter_service[] =   array( 'host_checks_enabled' => 1 );
		    $host_prop_filtername_list[] = 'Checks Enabled';
		}
		if( $number & nagstat::HOST_EVENT_HANDLER_DISABLED ) {    # 64 - Event Handler Disabled
		    $host_prop_filter[] =           array( 'event_handler_enabled'      => 0 );
		    $host_prop_filter_service[] =   array( 'host_event_handler_enabled' => 0 );
		    $host_prop_filtername_list[] = 'Event Handler Disabled';
		}
		if( $number & nagstat::HOST_EVENT_HANDLER_ENABLED ) {    # 128 - Event Handler Enabled
		    $host_prop_filter[] =           array( 'event_handler_enabled'      => 1 );
		    $host_prop_filter_service[] =   array( 'host_event_handler_enabled' => 1 );
		    $host_prop_filtername_list[] = 'Event Handler Enabled';
		}
		if( $number & nagstat::HOST_FLAP_DETECTION_DISABLED ) {    # 256 - Flap Detection Disabled
		    $host_prop_filter[] =           array( 'flap_detection_enabled'      => 0 );
		    $host_prop_filter_service[] =   array( 'host_flap_detection_enabled' => 0 );
		    $host_prop_filtername_list[] = 'Flap Detection Disabled';
		}
		if( $number & nagstat::HOST_FLAP_DETECTION_ENABLED ) {    # 512 - Flap Detection Enabled
		    $host_prop_filter[] =           array( 'flap_detection_enabled'      => 1 );
		    $host_prop_filter_service[] =   array( 'host_flap_detection_enabled' => 1 );
		    $host_prop_filtername_list[] = 'Flap Detection Enabled';
		}
		if( $number & nagstat::HOST_IS_FLAPPING ) {    # 1024 - Is Flapping
		    $host_prop_filter[] =           array( 'is_flapping'      => 1 );
		    $host_prop_filter_service[] =   array( 'host_is_flapping' => 1 );
		    $host_prop_filtername_list[] = 'Is Flapping';
		}
		if( $number & nagstat::HOST_IS_NOT_FLAPPING ) {    # 2048 - Is Not Flapping
		    $host_prop_filter[] =           array( 'is_flapping'      => 0 );
		    $host_prop_filter_service[] =   array( 'host_is_flapping' => 0 );
		    $host_prop_filtername_list[] = 'Is Not Flapping';
		}
		if( $number & nagstat::HOST_NOTIFICATIONS_DISABLED ) {    # 4096 - Notifications Disabled
		    $host_prop_filter[] =           array( 'notifications_enabled'      => 0 );
		    $host_prop_filter_service[] =   array( 'host_notifications_enabled' => 0 );
		    $host_prop_filtername_list[] = 'Notifications Disabled';
		}
		if( $number & nagstat::HOST_NOTIFICATIONS_ENABLED) {    # 8192 - Notifications Enabled
		    $host_prop_filter[] =           array( 'notifications_enabled'      => 1 );
		    $host_prop_filter_service[] =   array( 'host_notifications_enabled' => 1 );
		    $host_prop_filtername_list[] = 'Notifications Enabled';
		}
		if( $number & nagstat::HOST_PASSIVE_CHECKS_DISABLED ) {    # 16384 - Passive Checks Disabled
		    $host_prop_filter[] =           array( 'accept_passive_checks'      => 0 );
		    $host_prop_filter_service[] =   array( 'host_accept_passive_checks' => 0 );
		    $host_prop_filtername_list[] = 'Passive Checks Disabled';
		}
		if( $number & nagstat::HOST_PASSIVE_CHECKS_ENABLED ) {    # 32768 - Passive Checks Enabled
		    $host_prop_filter[] =           array( 'accept_passive_checks'      => 1 );
		    $host_prop_filter_service[] =   array( 'host_accept_passive_checks' => 1 );
		    $host_prop_filtername_list[] = 'Passive Checks Enabled';
		}
		if( $number & nagstat::HOST_PASSIVE_CHECK ) {    # 65536 - Passive Checks
		    $host_prop_filter[] =           array( 'check_type'      => 1 );
		    $host_prop_filter_service[] =   array( 'host_check_type' => 1 );
		    $host_prop_filtername_list[] = 'Passive Checks';
		}
		if( $number & nagstat::HOST_ACTIVE_CHECK ) {    # 131072 - Active Checks
		    $host_prop_filter[] =           array( 'check_type'      => 0 );
		    $host_prop_filter_service[] =   array( 'host_check_type' => 0 );
		    $host_prop_filtername_list[] = 'Active Checks';
		}
		if( $number & nagstat::HOST_HARD_STATE ) {    # 262144 - In Hard State
		    $host_prop_filter[] =           array( 'state_type'      => 1 );
		    $host_prop_filter_service[] =   array( 'host_state_type' => 1 );
		    $host_prop_filtername_list[] = 'In Hard State';
		}
		if( $number & nagstat::HOST_SOFT_STATE ) {    # 524288 - In Soft State
		    $host_prop_filter[] =           array( 'state_type'      => 0 );
		    $host_prop_filter_service[] =   array( 'host_state_type' => 0 );
		    $host_prop_filtername_list[] = 'In Soft State';
		}

		$host_prop_filtername = join( ' &amp; ', $host_prop_filtername_list );
	    }

	    $hostfilter    = Livestatus::combineFilter( '-and', $host_prop_filter );
	    $servicefilter = Livestatus::combineFilter( '-and', $host_prop_filter_service );

	    return ( array( $number, $host_prop_filtername, $hostfilter, $servicefilter ));
	}


	private function get_service_statustype_filter($number) {
	    $servicestatusfilter     = array();
	    $servicestatusfilternamelist = array();

	    define('SERVICE_ALL', (nagstat::SERVICE_OK | nagstat::SERVICE_WARNING | nagstat::SERVICE_CRITICAL | nagstat::SERVICE_UNKNOWN | nagstat::SERVICE_PENDING));
	    define('SERVICE_PROBLEM', ( nagstat::SERVICE_WARNING | nagstat::SERVICE_CRITICAL | nagstat::SERVICE_UNKNOWN ));
	    $servicestatusfiltername = 'All';
	    if(!isset($number) or !is_numeric($number)) { return(array( SERVICE_ALL, $servicestatusfiltername, "" )); }

	    if( $number and $number != SERVICE_ALL ) {

		if( $number & nagstat::SERVICE_PENDING ) {    # 1 - pending
		    $servicestatusfilter[] = array( 'has_been_checked' => 0 );
		    $servicestatusfilternamelist[] = 'Pending';
		}
		if( $number & nagstat::SERVICE_OK ) {    # 2 - ok
		    $servicestatusfilter[] = array( 'has_been_checked' => 1, 'state' => 0 );
		    $servicestatusfilternamelist[] = 'Ok';
		}
		if( $number & nagstat::SERVICE_WARNING ) {    # 4 - warning
		    $servicestatusfilter[] = array( 'has_been_checked' => 1, 'state' => 1 );
		    $servicestatusfilternamelist[] = 'Warning';
		}
		if( $number & nagstat::SERVICE_UNKNOWN ) {    # 8 - unknown
		    $servicestatusfilter[] = array( 'has_been_checked' => 1, 'state' => 3 );
		    $servicestatusfilternamelist[] = 'Unknown';
		}
		if( $number & nagstat::SERVICE_CRITICAL ) {    # 16 - critical
		    $servicestatusfilter[] = array( 'has_been_checked' => 1, 'state' => 2 );
		    $servicestatusfilternamelist[] = 'Critical';
		}
		$servicestatusfiltername = join( ' | ', $servicestatusfilternamelist );
		if($number == SERVICE_PROBLEM) { $servicestatusfiltername = 'All problems'; }
	    }

	    $servicefilter = Livestatus::combineFilter( '-or', $servicestatusfilter );

	    return(array( $number, $servicestatusfiltername, $servicefilter ));
	}

	private function get_service_prop_filter($number) {
	    $service_prop_filter = array();
	    $service_prop_filtername_list = array();
	    $service_prop_filtername = 'Any';
	    if(!isset($number) or !is_numeric($number)) { return (array( 0, $service_prop_filtername, "" )); }

	    if( $number > 0 ) {
		if( $number & nagstat::SERVICE_SCHEDULED_DOWNTIME ) {    # 1 - In Scheduled Downtime
		    $service_prop_filter[] = array( 'scheduled_downtime_depth' => array( '>' => 0 ) );
		    $service_prop_filtername_list[] = 'In Scheduled Downtime';
		}
		if( $number & nagstat::SERVICE_NO_SCHEDULED_DOWNTIME ) {    # 2 - Not In Scheduled Downtime
		    $service_prop_filter[] = array( 'scheduled_downtime_depth' => 0 );
		    $service_prop_filtername_list[] = 'Not In Scheduled Downtime';
		}
		if( $number & nagstat::SERVICE_STATE_ACKNOWLEDGED ) {    # 4 - Has Been Acknowledged
		    $service_prop_filter[] = array( 'acknowledged' => 1 );
		    $service_prop_filtername_list[] = 'Has Been Acknowledged';
		}
		if( $number & nagstat::SERVICE_STATE_UNACKNOWLEDGED ) {    # 8 - Has Not Been Acknowledged
		    $service_prop_filter[] = array( 'acknowledged' => 0 );
		    $service_prop_filtername_list[] = 'Has Not Been Acknowledged';
		}
		if( $number & nagstat::SERVICE_CHECKS_DISABLED ) {    # 16 - Checks Disabled
		    $service_prop_filter[] = array( 'checks_enabled' => 0 );
		    $service_prop_filtername_list[] = 'Active Checks Disabled';
		}
		if( $number & nagstat::SERVICE_CHECKS_ENABLED ) {    # 32 - Checks Enabled
		    $service_prop_filter[] = array( 'checks_enabled' => 1 );
		    $service_prop_filtername_list[] = 'Active Checks Enabled';
		}
		if( $number & nagstat::SERVICE_EVENT_HANDLER_DISABLED ) {    # 64 - Event Handler Disabled
		    $service_prop_filter[] = array( 'event_handler_enabled' => 0 );
		    $service_prop_filtername_list[] = 'Event Handler Disabled';
		}
		if( $number & nagstat::SERVICE_EVENT_HANDLER_ENABLED ) {    # 128 - Event Handler Enabled
		    $service_prop_filter[] = array( 'event_handler_enabled' => 1 );
		    $service_prop_filtername_list[] = 'Event Handler Enabled';
		}
		if( $number & nagstat::SERVICE_FLAP_DETECTION_ENABLED ) {    # 256 - Flap Detection Enabled
		    $service_prop_filter[] = array( 'flap_detection_enabled' => 1 );
		    $service_prop_filtername_list[] = 'Flap Detection Enabled';
		}
		if( $number & nagstat::SERVICE_FLAP_DETECTION_DISABLED ) {    # 512 - Flap Detection Disabled
		    $service_prop_filter[] = array( 'flap_detection_enabled' => 0 );
		    $service_prop_filtername_list[] = 'Flap Detection Disabled';
		}
		if( $number & nagstat::SERVICE_IS_FLAPPING ) {    # 1024 - Is Flapping
		    $service_prop_filter[] = array( 'is_flapping' => 1 );
		    $service_prop_filtername_list[] = 'Is Flapping';
		}
		if( $number & nagstat::SERVICE_IS_NOT_FLAPPING ) {    # 2048 - Is Not Flapping
		    $service_prop_filter[] = array( 'is_flapping' => 0 );
		    $service_prop_filtername_list[] = 'Is Not Flapping';
		}
		if( $number & nagstat::SERVICE_NOTIFICATIONS_DISABLED ) {    # 4096 - Notifications Disabled
		    $service_prop_filter[] = array( 'notifications_enabled' => 0 );
		    $service_prop_filtername_list[] = 'Notifications Disabled';
		}
		if( $number & nagstat::SERVICE_NOTIFICATIONS_ENABLED ) {    # 8192 - Notifications Enabled
		    $service_prop_filter[] = array( 'notifications_enabled' => 1 );
		    $service_prop_filtername_list[] = 'Notifications Enabled';
		}
		if( $number & nagstat::SERVICE_PASSIVE_CHECKS_DISABLED ) {    # 16384 - Passive Checks Disabled
		    $service_prop_filter[] = array( 'accept_passive_checks' => 0 );
		    $service_prop_filtername_list[] = 'Passive Checks Disabled';
		}
		if( $number & nagstat::SERVICE_PASSIVE_CHECKS_ENABLED ) {    # 32768 - Passive Checks Enabled
		    $service_prop_filter[] = array( 'accept_passive_checks' => 1 );
		    $service_prop_filtername_list[] = 'Passive Checks Enabled';
		}
		if( $number & nagstat::SERVICE_PASSIVE_CHECK ) {    # 65536 - Passive Checks
		    $service_prop_filter[] = array( 'check_type' => 1 );
		    $service_prop_filtername_list[] = 'Passive Checks';
		}
		if( $number & nagstat::SERVICE_ACTIVE_CHECK ) {    # 131072 - Active Checks
		    $service_prop_filter[] = array( 'check_type' => 0 );
		    $service_prop_filtername_list[] = 'Active Checks';
		}
		if( $number & nagstat::SERVICE_HARD_STATE ) {    # 262144 - In Hard State
		    $service_prop_filter[] = array( 'state_type' => 1 );
		    $service_prop_filtername_list[] = 'In Hard State';
		}
		if( $number & nagstat::SERVICE_SOFT_STATE ) {    # 524288 - In Soft State
		    $service_prop_filter[] = array( 'state_type' => 0 );
		    $service_prop_filtername_list[] = 'In Soft State';
		}

		$service_prop_filtername = join( ' &amp; ', $service_prop_filtername_list );
	    }

	    $servicefilter = Livestatus::combineFilter( '-and', $service_prop_filter );

	    return (array( $number, $service_prop_filtername, $servicefilter ));
	}


	private function _summary_add_host_stats( $prefix, &$group, $host ) {
		$group['hosts_total']++;

		if( $host[$prefix.'has_been_checked'] == 0 ) { $group['hosts_pending']++; }
		elseif ( $host[$prefix.'state'] == 0 ) { $group['hosts_up']++; }
		elseif ( $host[$prefix.'state'] == 1 ) { $group['hosts_down']++; }
		elseif ( $host[$prefix.'state'] == 2 ) { $group['hosts_unreachable']++; }

		if( $host[$prefix.'state'] == 1 and $host[$prefix.'scheduled_downtime_depth'] > 0 ) { $group['hosts_down_downtime']++; }
		if( $host[$prefix.'state'] == 1 and $host[$prefix.'acknowledged'] == 1 )            { $group['hosts_down_ack']++; }
		if( $host[$prefix.'state'] == 1 and $host[$prefix.'checks_enabled'] == 1 and $host[$prefix.'acknowledged'] == 0 and $host[$prefix.'scheduled_downtime_depth'] == 0 ) { $group['hosts_down_unhandled']++; }

		if( $host[$prefix.'state'] == 1 and $host[$prefix.'checks_enabled'] == 0 and $host[$prefix.'check_type'] == 0 ) { $group['hosts_down_disabled_active']++; }
		if( $host[$prefix.'state'] == 1 and $host[$prefix.'checks_enabled'] == 0 and $host[$prefix.'check_type'] == 1 ) { $group['hosts_down_disabled_passive']++; }

		if( $host[$prefix.'state'] == 2 and $host[$prefix.'scheduled_downtime_depth'] > 0 ) { $group['hosts_unreachable_downtime']++; }
		if( $host[$prefix.'state'] == 2 and $host[$prefix.'acknowledged'] == 1 )            { $group['hosts_unreachable_ack']++; }
		if( $host[$prefix.'state'] == 2 and $host[$prefix.'checks_enabled'] == 0 and $host[$prefix.'check_type'] == 0 ) { $group['hosts_unreachable_disabled_active']++; }
		if( $host[$prefix.'state'] == 2 and $host[$prefix.'checks_enabled'] == 0 and $host[$prefix.'check_type'] == 1 ) { $group['hosts_unreachable_disabled_passive']++; }
		if( $host[$prefix.'state'] == 2 and $host[$prefix.'checks_enabled'] == 1 and $host[$prefix.'acknowledged'] == 0 and $host[$prefix.'scheduled_downtime_depth'] == 0 ) { $group['hosts_unreachable_unhandled']++; }

		return 1;
	}
}
