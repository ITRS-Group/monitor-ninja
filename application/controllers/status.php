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
	public function host($host='all', $hoststatustypes=nagstat::HOST_UP, $sort_order='ASC', $sort_field='host_name', $show_services=false)
	{
		$host = link::decode($host);
		$this->template->content = $this->add_view('status/host');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		widget::add('status_totals', array('index', $this->current), $this);
		$this->xtra_css = array_merge($this->xtra_css, array($this->add_path('/css/common.css')));
		$this->template->content->widgets = $this->widgets;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;

		$conv_status = $this->convert_status_value($hoststatustypes);

		# set sort images, used in header_links() below
		$this->img_sort_up = $this->img_path('images/up.gif');
		$this->img_sort_down = $this->img_path('images/down.gif');

		# assign specific header fields and values for current method
		$header_link_fields = array(
			array('title' => $this->translate->_('Host'), 'sort_field_db' => 'host_name', 'sort_field_str' => 'host name'),
			array('title' => $this->translate->_('Status'), 'sort_field_db' => 'current_state', 'sort_field_str' => 'host status'),
			array('title' => $this->translate->_('Last Check'), 'sort_field_db' => 'last_check', 'sort_field_str' => 'last check time'),
			array('title' => $this->translate->_('Duration'), 'sort_field_db' => 'duration', 'sort_field_str' => 'duration'),
			array('title' => $this->translate->_('Status Information'))
		);

		# build header links array
		foreach ($header_link_fields as $fields) {
			if (sizeof($fields) > 1) {
				$header_links[] = $this->header_links('host', $host, $fields['title'], Router::$method, $fields['sort_field_db'], $fields['sort_field_str']);
			} else {
				$header_links[] = $this->header_links('host', $host, $fields['title']);
			}
		}

		$this->template->content->header_links = $header_links;

		$shown = $host == 'all' ? $this->translate->_('All Hosts') : $this->translate->_('Host')." '".$host."'";
		$sub_title = $this->translate->_('Host Status Details For').' '.$shown;
		$this->template->content->sub_title = $sub_title;

		$result = $this->current->host_status_subgroup_names($host, $show_services, $conv_status, $sort_field, $sort_order);
		$this->template->content->result = $result;
		$this->template->content->logos_path = $this->logos_path;
	}

	public function service($host='all', $servicestatustypes=false, $hoststatustypes=false, $serviceprops=false)
	{
		$host = link::decode($host);
		echo "servicestatustypes: ".$servicestatustypes."<br />";
		$conv_status = $this->convert_status_value($servicestatustypes, 'service');
		echo 'Conv status:'.$conv_status."<br />";
	}

	/**
	 * Convert Nagios status level to current_state
	 * stored in database.
	 *
	 * @param	int $value
	 * @param	str $type host/service
	 * @return	int
	 */
	private function convert_status_value($value=false,$type='host')
	{
		if ($value === false) {
			return false;
		}
		$conv_status = false;
		if ($type == 'host') {
			if ($value > 8) {
				return false;
			}
			$conv_status = $value == 1 ? -1 : ($value >> 2);
		} elseif ($type == 'service') {
			$service_states = array(2 => 0, 4 => 1, 8 => 3, 16 => 2, 1 => -1);
			if (array_key_exists($value, $service_states)) {
				$conv_status = $service_states[$value];
			}
		} else {
			return false;
		}
		return $conv_status;
	}

	private function header_links(
			$type='host',
			$filter_object='all',
			$title=false,
			$method=false,
			$sort_field_db=false,
			$sort_field_str=false)
	{

		$type = trim($type);
		$filter_object = trim($filter_object);
		$title = trim($title);
		if (empty($type) || empty($title))  {
			return false;
		}
		$header = false;
		switch ($type) {
			case 'host':
				$header['title'] = $title;
				if (!empty($method) &&!empty($filter_object) && !empty($sort_field_db)) {
					$header['url_asc'] = Router::$controller.'/'.$method.'/'.$filter_object.'/'.nagstat::HOST_UP.'/'.nagstat::SORT_ASC.'/'.$sort_field_db;
					$header['img_asc'] = $this->img_sort_up;
					$header['alt_asc'] = 'Sort by last '.$sort_field_str.' (ascending)';
					$header['img_asc'] = $this->img_sort_up;
					$header['alt_asc'] = 'Sort by last '.$sort_field_str.' (ascending)';
					$header['url_desc'] = Router::$controller.'/'.$method.'/'.$filter_object.'/'.nagstat::HOST_UP.'/'.nagstat::SORT_DESC.'/'.$sort_field_db;
					$header['img_desc'] = $this->img_sort_down;
					$header['alt_desc'] = 'Sort by '.$sort_field_str.' (descending)';
				}
				break;
			case 'service':

				break;
		}
		return $header;
	}
}
