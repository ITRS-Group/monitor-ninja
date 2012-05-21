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
	public $logos_path = '';
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

		$this->logos_path = Kohana::config('config.logos_path');

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
	public function host($host='all', $hoststatustypes=false, $sort_order='ASC', $sort_field='host_name', $show_services=false, $group_type=false, $serviceprops=false, $hostprops=false)
	{
		$host = urldecode($this->input->get('host', $host));
		$page = urldecode($this->input->get('page', false));
		$items_per_page = urldecode($this->input->get('items_per_page', config::get('pagination.default.items_per_page', '*')));
		$hoststatustypes = urldecode($this->input->get('hoststatustypes', $hoststatustypes));
		$sort_order = urldecode($this->input->get('sort_order', $sort_order));
		$sort_field = urldecode($this->input->get('sort_field', $sort_field));
		$show_services = urldecode($this->input->get('show_services', $show_services));
		$group_type = urldecode($this->input->get('group_type', $group_type));
		$serviceprops = urldecode($this->input->get('serviceprops', $serviceprops));
		$hostprops = urldecode($this->input->get('hostprops', $hostprops));
		$noheader = urldecode($this->input->get('noheader', false));
		$group_type = urldecode(strtolower($group_type));

		$host = trim($host);
		$hoststatustypes = strtolower($hoststatustypes);
		$hoststatustypes = $hoststatustypes ==='false' || $hoststatustypes ==='true' ? false : $hoststatustypes;

		$replace = array(
			1  => _('UP'),
			2  => _('Down'),
			4  => _('Unreachable'),
			6  => _('All problems'),
			64 => _('Pending')
		);

		$title = _('Monitoring » Host details').($hoststatustypes != false ? ' » '.$replace[$hoststatustypes] : '');
		$this->template->title = $title;

		$this->hoststatustypes = $hoststatustypes;
		$this->hostprops = $hostprops;
		$this->serviceprops = $serviceprops;
		$filters = $this->_show_filters();

		$this->template->content = $this->add_view('status/host');
		$this->template->content->filters = $filters;
		$this->template->content->noheader = $noheader;
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$widget = widget::get(Ninja_widget_Model::get(Router::$controller, 'status_totals'), $this);
		$widget->set_host($host);
		$widget->set_hoststatus($hoststatustypes);
		//$this->xtra_css = array_merge($this->xtra_css, array($this->add_path('/css/default/common.css')));
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
			array('title' => _('Status'), 'sort_field_db' => 'current_state', 'sort_field_str' => 'host status'),
			array('title' => _('Host'), 'sort_field_db' => 'host_name', 'sort_field_str' => 'host name'),
			array('title' => _('Last Check'), 'sort_field_db' => 'last_check', 'sort_field_str' => 'last check time'),
			array('title' => _('Duration'), 'sort_field_db' => 'duration', 'sort_field_str' => 'state duration'),
			array('title' => _('Status Information'), 'sort_field_db' => 'output', 'sort_field_str' => 'status information')
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

		# here we should fetch members of group if group_type is set and pass to get_host_status()
		$host_model = new Host_Model();
		$host_model->show_services = $show_services;
		$host_model->state_filter = $hoststatustypes;
		$host_model->set_sort_field($sort_field);
		$host_model->set_sort_order($sort_order);
		$host_model->serviceprops = $serviceprops;
		$host_model->hostprops = $hostprops;
		$host_model->count = true;

		if (!empty($group_type)) {
			# we ned to remove the 'group' part of the group_type variable value
			# since the method we are about to call expects 'host' or 'service'
			$grouptype = str_replace('group', '', $group_type);
			$group_info_res = Group_Model::get_group_info($grouptype, $host);
			if ($group_info_res) {
				$group_members = false;
				foreach ($group_info_res as $row) {
					$group_members[] = $row->host_name;
				}
			}

			$host_model->set_host_list($group_members);

		} else {
			if (strstr($host, ',')) {
				$host = explode(',', $host);
			}

			$host_model->set_host_list($host);
		}

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

		$result = $host_model->get_host_status();

		$this->template->content->date_format_str = nagstat::date_format();
		$this->template->content->result = $result;
		$this->template->content->pagination = $pagination;
		$this->template->content->total_items = $tot;
		$this->template->content->logos_path = $this->logos_path;

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
		$host_comments = Comment_Model::count_comments_by_object();
		$this->template->content->host_comments = $host_comments;
		$this->template->content->items_per_page = $items_per_page;
		$this->template->content->page = $page;


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
		$name = urldecode($this->input->get('name', $name));
		$page = urldecode($this->input->get('page', false));
		$items_per_page = urldecode($this->input->get('items_per_page', config::get('pagination.default.items_per_page', '*')));
		$hoststatustypes = urldecode($this->input->get('hoststatustypes', $hoststatustypes));
		$servicestatustypes = urldecode($this->input->get('servicestatustypes', $servicestatustypes));
		$service_props = urldecode($this->input->get('serviceprops', $service_props));
		$service_props = urldecode($this->input->get('service_props', $service_props));
		$hostprops = urldecode($this->input->get('hostprops', $hostprops));
		$sort_order = urldecode($this->input->get('sort_order', $sort_order));
		$sort_field = urldecode($this->input->get('sort_field', $sort_field));
		$group_type = urldecode($this->input->get('group_type', $group_type));
		$noheader = urldecode($this->input->get('noheader', false));
		$group_type = strtolower($group_type);

		$name = trim($name);
		$hoststatustypes = strtolower($hoststatustypes);
		$hoststatustypes = $hoststatustypes ==='false' || $hoststatustypes ==='true' ? false : $hoststatustypes;

		$servicestatustypes = strtolower($servicestatustypes);
		$servicestatustypes = $servicestatustypes ==='false' || $servicestatustypes==='true' ? false : $servicestatustypes;

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

		$sort_order = $sort_order == 'false' || empty($sort_order) ? 'ASC' : $sort_order;
		$sort_field = $sort_field == 'false' || empty($sort_field) ? 'host_name' : $sort_field;

		$this->hoststatustypes = $hoststatustypes;
		$this->hostprops = $hostprops;
		$this->servicestatustypes = $servicestatustypes;
		$this->serviceprops = $service_props;
		$filters = $this->_show_filters();

		$this->template->content = $this->add_view('status/service');
		$this->template->content->noheader = $noheader;
		$this->template->content->filters = $filters;
		$this->template->content->group_type = $group_type;
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$widget = widget::get(Ninja_widget_Model::get(Router::$controller, 'status_totals'), $this);
		$widget->set_host($name);
		$widget->set_hoststatus($hoststatustypes);
		$widget->set_servicestatus($servicestatustypes);
		$widget->set_grouptype($group_type);
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
			array('title' => _('Host'), 'sort_field_db' => 'host_name', 'sort_field_str' => 'host name'),
			array('title' => _('Status'), 'sort_field_db' => 'current_state', 'sort_field_str' => 'service status'),
			array('title' => _('Service'), 'sort_field_db' => 'service_description', 'sort_field_str' => 'service name'),
			array('title' => _('Last Check'), 'sort_field_db' => 'last_check', 'sort_field_str' => 'last check time'),
			array('title' => _('Duration'), 'sort_field_db' => 'duration', 'sort_field_str' => 'state duration'),
			array('title' => _('Attempt'), 'sort_field_db' => 'current_attempt', 'sort_field_str' => 'attempt'),
			array('title' => _('Status Information'), 'sort_field_db' => 'output', 'sort_field_str' => 'status information')
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

		# handle host- or servicegroup details
		$host_model = new Host_Model();
		$host_model->show_services = true;
		$host_model->state_filter = $hoststatustypes;
		$host_model->service_filter = $servicestatustypes;
		$host_model->set_sort_field($sort_field);
		$host_model->set_sort_order($sort_order);
		$host_model->serviceprops = $service_props;
		$host_model->hostprops = $hostprops;

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
		}
		$sub_title = _('Service Status Details For').' '.$shown;
		$this->template->content->sub_title = $sub_title;

		$this->template->content->pending_output = _('Service check scheduled for %s');
		$this->template->content->result = $result;
		$this->template->content->pagination = isset($pagination) ? $pagination : false;
		$this->template->content->logos_path = $this->logos_path;
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
		$host_comments = Comment_Model::count_comments_by_object();
		$this->template->content->host_comments = $host_comments;

		$svc_comments = Comment_Model::count_comments_by_object(true);
		$this->template->content->comments = $svc_comments;
		$this->template->content->items_per_page = $items_per_page;
		$this->template->content->page = $page;

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
		$group = urldecode($this->input->get('group', $group));
		$hoststatustypes = urldecode($this->input->get('hoststatustypes', $hoststatustypes));
		$servicestatustypes = urldecode($this->input->get('servicestatustypes', $servicestatustypes));
		$serviceprops = urldecode($this->input->get('serviceprops', $serviceprops));
		$hostprops = urldecode($this->input->get('hostprops', $hostprops));
		$style = urldecode($this->input->get('style', $style));
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
		$group = urldecode($this->input->get('group', $group));
		$hoststatustypes = urldecode($this->input->get('hoststatustypes', $hoststatustypes));
		$servicestatustypes = urldecode($this->input->get('servicestatustypes', $servicestatustypes));
		$serviceprops = urldecode($this->input->get('serviceprops', $serviceprops));
		$hostprops = urldecode($this->input->get('hostprops', $hostprops));
		$style = urldecode($this->input->get('style', $style));
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
		$grouptype = urldecode($this->input->get('grouptype', $grouptype));
		$group = urldecode($this->input->get('group', $group));
		$hoststatustypes = urldecode($this->input->get('hoststatustypes', $hoststatustypes));
		$servicestatustypes = urldecode($this->input->get('servicestatustypes', $servicestatustypes));
		$serviceprops = urldecode($this->input->get('serviceprops', $serviceprops));
		$hostprops = urldecode($this->input->get('hostprops', $hostprops));
		$style = urldecode($this->input->get('style', $style));
		$noheader = urldecode($this->input->get('noheader', false));
		$group = trim($group);
		$hoststatustypes = strtolower($hoststatustypes)==='false' ? false : $hoststatustypes;

		$this->hoststatustypes = $hoststatustypes;
		$this->hostprops = $hostprops;
		$this->servicestatustypes = $servicestatustypes;
		$this->serviceprops = $serviceprops;
		$filters = $this->_show_filters();

		switch ($style) {
			case 'overview':
				$this->template->title = _('Monitoring » ').$grouptype._('group overview');
				$this->template->header = _('Monitoring » ').$grouptype._('group overview');
				$this->template->content = $this->add_view('status/group_overview');
				$this->template->content->filters = $filters;
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
		$group = urldecode($this->input->get('group', $group));
		$hoststatustypes = urldecode($this->input->get('hoststatustypes', $hoststatustypes));
		$servicestatustypes = urldecode($this->input->get('servicestatustypes', $servicestatustypes));
		$serviceprops = urldecode($this->input->get('serviceprops', $serviceprops));
		$hostprops = urldecode($this->input->get('hostprops', $hostprops));
		$grouptype = 'service';
		$this->template->title = _('Servicegroup » Summary');
		return $this->_group_summary($grouptype, $group, $hoststatustypes, $servicestatustypes, $serviceprops, $hostprops);
	}

	public function hostgroup_summary($group='all', $hoststatustypes=false, $servicestatustypes=false, $serviceprops=false, $hostprops=false)
	{
		$group = urldecode($this->input->get('group', $group));
		$hoststatustypes = urldecode($this->input->get('hoststatustypes', $hoststatustypes));
		$servicestatustypes = urldecode($this->input->get('servicestatustypes', $servicestatustypes));
		$serviceprops = urldecode($this->input->get('serviceprops', $serviceprops));
		$hostprops = urldecode($this->input->get('hostprops', $hostprops));
		$grouptype = 'host';
		return $this->_group_summary($grouptype, $group, $hoststatustypes, $servicestatustypes, $serviceprops, $hostprops);
	}

	/**
	*	Create group summary page
	*/
	public function _group_summary($grouptype='service', $group='all', $hoststatustypes=false, $servicestatustypes=false, $serviceprops=false, $hostprops=false)
	{
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.group_items_per_page', '*'));
		$grouptype = urldecode($this->input->get('grouptype', $grouptype));
		$group = urldecode($this->input->get('group', $group));
		$hoststatustypes = urldecode($this->input->get('hoststatustypes', $hoststatustypes));
		$servicestatustypes = urldecode($this->input->get('servicestatustypes', $servicestatustypes));
		$serviceprops = urldecode($this->input->get('serviceprops', $serviceprops));
		$hostprops = urldecode($this->input->get('hostprops', $hostprops));
		$noheader = urldecode($this->input->get('noheader', false));
		$this->template->title = _('Monitoring » ').$grouptype._('group summary');

		$group = trim($group);
		$this->template->content = $this->add_view('status/group_summary');
		$this->template->content->noheader = $noheader;
		$content = $this->template->content;

		$this->hoststatustypes = $hoststatustypes;
		$this->hostprops = $hostprops;
		$this->servicestatustypes = $servicestatustypes;
		$this->serviceprops = $serviceprops;
		$filters = $this->_show_filters();
		$this->template->content->filters = $filters;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

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

		$group_details = false;
		$auth_groups = false;
		$group_info_res = false;
		if (strtolower($group) == 'all') {
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
			$content->pagination = $pagination;

			$content->lable_header = $grouptype == 'service' ? _('Status Summary For All Service Groups') : _('Status Summary For All Host Groups');
			$group_info_res = $grouptype == 'service'
				? Servicegroup_Model::summary($group, $items_per_page, $offset, $hostprops, $serviceprops, $hoststatustypes, $servicestatustypes)
				: Hostgroup_Model::summary($group, $items_per_page, $offset, $hostprops, $serviceprops, $hoststatustypes, $servicestatustypes);
		} else {
			# make sure we have the correct group
			$group_info_res = $grouptype == 'service' ?
				Servicegroup_Model::get($group) :
				Hostgroup_Model::get($group);
			if ($group_info_res) {
				$group_info_res = $group_info_res->current();
				$group = $group_info_res->{$grouptype.'group_name'}; # different field depending on object type
			} else {
				# overwrite previous view with the error view, add some text and bail out
				$this->template->content = $this->add_view('error');
				$this->template->content->error_message = sprintf(_("The requested group ('%s') wasn't found"), $group);
				return;
			}
			$label_header = $grouptype == 'service' ? _('Status Summary For Service Group ') : _('Status Summary For Host Group ');
			$content->lable_header = $label_header."'".$group."'";
			$group_info_res = $grouptype == 'service' ? Servicegroup_Model::summary($group) : Hostgroup_Model::summary($group);
		}
		if (count($group_info_res)) {
			$content->group_details = $group_info_res;
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
		$group = urldecode($this->input->get('group', $group));
		$hoststatustypes = urldecode($this->input->get('hoststatustypes', $hoststatustypes));
		$servicestatustypes = urldecode($this->input->get('servicestatustypes', $servicestatustypes));
		$noheader = urldecode($this->input->get('noheader', false));

		$this->template->content = $this->add_view('status/hostgroup_grid');
		$this->template->content->noheader = $noheader;
		$content = $this->template->content;

		$this->hoststatustypes = $hoststatustypes;
		$this->servicestatustypes = $servicestatustypes;
		$filters = $this->_show_filters();
		$this->template->content->filters = $filters;

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

		$group_details = false;
		if (strtolower($group) == 'all') {
			$auth = Nagios_auth_Model::instance();
			$auth_groups = $auth->get_authorized_hostgroups();

			$tot = count($auth_groups);
			$pagination = new Pagination(
				array(
					'total_items'=> $tot,
					'items_per_page' => $items_per_page
				)
			);
			$offset = $pagination->sql_offset;
			$content->pagination = $pagination;

			$content->label_header = _('Status Grid For All Host Groups');
			$group_info_res = Hostgroup_Model::get_all($items_per_page, $offset);
		} else {
			# make sure we have the correct servicegroup
			$group_info_res = Hostgroup_Model::get($group);
			$label_header = _('Status Grid For Host Group ');
			$content->label_header = $label_header."'".$group."'";
		}

		$content->group_details = $group_info_res;
		$content->error_message = _('No hostgroup data found');
		$content->grouptype = 'host';
		$content->logos_path = $this->logos_path;
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
		$group = urldecode($this->input->get('group', $group));
		$hoststatustypes = urldecode($this->input->get('hoststatustypes', $hoststatustypes));
		$servicestatustypes = urldecode($this->input->get('servicestatustypes', $servicestatustypes));
		$noheader = urldecode($this->input->get('noheader', false));

		$this->template->content = $this->add_view('status/servicegroup_grid');
		$this->template->content->noheader = $noheader;
		$content = $this->template->content;

		$this->hoststatustypes = $hoststatustypes;
		$this->servicestatustypes = $servicestatustypes;
		$filters = $this->_show_filters();
		$this->template->content->filters = $filters;

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

		$group_details = false;
		if (strtolower($group) == 'all') {
			$auth = Nagios_auth_Model::instance();
			$auth_groups = $auth->get_authorized_servicegroups();

			$tot = count($auth_groups);
			$pagination = new Pagination(
				array(
					'total_items'=> $tot,
					'items_per_page' => $items_per_page
				)
			);
			$offset = $pagination->sql_offset;
			$content->pagination = $pagination;

			$content->label_header = _('Status Grid For All Service Groups');
			$group_info_res = Servicegroup_Model::get_all($items_per_page, $offset);
		} else {
			# make sure we have the correct servicegroup
			$group_info_res = Servicegroup_Model::get($group);
			$label_header = _('Status Grid For Service Group ');
			$content->label_header = $label_header."'".$group."'";
		}

		$content->group_details = $group_info_res;
		$content->error_message = _('No servicegroup data found');
		$content->grouptype = 'service';
		$content->logos_path = $this->logos_path;
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
	public function _show_filters()
	{
		$all_host_status_types =
			nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|
			nagstat::HOST_UNREACHABLE;

		$all_service_status_types =
			nagstat::SERVICE_PENDING|nagstat::SERVICE_OK|
			nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL;

		$hostprops = $this->hostprops;

		$hoststatustypes = $this->hoststatustypes === false || $this->hoststatustypes ==='' ?
			$all_host_status_types : $this->hoststatustypes;

		$serviceprops = $this->serviceprops;

		$servicestatustypes = $this->servicestatustypes === false || $this->servicestatustypes ==='' ?
			$all_service_status_types : $this->servicestatustypes;

		/* Don't show filters box if not necessary */
		if (empty($hostprops) && empty($serviceprops) &&
			$hoststatustypes == $all_host_status_types &&
			$servicestatustypes == $all_service_status_types) {
			return false;
		}

		$service_status_types =
			nagstat::SERVICE_PENDING|nagstat::SERVICE_OK|
			nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_WARNING|
			nagstat::SERVICE_CRITICAL;

		$host_status_types = nagstat::HOST_PENDING|nagstat::HOST_UP|
			nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE;

		$all_service_problems = nagstat::SERVICE_UNKNOWN|
			nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL;

		$all_host_problems = nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE;

		$filters = $this->add_view('status/filters');
		$filters->header_title = _('Display Filters');
		$filters->lable_host_status_types = _('Host Status Types');
		$filters->lable_host_properties = _('Host Properties');
		$filters->lable_service_status_types = _('Service Status Types');
		$filters->lable_service_properties = _('Service Properties');
		$host_status_type_val = '';
		$hostprop_val = '';
		$service_status_type_val = '';
		$serviceprop_val = '';
		$found = 0;

		# Host status types
		if ($hoststatustypes == $all_host_status_types)
			$host_status_type_val = _("All");
		elseif ($hoststatustypes == $all_host_problems)
			$host_status_type_val = _("All problems");
		else {
			$found = 0;
			if ($hoststatustypes & nagstat::HOST_PENDING) {
				$host_status_type_val .= _(" Pending");
				$found = 1;
			}
			if ($hoststatustypes & nagstat::HOST_UP) {
				$host_status_type_val .= sprintf(_("%s Up"), ($found == 1) ? " |" : '');
				$found = 1;
		                }
			if ($hoststatustypes & nagstat::HOST_DOWN) {
				$host_status_type_val .= sprintf(_("%s Down"), ($found == 1) ? " |" : '');
				$found = 1;
			}
			if ($hoststatustypes & nagstat::HOST_UNREACHABLE)
				$host_status_type_val .= sprintf(_("%s Unreachable"), ($found == 1) ? " |" : '');
		}
		$filters->host_status_type_val = $host_status_type_val;

		# Host properties
		if (!$hostprops)
			$hostprop_val = _("Any");
		else {
			$found = 0;
			if ($hostprops & nagstat::HOST_SCHEDULED_DOWNTIME) {
				$hostprop_val .= ' ' . _('In Scheduled Downtime');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_NO_SCHEDULED_DOWNTIME) {
				$hostprop_val .= sprintf(_("%s Not In Scheduled Downtime"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_STATE_ACKNOWLEDGED) {
				$hostprop_val .= sprintf(_("%s Has Been Acknowledged"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_STATE_UNACKNOWLEDGED) {
				$hostprop_val .= sprintf(_("%s Has Not Been Acknowledged"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_CHECKS_DISABLED) {
				$hostprop_val .= sprintf(_("%s Checks Disabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_CHECKS_ENABLED) {
				$hostprop_val .= sprintf(_("%s Checks Enabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_EVENT_HANDLER_DISABLED) {
				$hostprop_val .= sprintf(_("%s Event Handler Disabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
		                }
			if ($hostprops & nagstat::HOST_EVENT_HANDLER_ENABLED) {
				$hostprop_val .= sprintf(_("%s Event Handler Enabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_FLAP_DETECTION_DISABLED) {
				$hostprop_val .= sprintf(_("%s Flap Detection Disabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_FLAP_DETECTION_ENABLED) {
				$hostprop_val .= sprintf(_("%s Flap Detection Enabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_IS_FLAPPING) {
				$hostprop_val .= sprintf(_("%s Is Flapping"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_IS_NOT_FLAPPING) {
				$hostprop_val .= sprintf(_("%s Is Not Flapping"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_NOTIFICATIONS_DISABLED) {
				$hostprop_val .= sprintf(_("%s Notifications Disabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_NOTIFICATIONS_ENABLED) {
				$hostprop_val .= sprintf(_("%s Notifications Enabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_PASSIVE_CHECKS_DISABLED) {
				$hostprop_val .= sprintf(_("%s Passive Checks Disabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_PASSIVE_CHECKS_ENABLED) {
				$hostprop_val .= sprintf(_("%s Passive Checks Enabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_PASSIVE_CHECK) {
				$hostprop_val .= sprintf(_("%s Passive Checks"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_ACTIVE_CHECK) {
				$hostprop_val .= sprintf(_("%s Active Checks"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_HARD_STATE) {
				$hostprop_val .= sprintf(_("%s In Hard State"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($hostprops & nagstat::HOST_SOFT_STATE) {
				$hostprop_val .= sprintf(_("%s In Soft State"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
		}
		$filters->hostprop_val = $hostprop_val;

		# Service Status Types
		if ($servicestatustypes == $all_service_status_types)
			$service_status_type_val = _("All");
		elseif ($servicestatustypes == $all_service_problems)
			$service_status_type_val = _("All Problems");
		else {
			$found = 0;
			if ($servicestatustypes & nagstat::SERVICE_PENDING) {
				$service_status_type_val = ' ' . _("Pending");
				$found = 1;
			}
			if ($servicestatustypes & nagstat::SERVICE_OK) {
				$service_status_type_val .= sprintf(_("%s OK"), ($found == 1) ? ' |' : '');
				$found = 1;
			}
			if ($servicestatustypes & nagstat::SERVICE_UNKNOWN) {
				$service_status_type_val .= sprintf(_("%s Unknown"), ($found == 1) ? ' |' : '');
				$found = 1;
			}
			if ($servicestatustypes & nagstat::SERVICE_WARNING) {
				$service_status_type_val .= sprintf(_("%s Warning"), ($found == 1) ? ' |' : '');
				$found = 1;
			}
			if ($servicestatustypes & nagstat::SERVICE_CRITICAL) {
				$service_status_type_val .= sprintf(_("%s Critical"), ($found == 1) ? ' |' : '');
				$found = 1;
			}
		}
		$filters->service_status_type_val = $service_status_type_val;

		# Service Properties
		if ($serviceprops == 0)
			$serviceprop_val = _("Any");
		else {
			$found = 0;
			if ($serviceprops & nagstat::SERVICE_SCHEDULED_DOWNTIME){
				$serviceprop_val .= ' ' .  _("In Scheduled Downtime");
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_NO_SCHEDULED_DOWNTIME) {
				$serviceprop_val .= sprintf(_("%s Not In Scheduled Downtime"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_STATE_ACKNOWLEDGED) {
				$serviceprop_val .= sprintf(_("%s Has Been Acknowledged"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_STATE_UNACKNOWLEDGED) {
				$serviceprop_val .= sprintf(_("%s Has Not Been Acknowledged"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_CHECKS_DISABLED) {
				$serviceprop_val .= sprintf(_("%s Checks Disabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_CHECKS_ENABLED) {
				$serviceprop_val .= sprintf(_("%s Checks Enabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_EVENT_HANDLER_DISABLED) {
				$serviceprop_val .= sprintf(_("%s Event Handler Disabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_EVENT_HANDLER_ENABLED) {
				$serviceprop_val .= sprintf(_("%s Event Handler Enabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_FLAP_DETECTION_DISABLED) {
				$serviceprop_val .= sprintf(_("%s Flap Detection Disabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_FLAP_DETECTION_ENABLED) {
				$serviceprop_val .= sprintf(_("%s Flap Detection Enabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_IS_FLAPPING) {
				$serviceprop_val .= sprintf(_("%s Is Flapping"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_IS_NOT_FLAPPING) {
				$serviceprop_val .= sprintf(_("%s Is Not Flapping"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_NOTIFICATIONS_DISABLED) {
				$serviceprop_val .= sprintf(_("%s Notifications Disabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_NOTIFICATIONS_ENABLED) {
				$serviceprop_val .= sprintf(_("%s Notifications Enabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_PASSIVE_CHECKS_DISABLED) {
				$serviceprop_val .= sprintf(_("%s Passive Checks Disabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_PASSIVE_CHECKS_ENABLED) {
				$serviceprop_val .= sprintf(_("%s Passive Checks Enabled"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_PASSIVE_CHECK) {
				$serviceprop_val .= sprintf(_("%s Passive Checks"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_ACTIVE_CHECK) {
				$serviceprop_val .= sprintf(_("%s Active Checks"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_HARD_STATE) {
				$serviceprop_val .= sprintf(_("%s In Hard State"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
			if ($serviceprops & nagstat::SERVICE_SOFT_STATE) {
				$serviceprop_val .= sprintf(_("%s In Soft State"), ($found == 1) ? ' &amp;' : '');
				$found = 1;
			}
		}
		$filters->serviceprop_val = $serviceprop_val;
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
}
