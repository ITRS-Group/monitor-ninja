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
	const ALERT_TOTALS_HG = 4;
	const ALERT_TOTALS_HOST = 5;
	const ALERT_TOTALS_SERVICE = 6;
	const ALERT_TOTALS_SG = 7;

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
		$template = $this->template->content;

		# get all saved reports for user
		$saved_reports = Saved_reports_Model::get_saved_reports($this->type);
		$this->js_strings .= "var report_id = ".(int)$this->options['report_id'].";\n";

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
		$this->js_strings .= "var _reports_confirm_delete = '"._("Are you really sure that you would like to remove this saved report?")."';\n";
		$this->js_strings .= "var _reports_confirm_delete_schedule = \""._("Do you really want to delete this schedule?\\nThis action can't be undone.")."\";\n";
		$this->js_strings .= "var _reports_confirm_delete_warning = '"._("Please note that this is a scheduled report and if you decide to delete it, \\n" .
			"the corresponding schedule will be deleted as well.\\n\\n Are you really sure that this is what you want?")."';\n";
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

		$template->json_periods = $json_periods;
		$template->type = $this->type;
		$template->old_config_names_js = $old_config_names_js;
		$template->old_config_names = $old_config_names;
		$template->scheduled_ids = $scheduled_ids;
		$template->scheduled_periods = $scheduled_periods;
		$template->available_schedule_periods = $periods;

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

		if ($this->options['report_id']!==false) {
			$this->inline_js .= "set_report_mode('custom');\n";
			$this->inline_js .= "$('#report_mode_custom').attr('checked', true);\n";
			if ($this->options['report_type'])
				$this->inline_js .= "set_selection('".$this->options['report_type']."');\n";
			$this->inline_js .= "expand_and_populate(" . $this->options->as_json() . ");\n";
		} else {
			$this->inline_js .= "set_report_mode('standard');\n";
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
	}

	/**
	 * Generates an alert summary report
	 */
	public function generate($input=false)
	{
		$this->setup_options_obj($input);
		$this->reports_model = new Reports_Model($this->options);

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
		$saved_reports = Saved_reports_Model::get_saved_reports($this->type);

		$this->js_strings .= "var report_id = ".(int)$this->options['report_id'].";\n";

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

		// convert report period to timestamps
		if ($this->options['report_period'] == 'custom' && !empty($syear) && !empty($eyear)) {
			// cgi compatibility
			$this->options['start_time'] = mktime($shour, $smin, $ssec, $smon, $sday, $syear);
			$this->options['end_time'] = mktime($ehour, $emin, $esec, $emon, $eday, $eyear);
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
			$this->add_view("summary/" . $views[$this->options['summary_type']]);

		$content = $this->template->content;

		$this->template->content->schedules = $this->add_view('summary/schedule');
		$template = $this->template->content->schedules;
		$template->json_periods = $json_periods;
		$template->type = $this->type;
		$template->old_config_names_js = $old_config_names_js;
		$template->old_config_names = $old_config_names;
		$template->scheduled_ids = $scheduled_ids;
		$template->scheduled_periods = $scheduled_periods;
		$template->available_schedule_periods = $periods;

		$template->saved_reports = $saved_reports;

		$content->host_state_names = $this->host_state_names;
		$content->service_state_names = $this->service_state_names;

		$result = false;
		switch ($this->options['summary_type']) {
		 case self::TOP_ALERT_PRODUCERS:
			$result = $this->reports_model->top_alert_producers();
			break;

		 case self::RECENT_ALERTS:
			$result = $this->reports_model->recent_alerts();
			break;

		 case self::ALERT_TOTALS:
		 case self::ALERT_TOTALS_HG:
		 case self::ALERT_TOTALS_SG:
		 case self::ALERT_TOTALS_HOST:
			$result = $this->reports_model->alert_totals();
			break;

		case self::ALERT_TOTALS_SERVICE:
			$this->options['service_description'] = $this->_populate_services();
			$result = $this->reports_model->alert_totals();
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
		$content->completion_time = $this->reports_model->completion_time;
		$this->template->title = _("Reporting » Alert summary » Report");
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
}
