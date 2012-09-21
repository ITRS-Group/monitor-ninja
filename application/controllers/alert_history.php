<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Alert History controller
 * This is just a special case of the recent alert view in the summary controller
 */
class Alert_history_Controller extends Summary_Controller
{
	public $type = 'alert_history';

	public function index($input = false) {
		url::redirect('alert_history/generate');
	}

	public function generate($input = false) {
		$this->setup_options_obj($input);
		if ($this->options['output_format'] == 'pdf') {
			return $this->generate_pdf($input);
		}

		$this->options['summary_type'] = self::RECENT_ALERTS;
		$this->xtra_js[] = $this->add_path('alert_history/js/alert_history.js');
		parent::generate();
		$this->template->content->report_options = $this->add_view('alert_history/options');
		$this->template->title = _('Alert history');
		$this->template->content->header->standard_header->title = _('Alert history');
		$this->template->content->content->pagination = new CountlessPagination(array('style' => 'digg-pageless'));
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

		$db = Database::instance();
		$db->query('DELETE FROM ninja_report_comments WHERE timestamp='.$db->escape($timestamp).' AND event_type = '.$db->escape($event_type).' AND host_name = '.$db->escape($host_name).' AND service_description = '.$db->escape($service));
		$db->query('INSERT INTO ninja_report_comments(timestamp, event_type, host_name, service_description, comment_timestamp, username, user_comment) VALUES ('.$db->escape($timestamp).', '.$db->escape($event_type).', '.$db->escape($host_name).', '.$db->escape($service).', UNIX_TIMESTAMP(), '.$db->escape($username).', '.$db->escape($comment).')');
		echo '<div class="content">'.$comment.'</div><div class="author">/'.$username.'</div>';
	}
}
