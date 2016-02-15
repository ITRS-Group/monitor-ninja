<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Alert History controller
 * This is just a special case of the recent alert view in the summary controller
 */
class Alert_history_Controller extends Summary_Controller
{
	public $type = 'alert_history';

	public function index($input = false)
	{
		if (isset($_SESSION['report_err_msg'])) {
			$this->template->content = _("<h1>You're not authorized to see any hosts, so I can't show you a history of their alerts.</h1>\n<p>But then, you <i>were</i> allowed to log in, so I bet something is broken. Please contact <a href=\"mailto:support@op5.com\">support@op5.com</a> with any information you have.</p>");
			unset($_SESSION['report_err_msg']);
		}
		else {
			return url::redirect('alert_history/generate');
		}
	}

	public function generate($input = false)
	{
		$this->setup_options_obj($input);

		unset($this->options['page']);
		$pagination = new CountlessPagination(array('items_per_page' => $this->options['summary_items'], 'extra_params' => $this->options->as_keyval_string()));
		$this->options['page'] = $pagination->current_page;

		$this->options['summary_type'] = Summary_options::RECENT_ALERTS;
		$this->template->js[] = 'modules/reports/views/alert_history/js/alert_history.js';
		$real_output_format = $this->options['output_format'];
		if ($this->options['output_format'] === 'pdf')
			$this->options['output_format'] = 'html';
		parent::generate();
		if ($this->options['output_format'] !== 'csv') {
			$this->template->content->report_options = $this->add_view('alert_history/options');
			$this->template->title = _('Alert history');
			$this->template->content->header->standard_header->title = _('Alert history');
			$pagination->hide_next = false;
			if(!$this->template->content->content->result || count($this->template->content->content->result) < $this->options['summary_items']) {
				$pagination->hide_next = true;
			}
			$this->template->content->content->pagination = $pagination;
		}
		if ($real_output_format == 'pdf') {
			return $this->generate_pdf();
		}
	}

	/**
	 * End-point for XHR requests, that creates an event comment which will be
	 * displayed with the event itself in reports.
	 */
	public function add_comment()
	{
		$this->auto_render = false;
		$timestamp = $this->input->post('timestamp');
		$event_type = $this->input->post('event_type');
		$host_name = $this->input->post('host_name');
		$service = $this->input->post('service_description');
		$comment = $this->input->post('comment');
		$username = Auth::instance()->get_user()->get_username();

		if (Summary_Reports_Model::add_event_comment($timestamp, $event_type, $host_name, $service, $comment, $username))
			echo '<div class="content">'.html::specialchars($comment).'</div><div class="author">/'.$username.'</div>';
	}

	public static function _helptexts($id)
	{
		$helptexts = array(
			'include_downtime' => _('If checked, downtime events from the included hosts/services and timerange will be included in the report.'),
			'include_flapping' => _('If checked, flapping start and stop events from the included hosts and services will be included in the report.'),
			'include_process' => _('If checked, messages about Monitor nodes starting or stopping during the timerange will be included in the report'),
			'filter_output' => _('Only include alerts whose check output contains the provided string. This can be useful for focusing on one specific error.'),
			'oldest_first' => _('This inverts the ordering, so it goes from earliest to most recent.'),
		);

		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else {
			echo sprintf(_("This helptext ('%s') is not translated yet"), $id);
		}
	}
}
