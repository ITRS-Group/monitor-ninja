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
class Summary_Controller extends Authenticated_Controller
{
	const RECENT_ALERTS = 1;
	const ALERT_TOTALS = 2;
	const TOP_ALERT_PRODUCERS = 3;
	const ALERT_TOTALS_HG = 4;
	const ALERT_TOTALS_HOST = 5;
	const ALERT_TOTALS_SERVICE = 6;
	const ALERT_TOTALS_SG = 7;

	public $reports_model = false;
	private $abbr_month_names = false;
	private $month_names = false;
	private $day_names = false;
	private $abbr_day_names = false;
	private $first_day_of_week = 1;
	private $report_id = false;
	public $create_pdf = false;
	public $pdf_data = false;
	public $mashing = false;
	public $template_prefix = false;
	private $pdf_filename = false;
	private $pdf_recipients = false; # when sending reports by email
	private $pdf_savepath = false;	# when saving pdf to a path
	private $schedule_id = false;
	private $type = 'summary';


	public function __construct($mashing=false, $obj=false)
	{
		parent::__construct();
		$this->mashing = $mashing;
		if (!empty($obj) && is_object($obj)) {
			$this->reports_model = $obj;
		} else {
			$this->reports_model = new Reports_Model();
		}

		$this->abbr_month_names = array(
			$this->translate->_('Jan'),
			$this->translate->_('Feb'),
			$this->translate->_('Mar'),
			$this->translate->_('Apr'),
			$this->translate->_('May'),
			$this->translate->_('Jun'),
			$this->translate->_('Jul'),
			$this->translate->_('Aug'),
			$this->translate->_('Sep'),
			$this->translate->_('Oct'),
			$this->translate->_('Nov'),
			$this->translate->_('Dec')
		);

		$this->month_names = array(
			$this->translate->_('January'),
			$this->translate->_('February'),
			$this->translate->_('March'),
			$this->translate->_('April'),
			$this->translate->_('May'),
			$this->translate->_('June'),
			$this->translate->_('July'),
			$this->translate->_('August'),
			$this->translate->_('September'),
			$this->translate->_('October'),
			$this->translate->_('November'),
			$this->translate->_('December')
		);

		$this->abbr_day_names = array(
			$this->translate->_('Sun'),
			$this->translate->_('Mon'),
			$this->translate->_('Tue'),
			$this->translate->_('Wed'),
			$this->translate->_('Thu'),
			$this->translate->_('Fri'),
			$this->translate->_('Sat')
		);

		$this->day_names = array(
			$this->translate->_('Sunday'),
			$this->translate->_('Monday'),
			$this->translate->_('Tuesday'),
			$this->translate->_('Wednesday'),
			$this->translate->_('Thursday'),
			$this->translate->_('Friday'),
			$this->translate->_('Saturday')
		);
	}

