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

	private $abbr_day_names = false;
	private $abbr_month_names = false;
	private $day_names = false;
	private $first_day_of_week = 1;
	private $month_names = false;
	private $pdf_filename = false;
	private $pdf_recipients = false; # when sending reports by email
	private $pdf_savepath = false;	# when saving pdf to a path
	public $pdf_local_persistent_filepath = false;
	private $report_id = false;
	private $schedule_id = false;
	private $type = 'summary';
	public $alerttypes = false;
	public $create_pdf = false;
	public $hoststates = false;
	public $mashing = false;
	public $pdf_data = false;
	public $report_periods = false;
	public $report_types = false;
	public $reports_model = false;
	public $servicestates = false;
	public $statetypes = false;
	public $template_prefix = false;

	private $host_state_names = array();
	private $service_state_names = array();


	public function __construct($mashing=false, $obj=false)
	{
		parent::__construct();
		$this->mashing = $mashing;
		if (!empty($obj) && is_object($obj)) {
			$this->reports_model = $obj;
		} else {
			$this->reports_model = new Reports_Model();
		}
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

		$this->abbr_month_names = array(
			_('Jan'),
			_('Feb'),
			_('Mar'),
			_('Apr'),
			_('May'),
			_('Jun'),
			_('Jul'),
			_('Aug'),
			_('Sep'),
			_('Oct'),
			_('Nov'),
			_('Dec')
		);

		$this->month_names = array(
			_('January'),
			_('February'),
			_('March'),
			_('April'),
			_('May'),
			_('June'),
			_('July'),
			_('August'),
			_('September'),
			_('October'),
			_('November'),
			_('December')
		);

		$this->abbr_day_names = array(
			_('Sun'),
			_('Mon'),
			_('Tue'),
			_('Wed'),
			_('Thu'),
			_('Fri'),
			_('Sat')
		);

		$this->day_names = array(
			_('Sunday'),
			_('Monday'),
			_('Tuesday'),
			_('Wednesday'),
			_('Thursday'),
			_('Friday'),
			_('Saturday')
		);
				# displaytype
		$this->report_types = array
			(self::RECENT_ALERTS => _("Most Recent Alerts"),
			 self::ALERT_TOTALS => _("Alert Totals"),
			 self::TOP_ALERT_PRODUCERS => _("Top Alert Producers"),
			 self::ALERT_TOTALS_HG => _("Alert Totals By Hostgroup"),
			 self::ALERT_TOTALS_HOST => _("Alert Totals By Host"),
			 self::ALERT_TOTALS_SG => _("Alert Totals By Servicegroup"),
			 self::ALERT_TOTALS_SERVICE => _("Alert Totals By Service"),
		);

		# timeperiod
		$this->report_periods = array(
			"today" => _('Today'),
			"last24hours" => _('Last 24 Hours'),
			"yesterday" => _('Yesterday'),
			"thisweek" => _('This Week'),
			"last7days" => _('Last 7 Days'),
			"lastweek" => _('Last Week'),
			"thismonth" => _('This Month'),
			"last31days" => _('Last 31 Days'),
			"lastmonth"	=> _('Last Month'),
			"thisyear" => _('This Year'),
			"lastyear" => _('Last Year'),
			"custom" => '* ' . _('CUSTOM REPORT PERIOD'). ' *'
		);

		#alerttypes
		$this->alerttypes = array(
			3 => _("Host and Service Alerts"),
			1 => _("Host Alerts"),
			2 => _("Service Alerts")
		);

		$this->statetypes = array(
			3 => _("Hard and Soft States"),
			2 => _("Hard States"),
			1 => _("Soft States")
		);

		$this->hoststates = array(
			7 => _("All Host States"),
			6 => _("Host Problem States"),
			1 => _("Host Up States"),
			2 => _("Host Down States"),
			4 => _("Host Unreachable States")
		);

		$this->servicestates = array(
			15 => _("All Service States"),
			14 => _("Service Problem States"),
			1 => _("Service Ok States"),
			2 => _("Service Warning States"),
			4 => _("Service Critical States"),
			8 => _("Service Unknown States"),
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
				$del_msg = _('Report was deleted successfully.');
				$del_result = 'ok';
			} else {
				$del_msg = _('An error occurred while trying to delete the report.');
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
				$report_setting = i18n::unserialize($report_info['setting']);
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
		$scheduled_label = _('Scheduled');
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
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = $this->add_path('reports/js/move_options.js');
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';
		$this->xtra_js[] = $this->add_path('summary/js/summary.js');

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox.css';
		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = $this->add_path('css/default/reports.css');
		$this->template->css_header->css = $this->xtra_css;
		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _reports_confirm_delete = '"._("Are you really sure that you would like to remove this saved report?")."';\n";
		$this->js_strings .= "var _reports_confirm_delete_schedule = \"".sprintf(_("Do you really want to delete this schedule?%sThis action can't be undone."), '\n')."\";\n";
		$this->js_strings .= "var _reports_confirm_delete_warning = '".sprintf(_("Please note that this is a scheduled report and if you decide to delete it, %s" .
			"the corresponding schedule will be deleted as well.%s Are you really sure that this is what you want?"), '\n', '\n\n')."';\n";
		$this->js_strings .= "var _scheduled_label = '".$scheduled_label."';\n";
		$this->js_strings .= "var _reports_edit_information = '"._('Double click to edit')."';\n";
		$this->js_strings .= "var _reports_success = '"._('Success')."';\n";
		$this->js_strings .= "var _reports_error = '"._('Error')."';\n";
		$this->js_strings .= "var _reports_schedule_error = '"._('An error occurred when saving scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_send_error = '"._('An error occurred when trying to send the scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_update_ok = '"._('Your schedule has been successfully updated')."';\n";
		$this->js_strings .= "var _reports_schedule_send_ok = '"._('Your report was successfully sent')."';\n";
		$this->js_strings .= "var _reports_schedule_create_ok = '"._('Your schedule has been successfully created')."';\n";
		$this->js_strings .= "var _reports_fatal_err_str = '"._('It is not possible to schedule this report since some vital information is missing.')."';\n";

		$template->label_create_new = _('Alert Summary Report');
		$template->new_saved_title = sprintf(_('Create new saved %s report'), _('Summary'));
		$template->scheduled_label = $scheduled_label;
		$template->is_scheduled_clickstr = _("This report has been scheduled. Click the icons below to change settings");
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
		$template->scheduled_info = $scheduled_info;

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
			1 => _("Most Recent Hard Alerts"),
			2 => _("Most Recent Hard Host Alerts"),
			3 => _("Most Recent Hard Service Alerts"),
			4 => _('Top Alert Producers'),
			5 => _("Top Hard Host Alert Producers"),
			6 => _("Top Hard Service Alert Producers"),
		);
		$template->label_default_show_items = $summary_items;
		$template->report_id = $this->report_id;
		$template->report_name = $report_name;


		# displaytype
		$template->report_types = $this->report_types;

		# timeperiod
		$template->report_periods = $this->report_periods;

		#alerttypes
		$template->alerttypes = $this->alerttypes;

		#statetypes
		$template->statetypes = $this->statetypes;

		#hoststates
		$template->hoststates = $this->hoststates;

		#servicestates
		$template->servicestates = $this->servicestates;

		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
		$this->template->title = _("Reporting » Alert summary » Setup");

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

		echo "<br /><table class=\"host_alerts\"".($this->create_pdf ? 'style="margin-top: 15px" border="1"' : '')."><tr>\n";
		echo "<caption style=\"margin-top: 15px\">".$topic.' '._('for').' '.$name."</caption>".$spacer;
		echo "<th ". ($this->create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone"') . '>' . _('State') . "</th>\n";
		echo "<th ". ($this->create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone"') . '>' . _('Soft Alerts') . "</th>\n";
		echo "<th ". ($this->create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone"') . '>' . _('Hard Alerts') . "</th>\n";
		echo "<th ". ($this->create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone"') . '>' . _('Total Alerts') . "</th>\n";
		echo "</tr>\n";

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

	/**
	 * @param int $report_type
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function _get_summary_variant_by_report_type($report_type) {
		$types = array(
			self::RECENT_ALERTS => _("Most recent hard alerts"),
			self::ALERT_TOTALS => _("Most recent hard host alerts"),
			self::TOP_ALERT_PRODUCERS => _("Top hard alert producers"),
			self::ALERT_TOTALS_HG => _('Overall totals'),
			self::ALERT_TOTALS_HOST => _('Overall totals'),
			self::ALERT_TOTALS_SERVICE => _('Overall totals'),
			self::ALERT_TOTALS_SG => _('Overall totals')
		);
		if(!array_key_exists($report_type, $types)) {
			throw new InvalidArgumentException("Invalid report type");
		}
		return $types[$report_type];
	}

	/**
	 * @param int $start_time unix timestamp
	 * @param int $end_time unix timestamp
	 * @return string
	 */
	private function _nice_format_duration($start_time, $end_time) {
		$duration = $end_time - $start_time;
		$days = $duration / 86400;
		$hours = ($duration % 86400) / 3600;
		$minutes = ($duration % 3600) / 60;
		$seconds = ($duration % 60);
		return sprintf("%s: %dd %dh %dm %ds", _("Duration"),
			   $days, $hours, $minutes, $seconds);
	}

	public function _print_duration($start_time, $end_time)
	{
		$fmt = nagstat::date_format();
		echo date($fmt, $start_time) . " to " .
			date($fmt, $end_time) . "<br />\n";

		echo $this->_nice_format_duration($start_time, $end_time);

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
			 'hostgroup', 'servicegroup', 'report_timeperiod');

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

		$this->create_pdf = arr::search($_REQUEST, 'create_pdf');
		if ($this->create_pdf || $this->mashing) {
			$this->auto_render=false;
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
				$report_setting = i18n::unserialize($report_info['setting']);
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
		$scheduled_label = _('Scheduled');
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

		if (isset($_REQUEST['rpttimeperiod']))
			$_REQUEST['report_timeperiod'] = $_REQUEST['rpttimeperiod'];

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
		if ($save_report_settings && !empty($_REQUEST['report_name'])) {
			$this->report_id = Saved_reports_Model::edit_report_info($this->type, $this->report_id, $report_options);
			$status_msg = $this->report_id ? _("Report was successfully saved") : "";
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
		$template->scheduled_info = $scheduled_info;

		$template->saved_reports = $saved_reports;

		$content->host_state_names = $this->host_state_names;
		$content->service_state_names = $this->service_state_names;

		$result = false;
		switch ($report_type) {
		 case self::TOP_ALERT_PRODUCERS:
			$result = $rpt->top_alert_producers();
			break;

		 case self::RECENT_ALERTS:
			$result = $rpt->recent_alerts();
			break;

		 case self::ALERT_TOTALS:
		 case self::ALERT_TOTALS_HG:
		 case self::ALERT_TOTALS_SG:
		 case self::ALERT_TOTALS_HOST:
			$result = $rpt->alert_totals();
			break;

		case self::ALERT_TOTALS_SERVICE:
			$services = $this->_populate_services($used_options);
			if (!empty($services))
				$rpt->set_option('service_description', $services);

			$result = $rpt->alert_totals();
			break;

		 default:
			echo Kohana::debug("Case fallthrough");
			exit(1);
		}

		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _reports_confirm_delete = '"._("Are you really sure that you would like to remove this saved report?")."';\n";
		$this->js_strings .= "var _reports_confirm_delete_schedule = \"".sprintf(_("Do you really want to delete this schedule?%sThis action can't be undone."), '\n')."\";\n";
		$this->js_strings .= "var _reports_confirm_delete_warning = '".sprintf(_("Please note that this is a scheduled report and if you decide to delete it, %s" .
			"the corresponding schedule will be deleted as well.%s Are you really sure that this is what you want?"), '\n', '\n\n')."';\n";
		$this->js_strings .= "var _scheduled_label = '".$scheduled_label."';\n";
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
		$content->options = $used_options;
		$content->summary_items = $rpt->summary_items;
		$content->completion_time = $rpt->completion_time;
		$this->template->title = _("Reporting » Alert summary » Report");
		$date_format = nagstat::date_format();
		if ($this->create_pdf || $this->mashing) {
			if('.csv' == substr($this->pdf_filename, -4, 4)) {
				// @todo move this piece of ** out of the controller

				// meta, array keys are there for you
				$csv_content = array('"'.implode('", "', array(
					'kind_of_report' => $this->_get_summary_variant_by_report_type($report_type), // @todo replace this with for example "Most recent hard alerts" (found in $report_type)
					'start_time' => "From: ".date($date_format, $used_options['start_time']),
					'end_time' => "To: ".date($date_format, $used_options['end_time']),
					'human_readable_duration' => $this->_nice_format_duration($used_options['start_time'], $used_options['end_time'])
				)).'"');

				if(self::RECENT_ALERTS == $report_type) {
					// headers
					$csv_content[] = '"'.implode('", "', array(
						'TIME',
						'ALERT TYPE',
						'HOST',
						'SERVICE',
						'STATE TYPE',
						'INFORMATION'
					)).'"';

					// content
					foreach($result as $log_entry) {
						$csv_content[] = '"'.implode('", "', array(
							date($date_format, $log_entry['timestamp']),
							Reports_Model::event_type_to_string($log_entry['event_type'], $log_entry['service_description'] ? 'service' : 'host'),
							$log_entry['host_name'],
							$log_entry['service_description'] ? $log_entry['service_description'] : 'N/A',
							$log_entry['hard'] ? _('Hard') : _('Soft'),
							$log_entry['output']
						)).'"';
					}
				} elseif(self::TOP_ALERT_PRODUCERS == $report_type) {
					// summary of services
					// headers
					$csv_content[] = '"'.implode('", "', array(
						'HOST',
						'SERVICE',
						'ALERT TYPE',
						'TOTAL ALERTS'
					)).'"';

					// content
					foreach($result as $log_entry) {
						$csv_content[] = '"'.implode('", "', array(
							$log_entry['host_name'],
							isset($log_entry['service_description']) ? $log_entry['service_description'] : null,
							Reports_Model::event_type_to_string($log_entry['event_type'], 'service'),
							$log_entry['total_alerts']
						)).'"';
					}
				} else {
					// custom settings, even more alert types to choose from;
					// also explains the nested layout of $result
					$header = array(
						'TYPE',
						'HOST',
						'STATE',
						'SOFT ALERTS',
						'HARD ALERTS',
						'TOTAL ALERTS'
					);
					switch($report_type) {
						case self::ALERT_TOTALS_HG:
							$label = _('Hostgroup');
							array_splice($header, 1, 1, 'HOSTGROUP');
							break;
						case self::ALERT_TOTALS_HOST:
							$label = _('Host');
							break;
						case self::ALERT_TOTALS_SERVICE:
							$label = _('Service');
							array_splice($header, 2, 0, 'SERVICE');
							break;
						case self::ALERT_TOTALS_SG:
							$label = _('Servicegroup');
							array_splice($header, 1, 1, 'SERVICEGROUP');
							break;
					}
					$csv_content[] = '"'.implode('", "', $header).'"';
					foreach ($result as $host_name => $ary) {
						$service_name = null;
						if($report_type == self::ALERT_TOTALS_SERVICE) {
							list($host_name, $service_name) = explode(';', $host_name);
						}
						foreach($ary['host'] as $state => $host) {
							$row = array(
								$label,
								$host_name,
								$this->host_state_names[$state],
								$host[0], # soft
								$host[1], # hard
								$host[0] + $host[1] # total
							);
							if($service_name) {
								array_splice($row, 2, 0, $service_name);
							}
						}
						$csv_content[] = '"'.implode('", "', $row).'"';
						foreach($ary['service'] as $state => $service) {
							$row = array(
								$label,
								$host_name,
								$this->service_state_names[$state],
								$service[0], # soft
								$service[1], # hard
								$service[0] + $service[1] # total
							);
							if($service_name) {
								array_splice($row, 2, 0, $service_name);
							}
						}
						$csv_content[] = '"'.implode('", "', $row).'"';
					}
				}

				$temp_name = tempnam('/tmp', 'report');
				// copying behavior for definition of K_PATH_CACHE (grep for it,
				// it should be in tcpdf somewhere)
				if(is_file($temp_name)) {
					unlink($temp_name);
				}
				mkdir($temp_name);

				$filename = preg_replace('/.(csv|pdf)$/', null, $this->pdf_filename).'.csv';
				$full_path = $temp_name.'/'.$filename;
				file_put_contents($full_path, implode("\n", $csv_content));

				if($this->pdf_local_persistent_filepath) {
					// we want to make sure the file exists forever and ever, which
					// means that name actually matters

					// once again, stealing methods from pdf to csv
					$previous_full_path = false;
					try {
						$previous_full_path = $full_path;
						$new_wanted_filename = rtrim($this->pdf_local_persistent_filepath, '/').'/'.$filename;
						$full_path = persist_pdf::save($full_path, $new_wanted_filename);
						$file_saved = true;
					} catch(Exception $e) {
						if($previous_full_path) {
							$full_path = $previous_full_path;
						}
						$file_saved = false;
					}
				}

				// Stealing the already used variable name, not touching it
				// since it's declared public and such it may be
				// depended upon from the outside
				if($this->pdf_recipients) {
					$report_sender = new Send_report_Model();
					$mail_sent = $report_sender->send($this->pdf_recipients, $full_path, $filename);
					if (PHP_SAPI == "cli") {
						echo $mail_sent;
					} elseif(request::is_ajax()) {
						return $mail_sent ? json::ok(_("Mail sent")) : json::fail(_("Mail could not be sent"));
					}
					return $mail_sent;
				}
				if(request::is_ajax() && $this->pdf_local_persistent_filepath) {
					return $file_saved ? json::ok(_("File saved")) : json::fail(_("File could not be saved"));
				}
				return true;
			}
			$this->pdf_data['content'] = $content->render();

			if ($this->create_pdf && $this->mashing) {
				return $this->pdf_data;
			} elseif ($this->mashing) {
				return $content->render();
			}

			$retval = $this->_pdf();
			if (PHP_SAPI == "cli") {
				echo $retval;
			} elseif(request::is_ajax()) {
				return $retval ? json::ok(_("Mail sent")) : json::fail(_("Mail could not be sent"));
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
		$this->pdf_local_persistent_filepath = $report_data['local_persistent_filepath'];
		$this->pdf_recipients = $report_data['recipients'];

		$request['create_pdf'] = 1;
		$request['new_report_setup'] = 1;

		$settings = i18n::unserialize($report_data['setting']);
		if(!$settings) {
			// we might not have any settings stored
			$settings = array();
		}
		unset($report_data['setting']);
		unset($report_data['objects']);
		unset($report_data['filename']);
		unset($report_data['recipients']);

		if (PHP_SAPI === "cli") {
			# set current user to the owner of the report
			# this should only be done when called through PHP CLI
			Auth::instance()->force_login($report_data[Saved_reports_Model::USERFIELD]);
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
			$l['w_page'] = _('page');
		}

		$type = $this->type;
		$filename = $this->pdf_filename;
		$save_path = $this->pdf_savepath;

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$title = isset($this->pdf_data['title']) ? $this->pdf_data['title'] : _('Ninja PDF Report');
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Ninja4Nagios');
		$pdf->SetTitle(_('Ninja PDF Report'));
		$pdf->SetSubject($title);
		$pdf->SetKeywords('Ninja, '.Kohana::config('config.product_name').', PDF, report, '.$type);


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

		if(isset($this->pdf_data['content']) && $this->pdf_data['content']) {
		       $pdf->writeHTML($this->pdf_data['content'], true, 0, true, 0);
		} else {
		       $pdf->writeHTML("<p>No data found. You seem to have created a report with only non existing objects in it.</p>", true, 0, true, 0);
		}
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
			$filename = K_PATH_CACHE.'/'.$filename;
			$send_by_mail = true;
		}

		$pdf->Output($filename, $action);

		// the local path must be specified and there must be an original pdf
		if($this->pdf_local_persistent_filepath && 'F' == $action) {
			try {
				persist_pdf::save($filename, $this->pdf_local_persistent_filepath.'/'.pathinfo($filename, PATHINFO_BASENAME));
			} catch(Exception $e) {
				// let's not do anything rational now.. we want to send the email even
				// though the local file saving business went to hell


				//if(request::is_ajax()) {
					//return json::fail($e->getMessage());
				//}

				//// @todo log failure
				//echo "<pre>";
				//var_dump(__LINE__);
				//var_dump($e->getMessage());
				//var_dump('DYING');
				//die;
			}
		}

		$mail_sent = 0;
		if ($send_by_mail) {
			$report_sender = new Send_report_Model();
			$mail_sent = $report_sender->send($this->pdf_recipients, $filename, str_replace(K_PATH_CACHE.'/', '', $filename));

			unlink($filename);
			return $mail_sent;
		}

		if(request::is_ajax()) {
			return json::ok();
		}
		return true;
	}

}
