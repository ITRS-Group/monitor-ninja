<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Status controller
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Status_Controller extends Authenticated_Controller {
	public $current = false;
	public $img_sort_up = false;
	public $img_sort_down = false;
	public $logos_path = '';

	public function __construct()
	{
		parent::__construct();

		# load current status for host/service status totals
		$this->current = new Current_status_Model();
		$this->current->analyze_status_data();

		$this->logos_path = Kohana::config('config.logos_path');
	}

	/**
	 * Equivalent to style=hostdetail
	 *
	 * @param string $host
	 * @param int $hoststatustypes
	 * @param str $sort_order
	 * @param str $sort_field
	 * @param bool $show_services
	 */
	public function host($host='all', $hoststatustypes=false, $sort_order='ASC', $sort_field='host_name', $show_services=false, $group_type=false)
	{
		$host = $this->input->get('host', $host);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$sort_order = $this->input->get('sort_order', $sort_order);
		$sort_field = $this->input->get('sort_field', $sort_field);
		$show_services = $this->input->get('show_services', $show_services);
		$group_type = $this->input->get('group_type', $group_type);
		$group_type = strtolower($group_type);

		$host = trim($host);
		$hoststatustypes = strtolower($hoststatustypes)==='false' ? false : $hoststatustypes;

		$this->template->content = $this->add_view('status/host');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		widget::add('status_totals', array('index', $this->current, $host, $hoststatustypes, false, $group_type), $this);
		$this->xtra_css = array_merge($this->xtra_css, array($this->add_path('/css/common.css')));
		$this->template->content->widgets = $this->widgets;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;

		# set sort images, used in header_links() below
		$this->img_sort_up = $this->img_path('images/up.gif');
		$this->img_sort_down = $this->img_path('images/down.gif');

		# assign specific header fields and values for current method
		$header_link_fields = array(
			array('title' => $this->translate->_('Host'), 'sort_field_db' => 'host_name', 'sort_field_str' => 'host name'),
			array('title' => $this->translate->_('Status'), 'sort_field_db' => 'current_state', 'sort_field_str' => 'host status'),
			array('title' => $this->translate->_('Last Check'), 'sort_field_db' => 'last_check', 'sort_field_str' => 'last check time'),
			array('title' => $this->translate->_('Duration'), 'sort_field_db' => 'duration', 'sort_field_str' => 'state duration'),
			array('title' => $this->translate->_('Status Information'))
		);

		# build header links array
		foreach ($header_link_fields as $fields) {
			if (sizeof($fields) > 1) {
				$header_links[] = $this->header_links(Router::$method, $host, $fields['title'], Router::$method, $fields['sort_field_db'], $fields['sort_field_str'], $hoststatustypes, false);
			} else {
				$header_links[] = $this->header_links(Router::$method, $host, $fields['title']);
			}
		}

		$this->template->content->header_links = $header_links;

		$shown = $host == 'all' ? $this->translate->_('All Hosts') : $this->translate->_('Host')." '".$host."'";
		$sub_title = $this->translate->_('Host Status Details For').' '.$shown;
		$this->template->content->sub_title = $sub_title;

		$result = $this->current->host_status_subgroup_names($host, $show_services, $hoststatustypes, $sort_field, $sort_order);
		$this->template->content->result = $result;
		$this->template->content->logos_path = $this->logos_path;
	}

	/**
	 * List status details for hosts and services
	 *
	 * @param str $name
	 * @param int $servicestatustypes
	 * @param int $hoststatustypes
	 * @param str $sort_order
	 * @param str $sort_field
	 * @param str $group_type
	 */
	public function service($name='all', $hoststatustypes=false, $servicestatustypes=false, $service_props=false, $sort_order='ASC', $sort_field='host_name', $group_type=false)
	{
		$name = $this->input->get('name', $name);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$service_props = $this->input->get('service_props', $service_props);
		$sort_order = $this->input->get('sort_order', $sort_order);
		$sort_field = $this->input->get('sort_field', $sort_field);
		$group_type = $this->input->get('group_type', $group_type);

		$name = trim($name);
		$hoststatustypes = strtolower($hoststatustypes)==='false' ? false : $hoststatustypes;
		$servicestatustypes = strtolower($servicestatustypes)==='false' ? false : $servicestatustypes;

		$sort_order = $sort_order == 'false' || empty($sort_order) ? 'ASC' : $sort_order;
		$sort_field = $sort_field == 'false' || empty($sort_field) ? 'host_name' : $sort_field;

		$this->template->content = $this->add_view('status/service');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		widget::add('status_totals', array('index', $this->current, $name, $hoststatustypes, $servicestatustypes, $group_type), $this);
		$this->xtra_css = array_merge($this->xtra_css, array($this->add_path('/css/common.css')));
		$this->template->content->widgets = $this->widgets;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;

		# set sort images, used in header_links() below
		$this->img_sort_up = $this->img_path('images/up.gif');
		$this->img_sort_down = $this->img_path('images/down.gif');

		# assign specific header fields and values for current method
		$header_link_fields = array(
			array('title' => $this->translate->_('Host'), 'sort_field_db' => 'h.host_name', 'sort_field_str' => 'host name'),
			array('title' => $this->translate->_('Service'), 'sort_field_db' => 's.service_description', 'sort_field_str' => 'service name'),
			array('title' => $this->translate->_('Status'), 'sort_field_db' => 's.current_state', 'sort_field_str' => 'service status'),
			array('title' => $this->translate->_('Last Check'), 'sort_field_db' => 'last_check', 'sort_field_str' => 'last check time'),
			array('title' => $this->translate->_('Duration'), 'sort_field_db' => 'duration', 'sort_field_str' => 'state duration'),
			array('title' => $this->translate->_('Status Information'))
		);

		# build header links array
		foreach ($header_link_fields as $fields) {
			if (sizeof($fields) > 1) {
				$header_links[] = $this->header_links(Router::$method, $name, $fields['title'], Router::$method, $fields['sort_field_db'], $fields['sort_field_str'], $hoststatustypes, $servicestatustypes, $service_props);
			} else {
				$header_links[] = $this->header_links(Router::$method, $name, $fields['title']);
			}
		}

		$this->template->content->header_links = $header_links;

		$shown = $name == 'all' ? $this->translate->_('All Hosts') : $this->translate->_('Host')." '".$name."'";

		# handle host- or servicegroup details
		if (!empty($group_type)) {
			$shown = $group_type == 'servicegroup' ? $this->translate->_('Service Group') : $this->translate->_('Host Group');
			$shown .= " '".$name."'";
			$hostlist = $this->current->get_servicegroup_hoststatus($name, $hoststatustypes, $servicestatustypes);
			$group_hosts = false;
			foreach ($hostlist as $host_info) {
				$group_hosts[] = $host_info->host_name;
			}

			$result = $this->current->host_status_subgroup_names($group_hosts, true, $hoststatustypes, $sort_field, $sort_order, $servicestatustypes);
		} else {
			$result = $this->current->host_status_subgroup_names($name, true, $hoststatustypes, $sort_field, $sort_order, $servicestatustypes);
		}
		$sub_title = $this->translate->_('Service Status Details For').' '.$shown;
		$this->template->content->sub_title = $sub_title;

		$this->template->content->result = $result;
		$this->template->content->logos_path = $this->logos_path;
	}

	/**
	*	Show servicegroup status, wrapper for group('service', ...)
	* 	@param 	str $group
	* 	@param 	int $hoststatustypes
	* 	@param 	int $servicestatustypes
	* 	@param 	str $style
	*
	*/
	public function servicegroup($group='all', $hoststatustypes=false, $servicestatustypes=false, $style='overview')
	{
		$grouptype = 'service';
		url::redirect(Router::$controller.'/group/'.$grouptype.'/'. $group. '?hoststatustypes=' . $hoststatustypes . '&servicestatustypes=' . $servicestatustypes . '&style='.$style);
	}

	/**
	*	Show hostgroup status, wrapper for group('host', ...)
	* 	@param 	str $group
	* 	@param 	int $hoststatustypes
	* 	@param 	int $servicestatustypes
	* 	@param 	str $style
	*
	*/
	public function hostgroup($group='all', $hoststatustypes=false, $servicestatustypes=false, $style='overview')
	{
		$grouptype = 'host';
		url::redirect(Router::$controller.'/group/'.$grouptype.'/'. $group. '?hoststatustypes=' . $hoststatustypes . '&servicestatustypes=' . $servicestatustypes . '&style='.$style);
	}

	/**
	 * Show status for host- or servicegroups
	 *
	 * @param str $grouptype
	 * @param str $group
	 * @param int $hoststatustypes
	 * @param int $servicestatustypes
	 * @param str $style
	 */

	public function group($grouptype='service', $group='all', $hoststatustypes=false, $servicestatustypes=false, $style='overview')
	{
		$grouptype = $this->input->get('grouptype', $grouptype);
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$style = $this->input->get('style', $style);
		$group = trim($group);
		$hoststatustypes = strtolower($hoststatustypes)==='false' ? false : $hoststatustypes;

		switch ($style) {
			case 'overview':
				$this->template->content = $this->add_view('status/group_overview');
				break;
			case 'detail': case 'details':
				url::redirect(Router::$controller.'/service/'. $group. '?hoststatustypes=' . $hoststatustypes . '&servicestatustypes=' . $servicestatustypes . '&group_type='.$grouptype.'group');
				break;
			case 'summary':
				url::redirect(Router::$controller.'/service/'. $group. '?hoststatustypes=' . $hoststatustypes . '&servicestatustypes=' . $servicestatustypes . '&group_type='.$grouptype.'group');
				break;
		}
		$group_details = false;
		$groupname_tmp = false;
		if ($group == 'all') {
			$group_info_res = $grouptype == 'service' ? Servicegroup_Model::get_all() : Hostgroup_Model::get_all();
			foreach ($group_info_res as $group_res) {
				$groupname_tmp = $group_res->{$grouptype.'group_name'}; # different db field depending on host- or servicegroup
				$group_details[] = $this->_show_group($grouptype, $groupname_tmp, $hoststatustypes, $servicestatustypes, $style);
			}
		} else {
			$group_details[] = $this->_show_group($grouptype, $group, $hoststatustypes, $servicestatustypes, $style);
		}

		$this->template->content->group_details = $group_details;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		widget::add('status_totals', array('index', $this->current, $group, $hoststatustypes, $servicestatustypes, $grouptype.'group'), $this);
		$this->xtra_css = array_merge($this->xtra_css, array($this->add_path('/css/common.css')));
		$this->template->content->widgets = $this->widgets;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;

		$content = $this->template->content;
		$t = $this->translate;

		if ($grouptype == 'service') {
			$content->lable_header = $group == 'all' ? $t->_("Service Overview For All Service Groups") : $t->_("Service Overview For Service Group");
		} else {
			$content->lable_header = $group == 'all' ? $t->_("Service Overview For All Host Groups") : $t->_("Service Overview For Host Group");
		}
		$content->lable_host = $t->_('Host');
		$content->lable_status = $t->_('Status');
		$content->lable_services = $t->_('Services');
		$content->lable_actions = $t->_('Actions');

		# @@@FIXME: handle macros
	}

	/**
	*	Display servicegroup summary
	*/
	public function servicegroup_summary($group='all', $hoststatustypes=false, $servicestatustypes=false)
	{
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);

		$group = trim($group);
		$this->template->content = $this->add_view('status/group_summary');
		$content = $this->template->content;
		$t = $this->translate;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		widget::add('status_totals', array('index', $this->current, $group, $hoststatustypes, $servicestatustypes, 'servicegroup'), $this);
		$this->template->content->widgets = $this->widgets;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = array_merge($this->xtra_css, array($this->add_path('/css/common.css')));

		$group_details = false;
		if ($group == 'all') {
			$content->lable_header = $t->_('Status Summary For All Service Groups');
			$group_info_res = Servicegroup_Model::get_all();
			foreach ($group_info_res as $group_res) {
				$group_details[] = $this->_show_servicegroup_totals_summary($group_res->servicegroup_name);
			}
		} else {
			# make sure we have the correct servicegroup
			$group_info_res = Servicegroup_Model::get_by_field_value('servicegroup_name', $group);
			if ($group_info_res) {
				$group = $group_info_res->servicegroup_name;
			} else {
				# overwrite previous view with the error view, add some text and bail out
				$this->template->content = $this->add_view('error');
				$this->template->content->error_message = sprintf($t->_("The requested servicegroup ('%s') wasn't found"), $group);
				return;
			}
			$content->lable_header = $t->_('Status Summary For Service Group ')."'".$group."'";
			$group_details[] = $this->_show_servicegroup_totals_summary($group);
		}

		# since we don't use these values yet we define a default value
		$hostproperties = false;
		$serviceproperties = false;

		$content->hoststatustypes = $hoststatustypes;
		$content->hostproperties = $hostproperties;
		$content->servicestatustypes = $servicestatustypes;
		$content->serviceproperties = $serviceproperties;
		$content->label_group_name = $t->_('Service Group');
		$content->label_host_summary = $t->_('Host Status Summary');
		$content->label_service_summary = $t->_('Service Status Summary');
		$content->label_no_data = $t->_('No matching hosts');
		$content->label_up = $t->_('UP');
		$content->label_down = $t->_('DOWN');
		$content->label_unhandled = $t->_('Unhandled');
		$content->label_scheduled = $t->_('Scheduled');
		$content->label_acknowledged = $t->_('Acknowledged');
		$content->label_disabled = $t->_('Disabled');
		$content->label_unreachable = $t->_('UNREACHABLE');
		$content->label_pending = $t->_('PENDING');
		$content->label_ok = $t->_('OK');
		$content->label_warning = $t->_('WARNING');
		$content->label_on_problem_hosts = $t->_('on Problem Hosts');
		$content->label_unknown = $t->_('UNKNOWN');
		$content->label_critical = $t->_('CRITICAL');
		$content->label_no_servicedata = $t->_('No matching services');

		$content->group_details = $group_details;
		#echo Kohana::debug($group_details);
	}

	/**
	*	shows host total summary information for a specific servicegroup
	*
	* 	@return obj
	*/
	public function _show_servicegroup_totals_summary($group=false)
	{
		$group = $this->input->get('group', $group);
		$content = false;
		#$hoststatustypes = strtolower($hoststatustypes)==='false' ? false : $hoststatustypes;

		$group_info_res = Servicegroup_Model::get_by_field_value('servicegroup_name', $group);
		if ($group_info_res === false) {
			return;
		}
		$hostlist = $this->current->get_servicegroup_hoststatus($group);
		$content->group_alias = $group_info_res->alias;
		$content->groupname = $group;
		if ($hostlist->count() > 0) {
			$service_states = false;
			$hostinfo = false;
			$hosts_up = 0;
			$hosts_down = 0;
			$hosts_unreachable = 0;
			$hosts_pending = 0;
			$hosts_down_scheduled = 0;
			$hosts_down_acknowledged = 0;
			$hosts_down_disabled = 0;
			$hosts_down_unacknowledged = 0;
			$hosts_unreachable_scheduled = 0;
			$hosts_unreachable_acknowledged = 0;
			$hosts_unreachable_disabled = 0;
			$hosts_unreachable_unacknowledged = 0;
			$seen_hosts = array();
			foreach ($hostlist as $host) {
				$host_problem = true;
				if (in_array($host->host_name, $seen_hosts))
					continue;
				switch ($host->current_state) {
					case Current_status_Model::HOST_UP:
						$hosts_up++;
						break;
					case Current_status_Model::HOST_DOWN:
						if ($host->scheduled_downtime_depth) {
							$hosts_down_scheduled++;
							$host_problem = false;
						}
						if ($host->problem_has_been_acknowledged) {
							$hosts_down_acknowledged++;
							$host_problem = false;
						}
						if ($host->active_checks_enabled) {
							$hosts_down_disabled++;
							$host_problem = false;
						}
						if ($host_problem) {
							$hosts_down_unacknowledged++;
						}
						$hosts_down++;
						break;
					case Current_status_Model::HOST_UNREACHABLE:
						if ($host->scheduled_downtime_depth) {
							$hosts_unreachable_scheduled++;
							$host_problem = false;
						}
						if ($host->problem_has_been_acknowledged) {
							$hosts_unreachable_acknowledged++;
							$host_problem = false;
						}
						if ($host->active_checks_enabled) {
							$hosts_unreachable_disabled++;
							$host_problem = false;
						}
						if ($host_problem) {
							$hosts_unreachable_unacknowledged++;
						}
						$hosts_unreachable++;
						break;
					default:
						$hosts_pending++;
				}
				$seen_hosts[] = $host->host_name;
			}

			$content->hosts_up = $hosts_up;
			$content->groupname = $group;
			$content->hosts_down = $hosts_down;
			$content->hosts_down_unacknowledged = $hosts_down_unacknowledged;
			$content->hosts_down_scheduled = $hosts_down_scheduled;
			$content->hosts_down_acknowledged = $hosts_down_acknowledged;
			$content->hosts_down_disabled = $hosts_down_disabled;
			$content->hosts_unreachable = $hosts_unreachable;
			$content->hosts_unreachable_unacknowledged = $hosts_unreachable_unacknowledged;
			$content->hosts_unreachable_scheduled = $hosts_unreachable_scheduled;
			$content->hosts_unreachable_acknowledged = $hosts_unreachable_acknowledged;
			$content->hosts_unreachable_disabled = $hosts_unreachable_disabled;
			$content->hosts_pending = $hosts_pending;

			# fetch servicedata
			$content->service_data = $this->_show_servicegroup_service_summary($seen_hosts, $group);
		} else {
			# nothing found
		}
		return $content;
	}

	/**
	*
	*
	*/
	public function _show_servicegroup_service_summary($hostlist=false, $group=false)
	{
		$hostlist = $this->input->get('hostlist', $hostlist);
		$group = $this->input->get('group', $group);
		if (empty($hostlist)) {
			return false;
		}
		$service_info = false;
		$result = $this->current->host_status_subgroup_names($hostlist, true);
		$service_model = new Service_Model();
		$service_data = $service_model->get_services_for_group($group);
		$service_list = false;
		if ($service_data) {
			foreach ($service_data as $row) {
				$service_list[] = $row->id;
			}
		}
		$services_ok = 0;
		$services_warning_host_problem = 0;
		$services_warning_scheduled = 0;
		$services_warning_acknowledged = 0;
		$services_warning_disabled = 0;
		$services_warning_unacknowledged = 0;
		$services_warning = 0;
		$services_unknown_host_problem = 0;
		$services_unknown_scheduled = 0;
		$services_unknown_acknowledged = 0;
		$services_unknown_disabled = 0;
		$services_unknown_unacknowledged = 0;
		$services_unknown = 0;
		$services_critical_host_problem = 0;
		$services_critical_scheduled = 0;
		$services_critical_acknowledged = 0;
		$services_critical_disabled = 0;
		$services_critical_unacknowledged = 0;
		$services_critical = 0;
		$services_pending = 0;

		foreach ($result as $row) {
			if (!in_array($row->service_id, $service_list))
				continue;
			$problem = true;
			switch ($row->current_state) {
				case Current_status_Model::SERVICE_OK:
					$services_ok++;
					break;
				case Current_status_Model::SERVICE_WARNING:
					if ($row->host_state == Current_status_Model::HOST_DOWN || $row->host_state == Current_status_Model::HOST_UNREACHABLE) {
						$services_warning_host_problem++;
						$problem = false;
					}
					if ($row->scheduled_downtime_depth > 0) {
						$services_warning_scheduled++;
						$problem = false;
					}
					if ($row->problem_has_been_acknowledged) {
						$services_warning_acknowledged++;
						$problem = false;
					}
					if (!$row->active_checks_enabled) {
						$services_warning_disabled++;
						$problem = false;
					}
					if ($problem == true)
						$services_warning_unacknowledged++;
					$services_warning++;
					break;
				case Current_status_Model::SERVICE_UNKNOWN:
					if ($row->host_state == Current_status_Model::HOST_DOWN || $row->host_state == Current_status_Model::HOST_UNREACHABLE) {
						$services_unknown_host_problem++;
						$problem = false;
					}
					if ($row->scheduled_downtime_depth > 0) {
						$services_unknown_scheduled++;
						$problem = false;
					}
					if ($row->problem_has_been_acknowledged) {
						$services_unknown_acknowledged++;
						$problem = false;
					}
					if (!$row->checks_enabled){
						$services_unknown_disabled++;
						$problem = false;
					}
					if ($problem == true)
						$services_unknown_unacknowledged++;
					$services_unknown++;
					break;
				case Current_status_Model::SERVICE_CRITICAL:
					if ($row->host_state == Current_status_Model::HOST_DOWN || $row->host_state == Current_status_Model::HOST_UNREACHABLE) {
						$services_critical_host_problem++;
						$problem = false;
					}
					if ($row->scheduled_downtime_depth > 0) {
						$services_critical_scheduled++;
						$problem = false;
					}
					if ($row->problem_has_been_acknowledged) {
						$services_critical_acknowledged++;
						$problem = false;
					}
					if (!$row->active_checks_enabled) {
						$services_critical_disabled++;
						$problem = false;
					}
					if ($problem == true)
						$services_critical_unacknowledged++;
					$services_critical++;
					break;
				case Current_status_Model::SERVICE_PENDING:
					$services_pending++;
					break;
				} # end switch
			} # end foreach

		$service_info->services_ok = $services_ok;
		$service_info->services_warning_host_problem = $services_warning_host_problem;
		$service_info->services_warning_scheduled = $services_warning_scheduled ;
		$service_info->services_warning_acknowledged = $services_warning_acknowledged;
		$service_info->services_warning_disabled = $services_warning_disabled;
		$service_info->services_warning_unacknowledged = $services_warning_unacknowledged;
		$service_info->services_warning = $services_warning;
		$service_info->services_unknown_host_problem = $services_unknown_host_problem;
		$service_info->services_unknown_scheduled = $services_unknown_scheduled;
		$service_info->services_unknown_acknowledged = $services_unknown_acknowledged;
		$service_info->services_unknown_disabled = $services_unknown_disabled;
		$service_info->services_unknown_unacknowledged = $services_unknown_unacknowledged;
		$service_info->services_unknown = $services_unknown;
		$service_info->services_critical_host_problem = $services_critical_host_problem;
		$service_info->services_critical_scheduled = $services_critical_scheduled;
		$service_info->services_critical_acknowledged = $services_critical_acknowledged;
		$service_info->services_critical_disabled = $services_critical_disabled;
		$service_info->services_critical_unacknowledged = $services_critical_unacknowledged;
		$service_info->services_critical = $services_critical;
		$service_info->services_pending = $services_pending;

		return $service_info;
	}

	/**
	*	Fetch info on single service- or hostgroup and assign to returned content object for later use in template
	*
	* 	@param 	str $grouptype [host|service]
	* 	@param 	str $group name of group
	* 	@param 	int $hoststatustypes
	* 	@param 	int $servicestatustypes
	* 	@param 	str $style
	* 	@return obj
	*/
	public function _show_group($grouptype='service', $group=false, $hoststatustypes=false, $servicestatustypes=false, $style='overview')
	{
		$grouptype = $this->input->get('grouptype', $grouptype);
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$style = $this->input->get('style', $style);

		$content = false;
		$hoststatustypes = strtolower($hoststatustypes)==='false' ? false : $hoststatustypes;

		$t = $this->translate;
		$group_info_res = $grouptype == 'service' ? Servicegroup_Model::get_by_field_value('servicegroup_name', $group) : Hostgroup_Model::get_by_field_value('hostgroup_name', $group);
		$hostlist = $this->current->get_group_hoststatus($grouptype, $group, $hoststatustypes, $servicestatustypes);
		$content->group_alias = $group_info_res->alias;
		$content->groupname = $group;
		if ($hostlist->count() > 0) {
			$content->lable_header .= " '".$group."'";
			$service_states = false;
			$hostinfo = false;
			$lable_extinfo_host = $t->_('View Extended Information For This Host');
			$lable_svc_status = $t->_('View Service Details For This Host');
			$lable_statusmap = $t->_('Locate Host On Map');
			foreach ($hostlist as $host) {
				$nacoma_link = false;
				/**
				 * Modify config/config.php to enable NACOMA
				 * and set the correct path in config/config.php,
				 * if installed, to use this
				 */
				if (Kohana::config('config.nacoma_path')!==false) {
					$lable_nacoma = $t->_('Configure this host using NACOMA (Nagios Configuration Manager)');
					$nacoma_link = '<a href="'.Kohana::config('config.nacoma_path').'edit.php?obj_type=host&amp;host='.$host->host_name.
						'"title="'.$lable_nacoma.'">'.html::image($this->img_path('images/nacoma.png')).'</a>';
				}

				/**
				 * Enable PNP4Nagios integration
				 * Set correct path in config/config.php
				 */
				$pnp_link = false;
				if (Kohana::config('config.pnp4nagios_path')!==false) {
					$lable_pnp = $t->_('Show performance graph');
					$pnp_link = '<a href="'.Kohana::config('config.pnp4nagios_path').'index.php?host='.$host->host_name.
						'"title="'.$lable_pnp.'">'.html::image($this->img_path('images/graphlight.png')).'</a>';
				}

				# decide status_link host- and servicestate parameters
				$hst_status_type = nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE;
				$svc_status_type = false;
				switch ($host->service_state) {
					case Current_status_Model::SERVICE_OK:
						$svc_status_type = nagstat::SERVICE_OK;
						break;
					case Current_status_Model::SERVICE_WARNING:
						$svc_status_type = nagstat::SERVICE_WARNING;
						break;
					case Current_status_Model::SERVICE_UNKNOWN:
						$svc_status_type = nagstat::SERVICE_UNKNOWN;
						break;
					case Current_status_Model::SERVICE_CRITICAL:
						$svc_status_type = nagstat::SERVICE_CRITICAL;
						break;
					case Current_status_Model::SERVICE_PENDING:
						$svc_status_type = nagstat::SERVICE_PENDING;
						break;
				}
				$service_states[$host->host_name][$host->service_state] = array(
					'class_name' => 'miniStatus' . $this->current->status_text($host->service_state, 'service'),
					'status_link' => html::anchor('status/'.$grouptype.'group/'.$group.'?hoststatustypes='.$hst_status_type.'&servicestatustypes='.$svc_status_type.'&style=detail', html::specialchars($host->state_count.' '.$this->current->status_text($host->service_state, 'service')) ),
					'extinfo_link' => html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->img_path('images/detail.gif'), array('alt' => $lable_extinfo_host, 'title' => $lable_extinfo_host)) ),
					'svc_status_link' => html::anchor('status/service/'.$host->host_name, html::image($this->img_path('images/status2.gif'), array('alt' => $lable_svc_status, 'title' => $lable_svc_status)) ),
					'statusmap_link' => html::anchor('statusmap/host/'.$host->host_name, html::image($this->img_path('images/status3.gif'), array('alt' => $lable_statusmap, 'title' => $lable_statusmap)) ),
					'nacoma_link' => $nacoma_link,
					'pnp_link' => $pnp_link
					);

				$action_link = false;
				if (!is_null($host->action_url)) {
					$lable_host_action = $t->_('Perform Extra Host Actions');
					$action_link = '<a href="'.$host->action_url.'" target="_blank">'.html::image($this->img_path('images/action.gif'), array('alt' => $lable_host_action, 'title' => $lable_host_action)).'</a>';
				}
				$notes_link = false;
				if (!is_null($host->notes_url)) {
					$lable_host_notes = $t->_('View Extra Host Notes');
					$notes_link = '<a href="'.$host->notes_url.'" target="_blank">'.html::image($this->img_path('images/notes.gif'), array('alt' => $lable_host_notes, 'title' => $lable_host_notes)).'</a>';
				}

				$host_icon = false;
				# logos_path
				if (!empty($host->icon_image)) {
					$host_icon = '<img src="'.$this->logos_path.$host->icon_image.'" WIDTH=20 HEIGHT=20 border=0 alt="'.$host->icon_image_alt.'" title="'.$host->icon_image_alt.'" />';
				}
				$hostinfo[$host->host_name] = array(
					'state_str' => $this->current->status_text($host->current_state, 'host'),
					'class_name' => 'statusHOST' . $this->current->status_text($host->current_state, 'host'),
					'status_link' => html::anchor('status/service/'.$host->host_name.'?hoststatustypes='.$hoststatustypes.'&servicestatustypes='.(int)$servicestatustypes, html::specialchars($host->host_name), array('title' => $host->address)),
					'action_link' => $action_link,
					'notes_link' => $notes_link,
					'host_icon' => $host_icon
					);
			}
			$content->service_states = $service_states;
			$content->hostinfo = $hostinfo;
			$content->hoststatustypes = $hoststatustypes;
			$content->servicestatustypes = $servicestatustypes;
		} else {
			# nothing found
		}
		return $content;
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
			$service_props=false)
	{

		$type = trim($type);
		$filter_object = trim($filter_object);
		$title = trim($title);
		if (empty($type) || empty($title))  {
			return false;
		}
		$header = false;
		$lable_ascending = $this->translate->_('ascending');
		$lable_descending = $this->translate->_('descending');
		$lable_sort_by = $this->translate->_('Sort by');
		$lable_last = $this->translate->_('last');
		switch ($type) {
			case 'host':
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
					$header['url_asc'] = Router::$controller.'/'.$method.'/'.$filter_object.'?hoststatustypes='.$host_status.'&servicestatustypes='.$service_status.'&service_props='.(int)$service_props.'&sort_order='.nagstat::SORT_ASC.'&sort_field='.$sort_field_db;
					$header['img_asc'] = $this->img_sort_up;
					$header['alt_asc'] = $lable_sort_by.' '.$lable_last.' '.$sort_field_str.' ('.$lable_ascending.')';
					$header['url_desc'] = Router::$controller.'/'.$method.'/'.$filter_object.'?hoststatustypes='.$host_status.'&servicestatustypes='.$service_status.'&service_props='.(int)$service_props.'&sort_order='.nagstat::SORT_DESC.'&sort_field='.$sort_field_db;
					$header['img_desc'] = $this->img_sort_down;
					$header['alt_desc'] = $lable_sort_by.' '.$sort_field_str.' ('.$lable_descending.')';
				}
				break;
		}
		return $header;
	}
}
