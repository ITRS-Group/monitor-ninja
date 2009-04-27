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
	public function host($host='all', $hoststatustypes=false, $sort_order='ASC', $sort_field='host_name', $show_services=false)
	{
		$host = $this->input->get('host', $host);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$sort_order = $this->input->get('sort_order', $sort_order);
		$sort_field = $this->input->get('sort_field', $sort_field);
		$show_services = $this->input->get('show_services', $show_services);

		$host = trim($host);
		$hoststatustypes = strtolower($hoststatustypes)==='false' ? false : $hoststatustypes;

		$this->template->content = $this->add_view('status/host');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		widget::add('status_totals', array('index', $this->current, $host, $hoststatustypes), $this);
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
	*	@name	servicegroup
	*	@desc	Show servicegroup status
	* 	@param 	str $group
	* 	@param 	int $hoststatustypes
	* 	@param 	int $servicestatustypes
	* 	@param 	str $style
	*
	*/
	public function servicegroup($group='all', $hoststatustypes=false, $servicestatustypes=false, $style='overview')
	{
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
				url::redirect(Router::$controller.'/service/'. $group. '?hoststatustypes=' . $hoststatustypes . '&servicestatustypes=' . $servicestatustypes . '&group_type=servicegroup');
				break;
			case 'summary':
				url::redirect(Router::$controller.'/service/'. $group. '?hoststatustypes=' . $hoststatustypes . '&servicestatustypes=' . $servicestatustypes . '&group_type=servicegroup');
				break;
		}
		if ($group == 'all') {
			$group_info_res = Servicegroup_Model::get_all();
			foreach ($group_info_res as $group_res) {
				$group_details[] = $this->show_servicegroup($group_res->servicegroup_name, $hoststatustypes, $servicestatustypes, $style);
			}
		} else {
			$group_details[] = $this->show_servicegroup($group, $hoststatustypes, $servicestatustypes, $style);
		}

		$this->template->content->group_details = $group_details;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		widget::add('status_totals', array('index', $this->current, $group, $hoststatustypes, $servicestatustypes, 'servicegroup'), $this);
		$this->xtra_css = array_merge($this->xtra_css, array($this->add_path('/css/common.css')));
		$this->template->content->widgets = $this->widgets;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;

		$content = $this->template->content;
		$t = $this->translate;

		$content->lable_header = $group == 'all' ? $t->_("Service Overview For All Service Groups") : $t->_("Service Overview For Service Group");
		$content->lable_host = $t->_('Host');
		$content->lable_status = $t->_('Status');
		$content->lable_services = $t->_('Services');
		$content->lable_actions = $t->_('Actions');

		# @@@FIXME: handle macros
	}

	/**
	*	@name	show_servicegroup
	*	@desc	Fetch info on single servicegroup and assign to
	* 			returned content object for later use in template
	* 	@param 	str $group
	* 	@param 	int $hoststatustypes
	* 	@param 	int $servicestatustypes
	* 	@param 	str $style
	* 	@return obj
	*/
	public function show_servicegroup($group=false, $hoststatustypes=false, $servicestatustypes=false, $style='overview')
	{
		$group = $this->input->get('group', $group);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$style = $this->input->get('style', $style);

		$content = false;
		$hoststatustypes = strtolower($hoststatustypes)==='false' ? false : $hoststatustypes;

		$t = $this->translate;
		$group_info_res = Servicegroup_Model::get_by_field_value('servicegroup_name', $group);
		$hostlist = $this->current->get_servicegroup_hoststatus($group, $hoststatustypes, $servicestatustypes);
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
					'status_link' => html::anchor('status/servicegroup/'.$group.'?hoststatustypes='.$hst_status_type.'&servicestatustypes='.$svc_status_type.'&style=detail', html::specialchars($host->state_count.' '.$this->current->status_text($host->service_state, 'service')) ),
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
