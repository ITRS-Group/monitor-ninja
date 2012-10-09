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
		url::redirect('alert_history/generate');
	}

	public function generate($input = false)
	{
		$this->setup_options_obj($input);
		if ($this->options['output_format'] == 'pdf') {
			return $this->generate_pdf($input);
		}

		$this->options['summary_type'] = self::RECENT_ALERTS;
		$this->xtra_js[] = $this->add_path('alert_history/js/alert_history.js');
		parent::generate();
		$this->template->content->report_options = $this->add_view('alert_history/options');
		$this->template->title = _('Alert history');
		$this->template->content->header->standard_header->skip_save = true;
		$this->template->content->header->standard_header->title = _('Alert history');
		if ($this->options['summary_items']) {
			$this->template->content->content->pagination = new CountlessPagination(array('style' => 'digg-pageless'));
		}
	}
	
	public function add_comment()
	{
		$this->auto_render = false;
		$timestamp = $this->input->post('timestamp');
		$event_type = $this->input->post('event_type');
		$host_name = $this->input->post('host_name');
		$service = $this->input->post('service_description');
		$comment = $this->input->post('comment');
		$username = Auth::instance()->get_user()->username;

		if (Reports_Model::add_event_comment($timestamp, $event_type, $host_name, $service, $comment, $username))
			echo '<div class="content">'.$comment.'</div><div class="author">/'.$username.'</div>';
	}
}
