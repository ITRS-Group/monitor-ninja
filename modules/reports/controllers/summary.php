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
	*	Setup options for alert summary report
	*/
	public function index($input=false)
	{
		$this->setup_options_obj($input);
		$reports_model = new Summary_Reports_Model($this->options);

		# check if we have all required parts installed
		if (!$reports_model->_self_check()) {
			return url::redirect('reports/invalid_setup');
		}

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

		# get all saved reports for user
		$saved_reports = Saved_reports_Model::get_saved_reports($this->type);

		$old_config_names = Saved_reports_Model::get_all_report_names($this->type);
		$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", array_map('addslashes', $old_config_names))."');";

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('summary/js/summary.js');

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->template->css_header->css = $this->xtra_css;

		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _scheduled_label = '"._('Scheduled')."';\n";
		$this->inline_js .= "var invalid_report_names = ".$old_config_names_js .";\n";

		if ($this->options['report_id']) {
			$this->js_strings .= "var _report_data = " . $this->options->as_json() . "\n";
		}
		else if (!$this->options['standardreport']) {
			$this->inline_js .= "set_selection(document.getElementsByName('report_type').item(0).value);\n";
		}





		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;

		$template->type = $this->type;
		$template->old_config_names_js = $old_config_names_js;
		$template->old_config_names = $old_config_names;
		$template->scheduled_ids = $scheduled_ids;
		$template->scheduled_periods = $scheduled_periods;

		$template->available_schedule_periods = Scheduled_reports_Model::get_available_report_periods();

		$template->saved_reports = $saved_reports;

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
			$_SESSION['report_err_msg'] = "No objects could be found in your selected groups to base the report on";
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
			$this->template->date_format = nagstat::date_format();
			$this->template->host_state_names = $this->host_state_names;
			$this->template->service_state_names = $this->service_state_names;

			return;
		}

		$this->template->disable_refresh = true;
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('summary/js/summary.js');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->template->css_header->css = $this->xtra_css;

		if ($this->type == 'summary') {
			$old_config_names = Saved_reports_Model::get_all_report_names($this->type);
			$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", array_map('addslashes', $old_config_names))."');";
			$this->inline_js .= "var invalid_report_names = ".$old_config_names_js .";\n";
		}

		if($this->options['report_period'] && $this->options['report_period'] != 'custom')
			$report_time_formatted  = $this->options->get_value('report_period');
		else
			$report_time_formatted  = sprintf(_("%s to %s"), date(nagstat::date_format(), $this->options['start_time']), date(nagstat::date_format(), $this->options['end_time']));

		if($this->options['rpttimeperiod'] != '')
			$report_time_formatted .= " - {$this->options['rpttimeperiod']}";

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
		$header->report_time_formatted = $report_time_formatted;
		$this->template->content->content =
			$this->add_view("summary/" . $views[$this->options['summary_type']]);

		$content = $this->template->content->content;

		$content->host_state_names = $this->host_state_names;
		$content->service_state_names = $this->service_state_names;

		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _scheduled_label = '"._('Scheduled')."';\n";
		if (!$this->options['standardreport']) {
			$this->js_strings .= "var _report_data = " . $this->options->as_json() . "\n";
		}
		else if (!$this->options['standardreport']) {
			$this->inline_js .= "set_selection(document.getElementsByName('report_type').item(0).value);\n";
		}
		$this->template->js_strings = $this->js_strings;
		$this->template->inline_js = $this->inline_js;

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
	}

	/**
	* Translated helptexts for this controller
	*/
	public static function _helptexts($id)
	{
		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array(
			"standardreport" => _("Choose the type of report you want from the list of predefined summary reports."),
			"summary_type" => _('The format of the summary. &quot;Most recent alerts&quot; simply lists alerts, &quot;Top alert producers&quot; orders host and/or services by the one that has notified the most recently, and &quot;Alert totals&quot; sums up the number of alerts per selected object'),
			"summary_items" => _("Enter the number of items you wish the report to contain."),
			"alert_types" => _("Select whether to include only host alerts, service alerts, or both"),
			"state_types" => _("Whether to include only hard alerts, soft alerts, or both"),
			"host_states" => _("Restrict which state(s) you're interested in hosts entering"),
			"service_states" => _("Restrict which state(s) you're interested in services entering"),
			"include_long_output" => _("In views that displays individual alerts, include the full check output, instead of only the first line"),
		);
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else
			parent::_helptexts($id);
	}
}
