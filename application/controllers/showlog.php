<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Showlog controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Showlog_Controller extends Authenticated_Controller
{
	private $show;
	private $options = array();

	public function __construct()
	{
		parent::__construct();

		$this->get_options();
	}

	protected function get_options()
	{
		$this->options = $this->input->get();
		if (empty($this->options))
			$this->options = $this->input->post();

		if (isset($this->options['first']) && !empty($this->options['first'])) {
			$this->options['first'] = strtotime($this->options['first']);
		}
		if (isset($this->options['last']) && !empty($this->options['last'])) {
			$this->options['last'] = strtotime($this->options['last']);
		}
		if (!empty($this->options) && !empty($this->options['have_options'])) {
			if (!isset($this->options['host_state_options'])) {
				$this->options['host_state_options'] = array();
			}
			if (!isset($this->options['service_state_options'])) {
				$this->options['service_state_options'] = array();
			}
			return;
		}

		# set default if no options are found
		$this->options = array
			(
			 'state_type' => array('soft' => true, 'hard' => true),
			 'host_state_options' => array('r' => true, 'd' => true, 'u' => true),
			 'service_state_options' => array('r' => true, 'w' => true, 'c' => true, 'u' => true),
			 'hide_initial' => true
			 );

		$auth = Nagios_auth_Model::instance();
		if (!$auth->authorized_for_system_information) {
			$this->options['hide_process'] = 1;
			$this->options['hide_commands'] = 1;
		}
	}

	public function _show_log_entries()
	{
		$user = user::session('username');
		if (!empty($user))
			$this->options['user'] = $user;
		showlog::show_log_entries($this->options);
	}

	public function basic_setup()
	{
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('showlog/js/showlog.js');

		$this->template->js_header->js = $this->xtra_js;

		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = $this->add_path('showlog/css/showlog.css');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox.css';
		$this->template->css_header->css = $this->xtra_css;
		$this->js_strings .= reports::js_strings();
		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;

		$host_state_options = array
			(_('Host down') => 'd',
			 _('Host unreachable') => 'u',
			 _('Host recovery') => 'r');
		$service_state_options = array
			(_('Service warning') => 'w',
			 _('Service unknown') => 'u',
			 _('Service critical') => 'c',
			 _('Service recovery') => 'r');

		$this->template->content->host_state_options = $host_state_options;
		$this->template->content->service_state_options = $service_state_options;
	}

	public function alert_history($obj_name = false)
	{
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.default.items_per_page', '*'));
		$this->template->content = $this->add_view('showlog/alertlog');
		$this->basic_setup();
		$this->template->title = _("Reporting » Alert history");

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = $this->add_path('showlog/js/alertlog.js');
		$this->xtra_js[] = 'application/media/js/jquery.tablesorter.min.js';
		$filter_string = _('Enter text to filter');
		$this->js_strings .= "var _filter_label = '".$filter_string."';";
		$this->template->js_strings = $this->js_strings;
		$this->template->js_header->js = $this->xtra_js;

		$service = $this->input->get('service', $this->input->post('service', false));
		$hostgroup = $this->input->get('hostgroup', $this->input->post('hostgroup', false));
		$servicegroup = $this->input->get('servicegroup', $this->input->post('servicegroup', false));
		$host = $this->input->get('host', $this->input->post('host', $obj_name));
		if (is_array($host)) {
			foreach ($host as $k => $v) {
				if (empty($v))
					unset($host[$k]);
			}
		}
		if (is_array($service)) {
			foreach ($service as $k => $v) {
				if (empty($v))
					unset($host[$k]);
			}
		}
		if ($service && !is_array($service) && $host && !is_array($host)) {
			$service = array($host.';'.$service);
			$host = false;
		}
		else if ($host && !is_array($host) && strstr($host, ';') !== false) {
			$service = array($host);
			$host = false;
		}
		else if (!is_array($host) && $host) {
			$host = array($host);
		}
		else if (!is_array($service) && $service) {
			$service = array($service);
		}

		$auth = Nagios_auth_Model::instance();
		$is_authorized = false;
		if ($auth->authorized_for_system_information) {
			$is_authorized = true;
		}

		$log_model = new Alertlog_Model();
		$this->options = array_merge($this->options, array(
			'hosts' => $host ? $host : false,
			'services' => $service ? $service : false,
			'hostgroups' => $hostgroup ? array($hostgroup) : false,
			'servicegroups' => $servicegroup ? array($servicegroup) : false
		));
		$cnt = $log_model->get_log_entries($this->options, false, false, true);

		$pagination = new Pagination(
			array(
				'total_items' => $cnt,
				'items_per_page' => $items_per_page
			));
		$offset = $pagination->sql_offset;
		$entries = $log_model->get_log_entries($this->options, $items_per_page, $offset);
		$this->template->content->entries = $entries;
		$this->template->content->pagination = $pagination;
		$this->template->content->total_entries = $cnt;
		$this->template->content->is_authorized = $is_authorized;
		$this->template->content->options = $this->options;
		$this->template->content->filter_string = $filter_string;
	}

	public function showlog()
	{
		$this->template->content = $this->add_view('showlog/showlog');
		$this->basic_setup();
		$this->template->title = _("Reporting » Event Log");

		$auth = Nagios_auth_Model::instance();
		$is_authorized = false;
		if ($auth->authorized_for_system_information) {
			$is_authorized = true;
		}

		$this->template->content->is_authorized = $is_authorized;
		$this->template->content->options = $this->options;
	}
}
