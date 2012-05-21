<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Notifications controller
 * Requires authentication
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Notifications_Controller extends Authenticated_Controller {
	public $current = false;
	public $select_types = false;
	public $select_strings = false;

	public function __construct()
	{
		parent::__construct();
		$this->select_types = array(
			0  => array(false,false,false), // all notifications
			1  => array(nagstat::SERVICE_NOTIFICATION,false,false), // all services
			2  => array(nagstat::HOST_NOTIFICATION,false,false), // all hosts
			3  => array(nagstat::SERVICE_NOTIFICATION,false,nagstat::NOTIFICATION_SERVICE_ACK), // service ack ?
			4  => array(nagstat::SERVICE_NOTIFICATION,nagstat::NOTIFICATION_SERVICE_WARNING,false),
			5  => array(nagstat::SERVICE_NOTIFICATION,nagstat::NOTIFICATION_SERVICE_UNKNOWN,false),
			6  => array(nagstat::SERVICE_NOTIFICATION,nagstat::NOTIFICATION_SERVICE_CRITICAL,false),
			7  => array(nagstat::SERVICE_NOTIFICATION,nagstat::NOTIFICATION_SERVICE_RECOVERY,false),
			8  => array(nagstat::SERVICE_NOTIFICATION,false,nagstat::NOTIFICATION_SERVICE_FLAP), // service flapping ?
			9  => array(nagstat::HOST_NOTIFICATION,false,nagstat::NOTIFICATION_HOST_ACK), // host ack ?
			10 => array(nagstat::HOST_NOTIFICATION,nagstat::NOTIFICATION_HOST_DOWN,false),
			11 => array(nagstat::HOST_NOTIFICATION,nagstat::NOTIFICATION_HOST_UNREACHABLE,false),
			12 => array(nagstat::HOST_NOTIFICATION,nagstat::NOTIFICATION_HOST_RECOVERY,false),
			13 => array(nagstat::HOST_NOTIFICATION,false,nagstat::NOTIFICATION_HOST_FLAP), // host flapping ?
		);

		$this->select_strings = array(
			0  => _('All notifications'),
			1  => _('All service notifications'),
			2  => _('All host notifications'),
			3  => _('Service acknowledgements'),
			4  => _('Service warning'),
			5  => _('Service uknown'),
			6  => _('Service critical'),
			7  => _('Service recovery'),
			8  => _('Service flapping'),
			9  => _('Host acknowledgements'),
			10 => _('Host down'),
			11 => _('Host unreachable'),
			12 => _('Host recoverys'),
			13 => _('Host flapping'),
		);

	}

	/**
	 * Default controller method
	 */
	public function index($sort_field='start_time', $sort_order='DESC', $type = false, $query_type = nagstat::FIND_HOST)
	{
		$type = urldecode($this->input->get('type', $type));
		$noheader = urldecode($this->input->get('noheader', false));

		$items_per_page = urldecode($this->input->get('items_per_page', config::get('pagination.default.items_per_page', '*')));
		$note_model = new Notifications_Model();
		$note_model->sort_order = urldecode($this->input->get('sort_order', $sort_order));
		$note_model->sort_field = urldecode($this->input->get('sort_field', $sort_field));

		$this->xtra_js[] = $this->add_path('notifications/js/notifications.js');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;

		if ($type != '') {
			$value = $this->select_types[$type];
			$note_model->where = ($value[0] === false ? '' : " notification_type = '".$value[0]."'").($value[1] === false ? '' : " AND state = '".$value[1]."'").($value[2] === false ? '' : " AND reason_type = '".$value[2]."'");
		}

		$pagination = new Pagination(
			array(
				'total_items'=> $note_model->count_notifications(),
				'items_per_page' => $items_per_page
			)
		);
		$offset = $pagination->sql_offset;
		$result = $note_model->show_notifications($items_per_page, $offset, false);

		$header_link_fields = array(
			array('title' => _('Host'),'sort_field_db' => 'host_name'),
			array('title' => _('Service'),'sort_field_db' => 'service_description'),
			array('title' => _('Time'), 'sort_field_db' => 'start_time'),
			array('title' => _('Contact'), 'sort_field_db' => 'contact_name'),
			array('title' => _('Notification command'), 'sort_field_db' => 'command_name'),
			array('title' => _('Information'), 'sort_field_db' => 'output')
		);

		$header = false;
		$i = 0;
		foreach ($header_link_fields as $field) {
			$header_links[$i]['title'] = $field['title'];
			$header_links[$i]['url_asc'] = Router::$controller.'/?type='.$type.'&sort_order='.nagstat::SORT_ASC.'&sort_field='.$field['sort_field_db'];
			$header_links[$i]['alt_asc'] = _('Sort by').' '._('last').' '.$field['title'].' ('._('ascending').')';
			$header_links[$i]['url_desc'] = Router::$controller.'/?type='.$type.'&sort_order='.nagstat::SORT_DESC.'&sort_field='.$field['sort_field_db'];
			$header_links[$i]['alt_desc'] = _('Sort by').' '.$field['title'].' ('._('descending').')';
			$i++;
		}

		$this->template->title = _('Reporting').' » '._('Contact Notifications');
		$this->template->content = $this->add_view('notifications/index');
		$this->template->content->data = $result;
		$this->template->content->header_links = $header_links;
		$this->template->content->noheader = $noheader;
		$this->template->content->query_type = $query_type;
		$this->template->content->type = $type;
		$this->template->content->service = false;
		$this->template->content->pagination = isset($pagination) ? $pagination : false;
		$this->template->content->select_strings = $this->select_strings;
		$this->template->content->selected_val = $type;
		$this->template->content->date_format_str = nagstat::date_format();
	}

	public function host($host_name = false, $service = false, $sort_field='start_time', $sort_order='DESC', $query_type = nagstat::FIND_HOST)
	{
		$type = urldecode($this->input->get('type', false));
		$noheader = urldecode($this->input->get('noheader', false));
		$items_per_page = urldecode($this->input->get('items_per_page', config::get('pagination.default.items_per_page', '*')));
		$note_model = new Notifications_Model($items_per_page, true, true);
		$note_model->sort_order = urldecode($this->input->get('sort_order', $sort_order));
		$note_model->sort_field = urldecode($this->input->get('sort_field', $sort_field));

		$this->xtra_js[] = $this->add_path('notifications/js/notifications.js');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;

		$service = urldecode($this->input->get('service', $service));
		$host_name = urldecode($this->input->get('host_name', $host_name));
		$sql = '';
		if ($type != '') {
			$value = $this->select_types[$type];
			$sql .= ($value[0] === false ? '': " notification_type = '".$value[0]."'").($value[1] === false ? '': " AND state = '".$value[1]."'").($value[2] === false ? '' : " AND reason_type = '".$value[2]."'");
		}

		if ($host_name != false) {
			$sql .= !empty($sql) ? ' AND ' : '';
			if ($host_name == 'all')
				$sql .= " notification_type = 0";
			else
				$sql .= " host_name = '".$host_name."'";
		}
		if ($service != false)
			$sql .= " AND service_description = '".$service."'";

		$note_model->where = $sql;
		$pagination = new Pagination(
			array(
				'total_items'=> $note_model->count_notifications(),
				'items_per_page' => $items_per_page
			)
		);

		$offset = $pagination->sql_offset;
		$result = $note_model->show_notifications($items_per_page, $offset, false);

		$header_link_fields = array(
			array('title' => _('Host'),'sort_field_db' => 'host_name'),
			array('title' => _('Service'),'sort_field_db' => 'service_description'),
			array('title' => _('Time'), 'sort_field_db' => 'start_time'),
			array('title' => _('Contact'), 'sort_field_db' => 'contact_name'),
			array('title' => _('Notification command'), 'sort_field_db' => 'command_name'),
			array('title' => _('Information'), 'sort_field_db' => 'output')
		);

		$header = false;
		$i = 0;
		foreach ($header_link_fields as $field) {
			$header_links[$i]['title'] = $field['title'];
			$header_links[$i]['url_asc'] = Router::$controller.'/host/?type='.$type.'&sort_order='.nagstat::SORT_ASC.'&sort_field='.$field['sort_field_db'].'&amp;host_name='.$host_name.'&amp;service='.urlencode($service);
			$header_links[$i]['alt_asc'] = _('Sort by').' '._('last').' '.$field['title'].' ('._('ascending').')';
			$header_links[$i]['url_desc'] = Router::$controller.'/host/?type='.$type.'&sort_order='.nagstat::SORT_DESC.'&sort_field='.$field['sort_field_db'].'&amp;host_name='.$host_name.'&amp;service='.urlencode($service);
			$header_links[$i]['alt_desc'] = _('Sort by').' '.$field['title'].' ('._('descending').')';
			$i++;
		}

		$this->template->title = _('Reporting').' » '._('Contact Notifications');
		$this->template->content = $this->add_view('notifications/index');
		$this->template->content->header_links = $header_links;
		$this->template->content->data = $result;
		$this->template->content->noheader = $noheader;
		$this->template->content->host_name = $host_name;
		$this->template->content->service = $service;
		$this->template->content->query_type = $query_type;
		$this->template->content->pagination = isset($pagination) ? $pagination : false;
		$this->template->content->select_strings = $this->select_strings;
		$this->template->content->selected_val = $type;
		$this->template->content->date_format_str = nagstat::date_format();
	}
}
