<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Notifications controller
 * Requires authentication
 *
 * @package	NINJA
 * @author	op5 AB
 * @license	GPL
 * @copyright 2009 op5 AB
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

		$t = $this->translate;
		$this->select_strings = array(
			0  => $t->_('All notifications'),
			1  => $t->_('All service notifications'),
			2  => $t->_('All host notifications'),
			3  => $t->_('Service acknowledgements'),
			4  => $t->_('Service warning'),
			5  => $t->_('Service uknown'),
			6  => $t->_('Service critical'),
			7  => $t->_('Service recovery'),
			8  => $t->_('Service flapping'),
			9  => $t->_('Host acknowledgements'),
			10 => $t->_('Host down'),
			11 => $t->_('Host unreachable'),
			12 => $t->_('Host recoverys'),
			13 => $t->_('Host flapping'),
		);

	}

	/**
	 * Default controller method
	 */
	public function index($sort_field='start_time', $sort_order='DESC', $type = false, $query_type = nagstat::FIND_HOST)
	{
		$type = urldecode($this->input->get('type', $type));
		$noheader = urldecode($this->input->get('noheader', false));

		$items_per_page = urldecode($this->input->get('items_per_page', Kohana::config('pagination.default.items_per_page'))); # @@@FIXME: should be configurable from GUI
		$note_model = new Notifications_Model();
		$note_model->sort_order = urldecode($this->input->get('sort_order', $sort_order));
		$note_model->sort_field = urldecode($this->input->get('sort_field', $sort_field));

		$this->xtra_js[] = $this->add_path('notifications/js/notifications');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;

		$t = $this->translate;

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

		$this->template->title = $t->_('Reporting').' » '.$t->_('Contact Notifications');
		$this->template->content = $this->add_view('notifications/index');
		$this->template->content->data = $result;
		$this->template->content->noheader = $noheader;
		$this->template->content->query_type = $query_type;
		$this->template->content->type = $type;
		$this->template->content->pagination = isset($pagination) ? $pagination : false;
		$this->template->content->select_strings = $this->select_strings;
		$this->template->content->selected_val = $type;
	}

	public function host($host_name = false, $service = false, $sort_field='host_name', $sort_order='ASC', $query_type = nagstat::FIND_HOST)
	{
		$type = urldecode($this->input->get('type', false));
		$noheader = urldecode($this->input->get('noheader', false));
		$items_per_page = urldecode($this->input->get('items_per_page', Kohana::config('pagination.default.items_per_page'))); # @@@FIXME: should be configurable from GUI
		$note_model = new Notifications_Model($items_per_page, true, true);
		$note_model->sort_order = urldecode($this->input->get('sort_order', $sort_order));
		$note_model->sort_field = urldecode($this->input->get('sort_field', $sort_field));

		$this->xtra_js[] = $this->add_path('notifications/js/notifications');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;

		$t = $this->translate;

		$service = urldecode($this->input->get('service', $service));
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
			$sql .= " service_description = '".$service."'";

		$note_model->where = $sql;
		$pagination = new Pagination(
			array(
				'total_items'=> $note_model->count_notifications(),
				'items_per_page' => $items_per_page
			)
		);

		$offset = $pagination->sql_offset;
		$result = $note_model->show_notifications($items_per_page, $offset, false);

		$this->template->title = $t->_('Reporting').' » '.$t->_('Contact Notifications');
		$this->template->content = $this->add_view('notifications/index');
		$this->template->content->data = $result;
		$this->template->content->noheader = $noheader;
		$this->template->content->host_name = $host_name;
		$this->template->content->query_type = $query_type;
		$this->template->content->pagination = isset($pagination) ? $pagination : false;
		$this->template->content->select_strings = $this->select_strings;
		$this->template->content->selected_val = $type;
	}
}
