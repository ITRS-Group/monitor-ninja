<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Alert Summary controller
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
class Summary_Controller extends Base_reports_Controller
{
	public $type = 'summary';

	private $host_state_names = array();
	private $service_state_names = array();

	public function __construct()
	{
		parent::__construct();
		$this->host_state_names = array(
			Reports_Model::HOST_UP => _('UP'),
			Reports_Model::HOST_DOWN => _('DOWN'),
			Reports_Model::HOST_UNREACHABLE => _('UNREACHABLE')
		);
		$this->service_state_names = array(
			Reports_Model::SERVICE_OK => _('OK'),
			Reports_Model::SERVICE_WARNING => _('WARNING'),
			Reports_Model::SERVICE_CRITICAL => _('CRITICAL'),
			Reports_Model::SERVICE_UNKNOWN => _('UNKNOWN')
		);
	}

	/**
	 * Setup options for alert summary report
	 */
	public function index($input=false)
	{
		$this->setup_options_obj($input);
		$reports_model = new Summary_Reports_Model($this->options);

		# what scheduled reports are there?
		$scheduled_ids = array();
		$scheduled_periods = null;
		$scheduled_res = Scheduled_reports_Model::get_scheduled_reports($this->type);
		if ($scheduled_res && count($scheduled_res)!=0) {
			foreach ($scheduled_res as $sched_row) {
				$scheduled_ids[] = $sched_row->report_id;
				$scheduled_periods[$sched_row->report_id] = $sched_row->periodname;
			}
		}

		$this->template->disable_refresh = true;
		$this->template->content = $this->add_view('summary/setup');
		$this->template->content->report_options = $this->add_view('summary/options');
		$template = $this->template->content;

		if(isset($_SESSION['report_err_msg'])) {
			$template->error_msg = $_SESSION['report_err_msg'];
			unset($_SESSION['report_err_msg']);
		}

		$this->template->css[] = $this->add_path('reports/css/datePicker.css');

		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _scheduled_label = '"._('Scheduled')."';\n";

		$this->template->toolbar = new Toolbar_Controller(_('Summary report'));


		$this->template->js_strings = $this->js_strings;

		$template->scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->options['report_id']);

		$template->type = $this->type;
		$template->scheduled_ids = $scheduled_ids;
		$template->scheduled_periods = $scheduled_periods;

		$template->available_schedule_periods = Scheduled_reports_Model::get_available_report_periods();
		$template->saved_reports = $this->options->get_all_saved();

		$this->template->title = _("Reporting » Alert summary » Setup");
	}

	/**
	 * Generates an alert summary report
	 */
	public function generate($input=false)
	{
		$this->setup_options_obj($input);

		$report_members = $this->options->get_report_members();
		if (empty($report_members)) {
			$_SESSION['report_err_msg'] = _("No objects could be found in your selected groups to base the report on");
			return url::redirect(Router::$controller.'/index');
		}

		$reports_model = new Summary_Reports_Model($this->options);

		$result = false;
		switch ($this->options['summary_type']) {
		 case Summary_options::TOP_ALERT_PRODUCERS:
			$result = $reports_model->top_alert_producers();
			break;

		 case Summary_options::RECENT_ALERTS:
			$result = $reports_model->recent_alerts();
			break;

		 case Summary_options::ALERT_TOTALS:
			$result = $reports_model->alert_totals();
			break;

		 default:
			echo Kohana::debug("Case fallthrough");
			exit(1);
		}


		if ($this->options['output_format'] == 'csv') {
			csv::csv_http_headers($this->type, $this->options);
			$this->template = $this->add_view('summary/csv');
			$this->template->options = $this->options;
			$this->template->summary_type = $this->options['summary_type'];
			$this->template->result = $result;
			$this->template->date_format = date::date_format();
			$this->template->host_state_names = $this->host_state_names;
			$this->template->service_state_names = $this->service_state_names;

			return;
		}

		$this->template->disable_refresh = true;
		$this->template->css[] = $this->add_path('reports/css/datePicker.css');

		$views = array(
			Summary_options::TOP_ALERT_PRODUCERS => 'toplist',
			Summary_options::RECENT_ALERTS => 'latest',
			Summary_options::ALERT_TOTALS => 'alert_totals',
		);

		$this->template->content = $this->add_view('reports/index');
		$this->template->content->header = $this->add_view('summary/header');
		$this->template->content->header->standard_header = $this->add_view('reports/header');
		$header = $this->template->content->header->standard_header;
		$this->template->content->report_options = $this->add_view('summary/options');
		$header->report_time_formatted = $this->format_report_time(date::date_format());
		$this->template->content->content =
			$this->add_view("summary/" . $views[$this->options['summary_type']]);

		$content = $this->template->content->content;

		$content->host_state_names = $this->host_state_names;
		$content->service_state_names = $this->service_state_names;

		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _scheduled_label = '"._('Scheduled')."';\n";
		$this->template->js_strings = $this->js_strings;

		$content->result = $result;
		$this->template->title = _("Reporting » Alert summary » Report");
		$header->title = $this->options->get_value('summary_type');

		$scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->options['report_id']);
		if($scheduled_info) {
			$schedule_id = $this->input->get('schedule_id', null);
			if($schedule_id) {
				$le_schedule = current(array_filter($scheduled_info, function($item) use ($schedule_id) {
					return $item['id'] == $schedule_id && $item['attach_description'] && $item['description'];
				}));
				if($le_schedule) {
					$header->description = $this->options['description'] ? $this->options['description']."\n".$le_schedule['description'] : $le_schedule['description'];
				}
			}
		}

		if ($this->options['output_format'] == 'pdf') {
			return $this->generate_pdf();
		}
		$this->generate_toolbar();
	}

	public function edit_settings($input = false){
		$this->setup_options_obj($input);
		$this->template->content = $this->add_view('reports/edit_settings');
		$template = $this->template->content;
		$template->report_options = $this->add_view('summary/options');
	}
}
