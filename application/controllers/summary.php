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
	const RECENT_ALERTS = 1;
	const ALERT_TOTALS = 2;
	const TOP_ALERT_PRODUCERS = 3;

	public $type = 'summary';
	public $reports_model = false;

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
		$this->reports_model = new Reports_Model($this->options);

		# check if we have all required parts installed
		if (!$this->reports_model->_self_check()) {
			url::redirect('reports/invalid_setup');
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

		# get all saved reports for user
		$saved_reports = Saved_reports_Model::get_saved_reports($this->type);
		$this->js_strings .= "var report_id = ".(int)$this->options['report_id'].";\n";

		$old_config_names = Saved_reports_Model::get_all_report_names($this->type);
		$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", $old_config_names)."');";

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = 'application/media/js/move_options.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';
		$this->xtra_js[] = $this->add_path('summary/js/summary.js');

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox.css';
		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = $this->add_path('css/default/reports.css');
		$this->template->css_header->css = $this->xtra_css;
		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _scheduled_label = '"._('Scheduled')."';\n";
		$this->js_strings .= "var _reports_edit_information = '"._('Double click to edit')."';\n";
		$this->js_strings .= "var _reports_success = '"._('Success')."';\n";
		$this->js_strings .= "var _reports_error = '"._('Error')."';\n";
		$this->js_strings .= "var _reports_schedule_error = '"._('An error occurred when saving scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_send_error = '"._('An error occurred when trying to send the scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_update_ok = '"._('Your schedule has been successfully updated')."';\n";
		$this->js_strings .= "var _reports_schedule_send_ok = '"._('Your report was successfully sent')."';\n";
		$this->js_strings .= "var _reports_schedule_create_ok = '"._('Your schedule has been successfully created')."';\n";
		$this->js_strings .= "var _reports_fatal_err_str = '"._('It is not possible to schedule this report since some vital information is missing.')."';\n";

		$template->type = $this->type;
		$template->old_config_names_js = $old_config_names_js;
		$template->old_config_names = $old_config_names;
		$template->scheduled_ids = $scheduled_ids;
		$template->scheduled_periods = $scheduled_periods;

		$template->available_schedule_periods = Scheduled_reports_Model::get_available_report_periods();

		$template->saved_reports = $saved_reports;

		$this->inline_js .= "invalid_report_names = ".$old_config_names_js .";\n";

		if ($this->options['report_id']!==false) {
			if ($this->options['standardreport'])
				$this->inline_js .= "$('#report_mode_custom').attr('checked', false);\n";
			if ($this->options['report_type'])
				$this->inline_js .= "set_selection('".$this->options['report_type']."');\n";
			$this->inline_js .= "expand_and_populate(" . $this->options->as_json() . ");\n";
		}

		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
		$this->template->title = _("Reporting » Alert summary » Setup");
	}

	/**
	 * Test a massive amount of queries. For debugging only
	 */
	public function test_queries()
	{
		$this->setup_options_obj($input);
		$rpt = new Reports_Model($this->options);
		$rpt->set_option('start_time', 0);
		$rpt->set_option('end_time', time());
		$result = $rpt->test_summary_queries();
		echo "<pre>\n";
		$cnt = count($result);
		echo $cnt . " total different queries\n";
		$total_rows = 0.0;
		foreach ($result as $query => $ary) {
			echo $query . "\n";
			print_r($ary);
			$total_rows += $ary['rows'];
		}
		$avg_rows = $total_rows / $cnt;
		echo "Average row-count: $avg_rows\n";
		echo "</pre>\n";
		die;
	}

	/**
	 * Generates an alert summary report
	 */
	public function generate($input=false)
	{
		$this->setup_options_obj($input);
		if ($this->options['output_format'] == 'pdf') {
			return $this->generate_pdf($input);
		}

		$this->reports_model = new Reports_Model($this->options);

		$result = false;
		switch ($this->options['summary_type']) {
		 case self::TOP_ALERT_PRODUCERS:
			$result = $this->reports_model->top_alert_producers();
			break;

		 case self::RECENT_ALERTS:
			$result = $this->reports_model->recent_alerts();
			break;

		 case self::ALERT_TOTALS:
			$result = $this->reports_model->alert_totals();
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
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';
		$this->xtra_js[] = $this->add_path('summary/js/summary.js');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox.css';
		$this->xtra_css[] = $this->add_path('css/default/reports.css');
		$this->template->css_header->css = $this->xtra_css;

		$old_config_names = Saved_reports_Model::get_all_report_names($this->type);
		$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", $old_config_names)."');";

		$this->inline_js .= "var invalid_report_names = ".$old_config_names_js .";\n";

		# get all saved reports for user
		$saved_reports = Saved_reports_Model::get_saved_reports($this->type);

		$this->js_strings .= "var report_id = ".(int)$this->options['report_id'].";\n";

		if($this->options['report_period'] && $this->options['report_period'] != 'custom')
			$report_time_formatted  = $this->options->get_value('report_period');
		else
			$report_time_formatted  = sprintf(_("%s to %s"), date(nagstat::date_format(), $this->options['start_time']), date(nagstat::date_format(), $this->options['end_time']));

		if($this->options['rpttimeperiod'] != '')
			$report_time_formatted .= " - {$this->options['rpttimeperiod']}";

		$views = array(
			self::TOP_ALERT_PRODUCERS => 'toplist',
			self::RECENT_ALERTS => 'latest',
			self::ALERT_TOTALS => 'alert_totals',
		);

		$this->template->set_global('type', $this->type);

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
		$this->js_strings .= "var _reports_edit_information = '"._('Double click to edit')."';\n";
		$this->js_strings .= "var _reports_success = '"._('Success')."';\n";
		$this->js_strings .= "var _reports_error = '"._('Error')."';\n";
		$this->js_strings .= "var _reports_schedule_error = '"._('An error occurred when saving scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_send_error = '"._('An error occurred when trying to send the scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_update_ok = '"._('Your schedule has been successfully updated')."';\n";
		$this->js_strings .= "var _reports_schedule_send_ok = '"._('Your report was successfully sent')."';\n";
		$this->js_strings .= "var _reports_schedule_create_ok = '"._('Your schedule has been successfully created')."';\n";
		$this->js_strings .= "var _reports_fatal_err_str = '"._('It is not possible to schedule this report since some vital information is missing.')."';\n";
		$this->template->js_strings = $this->js_strings;

		$content->result = $result;
		$this->template->title = _("Reporting » Alert summary » Report");
		$header->title = $this->options->get_value('summary_type');
	}

	/**
	*
	*
	*/
	public function _populate_services()
	{
		$services = false;

		if (!empty($this->options['service_description'])) {
			$services = $this->options['service_description'];
		}
		else if (!empty($this->options['host_name'])) {
			foreach ($this->options['host_name'] as $host_name) {
				$service_res = Host_Model::get_services($host_name);
				if ($service_res !== false && count($service_res)) {
					foreach ($service_res as $svc) {
						$services[] = $host_name.';'.$svc->service_description;
					}
				}
			}
		}
		else if (!empty($this->options['hostgroup'])) {
			foreach ($this->options['hostgroup'] as $group) {
				$hg = new Hostgroup_Model();
				$hg_res = $hg->get_hosts_for_group($group);
				if ($hg_res !== false && count($hg_res)) {
					foreach ($hg_res as $row) {
						$service_res = Host_Model::get_services($row->host_name);
						if ($service_res !== false && count($service_res)) {
							foreach ($service_res as $svc) {
								$services[] = $row->host_name.';'.$svc->service_description;
							}
						}
					}
				}
			}
		}
		elseif (!empty($this->options['servicegroup'])) {
			foreach ($this->options['servicegroup'] as $group) {
				$sg = new Servicegroup_Model();
				$sg_res = $sg->get_services_for_group($group);
				if ($sg_res !== false && count($sg_res)) {
					foreach ($sg_res as $row) {
						$service_res = Host_Model::get_services($row->host_name);
						if ($service_res !== false && count($service_res)) {
							foreach ($service_res as $svc) {
								$services[] = $row->host_name.';'.$svc->service_description;
							}
						}
					}
				}
			}
		}

		return $services;
	}

	/**
	* Translated helptexts for this controller
	*/
	public static function _helptexts($id)
	{
		$nagios_etc_path = Kohana::config('config.nagios_etc_path');
		$nagios_etc_path = $nagios_etc_path !== false ? $nagios_etc_path : Kohana::config('config.nagios_base_path').'/etc';

		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array();
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else
			echo sprintf(_("This helptext ('%s') is yet not translated"), $id);
	}
}