	/**
	*	Setup options for alert summary report
	*/
	public function index()
	{
		# check if we have all required parts installed
		if (!$this->reports_model->_self_check()) {
			url::redirect('reports/invalid_setup');
		}

		if ($this->mashing) {
			$this->auto_render=false;
		}

		# delete report?
		$del_id = arr::search($_REQUEST, 'del_id', false);
		$del_ok = $del_result = $del_msg = null;
		if (arr::search($_REQUEST, 'del_report', false) !== false && $del_id !== false) {
			$del_ok = Saved_reports_Model::delete_report($this->type, $del_id);
			if ($del_ok != '') {
				$del_msg = $this->translate->_('Report was deleted successfully.');
				$del_result = 'ok';
			} else {
				$del_msg = $this->translate->_('An error occurred while trying to delete the report.');
				$del_result = 'error';
			}
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
		$t = $this->translate;
		$this->template->content = $this->add_view('summary/setup');
		$template = $this->template->content;

		$this->report_id = arr::search($_REQUEST, 'report_id', false);

		# get all saved reports for user
		$scheduled_info = false;
		$report_info = false;
		$report_setting = false;
		$summary_items = 25;
		$report_name = '';
		$standardreport = false;
		$sel_alerttype = false;
		$sel_reportperiod = false;
		$sel_statetype = false;
		$sel_hoststate = false;
		$sel_svcstate = false;
		$saved_reports = Saved_reports_Model::get_saved_reports($this->type);
		if ($this->report_id) {
			$report_info = Saved_reports_Model::get_report_info($this->type, $this->report_id);
			$scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->report_id);
			$template->is_scheduled = empty($scheduled_info) ? false: true;
			if ($report_info) {
				$report_setting = unserialize($report_info['setting']);
				$summary_items = $report_setting['summary_items'];
				$json_report_info = json::encode($report_setting);
				if (isset($report_setting['obj_type'])) {
					$this->inline_js .= "set_selection('".$report_setting['obj_type']."', 'false');\n";
				}
				$this->inline_js .= "expand_and_populate(" . $json_report_info . ");\n";
				$standardreport = arr::search($report_setting, 'report_period', false);
				$report_name = $report_setting['report_name'];
				$sel_alerttype = isset($report_setting['alert_types']) ? $report_setting['alert_types'] : false;
				$sel_reportperiod = isset($report_setting['report_period']) ? $report_setting['report_period'] : false;
				$sel_statetype = isset($report_setting['state_types']) ? $report_setting['state_types'] : false;
				$sel_hoststate = isset($report_setting['host_states']) ? $report_setting['host_states'] : false;
				$sel_svcstate = isset($report_setting['service_states']) ? $report_setting['service_states'] : false;
			}
		}
		$scheduled_label = $t->_('Scheduled');
		$this->js_strings .= "var report_id = ".(int)$this->report_id.";\n";

		$json_periods = false;
		$periods = array();
		$periods_res = Scheduled_reports_Model::get_available_report_periods();
		if ($periods_res) {
			foreach ($periods_res as $period_row) {
				$periods[$period_row->id] = $period_row->periodname;
			}
			if (!empty($periods)) {
				$json_periods = json::encode($periods);
			}
		}

		$old_config_names = Saved_reports_Model::get_all_report_names($this->type);
		$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", $old_config_names)."');";

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker';
		#$this->xtra_js[] = $this->add_path('summary/js/json');
		$this->xtra_js[] = $this->add_path('summary/js/move_options');
		$this->xtra_js[] = $this->add_path('reports/js/common');
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min';
		$this->xtra_js[] = $this->add_path('summary/js/summary');

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox';
		$this->xtra_css[] = $this->add_path('reports/css/datePicker');
		$this->xtra_css[] = $this->add_path('css/default/reports');
		#$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->template->css_header->css = $this->xtra_css;
		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _reports_confirm_delete = '".$t->_("Are you really sure that you would like to remove this saved report?")."';\n";
		$this->js_strings .= "var _reports_confirm_delete_schedule = \"".sprintf($t->_("Do you really want to delete this schedule?%sThis action can't be undone."), '\n')."\";\n";
		$this->js_strings .= "var _reports_confirm_delete_warning = '".sprintf($t->_("Please note that this is a scheduled report and if you decide to delete it, %s" .
			"the corresponding schedule will be deleted as well.%s Are you really sure that this is what you want?"), '\n', '\n\n')."';\n";
		$this->js_strings .= "var _scheduled_label = '".$scheduled_label."';\n";
		$this->js_strings .= "var _reports_edit_information = '".$t->_('Double click to edit')."';\n";
		$this->js_strings .= "var _reports_success = '".$t->_('Success')."';\n";
		$this->js_strings .= "var _reports_error = '".$t->_('Error')."';\n";
		$this->js_strings .= "var _reports_schedule_error = '".$t->_('An error occurred when saving scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_send_error = '".$t->_('An error occurred when trying to send the scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_update_ok = '".$t->_('Your schedule has been successfully updated')."';\n";
		$this->js_strings .= "var _reports_schedule_send_ok = '".$t->_('Your report was successfully sent')."';\n";
		$this->js_strings .= "var _reports_schedule_create_ok = '".$t->_('Your schedule has been successfully created')."';\n";
		$this->js_strings .= "var _reports_fatal_err_str = '".$t->_('It is not possible to schedule this report since some vital information is missing.')."';\n";

		$template->label_create_new = $this->translate->_('Alert Summary Report');
		$template->label_standardreport = $this->translate->_('Standard Reports');
		$template->label_reporttype = $this->translate->_('Report Type');
		$template->label_report_mode = $this->translate->_('Report Mode');
		$template->label_report_mode_standard = $this->translate->_('Standard');
		$template->label_report_mode_custom = $this->translate->_('Custom');
		$template->label_new = $t->_('New');
		$template->new_saved_title = sprintf($t->_('Create new saved %s report'), $t->_('Summary'));
		$template->label_delete = $t->_('Delete report');
		$template->scheduled_label = $scheduled_label;
		$template->title_label = $t->_('schedule');
		$template->is_scheduled_report = $t->_('This is a scheduled report');
		$template->is_scheduled_clickstr = $t->_("This report has been scheduled. Click the icons below to change settings");
		$template->json_periods = $json_periods;
		$template->type = $this->type;
		$template->report_id = $this->report_id;
		$template->report_info = $report_info;
		$template->old_config_names_js = $old_config_names_js;
		$template->old_config_names = $old_config_names;
		$template->scheduled_ids = $scheduled_ids;
		$template->scheduled_periods = $scheduled_periods;
		$template->sel_alerttype = $sel_alerttype;
		$template->sel_reportperiod = $sel_reportperiod;
		$template->sel_statetype = $sel_statetype;
		$template->sel_hoststate = $sel_hoststate;
		$template->sel_svcstate = $sel_svcstate;
		$template->available_schedule_periods = $periods;
		$template->label_interval = $t->_('Report Interval');
		$template->label_recipients = $t->_('Recipients');
		$template->label_filename = $t->_('Filename');
		$template->label_description = $t->_('Description');
		$template->label_save = $t->_('Save');
		$template->label_clear = $t->_('Clear');
		$template->label_view_schedule = $t->_('View schedule');
		$template->scheduled_info = $scheduled_info;
		$template->lable_schedules = $t->_('Schedules for this report');
		$template->label_dblclick = $t->_('Double click to edit');

		$template->saved_reports = $saved_reports;

		# fetch users date format in PHP style so we can use it
		# in date() below
		$date_format = cal::get_calendar_format(true);

		$js_month_names = "Date.monthNames = ".json::encode($this->month_names).";";
		$js_abbr_month_names = 'Date.abbrMonthNames = '.json::encode($this->abbr_month_names).';';
		$js_day_names = 'Date.dayNames = '.json::encode($this->day_names).';';
		$js_abbr_day_names = 'Date.abbrDayNames = '.json::encode($this->abbr_day_names).';';
		$js_day_of_week = 'Date.firstDayOfWeek = '.$this->first_day_of_week.';';
		$js_date_format = "Date.format = '".cal::get_calendar_format()."';";
		$js_start_date = "_start_date = '".date($date_format, mktime(0,0,0,1, 1, 1996))."';";

		# inline js should be the
		# var host =
		# var service =
		# 	etc...
		$this->inline_js .= "\n".$js_month_names."\n";
		$this->inline_js .= $js_abbr_month_names."\n";
		$this->inline_js .= $js_day_names."\n";
		$this->inline_js .= $js_abbr_day_names."\n";
		$this->inline_js .= $js_day_of_week."\n";
		$this->inline_js .= $js_date_format."\n";
		$this->inline_js .= $js_start_date."\n";
		$this->inline_js .= "invalid_report_names = ".$old_config_names_js .";\n";

		if (!is_null($del_ok) && !is_null($del_result)) {
			$this->inline_js .= "show_message('".$del_result."', '".$del_msg."');\n";
		}

		if ($standardreport!==false) {
			$this->inline_js .= "set_report_mode('custom');\n";
			$this->inline_js .= "$('#report_mode_custom').attr('checked', true);\n";
		} else {
			$this->inline_js .= "set_report_mode('standard');\n";
		}

		$template->standardreport = array(
			1 => $t->_("Most Recent Hard Alerts"),
			2 => $t->_("Most Recent Hard Host Alerts"),
			3 => $t->_("Most Recent Hard Service Alerts"),
			4 => $t->_('Top Alert Producers'),
			5 => $t->_("Top Hard Host Alert Producers"),
			6 => $t->_("Top Hard Service Alert Producers"),
		);
		$template->label_show_items = $t->_('Items to show');
		$template->label_default_show_items = $summary_items;
		$template->label_customreport_options = $t->_('Custom Report Options');
		$template->label_rpttimeperiod = $t->_('Report Period');
		$template->label_inclusive = $t->_('Inclusive');
		$template->label_startdate = $t->_('Start Date');
		$template->label_enddate = $t->_('End Date');
		$template->label_alert_type = $t->_('Alert Types');
		$template->label_state_type = $t->_('State Types');
		$template->label_host_state = $t->_('Host States');
		$template->label_service_state = $t->_('Service States');
		$template->label_max_items = $t->_('Max List Items');
		$template->label_create_report = $t->_('Create Summary Report!');
		$template->label_select = $t->_('Select');
		$template->label_startdate_selector = $t->_('Date Start selector');
		$template->label_enddate_selector = $t->_('Date End selector');
		$template->label_click_calendar = $t->_('Click calendar to select date');
		$template->label_hostgroups = $t->_('Hostgroups');
		$template->label_hosts = $t->_('Hosts');
		$template->label_servicegroups = $t->_('Servicegroups');
		$template->label_services = $t->_('Services');
		$template->label_available = $t->_('Available');
		$template->label_selected = $t->_('Selected');
		$template->report_id = $this->report_id;
		$template->label_save_report = $t->_('Save report');
		$template->label_saved_reports = $t->_('Saved reports');
		$template->report_name = $report_name;
		$template->label_new_schedule = $t->_('New schedule');


		# displaytype
		$template->report_types = array
			(self::RECENT_ALERTS => $t->_("Most Recent Alerts"),
			 self::ALERT_TOTALS => $t->_("Alert Totals"),
			 self::TOP_ALERT_PRODUCERS => $t->_("Top Alert Producers"),
			 self::ALERT_TOTALS_HG => $t->_("Alert Totals By Hostgroup"),
			 self::ALERT_TOTALS_HOST => $t->_("Alert Totals By Host"),
			 self::ALERT_TOTALS_SG => $t->_("Alert Totals By Servicegroup"),
			 self::ALERT_TOTALS_SERVICE => $t->_("Alert Totals By Service"),
		);

		# timeperiod
		$template->report_periods = array(
			"today" => $t->_('Today'),
			"last24hours" => $t->_('Last 24 Hours'),
			"yesterday" => $t->_('Yesterday'),
			"thisweek" => $t->_('This Week'),
			"last7days" => $t->_('Last 7 Days'),
			"lastweek" => $t->_('Last Week'),
			"thismonth" => $t->_('This Month'),
			"last31days" => $t->_('Last 31 Days'),
			"lastmonth"	=> $t->_('Last Month'),
			"thisyear" => $t->_('This Year'),
			"lastyear" => $t->_('Last Year'),
			"custom" => '* ' . $t->_('CUSTOM REPORT PERIOD'). ' *'

		);

		#alerttypes
		$template->alerttypes = array(
			3 => $t->_("Host and Service Alerts"),
			1 => $t->_("Host Alerts"),
			2 => $t->_("Service Alerts")
		);

		#statetypes
		$template->statetypes = array(
			3 => $t->_("Hard and Soft States"),
			2 => $t->_("Hard States"),
			1 => $t->_("Soft States")
		);

		#hoststates
		$template->hoststates = array(
			7 => $t->_("All Host States"),
			6 => $t->_("Host Problem States"),
			1 => $t->_("Host Up States"),
			2 => $t->_("Host Down States"),
			4 => $t->_("Host Unreachable States")
		);

		#servicestates
		$template->servicestates = array(
			15 => $t->_("All Service States"),
			14 => $t->_("Service Problem States"),
			1 => $t->_("Service Ok States"),
			2 => $t->_("Service Warning States"),
			4 => $t->_("Service Critical States"),
			8 => $t->_("Service Unknown States"),
		);

		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
		$this->template->title = $this->translate->_("Reporting » Alert summary » Setup");

		if ($this->mashing) {
			return $template->render();
		}

	}

	/**
	 * Test a massive amount of queries. For debugging only
	 */
	public function test_queries()
	{
		$rpt = new Reports_Model();
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
	 * Print one alert totals table. Since they all look more or
	 * less the same, we can re-use the same function for all of
	 * them, provided we get the statenames (OK, UP etc) from the
	 * caller, along with the array of state totals.
	 */
	public function _print_alert_totals_table($topic, $ary, $state_names, $totals, $name)
	{
		$spacer = '';
		$table_border = '';
		if ($this->create_pdf) {
			$spacer = "<br />";
			$table_border = ' border="1"';
		}

		$t = $this->translate;
		echo "<br /><table class=\"host_alerts\"".($this->create_pdf ? 'style="margin-top: 15px" border="1"' : '')."><tr>\n";
		echo "<caption style=\"margin-top: 15px\">".$topic.' '.$t->_('for').' '.$name."</caption>".$spacer;
		echo "<th ". ($this->create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone"') . '>' . $t->_('State') . "</th>\n";
		echo "<th ". ($this->create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone"') . '>' . $t->_('Soft Alerts') . "</th>\n";
		echo "<th ". ($this->create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone"') . '>' . $t->_('Hard Alerts') . "</th>\n";
		echo "<th ". ($this->create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone"') . '>' . $t->_('Total Alerts') . "</th>\n";
		echo "</tr>\n";

		$total = array(0, 0); # soft and hard
		$i = 0;
		foreach ($ary as $state_id => $sh) {
			if (!isset($state_names[$state_id]))
				continue;
			$i++;
			echo "<tr class=\"".($i%2 == 0 ? 'odd' : 'even')."\">\n";
			echo "<td>" . $state_names[$state_id] . "</td>\n"; # topic
			echo "<td>" . $sh[0] . "</td>\n"; # soft
			echo "<td>" . $sh[1] . "</td>\n"; # hard
			$tot = $sh[0] + $sh[1];
			echo "<td>" . $tot . "</td>\n"; # soft + hard
			echo "</tr>\n";
		}
		$i++;
		echo "<tr class=\"".($i%2 == 0 ? 'odd' : 'even')."\"><td>Total</td>\n";
		echo "<td>" . $totals['soft'] . "</td>\n";
		echo "<td>" . $totals['hard'] . "</td>\n";
		$tot = $totals['soft'] + $totals['hard'];
		echo "<td>" . $tot . "</td>\n";
		echo "</tr></table><br />\n";
	}

	public function _print_duration($start_time, $end_time)
	{
		$fmt = "Y-m-d H:i:s";
		echo date($fmt, $start_time) . " to " .
			date($fmt, $end_time) . "<br />\n";
		$duration = $end_time - $start_time;
		$days = $duration / 86400;
		$hours = ($duration % 86400) / 3600;
		$minutes = ($duration % 3600) / 60;
		$seconds = ($duration % 60);
		printf("%s: %dd %dh %dm %ds", $this->translate->_("Duration"),
			   $days, $hours, $minutes, $seconds);

		# we needan extra break in case of PDF
		if ($this->create_pdf) {
			echo "<br />";
		}
	}

	/**
	 * Generates an alert summary report
	 */
	public function generate($schedule_id=false, $input=false)
	{
		$valid_options = array
			('summary_items', 'alert_types', 'state_types',
			 'host_states', 'service_states', 'start_time', 'end_time',
			 'report_period', 'host_name', 'service_description',
			 'hostgroup', 'servicegroup');

		if (!empty($input) && is_array($input)) {
			$_REQUEST = $input;
		}
		$report_options = $this->_report_settings();

		$this->schedule_id = arr::search($_REQUEST, 'schedule_id', $schedule_id);
		$this->report_id = arr::search($_REQUEST, 'saved_report_id', $this->report_id);

		# Handle call from cron or GUI to generate PDF report and send by email
		#
		# NOTE:
		# Passing a schedule_id to this method will ignore all other data passed
		# in $_REQUEST as data from _scheduled_report() will overwrite it
		if ($this->schedule_id !== false) {
			$_REQUEST = $this->_scheduled_report();
		}

		$this->create_pdf	= arr::search($_REQUEST, 'create_pdf');
		if ($this->create_pdf || $this->mashing) {
			$this->auto_render=false;
		}

		$t = $this->translate;
		$this->template->disable_refresh = true;
		$this->xtra_js[] = 'application/media/js/date';
		$this->xtra_js[] = $this->add_path('reports/js/common');
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min';
		$this->xtra_js[] = $this->add_path('summary/js/summary');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox';
		$this->xtra_css[] = $this->add_path('css/default/reports');
		$this->template->css_header->css = $this->xtra_css;

		$date_format = cal::get_calendar_format(true);

		$js_month_names = "Date.monthNames = ".json::encode($this->month_names).";";
		$js_abbr_month_names = 'Date.abbrMonthNames = '.json::encode($this->abbr_month_names).';';
		$js_day_names = 'Date.dayNames = '.json::encode($this->day_names).';';
		$js_abbr_day_names = 'Date.abbrDayNames = '.json::encode($this->abbr_day_names).';';
		$js_day_of_week = 'Date.firstDayOfWeek = '.$this->first_day_of_week.';';
		$js_date_format = "Date.format = '".cal::get_calendar_format()."';";
		$js_start_date = "_start_date = '".date($date_format, mktime(0,0,0,1, 1, 1996))."';";

		$old_config_names = Saved_reports_Model::get_all_report_names($this->type);
		$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", $old_config_names)."');";

		$this->inline_js .= "\n".$js_month_names."\n";
		$this->inline_js .= $js_abbr_month_names."\n";
		$this->inline_js .= $js_day_names."\n";
		$this->inline_js .= $js_abbr_day_names."\n";
		$this->inline_js .= $js_day_of_week."\n";
		$this->inline_js .= $js_date_format."\n";
		$this->inline_js .= $js_start_date."\n";
		$this->inline_js .= "var invalid_report_names = ".$old_config_names_js .";\n";

		$rpt = new Reports_Model();
		// cgi compatibility variables
		// Start dates
		$syear 	= (int)arr::search($_REQUEST, 'syear');
		$smon 	= (int)arr::search($_REQUEST, 'smon');
		$sday 	= (int)arr::search($_REQUEST, 'sday');
		$shour 	= (int)arr::search($_REQUEST, 'shour');
		$smin 	= (int)arr::search($_REQUEST, 'smin');
		$ssec 	= (int)arr::search($_REQUEST, 'ssec');
		// end dates
		$eyear 	= (int)arr::search($_REQUEST, 'eyear');
		$emon 	= (int)arr::search($_REQUEST, 'emon');
		$eday 	= (int)arr::search($_REQUEST, 'eday');
		$ehour 	= (int)arr::search($_REQUEST, 'ehour');
		$emin 	= (int)arr::search($_REQUEST, 'emin');
		$esec 	= (int)arr::search($_REQUEST, 'esec');


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

		# get all saved reports for user
		$scheduled_info = false;
		$report_info = false;
		$report_setting = false;
		$summary_items = 25;
		$report_name = '';
		$standardreport = true;
		$sel_alerttype = false;
		$sel_reportperiod = false;
		$sel_statetype = false;
		$sel_hoststate = false;
		$sel_svcstate = false;
		$saved_reports = Saved_reports_Model::get_saved_reports($this->type);
		if ($this->report_id) {
			$report_info = Saved_reports_Model::get_report_info($this->type, $this->report_id);
			$scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->report_id);
			$template->is_scheduled = empty($scheduled_info) ? false: true;
			if ($report_info && $report_setting) {
				$report_setting = unserialize($report_info['setting']);
				$summary_items = $report_setting['summary_items'];
				$json_report_info = json::encode($report_setting);
				$standardreport = arr::search($report_setting, 'standardreport', false);
				$report_name = $report_setting['report_name'];
				$sel_alerttype = $report_setting['alert_types'];
				$sel_reportperiod = $report_setting['report_period'];
				$sel_statetype = $report_setting['state_types'];
				$sel_hoststate = $report_setting['host_states'];
				$sel_svcstate = $report_setting['service_states'];
			}
		}
		$scheduled_label = $t->_('Scheduled');
		$this->js_strings .= "var report_id = ".(int)$this->report_id.";\n";

		$json_periods = false;
		$periods = array();
		$periods_res = Scheduled_reports_Model::get_available_report_periods();
		if ($periods_res) {
			foreach ($periods_res as $period_row) {
				$periods[$period_row->id] = $period_row->periodname;
			}
			if (!empty($periods)) {
				$json_periods = json::encode($periods);
			}
		}

		if (isset($_REQUEST['displaytype'])) {
			$_REQUEST['report_type'] = $_REQUEST['displaytype'];
		}

		if (!empty($_REQUEST['report_type'])) {
			$report_type = $_REQUEST['report_type'];
		} else {
			$report_type = self::TOP_ALERT_PRODUCERS;
		}

		if (!isset($_REQUEST['report_period']) && isset($_REQUEST['timeperiod'])) {
			$_REQUEST['report_period'] = $_REQUEST['timeperiod'];
		}

		// convert report period to timestamps
		if (isset($_REQUEST['report_period']) && $_REQUEST['report_period'] == 'custom' && !empty($syear) && !empty($eyear)) {
			// cgi compatibility
			$_REQUEST['start_time'] = mktime($shour, $smin, $ssec, $smon, $sday, $syear);
			$_REQUEST['end_time'] = mktime($ehour, $emin, $esec, $emon, $eday, $eyear);
		}

		$options = $_REQUEST;
		if (isset($_REQUEST['standardreport'])) {
			# the default for standardreports is 'last7days'
			$options['report_period'] = 'last7days';
			if ($_REQUEST['standardreport'] < 4) {
				$report_type = self::RECENT_ALERTS;
			}

			switch ($_REQUEST['standardreport']) {
			 case 1: case 4:
				$options['alert_types'] = 3;
				$options['state_types'] = 2;
				break;

			 case 2: case 5:
				$options['alert_types'] = 1;
				$options['state_types'] = 2;
				break;

			 case 3: case 6:
				$options['alert_types'] = 2;
				$options['state_types'] = 2;
				break;

			 default:
				echo Kohana::debug("Unknown standardreport: $_REQUEST[standardreport]");
				die;
				break;
			}
		}

		$save_report_settings = arr::search($_REQUEST, 'save_report_settings', false);
		if ($save_report_settings) {
			$this->report_id = Saved_reports_Model::edit_report_info($this->type, $this->report_id, $report_options);
			$status_msg = $this->report_id ? $this->translate->_("Report was successfully saved") : "";
			$msg_type = $this->report_id ? "ok" : "";
		}

		$used_options = array();
		foreach ($valid_options as $opt) {
			if (!empty($options[$opt])) {
				if ($rpt->set_option($opt, $options[$opt]) !== false) {
					$used_options[$opt] = $options[$opt];
				} else {
					# handle the fact that we passed an
					# illegal option = value combo to
					# the reports model somehow
				}
			}
		}
		$used_options['start_time'] = $rpt->start_time;
		$used_options['end_time'] = $rpt->end_time;

		if ($report_type == self::ALERT_TOTALS) {
			if (isset($used_options['servicegroup']))
				$report_type = self::ALERT_TOTALS_SG;
			elseif (isset($used_options['hostgroup']))
				$report_type = self::ALERT_TOTALS_HG;
			elseif (isset($used_options['service_description']))
				$report_type = self::ALERT_TOTALS_SERVICE;
			elseif (isset($used_options['host_name']))
				$report_type = self::ALERT_TOTALS_HOST;
		}

		$views = array
			(self::TOP_ALERT_PRODUCERS => 'toplist',
			 self::RECENT_ALERTS => 'latest',
			 self::ALERT_TOTALS => 'alert_totals',
			 self::ALERT_TOTALS_HG => 'alert_totals_hg',
			 self::ALERT_TOTALS_HOST => 'alert_totals_host',
			 self::ALERT_TOTALS_SERVICE => 'alert_totals_service',
			 self::ALERT_TOTALS_SG => 'alert_totals_sg',
			 );
		$this->template->content =
			$this->add_view("summary/" . $views[$report_type]);

		$content = $this->template->content;
		$content->label_time = $t->_('Time');
		$content->label_alert_type = $t->_('Alert Type');
		$content->label_host = $t->_('Host');
		$content->label_service = $t->_('Service');
		$content->label_hostgroup = $t->_('Hostgroup');
		$content->label_servicegroup = $t->_('Servicegroup');
		$content->label_host_alerts = $t->_('Host Alerts');
		$content->label_service_alerts = $t->_('Service Alerts');
		$content->label_state = $t->_('State');
		$content->label_hard = $t->_('Hard');
		$content->label_soft = $t->_('Soft');
		$content->label_soft_alerts = $t->_('Soft Alerts');
		$content->label_hard_alerts = $t->_('Hard Alerts');
		$content->label_total_alerts = $t->_('Total Alerts');
		$content->create_pdf = $this->create_pdf;

		$this->template->content->schedules = $this->add_view('summary/schedule');
		$template = $this->template->content->schedules;
		$template->json_periods = $json_periods;
		$template->create_pdf = $this->create_pdf;
		$template->type = $this->type;
		$template->report_id = $this->report_id;
		$template->report_info = $report_info;
		$template->old_config_names_js = $old_config_names_js;
		$template->old_config_names = $old_config_names;
		$template->scheduled_ids = $scheduled_ids;
		$template->scheduled_periods = $scheduled_periods;
		$template->sel_alerttype = $sel_alerttype;
		$template->sel_reportperiod = $sel_reportperiod;
		$template->sel_statetype = $sel_statetype;
		$template->sel_hoststate = $sel_hoststate;
		$template->sel_svcstate = $sel_svcstate;
		$template->available_schedule_periods = $periods;
		$template->label_interval = $t->_('Report Interval');
		$template->label_recipients = $t->_('Recipients');
		$template->label_filename = $t->_('Filename');
		$template->label_description = $t->_('Description');
		$template->label_save = $t->_('Save');
		$template->label_clear = $t->_('Clear');
		$template->label_view_schedule = $t->_('View schedule');
		$template->scheduled_info = $scheduled_info;
		$template->lable_schedules = $t->_('Schedules for this report');
		$template->label_dblclick = $t->_('Double click to edit');
		$template->label_new_schedule = $t->_('New schedule');

		$template->saved_reports = $saved_reports;

		$content->host_state_names = array
			(Reports_Model::HOST_UP => $t->_('UP'),
			 Reports_Model::HOST_DOWN => $t->_('DOWN'),
			 Reports_Model::HOST_UNREACHABLE => $t->_('UNREACHABLE'));
		$content->service_state_names = array
			(Reports_Model::SERVICE_OK => $t->_('OK'),
			 Reports_Model::SERVICE_WARNING => $t->_('WARNING'),
			 Reports_Model::SERVICE_CRITICAL => $t->_('CRITICAL'),
			 Reports_Model::SERVICE_UNKNOWN => $t->_('UNKNOWN'));
		$content->label_all_states = $t->_('All States');

		$result = false;
		switch ($report_type) {
		 case self::TOP_ALERT_PRODUCERS:
			$content->label_rank = $t->_('Rank');
			$content->label_producer_type = $t->_('Producer Type');
			$content->label_total_alerts = $t->_('Total Alerts');
			$result = $rpt->top_alert_producers();
			break;

		 case self::RECENT_ALERTS:
			$content->label_state_type = $t->_('State Type');
			$content->label_information = $t->_('Information');
			$content->label_host_alert = $t->_('Host Alert');
			$content->label_service_alert = $t->_('Service Alert');
			$result = $rpt->recent_alerts();
			break;

		 case self::ALERT_TOTALS:
		 case self::ALERT_TOTALS_HG:
		 case self::ALERT_TOTALS_SG:
		 case self::ALERT_TOTALS_HOST:
			$content->label_overall_totals = $t->_('Overall Totals');
			$result = $rpt->alert_totals();
			break;

		case self::ALERT_TOTALS_SERVICE:
			$content->label_overall_totals = $t->_('Overall Totals');
			$services = $this->_populate_services($used_options);

			if (!empty($services)) {
				$rpt->set_option('service_description', $services);
			}

			$result = $rpt->alert_totals();
			break;

		 default:
			echo Kohana::debug("Case fallthrough");
			die;
			break;
		}

		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _reports_confirm_delete = '".$t->_("Are you really sure that you would like to remove this saved report?")."';\n";
		$this->js_strings .= "var _reports_confirm_delete_schedule = \"".sprintf($t->_("Do you really want to delete this schedule?%sThis action can't be undone."), '\n')."\";\n";
		$this->js_strings .= "var _reports_confirm_delete_warning = '".sprintf($t->_("Please note that this is a scheduled report and if you decide to delete it, %s" .
			"the corresponding schedule will be deleted as well.%s Are you really sure that this is what you want?"), '\n', '\n\n')."';\n";
		$this->js_strings .= "var _scheduled_label = '".$scheduled_label."';\n";
		$this->js_strings .= "var _reports_edit_information = '".$t->_('Double click to edit')."';\n";
		$this->js_strings .= "var _reports_success = '".$t->_('Success')."';\n";
		$this->js_strings .= "var _reports_error = '".$t->_('Error')."';\n";
		$this->js_strings .= "var _reports_schedule_error = '".$t->_('An error occurred when saving scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_send_error = '".$t->_('An error occurred when trying to send the scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_update_ok = '".$t->_('Your schedule has been successfully updated')."';\n";
		$this->js_strings .= "var _reports_schedule_send_ok = '".$t->_('Your report was successfully sent')."';\n";
		$this->js_strings .= "var _reports_schedule_create_ok = '".$t->_('Your schedule has been successfully created')."';\n";
		$this->js_strings .= "var _reports_fatal_err_str = '".$t->_('It is not possible to schedule this report since some vital information is missing.')."';\n";
		$this->template->js_strings = $this->js_strings;

		$content->result = $result;
		$content->options = $used_options;
		$content->summary_items = $rpt->summary_items;
		$content->completion_time = $rpt->completion_time;
		$this->template->title = $this->translate->_("Reporting » Alert summary » Report");
		if ($this->create_pdf || $this->mashing) {
			$this->pdf_data['content'] = $content->render();

			if ($this->create_pdf && $this->mashing) {
				return $this->pdf_data;
			} elseif ($this->mashing) {
				return $content->render();
			}

			$retval = $this->_pdf();
			if (PHP_SAPI == "cli") {
				echo $retval;
			}
			return $retval;
		}

		$this->template->inline_js = $this->inline_js;
	}

	/**
	*
	*
	*/
	public function _populate_services($used_options=false)
	{
		if (empty($used_options)) {
			return false;
		}
		$services = false;

		if (empty($used_options['service_description'])) {
			if (isset($used_options['host_name'])) {
				foreach ($used_options['host_name'] as $host_name) {
					$service_res = Host_Model::get_services($host_name);
					if ($service_res !== false && count($service_res)) {
						foreach ($service_res as $svc) {
							$services[] = $host_name.';'.$svc->service_description;
							$used_options['service_description'][] = $host_name.';'.$svc->service_description;
						}
					}
				}
			} elseif (isset($used_options['hostgroup'])) {
				foreach ($used_options['hostgroup'] as $group) {
					$hg = new Hostgroup_Model();
					$hg_res = $hg->get_hosts_for_group($group);
					if ($hg_res !== false && count($hg_res)) {
						foreach ($hg_res as $row) {
							$service_res = Host_Model::get_services($row->host_name);
							if ($service_res !== false && count($service_res)) {
								foreach ($service_res as $svc) {
									$services[] = $row->host_name.';'.$svc->service_description;
									$used_options['service_description'][] = $row->host_name.';'.$svc->service_description;
								}
							}
						}
					}
				}
			} elseif (isset($used_options['servicegroup'])) {
				foreach ($used_options['servicegroup'] as $group) {
					$sg = new Servicegroup_Model();
					$sg_res = $sg->get_services_for_group($group);
					if ($sg_res !== false && count($sg_res)) {
						foreach ($sg_res as $row) {
							$service_res = Host_Model::get_services($row->host_name);
							if ($service_res !== false && count($service_res)) {
								foreach ($service_res as $svc) {
									$services[] = $row->host_name.';'.$svc->service_description;
									$used_options['service_description'][] = $row->host_name.';'.$svc->service_description;
								}
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
		$translate = zend::instance('Registry')->get('Zend_Translate');

		$nagios_etc_path = Kohana::config('config.nagios_etc_path');
		$nagios_etc_path = $nagios_etc_path !== false ? $nagios_etc_path : Kohana::config('config.nagios_base_path').'/etc';

		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array();
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else
			echo sprintf($translate->_("This helptext ('%s') is yet not translated"), $id);
	}

	/**
	*	Fetch all input params, filter out unneeded
	*	and return as array
	*/
	public function _report_settings()
	{
		$input = false;
		$data = false;
		if (!empty($_POST)) {
			$input = $_POST;
		} elseif (!empty($_GET)) {
			$input = $_GET;
		}

		if (empty($input)) {
			return false;
		}

		$skip_keys = array(
			'create_report',
			'new_report_setup',
			'old_report_name',
			'save_report_settings'
		);
		foreach ($input as $key => $val) {
			if ($val == '' || in_array($key, $skip_keys)) {
				continue;
			}
			$data[$key] = $val;
		}
		if (isset($data['hostgroup'])) {
			$data['objects'] = $data['hostgroup'];
			$data['obj_type'] = 'hostgroups';
		} elseif (isset($data['servicegroup'])) {
			$data['objects'] = $data['servicegroup'];
			$data['obj_type'] = 'servicegroups';
		} elseif (isset($data['service_description'])) {
			$data['objects'] = $data['service_description'];
			$data['obj_type'] = 'services';
		} elseif (isset($data['host_name'])) {
			$data['objects'] = $data['host_name'];
			$data['obj_type'] = 'hosts';
		}

		if (isset($data['cal_start']) && isset($data['start_time']) && isset($data['time_start'])) {
			$data['start_time'] = strtotime($data['start_time'].' '.$data['time_start']);
		}
		if (isset($data['cal_end']) && isset($data['end_time']) && isset($data['time_end'])) {
			$data['end_time'] = strtotime($data['end_time'].' '.$data['time_end']);
		}
		return $data;
	}

	/**
	*	Fetch informaton on a sheduled report and
	* 	return all data in an array that will replace
	* 	$_REQUEST.
	*
	* 	If called through a call from the commandline, the script will
	* 	be authorized as the owner of the current schedule.
	*/
	public function _scheduled_report()
	{
		# Fetch info on the scheduled report
		$report_data = Scheduled_reports_Model::get_scheduled_data($this->schedule_id);
		if ($report_data == false) {
			die("No data returned for schedule (ID:".$this->schedule_id.")\n");
		}

		$this->pdf_filename = $report_data['filename'];
		$this->pdf_recipients = $report_data['recipients'];

		$request['create_pdf'] = 1;
		$request['new_report_setup'] = 1;

		$settings = unserialize($report_data['setting']);
		unset($report_data['setting']);
		unset($report_data['objects']);
		unset($report_data['filename']);
		unset($report_data['recipients']);

		if (PHP_SAPI === "cli") {
			# set current user to the owner of the report
			# this should only be done when called through PHP CLI
			Auth::instance()->force_login($report_data['user']);
		}
		return array_merge($request, $settings, $report_data);
	}

	/**
	*	Create pdf
	* 	Will also send the generated PDF as an attachment
	* 	if $this->pdf_recipients is set.
	* 	@@@FIXME: Break reports_controller::_pdf() and summary_controller::_pdf() into helper?
	*/
	public function _pdf()
	{
		# include necessary files for PDF creation
		pdf::start();
		$this->auto_render=false;

		global $l; # required for tcpdf

		if (isset($l['w_page'])) { # use ninja translation
			$l['w_page'] = $this->translate->_('page');
		}

		$type = $this->type;
		$filename = $this->pdf_filename;
		$save_path = $this->pdf_savepath;

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$title = isset($this->pdf_data['title']) ? $this->pdf_data['title'] : $this->translate->_('Ninja PDF Report');
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Ninja4Nagios');
		$pdf->SetTitle($this->translate->_('Ninja PDF Report'));
		$pdf->SetSubject($title);
		$pdf->SetKeywords('Ninja, '.Kohana::config('config.product_name').', PDF, report, '.$type);

		// set default header data
		#$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$pdf->setLanguageArray($l);

		// ---------------------------------------------------------

		// set font
		$pdf->SetFont('helvetica', 'B', 10);

		// add a page
		$pdf->AddPage();

		// set color for filler
		$pdf->SetFillColor(255, 255, 0);

		// ---------------------------------------------------------

		if (PHP_SAPI == 'cli') {
			$site = Kohana::config('config.site_domain');
			$path = realpath(dirname(__FILE__).'/../../').'/';
			$cont = $this->pdf_data['content'];
			$this->pdf_data['content'] = str_replace($site, $path, $cont);
		}

		$images = array();

		$pdf->writeHTML($this->pdf_data['content'], true, 0, true, 0);
		$filename = !empty($filename) ? $filename : str_replace(' ', '_', $title);
		$filename = trim($filename);
		if (strtolower(substr($filename, -4, 4))!='.pdf') {
			$filename .= '.pdf';
		}

		# Close and output PDF document
		# change last parameter to 'F' to save generated file to a path ($filename)
		# 'I' is default and pushes the file to browser for download
		$action = 'I';
		$send_by_mail = false;
		if (!empty($this->pdf_recipients)) {
			$action = 'F';
			$filename = K_PATH_CACHE.$filename;
			$send_by_mail = true;
		}

		$pdf->Output($filename, $action);
		$mail_sent = 0;
		if ($send_by_mail) {
			# send file as email to recipients
			$to = $this->pdf_recipients;
			if (strstr($to, ',')) {
				$recipients = explode(',', $to);
				if (is_array($recipients) && !empty($recipients)) {
					unset($to);
					foreach ($recipients as $user) {
						$to[$user] = $user;
					}
				}
			}

			$config = Kohana::config('reports');
			$mail_sender_address = $config['from_email'];

			if (!empty($mail_sender_address)) {
				$from = $mail_sender_address;
			} else {
				$hostname = exec('hostname --long');
				$from = !empty($config['from']) ? $config['from'] : Kohana::config('config.product_name');
				$from = str_replace(' ', '', trim($from));
				if (empty($hostname) && $hostname != '(none)') {
					// unable to get a valid hostname
					$from = $from . '@localhost';
				} else {
					$from = $from . '@'.$hostname;
				}
			}

			$plain = sprintf($this->translate->_('Scheduled report sent from %s'),!empty($config['from']) ? $config['from'] : $from);
			$subject = $this->translate->_('Scheduled report').": ".basename($filename);

			# $mail_sent will contain the nr of mail sent - not used at the moment
			$mail_sent = email::send_multipart($to, $from, $subject, $plain, '', array($filename => 'pdf'));

			# remove file from cache folder
			unlink($filename);
			return $mail_sent;
		}

		return true;
	}

}
