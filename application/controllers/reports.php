<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Reports controller
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
class Reports_Controller extends Authenticated_Controller
{
	public static $colors = array(
		'green' => '#aade53',
		'yellow' => '#ffd92f',
		'orange' => '#ff9d08',
		'red' 	=> '#f7261b',
		'grey' 	=> '#a19e95',
		'lightblue' => '#EAF0F2', # actual color is #ddeceb, but it is hardly visible
		'white' => '#ffffff'
	);

	public static $options = array(
		'rpttimeperiod' => 'report_timeperiod',
		'scheduleddowntimeasuptime' => 'scheduled_downtime_as_uptime',
		'assumestatesduringnotrunning' => 'assume_states_during_not_running',
		'includesoftstates' => 'include_soft_states',
		'assumeinitialstates' => 'assume_initial_states',
		'cluster_mode' => 'cluster_mode',
		'use_average' =>'use_average',
		'host_filter_status_up' => 'host_filter_status_up',
		'host_filter_status_down' => 'host_filter_status_down',
		'host_filter_status_unreachable' => 'host_filter_status_unreachable',
		'host_filter_status_undetermined' => 'host_filter_status_undetermined',
		'service_filter_status_ok' => 'service_filter_status_ok',
		'service_filter_status_warning' => 'service_filter_status_warning',
		'service_filter_status_unknown' => 'service_filter_status_unknown',
		'service_filter_status_critical' => 'service_filter_status_critical',
		'service_filter_status_pending' => 'service_filter_status_pending',
	);

	public static $dep_vars = array(
		'assumeinitialstates' => array(
			'initialassumedhoststate' => 'initial_assumed_host_state',
			'initialassumedservicestate' => 'initial_assumed_service_state'
		)
	);

	public $setup_keys = array(
		'report_name',
		'info',
		'rpttimeperiod',
		'report_period',
		'start_time',
		'end_time',
		'report_type',
		'initialassumedhoststate',
		'initialassumedservicestate',
		'assumeinitialstates',
		'scheduleddowntimeasuptime',
		'assumestatesduringnotrunning',
		'includesoftstates',
		'use_average',
		'cluster_mode',
		'use_alias',
		'host_filter_status',
		'service_filter_status'
	);

	public static $map_type_field = array(
		'hosts' => "host_name",
		'services' => "service_description",
		'hostgroups' => "hostgroup",
		'servicegroups' => "servicegroup"
	);

	private $err_msg = '';

	public static $initial_assumed_host_states = array(
	   -1 => 'Current state',
	   -2 => 'Unspecified',
	   -3 => 'First Real State',
	    0 => 'Host Up',
	    1 => 'Host Down',
	    2 => 'Host Unreachable',
	);

	public static $initial_assumed_service_states = array(
	   -1 => 'Current state',
	   -2 => 'Unspecified',
	   -3 => 'First Real State',
	    0 => 'Service Ok',
	    1 => 'Service Warning',
	    2 => 'Service Critical',
	    3 => 'Service Unknown',
	);

	public static $sla_field_names = array(
		'hosts' => 'PERCENT_TOTAL_TIME_UP',
		'hostgroups' => 'PERCENT_TOTAL_TIME_UP',
		'services' => 'PERCENT_TOTAL_TIME_OK',
		'servicegroups' => 'PERCENT_TOTAL_TIME_OK'
	);

	public $template_prefix = false;

	private $state_values = false;

	private $initialassumedhoststate = -1;
	private $initialassumedservicestate = -1;

	private $abbr_month_names = false;
	private $month_names = false;
	private $day_names = false;
	private $abbr_day_names = false;
	private $first_day_of_week = 1;

	# default values
	private $assume_state_retention = true;
	private $assume_states_during_not_running = true;
	private $include_soft_states = false;
	private $cluster_mode = false;
	private $scheduled_downtime_as_uptime = false;
	private $csv_output = false;
	private $create_pdf = false;
	public $pdf_data = false;
	public $pdf_filename = false;
	public $pdf_local_persistent_filepath = false;
	public $pdf_recipients = false; # when sending reports by email
	private $schedule_id = false;

	private $assume_initial_states = true;
	private $initial_assumed_host_state = -3;
	private $initial_assumed_service_state = -3;

	private $host_filter_status_up = 1;
	private $host_filter_status_down = 1;
	private $host_filter_status_unreachable = 1;
	private $host_filter_status_undetermined = 1;
	private $service_filter_status_ok = 1;
	private $service_filter_status_warning = 1;
	private $service_filter_status_unknown = 1;
	private $service_filter_status_critical = 1;
	private $service_filter_status_pending = 1;

	private $use_average = 0;
	private $use_alias = 0;

	private $type = false;
	public $report_id = false;
	public $data_arr = false;
	private $report_type = false;
	private $object_varname = false;

	private $status_link = "status/host/";
	private $trend_link = "trends/generate";
	private $histogram_link = "histogram/host";
	private $history_link = "showlog/alert_history";
	private $notifications_link = "notifications/host";

	public $reports_model = false;
	private $trends_graph_model = false;
	public $start_date = false;
	public $end_date = false;
	public $mashing = false;
	public $report_options = false;
	private $in_months = false;
	public $extra_template_data = false;

	public function __construct()
	{
		parent::__construct();

		$this->reports_model = new Reports_Model();
		$this->trends_graph_model = new Trends_graph_Model();

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

		$this->state_values = array(
			'OK' => $this->translate->_('OK'),
			'WARNING' => $this->translate->_('WARNING'),
			'UNKNOWN' => $this->translate->_('UNKNOWN'),
			'CRITICAL' => $this->translate->_('CRITICAL'),
			'PENDING' => $this->translate->_('PENDING'),
			'UP' => $this->translate->_('UP'),
			'DOWN' => $this->translate->_('DOWN'),
			'UNREACHABLE' => $this->translate->_('UNREACHABLE')
		);

		$this->scheduled_downtime_options = array(
			0 => $this->translate->_('Actual state'),
			1 => $this->translate->_('Uptime'),
			2 => $this->translate->_('Uptime, with difference')
		);
	}

	public function add_view($view=false) {
		$ret = parent::add_view($view);
		if (is_array($this->extra_template_data)) {
			foreach ($this->extra_template_data as $key => $val) {
				$ret->$key = $val;
			}
		}
		return $ret;
	}


	/**
	*	Display report selection/setup page
	*/
	public function index($type='avail')
	{
		# check if we have all required parts installed
		if (!$this->reports_model->_self_check()) {
			url::redirect(Router::$controller.'/invalid_setup');
		}

		if ($this->mashing) {
			$this->auto_render=false;
		}
		$this->template->disable_refresh = true;

		# reset current_report_params and main_report_params
		# just to be sure they're not left behind
		Session::instance()->set('current_report_params', null);
		Session::instance()->set('main_report_params', null);

		# 	The following basically means:
		# 	Fetch the input variable 'type' from
		#	either $_GET or $_POST and use default
		# 	method param if nothing found
		$this->type = urldecode(
			$this->input->post(
				'type', $this->input->get(
					'type', $type)
					)
				);

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

		$cluster_mode = arr::search($_REQUEST, 'cluster_mode', $this->cluster_mode);
		$cluster_mode_checked =	!empty($cluster_mode) ? 'checked="checked"' : '';
		$assume_initial_states_checked =
			arr::search($_REQUEST, 'assumeinitialstates', $this->assume_initial_states) ? 'checked="checked"' : '';
		$assume_states_during_not_running_checked =
			arr::search($_REQUEST, 'assumestatesduringnotrunning', $this->assume_states_during_not_running) ? 'checked="checked"' : '';

		$host_filter_status_up_checked = arr::search($_REQUEST, 'host_filter_status[0]', $this->host_filter_status_up) ? 'checked="checked"' : '';
		$host_filter_status_down_checked = arr::search($_REQUEST, 'host_filter_status[1]', $this->host_filter_status_down) ? 'checked="checked"' : '';
		$host_filter_status_unreachable_checked =	arr::search($_REQUEST, 'host_filter_status[2]', $this->host_filter_status_unreachable) ? 'checked="checked"' : '';
		$host_filter_status_undetermined_checked = arr::search($_REQUEST, 'host_filter_status[3]', $this->host_filter_status_undetermined) ? 'checked="checked"' : '';
		$service_filter_status_ok_checked = arr::search($_REQUEST, 'service_filter_status[0]', $this->service_filter_status_ok) ? 'checked="checked"' : '';
		$service_filter_status_warning_checked = arr::search($_REQUEST, 'service_filter_status[1]', $this->service_filter_status_warning) ? 'checked="checked"' : '';
		$service_filter_status_unknown_checked = arr::search($_REQUEST, 'service_filter_status[2]', $this->service_filter_status_unknown) ? 'checked="checked"' : '';
		$service_filter_status_critical_checked = arr::search($_REQUEST, 'service_filter_status[3]', $this->service_filter_status_critical) ? 'checked="checked"' : '';
		$service_filter_status_pending_checked = arr::search($_REQUEST, 'service_filter_status[4]', $this->service_filter_status_pending) ? 'checked="checked"' : '';

		$include_soft_states_checked = '';//'checked="checked"';
		$old_config_names = Saved_reports_Model::get_all_report_names($this->type);
		$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", $old_config_names)."');";
		$this->report_id =
			arr::search($_REQUEST, 'report_id', false);
		$initial_assumed_host_state_selected =
			arr::search($_REQUEST, 'initialassumedhoststate', $this->initial_assumed_host_state);
		$initial_assumed_service_state_selected =
			arr::search($_REQUEST, 'initialassumedservicestate', $this->initial_assumed_service_state);
		$csv_output_checked =
			arr::search($_REQUEST, 'csvoutput', $this->csv_output) ? 'checked="checked"' : '';
		$use_alias  =
			arr::search($_REQUEST, 'use_alias', $this->use_alias);
		$use_alias_checked = $use_alias ? 'checked="checked"' : '';

		$use_average_yes_selected = $use_average_no_selected = '';
		if(arr::search($_REQUEST, 'use_average', $this->use_average) == 1)
			$use_average_yes_selected = 'selected="selected"';
		else
			$use_average_no_selected = 'selected="selected"';


		$type_str = $this->type == 'avail'
			? $this->translate->_('availability')
			: $this->translate->_('SLA');
		#html2ps::instance();

		$this->template->content = $this->add_view('reports/'.$this->template_prefix.'setup');
		$template = $this->template->content;
		#$this->template->content->noheader = $noheader;

		# we should set the required js-files
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min';

		$this->xtra_js[] = 'application/media/js/jquery.datePicker';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker';
		$this->xtra_js[] = $this->add_path('reports/js/json');
		$this->xtra_js[] = $this->add_path('reports/js/move_options');
		$this->xtra_js[] = $this->add_path('reports/js/common');
		$this->xtra_js[] = $this->add_path('reports/js/reports');

		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox';
		$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->xtra_css[] = $this->add_path('css/default/reports');
		$this->template->css_header->css = $this->xtra_css;

		$t = $this->translate;

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
		$scheduled_info = false;
		$report_info = false;
		$json_report_info = false;
		if ($this->report_id) {
			$report_info = Saved_reports_Model::get_report_info($this->type, $this->report_id);
			if ($report_info) {
				if ($this->type == 'sla') {
					if (isset($report_info['start_time'])) {
						$report_info['start_year'] = date('Y', $report_info['start_time']);
						$report_info['start_month'] = date('m', $report_info['start_time']);
					}
					if (isset($report_info['end_time'])) {
						$report_info['end_year'] = date('Y', $report_info['end_time']);
						$report_info['end_month'] = date('m', $report_info['end_time']-1);
					}
				}
				$json_report_info = json::encode($report_info);
			}
			$scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->report_id);
			$template->is_scheduled = empty($scheduled_info) ? false: true;

			if(isset($report_info["assumeinitialstates"]) && $report_info["assumeinitialstates"] != 0)
				$assume_initial_states_checked = 'checked="checked"';
			else
				$assume_initial_states_checked = '';

			if($report_info["initialassumedhoststate"] != 0)
				$initial_assumed_host_state_selected = 'checked="checked"';
			else
				$initial_assumed_host_state_selected = '';

			if($report_info["cluster_mode"] != 0)
				$cluster_mode_checked = 'checked="checked"';
			else
				$cluster_mode_checked = '';

			if($report_info["initialassumedservicestate"] != 0)
				$initial_assumed_service_state_selected = 'checked="checked"';
			else
				$initial_assumed_service_state_selected = '';

			if($report_info["assumestatesduringnotrunning"] != 0)
				$assume_states_during_not_running_checked = 'checked="checked"';
			else
				$assume_states_during_not_running_checked = '';

			if (!empty($report_info['host_filter_status'])) {
				$hostfilterstatus = i18n::unserialize($report_info['host_filter_status']);
				$host_filter_status_up_checked = ($hostfilterstatus['up'] != 0) ? 'checked="checked"' : '';
				$host_filter_status_down_checked = ($hostfilterstatus['down'] != 0) ? 'checked="checked"' : '';
				$host_filter_status_unreachable_checked = ($hostfilterstatus['unreachable'] != 0) ? 'checked="checked"' : '';
				$host_filter_status_undetermined_checked = ($hostfilterstatus['undetermined'] != 0) ? 'checked="checked"' : '';
			}
			else {
				$host_filter_status_up_checked = 'checked="checked"';
				$host_filter_status_down_checked = 'checked="checked"';
				$host_filter_status_unreachable_checked = 'checked="checked"';
				$host_filter_status_undetermined_checked = 'checked="checked"';
			}
			if (!empty($report_info['service_filter_status'])) {
				$servicefilterstatus = i18n::unserialize($report_info['service_filter_status']);
				$service_filter_status_ok_checked = ($servicefilterstatus['ok'] != 0) ? 'checked="checked"' : '';
				$service_filter_status_warning_checked = ($servicefilterstatus['warning'] != 0) ? 'checked="checked"' : '';
				$service_filter_status_unknown_checked = ($servicefilterstatus['unknown'] != 0) ? 'checked="checked"' : '';
				$service_filter_status_critical_checked = ($servicefilterstatus['critical'] != 0) ? 'checked="checked"' : '';
				$service_filter_status_pending_checked = ($servicefilterstatus['pending'] != 0) ? 'checked="checked"' : '';
			}
			else {
				$service_filter_status_ok_checked = 'checked="checked"';
				$service_filter_status_warning_checked = 'checked="checked"';
				$service_filter_status_unknown_checked = 'checked="checked"';
				$service_filter_status_critical_checked = 'checked="checked"';
				$service_filter_status_pending_checked = 'checked="checked"';
			}

			if($report_info["includesoftstates"] != 0)
				$include_soft_states_checked = 'checked="checked"';
			else
				$include_soft_states_checked = '';

			if(isset($report_info["use_average"]) && $report_info["use_average"] != 0) {
				$use_average_no_selected = '';
				$use_average_yes_selected = "selected='selected'";
			} else {
				$use_average_no_selected = "selected='selected'";
				$use_average_yes_selected = '';
			}

			$use_alias_checked = (isset($report_info["use_alias"]) && $report_info["use_alias"] != 0) ? 'checked="checked"' : '';

			$report_period = $report_info["report_period"];
		}
		$template->json_periods = $json_periods;
		$template->scheduled_info = $scheduled_info;
		$scheduled_label = $t->_('Scheduled');

		$label_avail = $this->translate->_('Availability');
		$template->label_avail = $label_avail;

		$label_sla = $t->_('SLA');
		$template->label_sla = $label_sla;
		$label_switch_to = $this->translate->_('Switch to');
		$template->label_switch_to = $label_switch_to;

		$label_report = $this->translate->_('report');
		$template->label_report = $label_report;

		# fetch users date format in PHP style so we can use it
		# in date() below
		$date_format = $this->_get_date_format(true);

		$js_month_names = "Date.monthNames = ".json::encode($this->month_names).";";
		$js_abbr_month_names = 'Date.abbrMonthNames = '.json::encode($this->abbr_month_names).';';
		$js_day_names = 'Date.dayNames = '.json::encode($this->day_names).';';
		$js_abbr_day_names = 'Date.abbrDayNames = '.json::encode($this->abbr_day_names).';';
		$js_day_of_week = 'Date.firstDayOfWeek = '.$this->first_day_of_week.';';
		$js_date_format = "Date.format = '".$this->_get_date_format()."';";
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
		$this->js_strings .= $js_date_format."\n";
		$this->inline_js .= $js_start_date."\n";

		if (arr::search($_REQUEST, 'show_schedules') !== false) {
			$this->js_strings .= "var _show_schedules = true;\n";
		}

		if ($this->report_id) {
			$this->inline_js .= "$('#assumed_host_state').hide();
			$('#assumed_service_state').hide();\n";
		}
		if (!$report_info) {
			$this->inline_js .= "set_selection(document.getElementsByName('report_type').item(0).value);\n";
		} else {
			$this->inline_js .= "expand_and_populate(" . $json_report_info . ");\n";
		}

		if($assume_initial_states_checked) {
			$this->inline_js .= "show_state_options(true);\n";
			$this->inline_js .= "toggle_label_weight(true, 'assume_initial');\n";
		}
		if($include_soft_states_checked)
			$this->inline_js .= "toggle_label_weight(true, 'include_softstates');\n";
		if($assume_states_during_not_running_checked)
			$this->inline_js .= "toggle_label_weight(true, 'assume_progdown');\n";
		if($csv_output_checked)
			$this->inline_js .= "toggle_label_weight(true, 'csvout');\n";
		$this->inline_js .= "invalid_report_names = ".$old_config_names_js .";\n";
		$this->inline_js .= "uncheck('save_report_settings', 'report_form');\n";
		$this->inline_js .= "$('#report_save_information').hide();\n";

		if (!is_null($del_ok) && !is_null($del_result)) {
			$this->inline_js .= "show_message('".$del_result."', '".$del_msg."');\n";
		}

		$this->js_strings .= "var assumeinitialstates = '".$this->assume_initial_states."';\n";
		$this->js_strings .= "var _edit_str = '".$t->_('edit')."';\n";
		$this->js_strings .= "var _hide_str = '".$t->_('hide')."';\n";
		$this->js_strings .= "var _scheduled_label = '".$scheduled_label."';\n";
		$this->js_strings .= "var _label_avail = '".$label_avail."';\n";
		$this->js_strings .= "var _label_sla = '".$label_sla."';\n";
		$this->js_strings .= "var _label_switch_to = '".$label_switch_to."';\n";
		$this->js_strings .= "var _label_report = '".$label_report."';\n";
		$this->js_strings .= "var nr_of_scheduled_instances = ". (!empty($scheduled_info) ? sizeof($scheduled_info) : 0).";\n";
		$this->js_strings .= "var _reports_edit_information = '".$t->_('Double click to edit')."';\n";
		$this->js_strings .= "var _reports_schedule_deleted = '".$t->_('Your schedule has been deleted')."';\n";
		$this->js_strings .= "var _reports_propagate = '".$t->_('Would you like to propagate this value to all months')."';\n";
		$this->js_strings .= "var _reports_propagate_remove = '".$t->_("Would you like to remove all values from all months")."';\n";
		$this->js_strings .= "var _schedule_change_filename = \"".$t->_('Would you like to change the filename based on your selections?')."\";\n";

		$this->js_strings .= reports::js_strings();

		$this->js_strings .= "var _reports_name_empty = '".$t->_("Please give your report a meaningful name.")."';\n";
		$this->js_strings .= "var _reports_error_name_exists = '".sprintf($t->_("You have entered a name for your report that already exists. %sPlease select a new name"), '<br />')."';\n";
		$this->js_strings .= "var _reports_error_name_exists_replace = \"".$t->_("The entered name already exists. Press 'Ok' to replace the entry with this name")."\";\n";
		$this->js_strings .= "var _reports_missing_objects = \"".$t->_("Some items in your saved report doesn't exist anymore and has been removed")."\";\n";
		$this->js_strings .= "var _reports_missing_objects_pleaseremove = '".$t->_('Please modify the objects to include in your report below and then save it.')."';\n";
		$this->js_strings .= "var _reports_confirm_delete = '".$t->_("Are you really sure that you would like to remove this saved report?")."';\n";
		$this->js_strings .= "var _reports_confirm_delete_schedule = \"".sprintf($t->_("Do you really want to delete this schedule?%sThis action can't be undone."), '\n')."\";\n";
		$this->js_strings .= "var _reports_confirm_delete_warning = '".sprintf($t->_("Please note that this is a scheduled report and if you decide to delete it, %s" .
			"the corresponding schedule will be deleted as well.%s Are you really sure that this is what you want?"), '\n', '\n\n')."';\n";

		$this->template->inline_js = $this->inline_js;

		$template->type = $this->type;
		$template->scheduled_label = $scheduled_label;
		$template->title_label = $t->_('schedule');
		$template->label_select = $t->_('Select');
		$template->label_new = $t->_('New');
		$template->new_saved_title = sprintf($t->_('Create new saved %s report'), $type_str);
		$template->label_delete = $t->_('Delete report');
		$template->label_dblclick = $t->_('Double click to edit');
		$template->label_sch_interval = $t->_('Interval');
		$template->label_sch_recipients = $t->_('Recipients');
		$template->label_sch_filename = $t->_('Filename');
		$template->label_sch_description = $t->_('Description');
		$template->label_create_new = $this->type == 'avail' ? $t->_('Availability report') : $t->_('SLA report');
		$template->label_saved_reports = $t->_('Saved reports');
		$template->label_hostgroups = $t->_('Hostgroups');
		$template->label_hosts = $t->_('Hosts');
		$template->label_servicegroups = $t->_('Servicegroups');
		$template->label_services = $t->_('Services');
		$template->label_available = $t->_('Available');
		$template->label_selected = $t->_('Selected');
		$template->label_report_period = $t->_('Reporting period');
		$template->label_today = $t->_('Today');
		$template->label_last24 = $t->_('Last 24 Hours');
		$template->label_yesterday = $t->_('Yesterday');
		$template->label_thisweek = $t->_('This Week');
		$template->label_last7days = $t->_('Last 7 Days');
		$template->label_lastweek = $t->_('Last Week');
		$template->label_thismonth = $t->_('This Month');
		$template->label_last31days = $t->_('Last 31 Days');
		$template->label_lastmonth = $t->_('Last Month');
		$template->label_last3months = $t->_('Last 3 Months');
		$template->label_last6months = $t->_('Last 6 months');
		$template->label_last12months = $t->_('Last 12 months');
		$template->label_lastquarter = $t->_('Last Quarter');
		$template->label_thisyear = $t->_('This Year');
		$template->label_lastyear = $t->_('Last Year');
		$template->label_custom_period = $t->_('CUSTOM REPORT PERIOD');
		$template->label_startdate = $t->_('Start date');
		$template->label_start_year = $t->_('Start year');
		$template->label_start_month = $t->_('Start month');
		$template->label_end_year = $t->_('End year');
		$template->label_end_month = $t->_('End month');
		$template->label_enddate = $t->_('End date');
		$template->label_startdate_selector = $t->_('Date Start selector');
		$template->label_enddate_selector = $t->_('Date End selector');
		$template->label_click_calendar = $t->_('Click calendar to select date');
		$template->label_rpttimeperiod = $t->_('Report time period');
		$template->label_initialassumedhoststate = $t->_('First assumed host state');
		$template->label_scheduleddowntimeasuptime = $t->_('Count scheduled downtime as');
		$template->scheduleddowntimeasuptime_options = $this->scheduled_downtime_options;
		$template->scheduleddowntimeasuptime_selected = $this->scheduled_downtime_as_uptime;
		$template->label_initialassumedservicestate = $t->_('First assumed service state');
		$template->label_assumestatesduringnotrunning = $t->_('Assume states during program downtime');
		$template->label_assumeinitialstates = $t->_('Assume initial states');
		$template->label_cluster_mode = $t->_('Cluster mode');
		$template->label_propagate = $t->_('Click to propagate this value to all months');
		$template->label_enter_sla = $t->_('Enter SLA');
		$template->reporting_periods = $this->_get_reporting_periods();
		$template->cluster_mode_checked = $cluster_mode_checked;
		$template->assume_initial_states_checked = $assume_initial_states_checked;
		$template->initial_assumed_host_states = self::$initial_assumed_host_states;
		$template->initial_assumed_service_states = self::$initial_assumed_service_states;
		$template->assume_states_during_not_running_checked = $assume_states_during_not_running_checked;
		$template->host_filter_status_up_checked = $host_filter_status_up_checked;
		$template->host_filter_status_down_checked = $host_filter_status_down_checked;
		$template->host_filter_status_unreachable_checked = $host_filter_status_unreachable_checked;
		$template->host_filter_status_undetermined_checked = $host_filter_status_undetermined_checked;
		$template->service_filter_status_ok_checked = $service_filter_status_ok_checked;
		$template->service_filter_status_warning_checked = $service_filter_status_warning_checked;
		$template->service_filter_status_unknown_checked = $service_filter_status_unknown_checked;
		$template->service_filter_status_critical_checked = $service_filter_status_critical_checked;
		$template->service_filter_status_pending_checked = $service_filter_status_pending_checked;
		$template->include_soft_states_checked = $include_soft_states_checked;
		$template->label_includesoftstates = $t->_('Include soft states');
		$template->label_sla_calc_method = $t->_('SLA calculation method');
		$template->label_avg_sla = $t->_('Group availability (SLA)');
		$template->label_avg = $t->_('Average');
		$template->label_use_alias = $t->_('Use alias');
		$template->label_csvoutput = $t->_('Output in CSV format');
		$template->label_create_report = $t->_('Create report');
		$template->label_save_report = $t->_('Save report');
		$template->use_alias_checked = $use_alias_checked;
		$template->use_average_yes_selected = $use_average_yes_selected;
		$template->use_average_no_selected = $use_average_no_selected;
		$template->initial_assumed_host_state_selected = $initial_assumed_host_state_selected;
		$template->initial_assumed_service_state_selected = $initial_assumed_service_state_selected;

		$template->csv_output_checked = $csv_output_checked;
		$template->months = $this->abbr_month_names;
		$template->is_scheduled_report = $t->_('This is a scheduled report');
		$edit_str = $t->_('edit');
		$template->edit_str = $edit_str;
		$template->old_avail_link = config::get_cgi_cfg_key('url_html_path').'/cgi-bin/avail.cgi';
		$template->is_scheduled_clickstr = sprintf($t->_("This report has been scheduled. Click on '[%s]' to change settings"), $edit_str);

		if ($report_info) {
			$date_format = $this->_get_date_format(true);
			# due to an old bug, some reports could have been saved
			# with the timestamp being the year which would result in
			# dates being printed as 1970-01-01
			# Checking that it is > the timestamp for 1980-01-01 (315525600)
			# will make us being possible to handle this anyway
			$ts_check = 315525600; # 1980-01-01
			$template->start_date = !empty($report_info['start_time']) && $report_info['start_time'] > $ts_check ? date($date_format, $report_info['start_time']) : '';
			$template->start_time = !empty($report_info['start_time']) && $report_info['start_time'] > $ts_check ? date('H:i', $report_info['start_time']) : '08:00';
			$template->end_date = !empty($report_info['end_time']) && $report_info['end_time'] > $ts_check ? date($date_format, $report_info['end_time']) : '';
			$template->end_time = !empty($report_info['end_time']) && $report_info['end_time'] > $ts_check ? date('H:i', $report_info['end_time']) : '09:00';
		}

		$template->report_id = $this->report_id;
		$template->report_info = $report_info;
		$template->old_config_names_js = $old_config_names_js;
		$template->old_config_names = $old_config_names;
		$template->scheduled_ids = $scheduled_ids;
		$template->scheduled_periods = $scheduled_periods;
		$template->saved_reports = $saved_reports;

		# decide what report periods to print
		$report_period_strings = $this->_report_period_strings($this->type);

		$report_periods = $report_period_strings["report_period_strings"];
		$report_periods['custom'] = "* " . $template->label_custom_period . " *";
		$template->report_periods = $report_periods;
		$template->selected = $report_period_strings["selected"];

		$new_schedule = $this->add_view('reports/'.$this->template_prefix.'new_schedule');
		$new_schedule->label_new_schedule = $t->_('Add new schedule');
		$new_schedule->available_schedule_periods = $periods;
		$new_schedule->label_interval = $t->_('Report Interval');
		$new_schedule->label_recipients = $t->_('Recipients');
		$new_schedule->label_filename = $t->_('Filename');
		$new_schedule->label_description = $t->_('Description');
		$new_schedule->label_save = $t->_('Save');
		$new_schedule->label_clear = $t->_('Clear');

		# we currently only have avail and SLA reports so hard-coding
		# this somewhat here shouldn't be a big issue.
		# Extend switch below if we add more schedulable reports.
		$defined_report_types_res = Scheduled_reports_Model::get_all_report_types();
		$defined_report_types = false;
		$report_types = false;
		if ($defined_report_types_res !== false) {
			foreach ($defined_report_types_res as $rpt_type) {
				$report_types[$rpt_type->id] = $rpt_type->identifier; # needed for javascript json
				switch ($rpt_type->identifier) {
					case 'avail':
						$defined_report_types[$rpt_type->identifier] = $t->_('Availability Report');
						break;
					case 'sla':
						$defined_report_types[$rpt_type->identifier] = $t->_('SLA Report');
						break;
					case 'summary':
						$defined_report_types[$rpt_type->identifier] = $t->_('Alert Summary Report');
						break;
				}
			}
		}

		$new_schedule->defined_report_types = $defined_report_types;
		$new_schedule->label_report_type = $t->_('Select report type');
		$avail_reports = Saved_reports_Model::get_saved_reports('avail');
		$sla_reports = Saved_reports_Model::get_saved_reports('sla');
		$summary_reports = Saved_reports_Model::get_saved_reports('summary');

		$new_schedule->saved_reports = $avail_reports;
		$new_schedule->label_select_report = $t->_('Select report');
		$new_schedule->label_select_saved_report = $t->_('Select saved report');

		$template->available_schedules = $this->add_view('reports/'.$this->template_prefix.'schedules');
		$available_schedules = $template->available_schedules;
		$available_schedules->label_no_schedules = $t->_('There are no scheduled reports');
		$available_schedules->avail_header = $t->_('Availability Reports');
		$available_schedules->sla_header = $t->_('SLA Reports');
		$available_schedules->summary_header = $t->_('Alert Summary Reports');

		# fetch ALL schedules (avail + SLA + Alert Summary)
		$available_schedules->avail_schedules = Scheduled_reports_Model::get_scheduled_reports('avail');
		$available_schedules->sla_schedules = Scheduled_reports_Model::get_scheduled_reports('sla');
		$available_schedules->summary_schedules = Scheduled_reports_Model::get_scheduled_reports('summary');

		# re-use parent template's translations
		$available_schedules->label_sch_interval = $template->label_sch_interval;
		$available_schedules->label_sch_name = $t->_('Report');
		$available_schedules->label_sch_recipients = $template->label_sch_recipients;
		$available_schedules->label_sch_filename = $template->label_sch_filename;
		$available_schedules->label_sch_description = $template->label_sch_description;
		$available_schedules->label_dblclick = $template->label_dblclick;

		# add new schedule template to available_schedules template
		$available_schedules->new_schedule = $new_schedule;

		$available_schedules->avail_reports = $avail_reports;
		$available_schedules->sla_reports = $sla_reports;
		$available_schedules->summary_reports = $summary_reports;

		# we need some data available as json for javascript
		$avail_reports_arr = false;
		foreach ($avail_reports as $rep) {
			$avail_reports_arr[$rep->id] = $rep->report_name;
		}

		$summary_reports_arr = false;
		foreach ($summary_reports as $rep) {
			$summary_reports_arr[$rep->id] = $rep->report_name;
		}

		$sla_reports_arr = false;
		foreach ($sla_reports as $rep) {
			$sla_reports_arr[$rep->id] = $rep->sla_name;
		}

		$this->js_strings .= "var _report_types_json = '(".json::encode($report_types).")';\n";
		$this->js_strings .= "var _saved_avail_reports = '(".json::encode($avail_reports_arr).")';\n";
		$this->js_strings .= "var _saved_sla_reports = '(".json::encode($sla_reports_arr).")';\n";
		$this->js_strings .= "var _saved_summary_reports = '(".json::encode($summary_reports_arr).")';\n";
		$this->js_strings .= "var _reports_success = '".$t->_('Success')."';\n";
		$this->js_strings .= "var _reports_error = '".$t->_('Error')."';\n";
		$this->js_strings .= "var _reports_schedule_error = '".$t->_('An error occurred when saving scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_send_error = '".$t->_('An error occurred when trying to send the scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_update_ok = '".$t->_('Your schedule has been successfully updated')."';\n";
		$this->js_strings .= "var _reports_schedule_send_ok = '".$t->_('Your report was successfully sent')."';\n";
		$this->js_strings .= "var _reports_schedule_create_ok = '".$t->_('Your schedule has been successfully created')."';\n";
		$this->js_strings .= "var _reports_fatal_err_str = '".$t->_('It is not possible to schedule this report since some vital information is missing.')."';\n";

		$this->js_strings .= "var _reports_no_sla_str = '".$t->_('Please enter at least one SLA value')."';\n";
		$this->js_strings .= "var _reports_sla_err_str = '".$t->_('Please check SLA values in fields marked red below and try again')."';\n";

		$this->template->js_strings = $this->js_strings;

		$this->template->title = $this->translate->_('Reporting » ').($this->type == 'avail' ? $t->_('Availability Report') : $t->_('SLA Report')).(' » Setup');

		if ($this->mashing) {
			return $template->render();
		}
	}

	/**
 	 * Generate (availability) report from parameters set in index()
	 *
	 * @param string $type = "avail"
	 * @param int $schedule_id = false
	 * @param array $input = false
	 */
	public function generate($type='avail', $schedule_id=false, $input=false)
	{

		# check if we have all required parts installed
		if (!$this->reports_model->_self_check()) {
			url::redirect(Router::$controller.'/invalid_setup');
		}

		if (!empty($input) && is_array($input)) {
			$_REQUEST = $input;
		}

		$this->template->disable_refresh = true;

		if (!$this->create_pdf) {
			$this->_stash_params();
		}

		# 	Fetch the input variable 'type' from
		#	either $_GET or $_POST and use default
		# 	method param if nothing found
		$this->type = urldecode(
			$this->input->post(
				'type', $this->input->get('type', $type)
			)
		);

		$regexp = urldecode(
			$this->input->post(
				'regexp', $this->input->get('regexp', false)
			)
		);

		$t = $this->translate;

		$this->schedule_id = arr::search($_REQUEST, 'schedule_id', $schedule_id);

		# handle direct link from other page
		if (!arr::search($_REQUEST, 'report_period') && ! arr::search($_REQUEST, 'timeperiod')) {
			$_REQUEST['report_period'] = 'last24hours';
			$_REQUEST['assumeinitialstates'] = 1;
		}

		# Handle call from cron or GUI to generate PDF report and send by email
		#
		# NOTE:
		# Passing a schedule_id to this method will ignore all other data passed
		# in $_REQUEST as data from _scheduled_report() will overwrite it
		if ($this->schedule_id !== false) {
			$_REQUEST = $this->_scheduled_report();
		}

		$this->report_id = arr::search($_REQUEST, 'saved_report_id', $this->report_id);
		$this->create_pdf = arr::search($_REQUEST, 'create_pdf');

		if ($this->create_pdf || $this->mashing) {
			$this->auto_render=false;
		}

		$in_host = arr::search($_REQUEST, 'host', false);
		if ($in_host === false)
			$in_host = arr::search($_REQUEST, 'host_name', false);

		$in_service = arr::search($_REQUEST, 'service', array());
		if (empty($in_service))
			$in_service = arr::search($_REQUEST, 'service_description', array());

		$in_hostgroup 		= arr::search($_REQUEST, 'hostgroup', array());
		$in_servicegroup	= arr::search($_REQUEST, 'servicegroup', array());

		$use_average_selected = arr::search($_REQUEST, 'use_average', $this->use_average);

		$report_options = false;
		foreach ($this->setup_keys as $k)	$report_options[$k] = false;

		if ($this->type == 'sla') {
			// take care of start_year, end_year etc
			$start_year = arr::search($_REQUEST, 'start_year');
			$start_month = arr::search($_REQUEST, 'start_month');
			$end_year = arr::search($_REQUEST, 'end_year');
			$end_month = arr::search($_REQUEST, 'end_month');
			if ($start_year && $start_month) {
				$_REQUEST['start_time'] = "$start_year-$start_month-01";
			}
			if ($end_year && $end_month) {
				$tmp_end_time = mktime(0, 0, 0, $end_month+1, 1, $end_year);
				$_REQUEST['end_time'] = date('Y-m-d', $tmp_end_time);
			}
		}

		// store all variables in array for later use
		foreach ($_REQUEST as $key => $value) {
			if (in_array($key, $this->setup_keys)) {
				if ($key == 'host_filter_status') {
					if ($value == NULL) { // for old reports without host_filter_status values defined
						$report_options[$key] = false;
						$host_filter_status = false;
					}
					elseif (!is_array($value)) { // if already serialized
						$report_options[$key] = $value;
						$host_filter_status = i18n::unserialize($value);
					}
					else {
						$report_options[$key] = serialize(self::_create_filter_array($value, 'host'));
						$host_filter_status = self::_create_filter_array($value,'host' );
					}
				}
				elseif ($key == 'service_filter_status') {
					if ($value == NULL) { // for old reports without service_filter_status values defined
						$report_options[$key] = false;
						$service_filter_status = false;
					}
					elseif (!is_array($value)) { // if already serialized
						$report_options[$key] = $value;
						$service_filter_status = i18n::unserialize($value);
					}
					else {
						$report_options[$key] = serialize(self::_create_filter_array($value,'service'));
						$service_filter_status = self::_create_filter_array($value,'service');
					}
				}
				else {
					$report_options[$key] = $value;
				}
				if (arr::search($_REQUEST, 'report_period') == 'custom' && ($key=='start_time' || $key=='end_time')) {
					if (is_numeric($value)) {
						$_REQUEST[$key] = date("Y-m-d H:i", $value);
					}
				}

			}  else {
				if ($this->type == 'sla' && preg_match('/^month/', trim($key))) {
					$id = (int)str_replace('month_', '', $key);
					if (trim($value) == '') continue;
					$value = str_replace(',', '.', $value);
					$value = (float)$value;
					// values greater than 100 doesn't make sense
					if ($value>100)
						$value = 100;
					$this->in_months[$id] = $value;
				}
			}
		}

		if ($this->type == 'sla') {
			$report_name = arr::search($_REQUEST, 'report_name', false);
			unset($report_options['report_name']);
			$report_options['sla_name'] = $report_name;
			if (isset($report_options['host_filter_status']))
				unset($report_options['host_filter_status']);
			if (isset($report_options['service_filter_status']))
				unset($report_options['service_filter_status']);
		}

		$this->report_options = $report_options;
		$obj_field = $report_options['report_type'] !== false ? self::$map_type_field[$report_options['report_type']] : false;
		$obj_value = arr::search($_REQUEST, $obj_field, array());
		// obj_value is ALWAYS an array

		if (!empty($regexp) && $report_options['report_type'] !== false) {
			# remove last 's' from report_type to get object type
			$obj_type = substr($report_options['report_type'], 0, -1);
			$obj_name = ucfirst($obj_type).'_Model';
			$obj = new $obj_name();
			$obj_res = $obj->regexp_where($obj_type.'_name', $regexp);
			unset(${'in_'.$obj_type});
			foreach ($obj_res as $row) {
				${'in_'.$obj_type}[] = $row->{$obj_type.'_name'};
			}
		}

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker';
		$this->xtra_js[] = $this->add_path('reports/js/move_options');
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min';
		$this->xtra_js[] = $this->add_path('reports/js/common');
		$this->xtra_js[] = $this->add_path('reports/js/reports');

		$this->template->js_header->js = $this->xtra_js;

		$this->xtra_css[] = $this->add_path('reports/css/datePicker');
		$this->xtra_css[] = $this->add_path('css/default/reports');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox';
		$this->template->css_header = $this->add_view('css_header');

		$old_config_names = Saved_reports_Model::get_all_report_names($this->type);
		$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", $old_config_names)."');";
		$this->inline_js .= "invalid_report_names = ".$old_config_names_js .";\n";

		$this->template->content = $this->add_view('reports/'.$this->template_prefix.'index'); # base template with placeholders for all parts
		$template = $this->template->content;

		$status_msg = false;
		$report_info = false;
		$msg_type = false;
		$save_report_settings = arr::search($_REQUEST, 'save_report_settings');

		if ($save_report_settings) {
			$this->report_id = Saved_reports_Model::edit_report_info($this->type, $this->report_id, $report_options, $obj_value, $this->in_months);
			$status_msg = $this->report_id ? $this->translate->_("Report was successfully saved") : "";
			$msg_type = $this->report_id ? "ok" : "";
		}

		if (!empty($this->report_id)) {
			$report_info = Saved_reports_Model::get_report_info($this->type, $this->report_id);
		}

		$scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->report_id);

		$mon_auth = new Nagios_auth_Model();
		if (is_string($in_host)) {
			// shorthand aliases - host=all is used for 'View avail for all hosts'
			if ($in_host == 'all') {
				$in_host = $mon_auth->get_authorized_hosts();
			} elseif($in_host == 'null' && is_string($in_service) && $in_service == 'all') {
				// Used for link 'View avail for all services'
				$in_host = $mon_auth->get_authorized_hosts();
				$in_service = $mon_auth->get_authorized_services();
			} else {
				// handle call from trends.cgi, which does not pass host parameter as array
				if ($mon_auth->is_authorized_for_host($in_host))
					$in_host = array($in_host);
				else
					$in_host = array();
			}
		} elseif (is_array($in_host) && !empty($in_host)) {
			foreach ($in_host as $k => $host) {
				if (!$mon_auth->is_authorized_for_host($host))
					unset($in_host[$k]);
			}
		}

		# Service report in old system sends host and service as separate parameters.
		# Anyone knows a nicer way to check this?
		if(is_string($in_service) && strpos($in_service, ';') === false && count($in_host) == 1) {
			$in_service = array(current($in_host).";$in_service");
		}

		foreach ($in_service as $k => $service) {
			if (!$mon_auth->is_authorized_for_service($service))
				unset($in_service[$k]);
		}

		foreach ($in_hostgroup as $k => $hostgroup) {
			if (!$mon_auth->is_authorized_for_hostgroup($hostgroup))
				unset($in_hostgroup[$k]);
		}

		foreach ($in_servicegroup as $k => $servicegroup) {
			if (!$mon_auth->is_authorized_for_servicegroup($servicegroup))
				unset($in_servicegroup[$k]);
		}

		$this->report_type = arr::search($_REQUEST, 'report_type');
		$in_csvoutput = arr::search($_REQUEST, 'csvoutput');
		if(!$in_csvoutput && '.csv' == substr($this->pdf_filename, -4 , 4)) {
			$in_csvoutput = true;
		}
		$start_time = arr::search($_REQUEST, 't1') ? arr::search($_REQUEST, 't1') : arr::search($_REQUEST, 'start_time');
		$end_time = arr::search($_REQUEST, 't2') ? arr::search($_REQUEST, 't2') : arr::search($_REQUEST, 'end_time');
		$report_period = arr::search($_REQUEST, 'timeperiod') ? arr::search($_REQUEST, 'timeperiod') : arr::search($_REQUEST, 'report_period');
		$rpttimeperiod = arr::search($_REQUEST, 'rpttimeperiod', '');
		$cluster_mode = arr::search($_REQUEST, 'cluster_mode', $this->cluster_mode);
		$hostgroup = false;
		$hostname = false;
		$servicegroup = false;
		$service = false;
		$sub_type = false;
		$time_parts = false;

		# just make sure the javascript isn't messing things up for us
		$start_time = trim($start_time) == 'undefined' ? '' : $start_time;
		$end_time = trim($end_time) == 'undefined' ? '' : $end_time;

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
		$this->report_type = $this->_check_report_type($this->report_type, $in_host, $in_service, $servicegroup, $hostgroup);

		# default to "Current state" = -1 both in new and old avail
		if(!isset($_REQUEST['initialassumedhoststate']))
			$_REQUEST['initialassumedhoststate'] = $this->initialassumedhoststate;

		if(!isset($_REQUEST['initialassumedservicestate']))
			$_REQUEST['initialassumedservicestate'] = $this->initialassumedservicestate;

		$err_msg = "";
		$report_class = $this->reports_model;
		foreach (self::$options as $var => $new_var) {
			if (!$report_class->set_option($new_var, arr::search($_REQUEST, $var))) {
				$err_msg .= sprintf($t->_("Could not set option '%s' to '%s'"), $new_var, arr::search($_REQUEST, $var))."'<br />";
			}
		}

		// convert report period to timestamps
		if ($report_period == 'custom' && !empty($syear) && !empty($eyear)) {
			// cgi compatibility
			$time_parts[0] = mktime($shour, $smin, $ssec, $smon, $sday, $syear);
			$time_parts[1] = mktime($ehour, $emin, $esec, $emon, $eday, $eyear);
		} elseif(!empty($report_period)) {
			$time_parts = $this->_calculate_time($report_period, $start_time, $end_time);
		} else {
			# Use time from t1 and t2 - when called from trends.cgi
			$time_parts = array($start_time, $end_time);
		}

		$this->start_date = $time_parts[0]; // used in calculations by lib_report
		$this->end_date = $time_parts[1];  // used in calculations by lib_report
		$str_start_date = date($this->_get_date_format(true), $this->start_date); // used to set calendar
		$str_end_date 	= date($this->_get_date_format(true), $this->end_date); // used to set calendar

		$report_class->set_option('start_time', $this->start_date);
		$report_class->set_option('end_time', $this->end_date);

		if('custom' == $report_period)
			$report_time_formatted  = sprintf($t->_("%s to %s"), date(nagstat::date_format(), $this->start_date), date(nagstat::date_format(), $this->end_date));
		else
			$report_time_formatted  = (isset($report_period_strings[$report_period]) ? $report_period_strings[$report_period] : $report_period);

		if($rpttimeperiod != '')
			$report_time_formatted .= " - $rpttimeperiod";

		$group_name = false;
		switch ($this->report_type) {
			case 'hostgroups':
				$sub_type = "host";
				$hostgroup = $in_hostgroup;
				$group_name = $hostgroup;
				$this->object_varname = 'host_name';
				break;
			case 'servicegroups':
				$sub_type = "service";
				$servicegroup = $in_servicegroup;
				$group_name = $servicegroup;
				$this->object_varname = 'service_description';
				break;
			case 'hosts':
				$sub_type = "host";
				$hostname = $in_host;
				$this->object_varname = 'host_name';
				break;
			case 'services':
				$sub_type = "service";
				$service = $in_service;
				$this->object_varname = 'service_description';
				break;
			default:
				url::redirect(Router::$controller.'/index');
		}

		$report_class->set_option('host_name', $hostname);
		$report_class->set_option('service_description', $service);

		$this->scheduled_downtime_as_uptime = arr::search($_REQUEST, 'scheduleddowntimeasuptime');
		$assume_initial_states            = arr::search($_REQUEST, 'assumeinitialstates');
		$assume_states_during_not_running = arr::search($_REQUEST, 'assumestatesduringnotrunning');
		$include_soft_states              = arr::search($_REQUEST, 'includesoftstates');
		$this->initial_assumed_host_state = arr::search($_REQUEST, 'initialassumedhoststate', $this->initial_assumed_host_state);
		$this->initial_assumed_service_state = arr::search($_REQUEST, 'initialassumedservicestate', $this->initial_assumed_service_state);
		$use_average = arr::search($_REQUEST, 'use_average', 0);
		$use_alias = arr::search($_REQUEST, 'use_alias', 0);

		# this part is probably not needed anymore since we won't have
		# any 'old' cgi's anymore
		if(!isset($_REQUEST['new_report_setup']))
		{
			$this->initial_assumed_host_state = $this->_convert_assumed_state($this->initial_assumed_host_state, $sub_type);
			$this->initial_assumed_service_state = $this->_convert_assumed_state($this->initial_assumed_service_state, $sub_type);

			$_REQUEST['initialassumedhoststate'] = $this->initial_assumed_host_state;
			$_REQUEST['initialassumedservicestate'] = $this->initial_assumed_service_state;
		}

		$dep_vars = self::$dep_vars;
		foreach ($dep_vars as $check => $set)
			if (isset($_REQUEST[$check]) && !empty($_REQUEST[$check]))
				foreach ($set as $dep => $key) {
					if (!$report_class->set_option($key, $_REQUEST[$dep])) {
						$err_msg .= sprintf($t->_("Could not set option '%s' to '%s'"), $key, $_REQUEST[$dep])."'<br />";
					}
				}


		$get_vars = "&report_period=$report_period";

		foreach (self::$options as $var => $new_var)
			$get_vars .= '&'.$var.'='.arr::search($_REQUEST, $var);


		# The following part is not needed when creating csv output
		# but is placed here because we want it in both the elseif and else
		# part later in the code. Since function calls seems to be somewhat
		# hard to get working when creating PDF reports, it is placed here.
		$html_options[] = array('hidden', 'report_type', $this->report_type);
		$selected_objects = ""; // string containing selected objects for this report

		# pass selected calculation method on to report options
		$html_options[] = array('hidden', 'use_alias', $use_alias);

		# $objects is an array used when creating report_error page (template).
		# Imploded into $missing_objects
		$objects = false;
		if (($this->report_type == 'hosts' || $this->report_type == 'services')) {
			if (is_array($in_host)) {
				foreach ($in_host as $host) {
					$html_options[] = array('hidden', 'host_name[]', $host);
					$selected_objects .= "&host_name[]=".$host;
					$objects[] = $host;
				}
			}
			if (is_array($in_service)) {
				foreach ($in_service as $svc) {
					$html_options[] = array('hidden', 'service_description[]', $svc);
					$selected_objects .= "&service_description[]=".$svc;
					$objects[] = $svc;
				}
			}
		} else {
			if (is_array($hostgroup)) {
				foreach ($hostgroup as $h_gr) {
					$html_options[] = array('hidden', 'hostgroup[]', $h_gr);
					$selected_objects .= "&hostgroup[]=".$h_gr;
					$objects[] = $h_gr;
				}
			}
			if (is_array($servicegroup)) {
				foreach ($servicegroup as $s_gr) {
					$html_options[] = array('hidden', 'servicegroup[]', $s_gr);
					$selected_objects .= "&servicegroup[]=".$s_gr;
					$objects[] = $s_gr;
				}
			}
		}

		# fetch data
		# avail:
		if ($this->type == 'avail') {
			$this->data_arr = $group_name!== false
				? $this->_expand_group_request($group_name, substr($this->report_type, 0, strlen($this->report_type)-1), $this->start_date, $this->end_date)
				: $report_class->get_uptime(false, false, $this->start_date, $this->end_date, $hostgroup, $servicegroup);
		} else {
			$this->data_arr = $this->get_sla_data($this->in_months, $objects);
		}

		$get_vars .= "&initialassumedhoststate=".$this->initial_assumed_host_state;
		$get_vars .= "&initialassumedservicestate=".$this->initial_assumed_service_state;

		$template->title = $this->type == 'avail' ? $t->_('Availability Report') : $t->_('SLA Report');

		$template->report_time_formatted = $report_time_formatted;
		$template->report_id = $this->report_id;
		$template->report_info = $report_info;
		$template->status_msg = $status_msg;
		$template->msg_type = $msg_type;
		$report_template_check = !empty($in_hostgroup) ? true : ((count($in_host) > 1) ? true : false);
		$template->report_template_check = $report_template_check;

		$csv_status = false;

		# AVAIL REPORT
		if ($in_csvoutput) {
			Kohana::close_buffers(FALSE);
			$csv_status = $this->_create_csv_output($this->type, $this->data_arr, $sub_type, $group_name, $in_hostgroup);
			die();
			# if all went OK we have csv_status === true or we have an error string
		} elseif ($this->type == 'avail' && (empty($this->data_arr)
			|| (sizeof($this->data_arr)==1 && empty($this->data_arr[0]))
			|| (!isset($this->data_arr['source']) && empty($this->data_arr[0][0]['source']) ))) {
			# avail report is empty

			# what objects were submitted?
			$template->report_header = $t->_('Empty report');

			$template->error = $this->add_view('reports/'.$this->template_prefix.'error');

			$template->error->error_msg = sprintf($t->_("The selected objects for this %s report doesn't seem to exist anymore.%s
			The reason for this is most likely that they have been removed or renamed in your configuration."), ucfirst(substr($this->report_type, 0, strlen($this->report_type)-1)), '<br />');
			if (!empty($objects)) {
				$template->error->label_missing_objects = $t->_('Missing objects');
				$template->error->missing_objects = $objects;
			}
		} else {
			# ==========================================
			# ========= REPORT STARTS HERE =============
			# ==========================================
			$html_options[] = array('hidden', 'rpttimeperiod', $rpttimeperiod);
			if($include_soft_states)
				$html_options[] = array('hidden', 'includesoftstates', $include_soft_states);

			if($assume_states_during_not_running)
				$html_options[] = array('hidden', 'assumestatesduringnotrunning', $assume_states_during_not_running);

			if ($this->type === 'sla') {
				# we need to stash all the variables needed for the report
				# to be able to save it later. In avail they are editable form elements

				foreach($this->report_options as $key => $val) {
					switch ($key) {
						case 'start_time': case 'end_time':
							if (is_numeric($val)) {
								$val = date('Y-m-d H:i', $val);
							} elseif (trim($val) == 'undefined') {
								$val = '';
							}
							break;
					}

					for ($i = 0; $i< sizeof($html_options);$i++) {
						if(isset($html_options[$i][1]) && $html_options[$i][1] === $key) {
							unset($html_options[$i]);
						}
					}
					$html_options[] = array('hidden', $key, $val);
				}
			}

			$html_options[] = array('hidden', 'type', $this->type);
			if (!empty($this->in_months)) {
				foreach ($this->in_months as $k => $d) {
					$html_options[] = array('hidden', 'month_'.$k, $d);
				}
			}
			$label_report_period = $t->_('Reporting period');
			$label_custom_period = $t->_('CUSTOM REPORT PERIOD');

			# decide what report periods to print
			$report_period_strings = $this->_report_period_strings($this->type);
			$report_periods = $report_period_strings["report_period_strings"];
			$report_periods['custom'] = "* " . $label_custom_period . " *";

			if (!$this->create_pdf) {
				$this->template->content->report_options = $this->add_view('reports/'.$this->template_prefix.'options');

				$tpl_options = $this->template->content->report_options;

				$tpl_options->label_report_period = $label_report_period;

				$tpl_options->report_periods = $report_periods;
				$tpl_options->selected = empty($report_period) ? $report_period_strings["selected"] : $report_period;
				$tpl_options->label_settings = $t->_('Report settings');
				$tpl_options->label_startdate = $t->_('Start date');
				$tpl_options->label_enddate = $t->_('End date');
				$tpl_options->label_startdate_selector = $t->_('Date Start selector');
				$tpl_options->label_enddate_selector = $t->_('Date End selector');
				$tpl_options->label_click_calendar = $t->_('Click calendar to select date');

				$tpl_options->label_assumeinitialstates = $t->_('Assume initial states');
				$tpl_options->label_cluster_mode = $t->_('Cluster mode');

				$tpl_options->label_initialassumedhoststate = $t->_('First assumed host state');
				$tpl_options->label_scheduleddowntimeasuptime = $t->_('Count scheduled downtime as');
				$tpl_options->scheduleddowntimeasuptime_options = $this->scheduled_downtime_options;
				$tpl_options->scheduleddowntimeasuptime_selected = $this->scheduled_downtime_as_uptime;
				$tpl_options->label_initialassumedservicestate = $t->_('First assumed service state');
				$tpl_options->initial_assumed_host_states = self::$initial_assumed_host_states;
				$tpl_options->selected_initial_assumed_host_state = $this->initial_assumed_host_state;

				$tpl_options->initial_assumed_service_states = self::$initial_assumed_service_states;
				$tpl_options->selected_initial_assumed_service_state = $this->initial_assumed_service_state;

				if (isset($host_filter_status)) {
					$tpl_options->host_filter_status_up = $host_filter_status['up'];
					$tpl_options->host_filter_status_down = $host_filter_status['down'];
					$tpl_options->host_filter_status_undetermined = $host_filter_status['undetermined'];
					$tpl_options->host_filter_status_unreachable = $host_filter_status['unreachable'];
					$tpl_options->service_filter_status_ok = $service_filter_status['ok'];
					$tpl_options->service_filter_status_warning = $service_filter_status['warning'];
					$tpl_options->service_filter_status_unknown = $service_filter_status['unknown'];
					$tpl_options->service_filter_status_critical = $service_filter_status['critical'];
					$tpl_options->service_filter_status_pending = $service_filter_status['pending'];
				}

				$tpl_options->label_save_report = $t->_('Save report');
				$tpl_options->label_as = $t->_('as');
				$tpl_options->label_new_schedule = $t->_('New schedule');
				$tpl_options->label_view_schedule = $t->_('View schedule');
				$tpl_options->label_save_to_schedule = $t->_('To schedule this report, save it first');
				$tpl_options->label_update = $t->_('Update report');
				$tpl_options->label_interval = $t->_('Report Interval');
				$tpl_options->label_recipients = $t->_('Recipients');
				$tpl_options->label_edit_settings = $t->_('edit settings');
				$tpl_options->label_sla_calc_method = $t->_('SLA calculation method');
				$tpl_options->label_filename = $t->_('Filename');
				$tpl_options->label_description = $t->_('Description');
				$tpl_options->label_save = $t->_('Save');
				$tpl_options->label_clear = $t->_('Clear');
				$tpl_options->report_id = $this->report_id;
				$tpl_options->report_info = $report_info;
				$tpl_options->html_options = $html_options;
				$tpl_options->old_avail_link = config::get_cgi_cfg_key('url_html_path').'/cgi-bin/avail.cgi';

				$use_average_options = array(
					0 => $t->_('Group availability (SLA)'),
					1 => $t->_('Average')
				);
				$tpl_options->use_average_options = $use_average_options;
				$tpl_options->use_average_selected = $use_average_selected;

				$date_format = $this->_get_date_format(true);
				$tpl_options->start_date = date($date_format, $this->start_date);
				$tpl_options->start_time = date('H:i', $this->start_date);
				$tpl_options->end_date = date($date_format, $this->end_date);
				$tpl_options->end_time = date('H:i', $this->end_date);

				$available_schedule_periods = false;
				$json_periods = false;
				$schedule_periods = Scheduled_reports_Model::get_available_report_periods();
				if ($schedule_periods !== false && !empty($schedule_periods)) {
					foreach ($schedule_periods as $s) {
						$available_schedule_periods[$s->id] = $s->periodname;
					}
					$json_periods = json::encode($available_schedule_periods);
				}
				$tpl_options->json_periods = $json_periods;
				$tpl_options->available_schedule_periods = $available_schedule_periods;
				$tpl_options->type = $this->type;
				$tpl_options->rep_type = $this->type == 'avail' ? 1 : 2;
				$tpl_options->lable_schedules = $t->_('Schedules for this report');
				$tpl_options->scheduled_info = $scheduled_info;
				$tpl_options->label_dblclick = $t->_('Double click to edit');
				$scheduled_label = $t->_('Scheduled');
				if ($this->type == 'avail') {
					$this->inline_js .= "set_initial_state('host', '".$this->initial_assumed_host_state."');\n";
					$this->inline_js .= "set_initial_state('service', '".$this->initial_assumed_service_state."');\n";
					$this->inline_js .= "set_initial_state('assumeinitialstates', '".$assume_initial_states."');\n";
					$this->inline_js .= "set_initial_state('scheduleddowntimeasuptime', '".$this->scheduled_downtime_as_uptime."');\n";
					$this->inline_js .= "set_initial_state('report_period', '".$report_period."');\n";
					$this->inline_js .= "show_calendar('".$report_period."');\n";
					$this->js_strings .= "var initial_assumed_host_state = '".$this->initial_assumed_host_state."';\n";
					$this->js_strings .= "var initial_assumed_service_state = '".$this->initial_assumed_service_state."';\n";
				}

				$this->js_strings .= "var cluster_mode = '".(int)$cluster_mode."';\n";
				$this->js_strings .= "var assumeinitialstates = '".$assume_initial_states."';\n";
				$this->js_strings .= "var scheduleddowntimeasuptime = '".$this->scheduled_downtime_as_uptime."';\n";

				$this->js_strings .= "var _reports_success = '".$t->_('Success')."';\n";
				$this->js_strings .= "var _reports_error = '".$t->_('Error')."';\n";
				$this->js_strings .= "var _reports_schedule_send_ok = '".$t->_('Your report was successfully sent')."';\n";
				$this->js_strings .= "var nr_of_scheduled_instances = ". (!empty($scheduled_info) ? sizeof($scheduled_info) : 0).";\n";
				$this->js_strings .= "var _reports_fatal_err_str = '".$t->_('It is not possible to schedule this report since some vital information is missing.')."';\n";
				$this->js_strings .= "var _reports_schedule_interval_error = '".$t->_(' -Please select a schedule interval')."';\n";
				$this->js_strings .= "var _reports_schedule_recipient_error = '".$t->_(' -Please enter at least one recipient')."';\n";
				$this->js_strings .= "var _edit_str = '".$t->_('edit')."';\n";
				$this->js_strings .= "var _hide_str = '".$t->_('hide')."';\n";
				$this->js_strings .= "var _scheduled_label = '".$scheduled_label."';";
				$this->js_strings .= "var _reports_schedule_error = '".$t->_('An error occurred when saving scheduled report')."';\n";
				$this->js_strings .= "var _reports_schedule_update_ok = '".$t->_('Your schedule has been successfully updated')."';\n";
				$this->js_strings .= "var _reports_schedule_create_ok = '".$t->_('Your schedule has been successfully created')."';\n";
				$this->js_strings .= "var _reports_view_schedule = '".$t->_('View schedule')."';\n";
				$this->js_strings .= "var _reports_edit_information = '".$t->_('Double click to edit')."';\n";
				$this->js_strings .= "var _reports_errors_found = '".$t->_('Found the following error(s)')."';\n";
				$this->js_strings .= "var _reports_please_correct = '".$t->_('Please correct this and try again')."';\n";

				$this->js_strings .= "var _reports_schedule_deleted = '".$t->_('Your schedule has been deleted')."';\n";

				$this->js_strings .= "var _reports_error_name_exists = '".sprintf($t->_("You have entered a name for your report that already exists. %sPlease select a new name"), '<br />')."';\n";
				$this->js_strings .= reports::js_strings();
				$this->js_strings .= "var _reports_name_empty = '".$t->_("Please give your report a meaningful name.")."';\n";
				$this->js_strings .= "var _reports_error_name_exists_replace = \"".$t->_("The entered name already exists. Press 'Ok' to replace the entry with this name")."\";\n";
				$this->js_strings .= "var _reports_confirm_delete = '".$t->_("Are you really sure that you would like to remove this saved report?")."';\n";
				$this->js_strings .= "var _reports_confirm_delete_schedule = \"".sprintf($t->_("Do you really want to delete this schedule?%sThis action can't be undone."), '\n')."\";\n";
				$this->js_strings .= "var _reports_confirm_delete_warning = '".sprintf($t->_("Please note that this is a scheduled report and if you decide to delete it, %s" .
					"the corresponding schedule(s) will be deleted as well.%s Are you really sure that this is what you want?"), '\n', '\n\n')."';\n";

				$csv_link = $this->_get_csv_link();
				$tpl_options->csv_link = $csv_link;
				if(!isset($_REQUEST['generating_pdf']))
					$pdf_link = $this->_get_pdf_link($this->type);
				else
					$pdf_link = '';
				$tpl_options->pdf_link = $pdf_link;

			} #end if NOT create_pdf

			$host_graph_items = array('TOTAL_TIME_UP' => $t->_('Up'),
					'TOTAL_TIME_DOWN' => $t->_('Down'),
					'TOTAL_TIME_UNREACHABLE' => $t->_('Unreachable'),
					'TOTAL_TIME_UNDETERMINED' => $t->_('Undetermined'));
			$service_graph_items = array('TOTAL_TIME_OK' => $t->_('Ok'),
					'TOTAL_TIME_WARNING' => $t->_('Warning'),
					'TOTAL_TIME_UNKNOWN' => $t->_('Unknown'),
					'TOTAL_TIME_CRITICAL' => $t->_('Critical'),
					'TOTAL_TIME_UNDETERMINED' => $t->_('Undetermined'));
			$graph_filter = ${$sub_type.'_graph_items'};

			# hostgroups / servicegroups
			if ($this->type == 'avail' && isset($this->data_arr[0])) {

				$template->header = $this->add_view('reports/'.$this->template_prefix.'header');
				$template->header->report_time_formatted = $report_time_formatted;
				$template->header->create_pdf = $this->create_pdf;
				if (!$this->create_pdf) {
					$csv_link = $this->_get_csv_link();
					$template->header->csv_link = $csv_link;
					$template->header->pdf_link = $pdf_link;
				} #end if NOT create_pdf
				if ($report_period != 'custom') {
					$template->header->str_start_date = $str_start_date;
					$template->header->str_end_date = $str_end_date;
				}
				$template->header->use_average = $use_average;

				$template->header->label_report_period = $label_report_period;
				$template->header->label_to = $t->_('to');
				$template->header->label_using_avg = $t->_('using averages');
				$template->header->label_print = $t->_('Print report');

				if ($group_name) {
					foreach ($this->data_arr as $data) {
						if (empty($data))
							continue;
						array_multisort($data);
						$template_values[] = $this->_get_multiple_state_info($data, $sub_type, $get_vars, $this->start_date, $this->end_date, $this->type);
					}
				} else {
					array_multisort($this->data_arr);
					$template_values[] = $this->_get_multiple_state_info($this->data_arr, $sub_type, $get_vars, $this->start_date, $this->end_date, $this->type);
				}

				if (!empty($template_values) && count($template_values))
					for($i=0,$num_groups=count($template_values)  ; $i<$num_groups ; $i++) {
						$this->_reorder_by_host_and_service($template_values[$i], $this->report_type);
					}

				if($group_name) {
					// Copy-pasted from controllers/trends.php
					foreach ($this->data_arr as $key => $data) {
						# >= 2 hosts or services won't have the extra
						# depth in the array, so we break out early
						if (empty($data['log']) || !is_array($data['log'])) {
							$graph_data = $this->data_arr['log'];
							break;
						}

						# $data is the outer array (with, source, log,
						# states etc)
						if (empty($graph_data)) {
							$graph_data = $data['log'];
						} else {
							$graph_data = array_merge($data['log'], $graph_data);
						}
					} # end foreach
				} else {
					// We are not checking groups
					$graph_data = $this->data_arr['log'];
				}

				$template->trends_graph = $this->add_view('trends/new_report');
				$template->trends_graph->graph_image_source = $this->trends_graph_model->get_graph_src_for_data(
					$graph_data,
					$report_class->start_time,
					$report_class->end_time,
					$template->title
				);
				$template->trends_graph->is_avail = true;
				$template->trends_graph->create_pdf = $this->create_pdf;
				if ($this->create_pdf) {
					$template->trends_graph->graph_chart_pdf_src = $this->trends_graph_model->get_graph_pdf_src_for_data(
						$graph_data,
						$report_class->start_time,
						$report_class->end_time,
						$template->title
					);
					$this->pdf_data['trends_graph'] = $template->trends_graph->render();
				}

				$template->content = $this->add_view('reports/'.$this->template_prefix.'multiple_'.$sub_type.'_states');
				$template->content->multiple_states = $template_values;
				$template->content->hide_host = false;
				$template->content->create_pdf = $this->create_pdf;
				$template->content->use_average = $use_average;
				if (isset($host_filter_status)) {
					$template->content->host_filter_status = $host_filter_status;
					$template->content->service_filter_status = $service_filter_status;
				}
				$template->content->service_filter_status_show = true;
				$template->content->use_alias = $use_alias;
				$template->content->start_time = $this->start_date;
				$template->content->end_time = $this->end_date;
				$template->content->report_time_formatted = $report_time_formatted;
				if ($this->create_pdf) {
					$content = $template->content;
				}

				$template->pie = $this->add_view('reports/'.$this->template_prefix.'pie_chart');
				$template->pie->label_status = $t->_('Status overview');

				// ===== SETUP PIECHART VALUES =====
				$image_data = array();
				foreach($graph_filter as $key => $val) { $image_data[strtoupper($val)] = 0; }

				# We've either got
				# 1) custom group
				# 2) hostgroup / servicegroup

				$groups_added = 0;
				$pie_groupname = false;
				if(!isset($this->data_arr['groupname'])) { # actual hostgroup/servicegroup.
					$tmp_title = ucfirst($sub_type).$t->_('group breakdown');
					$template->header->title = $tmp_title;
					if ($this->create_pdf || $this->mashing) {
						$this->pdf_data['title'] = $tmp_title;
					}
					foreach($this->data_arr as $data) { # for every group
						$added_group = false;
						if (is_array($data['states'])) {
							foreach ($graph_filter as $key => $val) {
								if ($data['states'][$key]!=0) {
									if (isset($image_data[$groups_added][strtoupper($val)])) {
										$image_data[$groups_added][strtoupper($val)] += $data['states'][$key];
									} else {
										$image_data[$groups_added][strtoupper($val)] = $data['states'][$key];
									}
									$pie_groupname[$groups_added] = $data['groupname'];
									$added_group = true;
								}
							}
						}
						if($added_group)
							$groups_added++;
					}
				} else {
					$added_group = false;
					$tmp_title = ucfirst($sub_type).' '.$t->_('state breakdown');
					$template->header->title = $tmp_title;
					if ($this->create_pdf || $this->mashing) {
						$this->pdf_data['title'] = $tmp_title;
					}
					if (is_array($this->data_arr['states'])) {
						foreach ($graph_filter as $key => $val) {
							if ($this->data_arr['states'][$key]!=0)
							{
								if (isset($image_data[0][strtoupper($val)])) {
									$image_data[0][strtoupper($val)] += $this->data_arr['states'][$key];
								} else {
									$image_data[0][strtoupper($val)] = $this->data_arr['states'][$key];
								}
								$added_group = true;
							}
						}
					}
					if($added_group)
						$groups_added++;
				}

				if ($groups_added > 0) {
					foreach($graph_filter as $key => $val) {
						for($i = 0; $i < $groups_added; $i++) {
							if(isset($image_data[$i][strtoupper($val)]) && $image_data[$i][strtoupper($val)] == 0)
								unset($image_data[$i][strtoupper($val)]);
							else {
								if (isset($image_data[$i][strtoupper($val)]))
									$image_data[$i][strtoupper($val)] /= $groups_added;
							}
						}
					}
					$charts = false;
					$page_js = '';
					for($i = 0; $i < $groups_added; $i++) {
						$data_str[$i]['img'] = base64_encode(serialize($image_data[$i]));
						$data_str[$i]['host'] = $pie_groupname[$i];
					}

					$template->pie->data_str = $data_str;
					$template->pie->image_data = $image_data;
					if ($this->create_pdf || $this->mashing) {
						$this->pdf_data['pie_data'] = $data_str;
					}
				}
			} else { # host/services
				$image_data = false;
				$data_str = '';
				if (!empty($this->data_arr)) {
					$data = $this->data_arr;
					$template->content = $this->add_view('reports/'.$this->template_prefix.$this->type);
					$template->content->scheduled_downtime_as_uptime = $this->scheduled_downtime_as_uptime;
					$template->content->create_pdf = $this->create_pdf;
					$template->content->start_time = $this->start_date;
					$template->content->end_time = $this->end_date;

					$template->header = $this->add_view('reports/'.$this->template_prefix.'header');
					$template->header->report_time_formatted = $report_time_formatted;
					$template->header->str_start_date = $str_start_date;
					$template->header->str_end_date = $str_end_date;
					$template->header->create_pdf = $this->create_pdf;
					$csv_link = isset($csv_link) ? $csv_link : false;
					$template->header->csv_link = $this->type == 'avail' ? $csv_link : false;
					$template->header->pdf_link = isset($pdf_link) ? $pdf_link : false;
					$template->header->label_report_period = $label_report_period;
					$template->header->label_to = $t->_('to');
					$template->header->label_using_avg = $t->_('using averages');
					$template->header->label_print = $t->_('Print report');
					$template->header->use_average = $use_average;
					$template->header->use_alias = $use_alias;
					if ($this->create_pdf) {
						$this->pdf_data['report_time_formatted'] = $report_time_formatted;
						$this->pdf_data['str_start_date'] = $str_start_date;
						$this->pdf_data['str_end_date'] = $str_end_date;
						$this->pdf_data['label_report_period'] = $label_report_period;
					}

					if ($this->type == 'avail') {
						$avail_data = $this->_print_state_breakdowns($data['source'], $data['states'], $this->report_type);
						$avail = $template->content;
						$avail->label_type_reason = $t->_('Type / Reason');
						$avail->label_time = $t->_('Time');
						$avail->label_tot_time = $t->_('Total time');
						$avail->label_unscheduled = $t->_('Unscheduled');
						$avail->label_scheduled = $t->_('Scheduled');
						$avail->label_total = $t->_('Total');
						$avail->label_undetermined = $t->_('Undetermined');
						$avail->label_not_running = $t->_('Not running');
						$avail->label_insufficient_data = $t->_('Insufficient data');
						$avail->label_all = $t->_('All');
						$avail->state_values = $this->state_values;
						$avail->create_pdf = $this->create_pdf;

						$avail->avail_data = $avail_data;
						$avail->source = $data['source'];
						$avail->report_time_formatted = $report_time_formatted;
						$avail->testbutton = $this->_build_testcase_form($data[';testcase;']);

						$avail->header_string = ucfirst($this->report_type)." ".$t->_('state breakdown');
						if ($this->create_pdf) {
							$content = $avail;
						}

						$trends_data = false;
						if (isset($data['log']) && isset($data['source']) && !empty($data['source'])) {
							$trends_data = $data['log'];
						}

						if($group_name) {
							// Copy-pasted from controllers/trends.php
							foreach ($this->data_arr as $key => $data) {
								# >= 2 hosts or services won't have the extra
								# depth in the array, so we break out early
								if (empty($data['log']) || !is_array($data['log'])) {
									$graph_data = $this->data_arr['log'];
									break;
								}

								# $data is the outer array (with, source, log,
								# states etc)
								if (empty($graph_data)) {
									$graph_data = $data['log'];
								} else {
									$graph_data = array_merge($data['log'], $graph_data);
								}
							} # end foreach
						} else {
							// We are not checking groups
							$graph_data = $this->data_arr['log'];
						}

						$template->trends_graph = $this->add_view('trends/new_report');
						$template->trends_graph->graph_image_source = $this->trends_graph_model->get_graph_src_for_data(
							$graph_data,
							$report_class->start_time,
							$report_class->end_time,
							$template->title
						);
						$template->trends_graph->report_time_formatted = $report_time_formatted;
						$template->trends_graph->create_pdf = $this->create_pdf;
						$this->xtra_css[] = $this->add_path('css/default/reports');
						$this->xtra_js[] = $this->add_path('trends/js/trends');
						if ($this->create_pdf) {
							$template->trends_graph->graph_chart_pdf_src = $this->trends_graph_model->get_graph_pdf_src_for_data(
								$graph_data,
								$report_class->start_time,
								$report_class->end_time,
								$template->title
							);
							$this->pdf_data['trends_graph'] = $template->trends_graph->render();
						}

						$avail->pie = $this->add_view('reports/'.$this->template_prefix.'pie_chart');
						$avail->pie->label_status = $t->_('Status overview');
						$avail->pie->report_time_formatted = $report_time_formatted;

						// ===== SETUP PIECHART VALUES =====
						if (is_array($data['states'])) {
							foreach ($graph_filter as $key => $val) {
								if ($data['states'][$key]!=0)
									$image_data[strtoupper($val)] = $data['states'][$key];
							}
						}

						if ($image_data) {
							$data_str = base64_encode(serialize($image_data));
							if (!$this->create_pdf) {
								$avail->pie->data_str = $data_str;
								$avail->pie->source = $data['source'];
							} else {
								$this->pdf_data['pie_data'] = $data_str;
							}
						}

						if ($sub_type=='host') {
							$service_states = $this->_print_states_for_services($this->data_arr['source'], $this->start_date, $this->end_date, $this->report_type);

							if ($service_states !== false) {
								$template_values[] = $this->_get_multiple_state_info($service_states, 'service', $get_vars, $this->start_date, $this->end_date, $this->type);
								$header_str = $t->_("Service state breakdown");
								$template->svc_content = $this->add_view('reports/'.$this->template_prefix.'multiple_service_states');
								$content = $template->svc_content;
								$content->header_string = $header_str;
								$content->multiple_states = $template_values;
								$content->hide_host = true;
								$content->use_average = $use_average;
								$content->use_alias = $use_alias;
								$content->start_time = $this->start_date;
								$content->end_time = $this->end_date;
								if (isset($service_filter_status))
									$content->service_filter_status = $service_filter_status;
								$content->service_filter_status_show = false;
								$content->source = $data['source'];
								$content->create_pdf = $this->create_pdf;
								$content->report_time_formatted = $report_time_formatted;
								$this->pdf_data['svc_content'] = $template->svc_content->render();
							}
						}

						// fetch and display log messages
						$log = arr::search($data, 'log');
						if ($log !== false) {
							$label_entries = $t->_("Log Entries for");
							$template->log_content = $this->add_view('reports/'.$this->template_prefix.'log');
							$log_template = $template->log_content;
							$log_template->log = array_shift($log);
							$log_template->type = $sub_type;
							$log_template->label_entries = $label_entries;
							$log_template->source = $data['source'];
							$log_template->create_pdf = $this->create_pdf;
							$log_template->report_time_formatted = $report_time_formatted;
							if ($this->create_pdf) {
								$this->pdf_data['log_data'] = $log_template->render();
							}
						}

						$t1 = $this->start_date;
						$t2 = $this->end_date;

						# assume default values for the following
						$assume_state_retention = $this->assume_state_retention ? 1 : 0;
						$backtrack = 1;

						$links = array();
						$trends_img_params = '';
						$trends_link_params = '';
						$downtime       = $this->scheduled_downtime_as_uptime;
						$assume_initial = $assume_initial_states;
						$not_running    = $assume_states_during_not_running;
						$soft_states    = $include_soft_states;

						// convert "First Real State" (-3) to value returned from report_class
						// other values are converted to old cgi value equivalent
						$trends_assumed_initial_host_state 		= $this->initial_assumed_host_state == -3 ? $report_class->initial_state : $this->_convert_assumed_state($this->initial_assumed_host_state, $sub_type, false);
						$trends_assumed_initial_service_state 	= $this->initial_assumed_service_state == -3 ? $report_class->initial_state : $this->_convert_assumed_state($this->initial_assumed_service_state, $sub_type, false);

						# links - only for HTML reports
						switch($this->report_type) {
							case 'hosts':
								# only meaningful to print these links if only one host selected
								if(count($hostname) != 1)
									break;

								$host = $hostname[0];
								$template->header->title = ucfirst($this->report_type).' '.$t->_('details for').': '.ucfirst($host);
								$all_avail_params = "report_type=".$this->report_type.
									 "&amp;host_name=all".
									 "&amp;report_period=$report_period".
									 "&amp;rpttimeperiod=$rpttimeperiod".
									 "&amp;start_time=".$this->start_date.
									 "&amp;end_time=".$this->end_date.
									 "&amp;initialassumedhoststate=".$this->initial_assumed_host_state.
									 "&amp;initialassumedservicestate=".$this->initial_assumed_service_state;

								if($downtime)			$all_avail_params .= "&amp;scheduleddowntimeasuptime=$downtime";
								if($assume_initial)		$all_avail_params .= "&amp;assumeinitialstates=$assume_initial";
								if($not_running)		$all_avail_params .= "&amp;assumestatesduringnotrunning=$not_running";
								if($soft_states)		$all_avail_params .= "&amp;includesoftstates=$soft_states";

								$links[Router::$controller.'/'.Router::$method."?".$all_avail_params] = $t->_('Availability report for all hosts');

								$trends_params = "host=$host".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;assumestateretention=$assume_state_retention".
									"&amp;assumeinitialstates=".$assume_initial_states.
									"&amp;includesoftstates=".$include_soft_states.
									"&amp;assumestatesduringnotrunning=".$assume_states_during_not_running.
									"&amp;initialassumedhoststate=".$trends_assumed_initial_host_state.
									"&amp;backtrack=$backtrack";

								$trends_img_params = $this->trend_link."?".
									"host=$host".
									"&amp;createimage&amp;smallimage".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;assumestateretention=$assume_state_retention".
									"&amp;assumeinitialstates=".$assume_initial_states.
									"&amp;includesoftstates=".$include_soft_states.
									"&amp;assumestatesduringnotrunning=".$assume_states_during_not_running.
									"&amp;initialassumedhoststate=".$trends_assumed_initial_host_state.
									"&amp;backtrack=$backtrack";

								$trends_link_params = $this->trend_link."?".
									"host=$host".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;assumestateretention=$assume_state_retention".
									"&amp;assumeinitialstates=".$assume_initial_states.
									"&amp;includesoftstates=".$include_soft_states.
									"&amp;assumestatesduringnotrunning=".$assume_states_during_not_running.
									"&amp;initialassumedhoststate=".$trends_assumed_initial_host_state.
									"&amp;backtrack=$backtrack";



								$links[$this->trend_link."?".$trends_params] = $t->_('Trends');

								$histogram_params = "host=$host&amp;t1=$t1&amp;t2=$t2&amp;assumestateretention=$assume_state_retention";

								$links[$this->histogram_link . "?" . $histogram_params] = $t->_('Alert histogram');

								$links[$this->status_link.$host] = $t->_('Status detail');

								$links[$this->history_link . "/" .$host] = $t->_('Alert history');
								$links[$this->notifications_link . "/" . $host] = $t->_('Notifications');
								break;

							case 'services':

								list($host, $service) = explode(';',$service[0]);

								$template->header->title = ucfirst($this->report_type).' '.$t->_('details for').': '.ucfirst($service).' '.$t->_('on host').': '.ucfirst($host);
								if (isset($template->content)) {
									$template->content->host = $host;
									$template->content->service = $service;
								}
								$avail_params = "&show_log_entries".
									 "&amp;t1=$t1".
									 "&amp;t2=$t2".
									 "&amp;report_period=".$report_period.
									 "&amp;rpttimeperiod=$rpttimeperiod".
									 "&amp;backtrack=$backtrack".
									 "&amp;assumestateretention=$assume_state_retention".
									 "&amp;assumeinitialstates=".$this->_convert_yesno_int($assume_initial_states, false).
									 "&amp;assumestatesduringnotrunning=".$this->_convert_yesno_int($assume_states_during_not_running, false).
									 "&amp;initialassumedhoststate=".$this->initialassumedhoststate.
									 "&amp;initialassumedservicestate=".$this->initialassumedservicestate.
									 "&amp;show_log_entries".
									 "&amp;showscheduleddowntime=yes";


								if($downtime)			$avail_params .= "&amp;scheduleddowntimeasuptime=$downtime";
								if($assume_initial)		$avail_params .= "&amp;assumeinitialstates=$assume_initial";
								if($not_running)		$avail_params .= "&amp;assumestatesduringnotrunning=$not_running";
								if($soft_states)		$avail_params .= "&amp;includesoftstates=$soft_states";

								$trends_params = "host=$host".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;assumestateretention=$assume_state_retention".
									"&amp;assumeinitialstates=".$assume_initial_states.
									"&amp;includesoftstates=".$include_soft_states.
									"&amp;assumestatesduringnotrunning=".$assume_states_during_not_running.
									"&amp;initialassumedservicestate=".$trends_assumed_initial_service_state.
									"&amp;backtrack=$backtrack";

								$trends_img_params = $this->trend_link."?".
									"host=$host".
									"&amp;service=$service".
									"&amp;createimage&amp;smallimage".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;assumestateretention=$assume_state_retention".
									"&amp;assumeinitialstates=".$assume_initial_states.
									"&amp;includesoftstates=".$include_soft_states.
									"&amp;assumestatesduringnotrunning=".$assume_states_during_not_running.
									"&amp;initialassumedservicestate=".$trends_assumed_initial_service_state.
									"&amp;backtrack=$backtrack";

								$trends_link_params = $this->trend_link."?".
									"host=$host".
									"&amp;service=$service".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;assumestateretention=$assume_state_retention".
									"&amp;assumeinitialstates=".$assume_initial_states.
									"&amp;includesoftstates=".$include_soft_states.
									"&amp;assumestatesduringnotrunning=".$assume_states_during_not_running.
									"&amp;initialassumedservicestate=".$trends_assumed_initial_service_state.
									"&amp;backtrack=$backtrack";

								$histogram_params     = "host=$host&amp;service=$service&amp;t1=$t1&amp;t2=$t2&amp;assumestateretention=$assume_state_retention";
								$history_params       = "host=$host&amp;service=$service";
								$notifications_params = "host=$host&amp;service=$service";


								$links[Router::$controller.'/'.Router::$method."?host=$host$avail_params"] 			= $t->_('Availability report for this host');
								$links[Router::$controller.'/'.Router::$method."?host=null&amp;service=all$avail_params"] = $t->_('Availability report for all services');
								$links[$this->trend_link . "?" . $trends_params . "&amp;service_description=".$host.';'.$service] = $t->_('Trends');
								$links[$this->histogram_link . "?" . $histogram_params] 		= $t->_('Alert histogram');
								$links[$this->history_link . "?" . $history_params] 			= $t->_('Alert history');
								$links[$this->notifications_link . "?" . $notifications_params] = $t->_('Notifications');

								break;
						}

						if (!$this->create_pdf) {
							$template->links = $links;
							$template->trends = $trends_img_params;
							$template->trends_link = $trends_link_params;
							$template->source = $data['source'];
							$template->header_string = sprintf($t->_("State breakdown for %s"), $data['source']);
						}
					} else {
						# SLA report
						$template->header->title = $t->_('SLA breakdown');
						if ($this->create_pdf) {
							$this->pdf_data['title'] = $t->_('SLA breakdown');
						}
						$sla = $template->content;
						$sla->report_data = $this->data_arr;
						$sla->use_alias = $use_alias;
					}

				} # end if not empty. Display message to user?
			}

			if (!$this->create_pdf) {
				# fetch users date format in PHP style so we can use it
				# in date() below
				$date_format = $this->_get_date_format(true);

				$js_month_names = "Date.monthNames = ".json::encode($this->month_names).";";
				$js_abbr_month_names = 'Date.abbrMonthNames = '.json::encode($this->abbr_month_names).';';
				$js_day_names = 'Date.dayNames = '.json::encode($this->day_names).';';
				$js_abbr_day_names = 'Date.abbrDayNames = '.json::encode($this->abbr_day_names).';';
				$js_day_of_week = 'Date.firstDayOfWeek = '.$this->first_day_of_week.';';
				$js_date_format = "Date.format = '".$this->_get_date_format()."';";
				$js_start_date = "_start_date = '".date($date_format, mktime(0,0,0,1, 1, 1996))."';";

				# inline js should be the
				# var host =
				# var service =
				# 	etc...
				$this->js_strings .= "\n".$js_month_names."\n";
				$this->js_strings .= $js_abbr_month_names."\n";
				$this->js_strings .= $js_day_names."\n";
				$this->js_strings .= $js_abbr_day_names."\n";
				$this->js_strings .= $js_day_of_week."\n";
				$this->js_strings .= $js_date_format."\n";
				$this->js_strings .= $js_start_date."\n";

				$this->template->inline_js = $this->inline_js;
				$this->template->js_strings = $this->js_strings;
				$this->template->css_header->css = $this->xtra_css;
			}

			# skip the rest if pdf or mashing
			if ($this->create_pdf || $this->mashing) {
				$this->pdf_data['content'] = $template->content->render();
				$this->pdf_data['header'] = $template->header->render();

				if ($this->create_pdf && $this->mashing) {
					return $this->pdf_data;
				} elseif ($this->mashing) {
					return $template->render();
				}

				$retval = $this->_pdf();
				if (PHP_SAPI == "cli") {
					echo $retval;
				}
				return $retval;
			}
		}

		$this->template->title = $this->translate->_('Reporting » ').($this->type == 'avail' ? $t->_('Availability Report') : $t->_('SLA Report')).(' » Report');
	}

	/**
	*	Stash parameters in session from setup form to be used
	*	for re-generating report.
	*/
	public function _stash_params()
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

		Session::instance()->set('current_report_params', null);

		$skip_keys = array(
			'saved_report_id',
			'time_start',
			'time_end',
			'cal_start',
			'cal_end'
		);
		foreach ($input as $key => $val) {
			if ($val == '' || in_array($key, $skip_keys)) {
				continue;
			}
			if (is_array($val)) {
				foreach ($val as $v) {
					$data[] = $key.'[]='.$v;
				}
			} else {
				$data[] = $key.'='.$val;
			}
		}
		if (!empty($data)) {
			if (array_key_exists('new_report_setup', $input)) {
				# directly from setup form - keep data for backlink
				Session::instance()->set('main_report_params', implode('&', $data));
			}

			Session::instance()->set('current_report_params', implode('&', $data));
		}
	}

	/**
	*	Save a report via ajax call
	* 	Called from reports.js (trigger_ajax_save())
	* 	@return JSON string
	*/
	public function save()
	{
		if(!request::is_ajax()) {
			$msg = $this->translate->_('Only Ajax calls are supported here');
			die($msg);
		}

		$this->auto_render=false;

		# 	Fetch the input variable 'type' from
		#	either $_GET or $_POST and use default
		# 	method param if nothing found
		$this->type = arr::search($_REQUEST, 'type');
		$this->report_id = arr::search($_REQUEST, 'saved_report_id', $this->report_id);

		$report_options = false;
		foreach ($this->setup_keys as $k)	$report_options[$k] = false;
		// store all variables in array for later use
		foreach ($_REQUEST as $key => $value) {
			if (in_array($key, $this->setup_keys)) {
				if (arr::search($_REQUEST, 'report_period') == 'custom' && ($key=='start_time' || $key=='end_time')) {
					if (is_numeric($value)) {
						$report_options[$key] = $value;
						$_REQUEST[$key] = date("Y-m-d H:i", $value);
					} else {
						$report_options[$key] = strtotime($value);
						$_REQUEST[$key] = $value;
					}
				} elseif (arr::search($_REQUEST, 'host_filter_status')) {
					$report_options[$key] = serialize(self::_create_filter_array($value, 'host'));
					$host_filter_status = self::_create_filter_array($value,'host' );
				}
				elseif (is_array(arr::search($_REQUEST, 'service_filter_status')) == true) {
					$report_options[$key] = serialize(self::_create_filter_array($value, 'service'));
					$service_filter_status = self::_create_filter_array($value,'service' );
				}
				 else  {
					$report_options[$key] = $value;
				}
			} else {
				if ($this->type == 'sla' && preg_match('/^month/', trim($key))) {
					$id = (int)str_replace('month_', '', $key);
					if (trim($value) == '') continue;
					$value = str_replace(',', '.', $value);
					$value = (float)$value;
					// values greater than 100 doesn't make sense
					if ($value>100)
						$value = 100;
					$this->in_months[$id] = $value;
				}
			}
		}

		if ($this->type == 'sla') {
			$report_name = arr::search($_REQUEST, 'report_name', false);
			unset($report_options['report_name']);
			unset($report_options['host_filter_status']);
			unset($report_options['service_filter_status']);
			$report_options['sla_name'] = $report_name;
		}

		$obj_field = arr::search($_REQUEST, 'report_type', false);
		$obj_value = arr::search($_REQUEST, self::$map_type_field[$obj_field], array());

		$save_report_settings = arr::search($_REQUEST, 'save_report_settings');
		$report_name = arr::search($_REQUEST, 'report_name', false);
		$return = false;
		if ($save_report_settings && $report_name !== false && !empty($obj_value)) {
			$this->report_id = Saved_reports_Model::edit_report_info($this->type, $this->report_id, $report_options, $obj_value, $this->in_months);
			$status_msg = $this->report_id ? $this->translate->_("Report was successfully saved") : "";
			$msg_type = $this->report_id ? "ok" : "";
			#$return = array('status' => $obj_field, 'status_msg' => Kohana::debug($this->report_id));
			$return = array('status' => $msg_type, 'status_msg' => $status_msg, 'report_id' => $this->report_id);
		} else {
			$return = array('status' => '', 'status_msg' => $this->translate->_('Unable to save this report.'));
		}
		echo json::encode($return);
	}

	/**
	*	Create a piechart
	*/
	public function piechart($in_data=false, $path=null)
	{
		$this->auto_render = false;
		$data = i18n::unserialize( base64_decode($in_data) );
		charts::load('Pie');
		$graph = new PieChart(300, 200);
		$graph->set_data($data, 'pie');
		$graph->set_margins(30);

		$graph->draw();
		if (!is_null($path)) {
			# save rendered image to somewhere ($path)
			if (file_exists($path) && is_writable($path)) {
				$image = $graph->get_image();

				# create temp filename with 'pie' as prefix just to
				# be able to tell where they come from in case of problems
				$tmpname = tempnam($path, 'pie');

				# remove the created empty file - we really just want the filename
				unlink($tmpname);

				$tmpname .= '.png';
				file_put_contents($tmpname, $image);

				# return path to file
				return $tmpname;
			}
		} else {
			$graph->display();
		}
	}

	/**
	*	Create a barchart
	*/
	public function barchart($in_data=false, $path=null)
	{
		$this->auto_render = false;
		$data = i18n::unserialize( base64_decode($in_data) );
		charts::load('MultipleBar');
		if (!$this->create_pdf) {
			$graph = new MultipleBarChart(800, 600);
		} else {
			$graph = new MultipleBarChart(600, 400);
			$graph->set_bar_width(20);
			$graph->set_bar_gap(-10);
		}

		$barvalues = false;
		$barcolors = false;
		foreach ($data as $tmpkey => $tmpval) {
			$barvalues[$tmpkey] = array($tmpval[1], $tmpval[0]);
			$barcolors[] = false;
			$barcolors[] = $tmpval[2] ? self::$colors['red'] : self::$colors['green'];
		}

		$graph->add_bar_colors($barcolors);
		$graph->set_background_style(null);
		$graph->set_plot_bg_color('#fff');
		$graph->set_data($barvalues);
		$graph->set_margins(7, 20);
		$graph->set_approx_line_gap(50);
		$graph->set_legend_y($this->translate->_('Percent (%)'));
		$graph->set_legend_x($this->translate->_('Period'));

		$graph->draw();
		if (!is_null($path)) {
			# save rendered image to somewhere ($path)
			if (file_exists($path) && is_writable($path)) {
				$image = $graph->get_image();

				# create temp filename with 'pie' as prefix just to
				# be able to tell where they come from in case of problems
				$tmpname = tempnam($path, 'bar');

				# remove the created empty file - we really just want the filename
				unlink($tmpname);

				$tmpname .= '.png';
				file_put_contents($tmpname, $image);

				# return path to file
				return $tmpname;
			}
		} else {
			$graph->display();
		}
	}

	/**
	*	Print message to user about invalid setup.
	*	This could be because of missing database or
	* 	reports module
	*/
	public function invalid_setup()
	{
		$this->template->content = $this->add_view('reports/reports_module');
		$template = $this->template->content;
		$template->error_msg  = $this->translate->_('Some parts in your setup is apparently missing.');
		$template->label_missing_objects = false;
		$template->info = $this->translate->_("make sure you install the latest version of merlin");
	}

	/**
	* Assigns color to labels to be used in a piechart
	*/
	public function _get_color_values($labels=false)
	{
		if (empty($labels)) return false;
		$green 	= '#88cd18';
		$yellow	= '#ffd92f';
		$orange	= '#ff9d08';
		$red 	= '#f7261b';
		$grey 	= '#a0a084';

		$return = false;
		$colors = array(
			'OK' => $green,
			'UP' => $green,
			'WARNING' => $yellow,
			'UNREACHABLE' => $orange,
			'UNKNOWN' => $orange,
			'DOWN' => $red,
			'CRITICAL' => $red,
			'UNDETERMINED' => $grey
		);
		foreach ($labels as $key) {
			$return[] = $colors[strtoupper($key)];
		}
		return $return;
	}

	/**
	 * @desc Re-order alphabetically a group to
	 * 1) sort by host name
	 * 2) sort by service description
	 * A group here refers to the return value given by a call to get_multiple_state_info().
	 * @param array &$group Return parameter.
	 */
	public function _reorder_by_host_and_service(&$group, $report_type=false)
	{
		$testcase = isset($group[';testcase;']) ? $group[';testcase;'] : false;
		unset($group[';testcase;']);

		$num_hosts = count($group['HOST_NAME']);

		# Set up structure ('host1' => array(1,5,8), 'host2' =>array(2,3,4,7), ...)
		# where the numbers are indices of services in original array.
		$host_idxs = array();
		for($i=0 ; $i<$num_hosts ; $i++) {
			$h = $group['HOST_NAME'][$i];
			if(array_key_exists($h, $host_idxs)) {
				$host_idxs[$h][] = $i;
			} else {
				$host_idxs[$h] = array($i);
			}
		}

		$new_order = array(); # The new sorting order. used to re-order every array in $group
		ksort($host_idxs);

		if(!array_key_exists('SERVICE_DESCRIPTION', $group)) {
			$new_order = array_values($host_idxs);
			for($i=0,$n=count($new_order) ; $i<$n ; $i++) {
				$new_order[$i] = $new_order[$i][0];
			}
		} else { #services or servicegroups
			# For every host: re-order service names by alphabet
			foreach($host_idxs as $h => $serv_indices) {
				$tmp_servs = array();
				foreach($serv_indices as $i) {
					$tmp_servs[$i] = $group['SERVICE_DESCRIPTION'][$i];
				}
				asort($tmp_servs);
				$new_order = array_merge($new_order, array_keys($tmp_servs));
			}
		}
		# $new_order now contains the indices to move elements of
		# arrays as for them to become correctly ordered.

		# use new order to reorder all arrays
		$a_names = array_keys($group);
		foreach($a_names as $a_name) {
			$arr =& $group[$a_name];
			if(!is_array($arr)) # only re-order arrays
				continue;

			$tmp_arr = array();
			foreach($new_order as $new_index => $old_index) {
				# print "moving ".$arr[$old_index]." from $old_index to $new_index\n";
				$tmp_arr[$new_index] = $arr[$old_index];
			}

			ksort($tmp_arr);
			$group[$a_name] = $tmp_arr;
		}

		$group[';testcase;'] = $testcase;
	}

	/**
	 * Generate csv output from report data. It also handles output
	 * formatting, like setting the appropriate headers or saving
	 * the file and/or emailing that file
	 *
	 * @param string $type
	 * @param array $data_arr
	 * @param string $sub_type
	 * @param string $group_name = false
	 * @param boolean $in_hostgroup
	 */
	public function _create_csv_output($type, $data_arr, $sub_type, $group_name=false, $in_hostgroup)
	{
		if (empty($data_arr)) {
			return sprintf($this->translate->_("No data found for selection...%sUse the browsers' back button to change report settings."), '<br />');
		}
		$this->auto_render=false;
		$filename = false;
		switch ($type) {
			case 'avail':
				$filename = 'availability.csv';
				break;
			case 'sla':
				$filename = 'sla.csv';
				break;
		}

		// Sometimes we want to save the file instead of sending it to the browser,
		// probably because it's scheduled and/or being triggered manually
		$save_file = request::is_ajax();
		if(!$save_file && PHP_SAPI == 'cli') {
			$save_file = true;
		}
		if (!$save_file) {
			header("Content-disposition: attachment; filename=".$filename);
			if (isset($_SERVER['HTTP_USER_AGENT']) &&
				(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 7') || strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 8')))
			{
				header("Pragma: hack");
				header("Content-Type: application/octet-stream");
				header("Content-Transfer-Encoding: binary");
			} else {
				header("Content-type: text/csv");
			}
		}

		// headlines, not HTTP header
		$csv =  $this->_csv_header($sub_type);
		// =========== GROUPS ===========
		if ($group_name !== false) { // We have a host- or servicegroup
			// Add new csv header fields
			$group_type = !empty($in_hostgroup) ? "HOST_GROUP, " : "SERVICE_GROUP, ";
			$csv = $group_type . $csv;
			foreach ($data_arr as $data_arr_group) {
				// Add group name to csv output
				$csv_group_name = $data_arr_group['groupname'];
				foreach ($data_arr_group as $k => $data) {
					if ($k === 'tot_time' || $k === 'source' || $k === 'states' || $k === 'groupname')
						continue;
					if (!empty($data['states'])) {
						$csv .= '"'.$csv_group_name.'", ';
						$csv .= self::_csv_content($data['states'], $sub_type)."\n";
					}
				}
			}
		} else {
			if (!arr::search($data_arr, 0)) {
				// if we can't find item with index 0, we
				// are dealing with a single item and should
				// skip the foreach loop
				$csv .= self::_csv_content($data_arr['states'], $sub_type)."\n";
			} else {
				foreach ($data_arr as $k => $data) {
					if ($k === 'tot_time' || $k === 'source' || $k === 'states' || $k === 'groupname')
						continue;
					$csv .= self::_csv_content($data['states'], $sub_type)."\n";
				}
			}
		}
		if($save_file) {
			$temp_name = tempnam('/tmp', 'report');
			// copying behavior for definition of K_PATH_CACHE (grep for it,
			// it should be in tcpdf somewhere)
			if(is_file($temp_name)) {
				unlink($temp_name);
			}
			mkdir($temp_name);
			file_put_contents($temp_name.'/'.$filename, $csv);
			// Stealing the already used name, not touching it
			// since it's declared public and such it may be
			// depended upon from the outside
			if($this->pdf_recipients) {
				$report_sender = new Send_report_Model();
				$mail_sent = $report_sender->send($this->pdf_recipients, $temp_name.'/'.$filename, $filename);
				if(request::is_ajax()) {
					if($mail_sent) {
						return json::ok(_("Mail sent"));
					} else {
						return json::fail(_("Could not send email"));
					}
				}

				return $mail_sent;
			}
		} else {
			echo $csv;
		}
		die();
	}

	/**
	*	Return report period strings depending on current
	*	report type (avail/sla)
	*/
	public static function _report_period_strings($type='avail')
	{
		$report_periods = false;
		$selected = false;
		$t = zend::instance('Registry')->get('Zend_Translate');
		$label_lastmonth = $t->_('Last Month');
		$label_thisyear = $t->_('This Year');
		$label_lastyear = $t->_('Last Year');

		switch ($type) {
			case 'avail':
				$report_periods = array(
					"today" => $t->_('Today'),
					"last24hours" => $t->_('Last 24 Hours'),
					"yesterday" => $t->_('Yesterday'),
					"thisweek" => $t->_('This Week'),
					"last7days" => $t->_('Last 7 Days'),
					"lastweek" => $t->_('Last Week'),
					"thismonth" => $t->_('This Month'),
					"last31days" => $t->_('Last 31 Days'),
					"lastmonth"	=> $label_lastmonth,
					"thisyear" => $label_thisyear,
					"lastyear" => $label_lastyear
				);
				$selected = 'last7days';
				break;
			case 'sla':
				$report_periods = array(
					"thisyear" => $label_thisyear,
					"lastyear" => $label_lastyear,
					"lastmonth" => $label_lastmonth,
					"last3months" => $t->_('Last 3 Months'),
					"last6months" => $t->_('Last 6 months'),
					"lastquarter" => $t->_('Last Quarter'),
					"last12months" => $t->_('Last 12 months')
				);
				$selected = 'thisyear';
				break;
		}

		return array('report_period_strings' => $report_periods, 'selected' => $selected);
	}

	/**
	*	Convert report_period strings to timestamp equivalent
	*/
	public function _calculate_time($report_period='', $start_date=false, $end_date=false)
	{
		$year_now 	= date('Y', time());
		$month_now 	= date('m', time());
		$day_now	= date('d', time());
		$week_now 	= date('W', time());
		$weekday_now = date('w', time())-1;
		$time_start	= false;
		$time_end	= false;
		$now = time();

		switch ($report_period) {
			case 'today':
				$time_start = mktime(0, 0, 0, $month_now, $day_now, $year_now);
				$time_end 	= time();
				break;
			case 'last24hours':
				$time_start = mktime(date('H', time()), date('i', time()), date('s', time()), $month_now, $day_now -1, $year_now);
				$time_end 	= time();
				break;
			case 'yesterday':
				$time_start = mktime(0, 0, 0, $month_now, $day_now -1, $year_now);
				$time_end 	= mktime(0, 0, 0, $month_now, $day_now, $year_now);
				break;
			case 'thisweek':
				$time_start = strtotime('today - '.$weekday_now.' days');
				$time_end 	= time();
				break;
			case 'last7days':
				$time_start	= strtotime('now - 7 days');
				$time_end	= time();
				break;
			case 'lastweek':
				$time_start = strtotime('midnight last monday -7 days');
				$time_end	= strtotime('midnight last monday');
				break;
			case 'thismonth':
				$time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01');
				$time_end	= time();
				break;
			case 'last31days':
				$time_start = strtotime('now - 31 days');
				$time_end	= time();
				break;
			case 'lastmonth':
				$time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01 -1 month');
				$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
				break;
			case 'thisyear':
				$time_start = strtotime('midnight '.$year_now.'-01-01');
				$time_end	= time();
				break;
			case 'lastyear':
				$time_start = strtotime('midnight '.$year_now.'-01-01 -1 year');
				$time_end	= strtotime('midnight '.$year_now.'-01-01');
				break;
			case 'last12months':
				$time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -12 months');
				$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
				break;
			case 'last3months':
				$time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -3 months');
				$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
				break;
			case 'last6months':
				$time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -6 months');
				$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
				break;
			case 'lastquarter':
				$t = getdate();
				if($t['mon'] <= 3){
					$lqstart = ($t['year']-1)."-10-01";
					$lqend = ($t['year']-1)."-12-31";
				} elseif ($t['mon'] <= 6) {
					$lqstart = $t['year']."-01-01";
					$lqend = $t['year']."-03-31";
				} elseif ($t['mon'] <= 9){
					$lqstart = $t['year']."-04-01";
					$lqend = $t['year']."-06-30";
				} else {
					$lqstart = $t['year']."-07-01";
					$lqend = $t['year']."-09-30";
				}
				$time_start = strtotime($lqstart);
				$time_end = strtotime($lqend);
				break;
			case 'custom':
				$time_start = is_numeric($start_date) ? $start_date : strtotime($start_date);
				$time_end = is_numeric($end_date) ? $end_date : strtotime($end_date);
				break;
			default:
				if (empty($start_date) || !is_numeric($start_date)) {
					# unknown report_period and no fallback available, so
					# default to last 24 hours, like old cgi's used to do
					return calculate_time('last24hours');
				}

				# $start_date looks sensible, so try some DWIM'ery
				$time_start = $start_date;

				# if no end-date, or end_date is bad, use $now
				if (empty($end_date) || !is_numeric($end_date)
				    || $end_date > $now || $end_date < $time_start)
				{
					$end_date = $now;
				}

				$time_end = $end_date;
		}

		if($time_start > $now)
			$time_start = $now;

		if($time_end > $now)
			$time_end = $now;

		return array($time_start, $time_end);
	}

	public function _print_state_breakdowns($source=false, $values=false, $type="hosts")
	{
		if (empty($values)) {
			return false;
		}
		$tot_time = 0;
		$tot_time_perc = 0;
		$tot_time_known_perc = 0;
		if ($type=='hosts' || $type=='hostgroups') {
			$tot_time = $values['KNOWN_TIME_UP'] +
				$values['KNOWN_TIME_DOWN'] +
				$values['KNOWN_TIME_UNREACHABLE'] +
				$values['TOTAL_TIME_UNDETERMINED'];
			$tot_time_perc = $values['PERCENT_KNOWN_TIME_UP'] +
				$values['PERCENT_KNOWN_TIME_DOWN'] +
				$values['PERCENT_KNOWN_TIME_UNREACHABLE'] +
				$values['PERCENT_TIME_UNDETERMINED_NOT_RUNNING'] +
				$values['PERCENT_TIME_UNDETERMINED_NO_DATA'];
			$var_types = array('UP', 'DOWN', 'UNREACHABLE');
		} else {
			$tot_time = $values['KNOWN_TIME_OK'] +
				$values['KNOWN_TIME_WARNING'] +
				$values['KNOWN_TIME_UNKNOWN'] +
				$values['KNOWN_TIME_CRITICAL'] +
				$values['TOTAL_TIME_UNDETERMINED'];
			$tot_time_perc = $values['PERCENT_KNOWN_TIME_OK'] +
				$values['PERCENT_KNOWN_TIME_WARNING'] +
				$values['PERCENT_KNOWN_TIME_UNKNOWN'] +
				$values['PERCENT_KNOWN_TIME_CRITICAL'] +
				$values['PERCENT_TIME_UNDETERMINED_NOT_RUNNING'] +
				$values['PERCENT_TIME_UNDETERMINED_NO_DATA'];
			$var_types = array('OK', 'WARNING', 'UNKNOWN', 'CRITICAL');
		}

		return array('tot_time' => $tot_time, 'tot_time_perc' => $tot_time_perc, 'var_types' => $var_types, 'values' => $values);
	}

	/**
	* 	@param array  $data_arr report source data, generated by report_class:get_uptime()
	* 	@param string $sub_type The report subtype. Can be 'host' or 'service'.
	* 	@param string $get_vars query string containing values of options for the report<br>
	*       Contains: report_period, rpttimeperiod, scheduleddowntimeasuptime, assumestatesduringnotrunning, includesoftstates, assumeinitialstates, initialassumedhoststate, initialassumedservicestate)
	* 	@param int $start_time Start timestamp for the report.
	* 	@param int $end_time End timestamp for the report.
	*
	* 	@return	array report info divided by states
	*/
	public function _get_multiple_state_info(&$data_arr, $sub_type, $get_vars, $start_time, $end_time, $type)
	{
		$prev_host = '';
		$php_self = url::site().Kohana::config('reports.reports_link').'/generate?type='.$type;
		if (array_key_exists('states', $data_arr) && !empty($data_arr['states']))
			$group_averages = $data_arr['states'];

		$return = array();
		$cnt = 0;
		if ($sub_type=='service') {
			$sum_ok = $sum_warning = $sum_unknown = $sum_critical = $sum_undetermined = 0;
			foreach ($data_arr as $k => $data) {
				if (!reports::is_proper_report_item($k, $data))
					continue;

				$host_name = $data['states']['HOST_NAME'];
				$service_description = $data['states']['SERVICE_DESCRIPTION'];

				$return['host_link'][] = $php_self . "&host_name[]=". $host_name . "&report_type=hosts" . '&start_time=' . $start_time . '&end_time=' . $end_time . '&new_avail_report_setup=1'.$get_vars;
				$return['service_link'][] = $php_self . "&host_name[]=". $host_name . '&service_description[]=' . "$host_name;$service_description" . '&report_type=services&start_time=' . $start_time . '&end_time=' . $end_time . '&new_avail_report_setup=1'.$get_vars;

				$return['HOST_NAME'][] 				= $host_name;
				$return['SERVICE_DESCRIPTION'][] 	= $service_description;
				$return['ok'][] 			= $data['states']['PERCENT_KNOWN_TIME_OK'];
				$return['warning'][] 		= $data['states']['PERCENT_KNOWN_TIME_WARNING'];
				$return['unknown'][] 		= $data['states']['PERCENT_KNOWN_TIME_UNKNOWN'];
				$return['critical'][] 		= $data['states']['PERCENT_KNOWN_TIME_CRITICAL'];
				$return['undetermined'][] 	= $data['states']['PERCENT_TOTAL_TIME_UNDETERMINED'];
				if ($this->scheduled_downtime_as_uptime == 2)
					$return['counted_as_ok'][]  = $data['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP'];

				$prev_host = $host_name;
				$sum_ok += $data['states']['PERCENT_KNOWN_TIME_OK'];
				$sum_warning += $data['states']['PERCENT_KNOWN_TIME_WARNING'];
				$sum_unknown += $data['states']['PERCENT_KNOWN_TIME_UNKNOWN'];
				$sum_critical += $data['states']['PERCENT_KNOWN_TIME_CRITICAL'];
				$sum_undetermined += $data['states']['PERCENT_TOTAL_TIME_UNDETERMINED'];
				$cnt++;
			}
			$return['nr_of_items'] = $cnt;
			$return['average_ok'] = $sum_ok!=0 ? reports::format_report_value($sum_ok/$cnt) : '0';
			$return['average_warning'] = $sum_warning!=0 ? reports::format_report_value($sum_warning/$cnt) : '0';
			$return['average_unknown'] = $sum_unknown!=0 ? reports::format_report_value($sum_unknown/$cnt) : '0';
			$return['average_critical'] = $sum_critical!=0 ? reports::format_report_value($sum_critical/$cnt) : '0';
			$return['average_undetermined'] = $sum_undetermined!=0 ? reports::format_report_value($sum_undetermined/$cnt) : '0';
			$return['group_average_ok'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_OK']);
			$return['group_average_warning'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_WARNING']);
			$return['group_average_unknown'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_UNKNOWN']);
			$return['group_average_critical'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_CRITICAL']);
			$return['group_average_undetermined'] = reports::format_report_value($group_averages['PERCENT_TOTAL_TIME_UNDETERMINED']);
			$return['groupname'] = $data_arr['groupname']!='' ? 'Servicegroup: '.$data_arr['groupname'] : false;
			$return[';testcase;'] = $data_arr[';testcase;'];
		} else {
			// host
			$sum_up = $sum_down = $sum_unreachable = $sum_undetermined = 0;
			foreach ($data_arr as $k => $data) {
			if (!reports::is_proper_report_item($k, $data))
					continue;
				$host_name = $data['states']['HOST_NAME'];
				$return['host_link'][] = $php_self . "&host_name[]=". $host_name. "&report_type=hosts" .
				'&start_time=' . $start_time . '&end_time=' . $end_time .$get_vars;
				$return['HOST_NAME'][] 		= $host_name;
				$return['up'][] 			= $data['states']['PERCENT_KNOWN_TIME_UP'];
				$return['down'][] 			= $data['states']['PERCENT_KNOWN_TIME_DOWN'];
				$return['unreachable'][]	= $data['states']['PERCENT_KNOWN_TIME_UNREACHABLE'];
				$return['undetermined'][]	= $data['states']['PERCENT_TOTAL_TIME_UNDETERMINED'];
				if ($this->scheduled_downtime_as_uptime == 2)
					$return['counted_as_up'][]  = $data['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP'];

				$sum_up += $data['states']['PERCENT_KNOWN_TIME_UP'];
				$sum_down += $data['states']['PERCENT_KNOWN_TIME_DOWN'];
				$sum_unreachable += $data['states']['PERCENT_KNOWN_TIME_UNREACHABLE'];
				$sum_undetermined += $data['states']['PERCENT_TOTAL_TIME_UNDETERMINED'];
				$cnt++;
			}
			$return['nr_of_items'] = $cnt;
			$return['average_up'] = $sum_up!=0 ? reports::format_report_value($sum_up/$cnt) : '0';
			$return['average_down'] =  $sum_down!=0 ? reports::format_report_value($sum_down/$cnt) : '0';
			$return['average_unreachable'] = $sum_unreachable!=0 ? reports::format_report_value($sum_unreachable/$cnt) : '0';
			$return['average_undetermined'] = $sum_undetermined!=0 ? reports::format_report_value($sum_undetermined/$cnt) : '0';

			$return['group_average_up'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_UP']);
			$return['group_average_down'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_DOWN']);
			$return['group_average_unreachable'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_UNREACHABLE']);
			$return['group_average_undetermined'] = reports::format_report_value($group_averages['PERCENT_TOTAL_TIME_UNDETERMINED']);
			$return['groupname'] = $data_arr['groupname']!='' ? 'Hostgroup: '.$data_arr['groupname'] : false;
			$return[';testcase;'] = $data_arr[';testcase;'];
		}
		return $return;
	}

	/**
	*	Returns the fields needed for csv output.
	* 	The order of the fields are the same as in avail.cgi
	*/
	public function _get_csv_fields($type=false)
	{
		$fields['host'] = array(
			'HOST_NAME',
			'TIME_UP_SCHEDULED',
			'PERCENT_TIME_UP_SCHEDULED',
			'PERCENT_KNOWN_TIME_UP_SCHEDULED',
			'TIME_UP_UNSCHEDULED',
			'PERCENT_TIME_UP_UNSCHEDULED',
			'PERCENT_KNOWN_TIME_UP_UNSCHEDULED',
			'TOTAL_TIME_UP',
			'PERCENT_TOTAL_TIME_UP',
			'PERCENT_KNOWN_TIME_UP',
			'TIME_DOWN_SCHEDULED',
			'PERCENT_TIME_DOWN_SCHEDULED',
			'PERCENT_KNOWN_TIME_DOWN_SCHEDULED',
			'TIME_DOWN_UNSCHEDULED',
			'PERCENT_TIME_DOWN_UNSCHEDULED',
			'PERCENT_KNOWN_TIME_DOWN_UNSCHEDULED',
			'TOTAL_TIME_DOWN',
			'PERCENT_TOTAL_TIME_DOWN',
			'PERCENT_KNOWN_TIME_DOWN',
			'TIME_UNREACHABLE_SCHEDULED',
			'PERCENT_TIME_UNREACHABLE_SCHEDULED',
			'PERCENT_KNOWN_TIME_UNREACHABLE_SCHEDULED',
			'TIME_UNREACHABLE_UNSCHEDULED',
			'PERCENT_TIME_UNREACHABLE_UNSCHEDULED',
			'PERCENT_KNOWN_TIME_UNREACHABLE_UNSCHEDULED',
			'TOTAL_TIME_UNREACHABLE',
			'PERCENT_TOTAL_TIME_UNREACHABLE',
			'PERCENT_KNOWN_TIME_UNREACHABLE',
			'TIME_UNDETERMINED_NOT_RUNNING',
			'PERCENT_TIME_UNDETERMINED_NOT_RUNNING',
			'TIME_UNDETERMINED_NO_DATA',
			'PERCENT_TIME_UNDETERMINED_NO_DATA',
			'TOTAL_TIME_UNDETERMINED',
			'PERCENT_TOTAL_TIME_UNDETERMINED'
		);
		$fields['service'] = array(
			'HOST_NAME',
			'SERVICE_DESCRIPTION',
			'TIME_OK_SCHEDULED',
			'PERCENT_TIME_OK_SCHEDULED',
			'PERCENT_KNOWN_TIME_OK_SCHEDULED',
			'TIME_OK_UNSCHEDULED',
			'PERCENT_TIME_OK_UNSCHEDULED',
			'PERCENT_KNOWN_TIME_OK_UNSCHEDULED',
			'TOTAL_TIME_OK',
			'PERCENT_TOTAL_TIME_OK',
			'PERCENT_KNOWN_TIME_OK',
			'TIME_WARNING_SCHEDULED',
			'PERCENT_TIME_WARNING_SCHEDULED',
			'PERCENT_KNOWN_TIME_WARNING_SCHEDULED',
			'TIME_WARNING_UNSCHEDULED',
			'PERCENT_TIME_WARNING_UNSCHEDULED',
			'PERCENT_KNOWN_TIME_WARNING_UNSCHEDULED',
			'TOTAL_TIME_WARNING',
			'PERCENT_TOTAL_TIME_WARNING',
			'PERCENT_KNOWN_TIME_WARNING',
			'TIME_UNKNOWN_SCHEDULED',
			'PERCENT_TIME_UNKNOWN_SCHEDULED',
			'PERCENT_KNOWN_TIME_UNKNOWN_SCHEDULED',
			'TIME_UNKNOWN_UNSCHEDULED',
			'PERCENT_TIME_UNKNOWN_UNSCHEDULED',
			'PERCENT_KNOWN_TIME_UNKNOWN_UNSCHEDULED',
			'TOTAL_TIME_UNKNOWN',
			'PERCENT_TOTAL_TIME_UNKNOWN',
			'PERCENT_KNOWN_TIME_UNKNOWN',
			'TIME_CRITICAL_SCHEDULED',
			'PERCENT_TIME_CRITICAL_SCHEDULED',
			'PERCENT_KNOWN_TIME_CRITICAL_SCHEDULED',
			'TIME_CRITICAL_UNSCHEDULED',
			'PERCENT_TIME_CRITICAL_UNSCHEDULED',
			'PERCENT_KNOWN_TIME_CRITICAL_UNSCHEDULED',
			'TOTAL_TIME_CRITICAL',
			'PERCENT_TOTAL_TIME_CRITICAL',
			'PERCENT_KNOWN_TIME_CRITICAL',
			'TIME_UNDETERMINED_NOT_RUNNING',
			'PERCENT_TIME_UNDETERMINED_NOT_RUNNING',
			'TIME_UNDETERMINED_NO_DATA',
			'PERCENT_TIME_UNDETERMINED_NO_DATA',
			'TOTAL_TIME_UNDETERMINED',
			'PERCENT_TOTAL_TIME_UNDETERMINED'
		);
		return $fields[$type];
	}

	/**
	 * Returns the csv value string to be printed for the selected type (host/service)
	 *
	 * @param array $states = false
	 * @param string $type = 'host'
	 * @return boolean|string
	 */
	public function _csv_content(&$states=false, $type='host')
	{
		if (!$states) {
			return false;
		}
		$csv = false;

		$fields = $this->_get_csv_fields($type);

		foreach ($fields as $field_name) {
			if ($field_name == 'HOST_NAME' || $field_name == 'SERVICE_DESCRIPTION') {
				$csv[] = '"' . $states[$field_name] . '"';
			} else {
				$csv[] = strstr($field_name, 'PERCENT') ? reports::format_report_value($states[$field_name]).'%' : $states[$field_name];
			}
		}
		return implode(', ', $csv);
	}

	/**
	*	Get the csv header line
	*/
	public function _csv_header($type=false)
	{
		$fields = $this->_get_csv_fields($type);
		$csv = implode(', ', $fields);
		return $csv."\n";
	}


	/**
	*	Convert nasty chars before creating report image file
	*/
	public function _img_filename_convert($filename=false)
	{
		$filename = trim($filename);
		$filename = str_replace('/', '-', $filename);
		$filename = str_replace(' ', '_', $filename);
		$filename = str_replace(';', '_', $filename);

		return $filename;
	}

	public function _print_states_for_services($host_name=false, $start_date=false, $end_date=false)
	{
		$options = self::$options;
		$dep_vars = self::$dep_vars;
		$err_msg = $this->err_msg;

		$host_name = trim($host_name);
		if (empty($host_name)) {
			return false;
		}
		$service_model = new Service_Model();
		$res = $service_model->get_where('host_name', $host_name);
		if (!empty($res)) {
			$res->result(false); # convert to array
			$service_arr = array();

			$report_class = new Reports_Model();
			foreach ($res as $row)
				$service_arr[] = $host_name.";".$row['service_description'];

			foreach ($options as $var => $new_var) {
				if (!$report_class->set_option($new_var, arr::search($_REQUEST, $var, false))) {
					$err_msg .= sprintf($this->translate->_("Could not set option '%s' to %s'"),
						$new_var, arr::search($_REQUEST, $var, false))."'<br />";
				}
			}

			foreach ($dep_vars as $check => $set)
				if (isset($_REQUEST[$check]) && !empty($_REQUEST[$check]))
					foreach ($set as $dep => $key)
						if (!$report_class->set_option($key, $_REQUEST[$dep])) {
							$err_msg .= "Could not set option '".$key."' to '".$_REQUEST[$dep]."'<br />";
							$err_msg .= sprintf($this->translate->_("Could not set option '%s' to %s'"),
								$key, $_REQUEST[$dep])."'<br />";
						}

			$report_class->set_option('host_name', $host_name);
			$report_class->set_option('service_description', $service_arr);

			$data_arr = $report_class->get_uptime($host_name, $service_arr, $start_date, $end_date, false, false);
			return $data_arr;
		}
		return false;
	}


	public function _check_report_type($report_type=false, $in_host=false, $in_service=false, $servicegroup=false, $hostgroup=false)
	{
		if (empty($report_type)) {
			if (!empty($in_host)) {
				if (!empty($in_service)) {
					$report_type = 'services';
				} else {
					$report_type = 'hosts';
				}
			} else {
				if (!empty($servicegroup)) {
					$report_type = 'servicegroups';
				} else {
					if (!empty($hostgroup)) {
						$report_type = 'hostgroups';
					}
				}
			}
		}
		return $report_type;
	}

	/**
	*	Fetch and print information on saved timperiods
	*/
	public static function _get_reporting_periods()
	{
		$res = Timeperiod_Model::get_all();
		if (!$res)
			return false;
		$return = false;
		foreach ($res as $row) {
			$return .= '<option value="'.$row->timeperiod_name.'">'.$row->timeperiod_name.'</option>';
		}
		return $return;
	}

	/**
	 * Fetch host alias information
	 */
	public function _get_host_alias($host_name=false)
	{
		if (empty($host_name))
			return false;

		$host_name = trim($host_name);
		$res = Host_Model::get_where('host_name', $host_name);
		if (!$res)
			return false;
		$row = $res->current();
		return $row->alias;
	}

	/**
	 * Expands a series of groupnames (host or service) into its member objects, and calculate uptime for each
	 *
	 * @param array $arr List of groups
	 * @param string $type The type of objects in $arr. Valid values are "hostgroup" or "servicegroup".
	 * @param mixed $start_date datetime or unix timestamp
	 * @param mixed $end_date datetime or unix timestamp
	 * @global array Report options
	 * @global array Dependent report options
	 * @global array
	 * @global string Error log.
	 * @return array Calculated uptimes.
	 */
	public function _expand_group_request($arr=false, $type='hostgroup', $start_date, $end_date)
	{
		$options = self::$options;
		$dep_vars = self::$dep_vars;
		$err_msg = $this->err_msg;

		if (empty($arr))
			return false;
		if ($type!='hostgroup' && $type!='servicegroup')
			return false;
		$hostgroup = false;
		$servicegroup = false;
		$data_arr = false;
		foreach ($arr as $$type) {
			$rpt_class = new Reports_Model();
			foreach ($options as $var => $new_var) {
				if (!$rpt_class->set_option($new_var, arr::search($_REQUEST, $var, false))) {
					$err_msg .= sprintf($this->translate->_("Could not set option '%s' to %s'"),
						$new_var, arr::search($_REQUEST, $var, false))."'<br />";
				}
			}
			foreach ($dep_vars as $check => $set)
				if (isset($_REQUEST[$check]) && !empty($_REQUEST[$check]))
					foreach ($set as $dep => $key)
						if (!$rpt_class->set_option($key, $_REQUEST[$dep]))
							$err_msg .= sprintf($this->translate->_("Could not set option '%s' to %s'"),
								$key, $_REQUEST[$dep])."'<br />";

			$rpt_class->set_option(substr($type, 0, strlen($type)).'_name', $$type);
			$data_arr[] = $rpt_class->get_uptime(false, false, $start_date, $end_date, $hostgroup, $servicegroup);
			unset($rpt_class);
		}
		return $data_arr;
	}


	/**
	*	Convert between yes/no and 1/0
	* 	@param 	mixed val, value to be converted
	* 	@param 	bool use_int, to indicate if we should use
	* 			1/0 instead of yes/no
	* 	@return mixed str/int
	*/
	public function _convert_yesno_int($val, $use_int=true)
	{
		$return = false;
		if ($use_int) {
			// This is the way that we normally do things
			switch (strtolower($val)) {
				case 'yes':
					$return = 1;
					break;
				case 'no':
					$return = 0;
					break;
				default:
					$return = $val;
			}
		} else {
			// This is the old way, using yes/no values
			switch ($val) {
				case 1:
					$return = 'yes';
					break;
				case 0:
					$return = 'no';
					break;
				default:
					$return = $val;
			}
		}
		return $return;
	}


	/**
	 * Convert assumed states between old cgi's and
	 * new avail_result.php.
	 *
	 * @param 	mixed $val, Value to be converted
	 * @param 	str $type, host/service
	 * @param 	bool $to_new,
	 * 				true => return NEW values,
	 * 				false => return OLD values
	 * @return str The converted state. It is important this number is string since report_class::set_option expects this
	 */
	public function _convert_assumed_state($val=false, $type='host', $to_new=true)
	{
		$arr = false;
		$retval = false;
		// new value => value used by cgi's
		$host_states = array(
			-1 => -1,
			-2 => 0,
			-3 => 0,
			0 => 3,
			1 => 4,
			2 => 5
		);

		$service_states = array(
			-1 => -1,
			-2 => 0,
			-3 => 0,
			0 => 6,
			1 => 8,
			2 => 9,
			3 => 7
		);

		switch ($type) {
			case 'host':
				$arr = $host_states;
				break;
			case 'service':
				$arr = $service_states;
				break;
			default:
				return (string)$val;
		}

		if ($to_new === false) {
			if (array_key_exists($val, $arr)) {
				return (string)$arr[$val];
			}
			// unable to convert...
			return (string)$val;
		} else {
			// convert the other way around
			// ie, return key corresponding to value
			$retval = array_search($val, $arr);
			return (string)($retval !== false ? $retval : $val);
		}
	}


	public function _get_csv_link($path=false, $params=false)
	{
		$path = addslashes(trim($path));
		$params = addslashes(trim($params));
		$return = form::open(Kohana::config('reports.reports_link').'/generate', array('style' => 'display:block; position: absolute; top: 0px; right: 71px'));
		$return .= "<div>\n";
		$url_params = '';
		$url_params_to_skip = array('js_start_time', 'js_end_time', 's1'); # params that just f--k up things

		foreach($_REQUEST as $key => $val)
		{
			if(is_array($val))
			{
				# note: only support arrays of depth==1
				foreach($val as $subval)
				{
					$return .= "<input type='hidden' name='{$key}[]' value='$subval' />\n";
				}
			}
			else
			{
				if (strstr($key, 'month_'))
					continue;
				if(!in_array($key, $url_params_to_skip))
					$return .= "<input type='hidden' name='$key' value='$val' />\n";
			}
		}
		$return .= form::hidden('csvoutput', 1);
		$label = $this->translate->_('Download report as CSV');
		$return .= "<input type='image' src='".Kohana::config('config.site_domain').$this->add_path('icons/32x32/page-csv.png').
			"' alt='".$label."' title='".$label."' style='border: 0px; width: 32px; height: 32px; margin-top: 13px; background: none; margin-right: 7px' /></div></form>\n";
		return $return;
	}

	/**
	*	decide what date format to use for calendar
	*/
	public function _get_date_format($get_php=false)
	{
		return cal::get_calendar_format($get_php);
	}

	/**
	*	Convert a date format string back to a timestamp
	*/
	public function _timestamp_format($date_str=false, $format_str = false)
	{
		if (empty($format_str))
			$format_str = self::_get_date_format(); # fetch if not set

		$date_str = str_replace('-', '/', $date_str);
		# use now as date if nothing supplied as input
		$date_str = empty($date_str) ? date($format_str) : $date_str;
		$format_str = trim($format_str);
		$timestamp_format = false;
		return strtotime($date_str);
	}

	public function _build_testcase_form($test, $prefix = '', $suffix = '')
	{
		if (!is_array($test) || empty($test))
			return '';

		$auth = new Nagios_auth_Model();
		if (!$auth->view_hosts_root) {
			return false;
		}
		unset($auth);

		if (!$prefix)
			$test_buf = form::open('reports/mktest');
		else
			$test_buf = '';

		foreach ($test as $k => $v) {
			if (is_array($v)) {
				$test_buf .= self::_build_testcase_form($v, $k, $suffix . '[]');
				continue;
			}
			$test_buf .= "\t<input type='hidden' value='$v' ";
			if ($prefix)
				$test_buf .= "name='test[$prefix]$suffix'";
			else
				$test_buf .= "name='test[$k]$suffix'";
			$test_buf .= " />\n";
		}
		if (!$prefix)
			$test_buf .= "<input type='submit' name='action' value='Make testcase' />" .
			"</form>\n";

		return $test_buf;
	}

	/**
	*	Create testcase
	*/
	public function mktest($desc='auto-created test')
	{
		$this->auto_render=false;
		Kohana::close_buffers(FALSE);
		$test = array();
		$correct = array();

		$table = 'report_data';
		if (!isset($_REQUEST['test'])) {
			die("No test data to produce test-case from");
		}
		$test = $_REQUEST['test'];
		if (isset($_REQUEST['correct']))
			$correct = $_REQUEST['correct'];
		if (!isset($test['db_start_time']) && isset($test['start_time']))
			$test['db_start_time'] = $test['start_time'];
		if (!isset($test['db_end_time']) && isset($test['end_time']))
			$test['db_end_time'] = $test['end_time'];
		if (!isset($test['start_time']) && isset($test['db_start_time']))
			$test['start_time'] = $test['db_start_time'];
		if (!isset($test['end_time']) && isset($test['db_end_time']))
			$test['end_time'] = $test['db_start_time'];

		if (!isset($test['db_start_time']))
			die("No db_start_time defined\n");
		if (!isset($test['db_end_time']))
			die("No db_end_time defined\n");

		$db_start_time = $test['db_start_time'];
		$db_end_time = $test['db_end_time'];
		unset($test['db_start_time']);
		unset($test['db_end_time']);

		$showlog = showlog::get_path();

		if ($showlog === false) {
			die($this->translate->_('Unable to find the showlog executable'));
		}

		if (PHP_SAPI !== 'cli') {
			header("Content-Type: text/plain");
			header("Content-Disposition: attachment; filename=report-test.txt");
		}

		$retcode = 0;
		echo "$desc {\n";
		reports::print_test_settings($test);
		echo Reports_Model::print_db_lines("\t\t", $table, $test, $db_start_time, $db_end_time);
		echo "\tlog {\n";
		$nagios_path = Kohana::config('config.nagios_base_path');
		$cmd = "$showlog ".$nagios_path."/var/nagios.log";
		passthru($cmd, $retcode);
		echo "\t}\n\n";

		echo "}\n";
		die();
	}

	/**
	*	Schedule a report
	*/
	public function schedule()
	{
		$this->auto_render=false;
		// collect input values
		$report_id = arr::search($_REQUEST, 'report_id'); // scheduled ID
		$rep_type = arr::search($_REQUEST, 'rep_type');
		$saved_report_id = arr::search($_REQUEST, 'saved_report_id'); // ID for report module
		$period = arr::search($_REQUEST, 'period');
		$recipients = arr::search($_REQUEST, 'recipients');
		$filename = arr::search($_REQUEST, 'filename');
		$description = arr::search($_REQUEST, 'description');
		$local_persistent_filepath = arr::search($_REQUEST, 'local_persistent_filepath');
		$module_save = arr::search($_REQUEST, 'module_save');

		if (!$module_save) {
			# if this parameter is set to false, we have to lookup
			# $rep_type since it is passed as a string (avail/sla)
			$rep_type = Scheduled_reports_Model::get_report_type_id($rep_type);
		}
		$recipients = str_replace(';', ',', $recipients);
		$rec_arr = explode(',', $recipients);
		$a_recipients = false;
		if (!empty($rec_arr)) {
			foreach ($rec_arr as $recipient) {
				if (trim($recipient)!='') {
					$a_recipients[] = trim($recipient);
				}
			}
			if (!empty($a_recipients)) {
				$recipients = implode(',', $a_recipients);
				$recipients = $this->_convert_special_chars($recipients);
			}
		}

		$filename = $this->_convert_special_chars($filename);
		$filename = $this->_check_filename($filename);

		$ok = Scheduled_reports_Model::edit_report($report_id, $rep_type, $saved_report_id, $period, $recipients, $filename, $description, $local_persistent_filepath);

		if (!is_int($ok)) {
			return json::fail(sprintf($this->translate->_("An error occurred when saving scheduled report (%s)"), $ok));
		}
		return json::ok(array('id' => $ok));
	}

	/**
	 * Generate "show as pdf" link with icon, as a small html form.
	 *
	 * @param string $report   The type of report to produce. Currently supported values are 'sla' and 'avail'.
	 * @param string $user_url The url to convert to PDF. If none is given, the calling script is used. All request variables are passed to the url.
	 * @param array $user_options Custom options sent to html2ps
	 * @param string $action_url The html2ps script that handles the link
	 * @return string Complete HTML for the resulting link
	 */
	public function _get_pdf_link($report, $user_url=false, $user_options=false, $user_action_url=false)
	{
		$pdf_img_src = $this->add_path('icons/32x32/page-pdf.png');
		$pdf_img_alt = $this->translate->_('Show as pdf');

		$default_filename = 'report.pdf';
		$default_options = array
		(
			'create_pdf' => true
		);
		$default_action_url = Kohana::config('reports.reports_link').'/generate';

		if (PHP_SAPI != "cli") {
			# never try to use $_SERVER variables when
			# called from commandline (test and such)
			$url = $_SERVER['SERVER_ADDR'].$_SERVER['PHP_SELF'];
		}

		if($user_url)
			$url = $user_url;


		$options = $default_options;
		if($user_options)
		{
			foreach($user_options as $opt => $val)
				$options[$opt] = $val;
		}

		$action_url = $default_action_url;
		if($user_action_url)
			$action_url = $user_action_url;

		$form = form::open($action_url, array('style' => 'display:block; position: absolute; top: -1px; right: 39px;'));
		$form .= '<div>';
		$form .= "<input type='hidden' name='report' value='$report' />\n";
		$url_params = '';
		$url_params_to_skip = array('js_start_time', 'js_end_time', 's1'); # params that just f--k up things
		foreach($_REQUEST as $key => $val)
		{
			if(is_array($val))
			{
				# note: only support arrays of depth==1
				foreach($val as $subval)
				{
					$form .= "<input type='hidden' name='{$key}[]' value='$subval' />\n";
				}
			}
			else
			{
				if(!in_array($key, $url_params_to_skip))
					$form .= "<input type='hidden' name='$key' value='$val' />\n";
			}
		}

		foreach($options as $opt => $val)
		{
			if(is_array($val))
			{
				foreach($val as $subkey => $subval)
					$form .= '<input type="hidden" name="'.$opt[$subkey].'" value="'.$subval.'" />'."\n";
			}
			else
				$form .= "<input type='hidden' name='$opt' value='$val' />\n";
		}

		$pdf_img_src = Kohana::config('config.site_domain').$pdf_img_src;
		$form .= '<input type="image" src="'.$pdf_img_src.'" title="'.$pdf_img_alt.'" '
			.'value="'.$pdf_img_alt.'"  style="border: 0px; width: 32px; height: 32px; margin-top: 14px; background: none" />';

		$form .= '</div>';
		$form .= "</form>";

		return $form;
	}


	/**
	*	Create pdf
	* 	Will also send the generated PDF as an attachment
	* 	if $this->pdf_recipients is set.
	*/
	public function _pdf()
	{
		# include necessary files for PDF creation
		pdf::start();
		$this->auto_render=false;

		global $l; # required for tcpdf

		if (isset($l['w_page'])) { # use ninja translation
			$l['w_page'] = $this->translate->_('Page');
		}

		if (isset($l['w_op5'])) { # use ninja translation
			$l['w_op5'] = $this->translate->_('This report is produced by op5 Monitor.');
		}

		$type = $this->type;
		$filename = $this->pdf_filename;

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$title = isset($this->pdf_data['title']) ? $this->pdf_data['title'] : $this->translate->_('Ninja PDF Report');
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Ninja4Nagios');
		$pdf->SetSubject($title);
		$pdf->SetKeywords('Ninja, '.Kohana::config('config.product_name').', PDF, report, '.$type);

		// set header and footer fonts
		$pdf->setFooterFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_DATA));

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
		$pdf->SetFont('helvetica', '', 10);

		// add a page
		$pdf->AddPage();

		// set color for filler
		$pdf->SetFillColor(255, 255, 0);
		//$pdf->SetLineStyle(array('color' => array(255, 255, 255)));

		// ---------------------------------------------------------

		if (PHP_SAPI == 'cli') {
			$site = Kohana::config('config.site_domain');
			$path = realpath(dirname(__FILE__).'/../../').'/';
			$cont = $this->pdf_data['content'];
			$this->pdf_data['content'] = str_replace($site, $path, $cont);
		}

		$images = array();
		if ($this->type == 'avail') {
			$image_string = '';
			# handle piechart data  - render images
			if (isset($this->pdf_data['pie_data'])) {
				if (is_array($this->pdf_data['pie_data'])) {
					$data_str = $this->pdf_data['pie_data'];
					for ($i = 0; $i < sizeof($data_str); $i++) {
						$image_string .= ($i%2 == 0) ? '<table cellspacing="30"><tr><td>' : '<td>';
						$image_string .= '<table border="1" cellpadding="5" style="width: 326px">';
						$img = $this->piechart($data_str[$i]['img'], K_PATH_CACHE);
						$images[] = $img; # store absolute path to file for later removal
						$image_string .= '<tr><td style="font-size: 0.9em; background-color: #f4f4f4; font-weight: bold">'.($this->translate->_('Status Overview').': '.$data_str[$i]['host']).'</td></tr>'.
						  '<tr><td><img style="width:320px; height:210px" src="'.$img.'" /></td></tr>';
						$image_string .= '</table>';
						$image_string .= ($i%2 == 0) ? '</td>' : '</td></tr></table>';
					}
					if (sizeof($data_str)%2 == 1)
						$image_string .= '</tr></table>';
				} else {
					# generate image
					$data_str = $this->pdf_data['pie_data'];
					$img = $this->piechart($data_str, K_PATH_CACHE);
					$images[] = $img;
					$image_string .= '<table border="1" cellpadding="5" style="width: 326px">';
					$image_string .= '<tr><td><img style="width:320px; height:210px" src="'.$img.'" /></td></tr>';
					$image_string .= '</table>';
				}
			}
		} else {
			# sla
			$nr = 0;
			foreach($this->data_arr as $i => $report) {
				$nr++;
				$data_str = $report['data_str'];
				$img = $this->barchart($data_str, K_PATH_CACHE);
				$images[] = $img;
				$this->pdf_data['content'] = str_replace("#chart_placeholder_$nr#", '<img src="'.$img.'" />', $this->pdf_data['content']);
			}
		}

		$pdf->writeHTML($this->pdf_data['header'], true, 0, true, 0);

		if (isset($this->pdf_data['trends_graph'])) {
			$pdf->writeHTML($this->pdf_data['trends_graph'], true, 0, true, 0);
		}

		$pdf->writeHTML($this->pdf_data['content'], true, 0, true, 0);

		if (isset($image_string) && !empty($image_string)) {
			$pdf->writeHTML($image_string, true, 0, true, 0);
		}
		# remove all temporary images
		foreach ($images as $i) {
			unlink($i);
		}

		if (isset($this->pdf_data['svc_content'])) {
			$pdf->writeHTML($this->pdf_data['svc_content'], true, 0, true, 0);
		}

		# print log data if available
		if (isset($this->pdf_data['log_data']) && !empty($this->pdf_data['log_data'])) {
			$pdf->writeHTML($this->pdf_data['log_data'], true, 0, true, 0);
		}

		$filename = !empty($filename) ? $filename : str_replace(' ', '_', $title);
		$filename = trim($filename);
		$pdf_title = str_replace('_', ' ', str_replace('.pdf', '', $filename));
		$pdf->SetTitle($pdf_title);

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

		if($this->pdf_local_persistent_filepath && 'F' == $action) {
			try {
				persist_pdf::save($filename, $this->pdf_local_persistent_filepath);
			} catch(Exception $e) {
				if(request::is_ajax()) {
					return json::fail($e->getMessage());
				}

				// @todo log failure
				echo "<pre>";
				var_dump(__LINE__);
				var_dump($e->getMessage());
				var_dump('DYING');
				die;
			}
		}

		$mail_sent = 0;
		if ($send_by_mail) {
			$report_sender = new Send_report_Model();
			$mail_sent = $report_sender->send($this->pdf_recipients, $filename, str_replace(K_PATH_CACHE.'/', '', $filename));

			if(request::is_ajax()) {
				if($mail_sent) {
					return json::ok(_("Mail sent"));
				} else {
					return json::fail(_("Could not send email"));
				}
			}
			return $mail_sent;
		}

		if(request::is_ajax()) {
			return json::ok();
		}

		return true;
	}

	/**
	*	Fetch data from report_class
	* 	Uses split_month_data() to split start- and end_time
	* 	on months.
	*/
	public function get_sla_data($months=false, $objects=false)
	{
		$report_data = false;

		if (empty($months) || empty($objects)) {
			return false;
		}

		// OK, we have start and end but we will have to split
		// this time into parts according to sla_periods (months)
		$time_tmp = $this->_split_month_data($months, $this->start_date, $this->end_date);
		$time_arr = $time_tmp;//array();
		// only use month entered by the user regardless of start- or endtime
		$option_name = false;
		$data = false;
		if (preg_match('/groups$/', $this->report_type)) {
			foreach ($time_arr as $mnr => $dates) {
				$data_tmp = $this->_expand_group_request($objects, substr($this->report_options['report_type'], 0,
					strlen($this->report_options['report_type'])-1), $dates['start'], $dates['end']);
				if (!empty($data_tmp))
					foreach ($data_tmp as $val) {
						if ($val !== false)
						# @@@DEBUG: groupname empty?
						$data[$val['groupname']][$mnr] = array(
							'source' => $val['source'],
							'states' => $val['states'],
							'tot_time' => $val['tot_time'],
							'groupname' => $val['groupname']
							);
					}
			}
			$report_data = $this->_sla_group_data($data);
		} else {
			$option_name = preg_match('/hosts/', $this->report_type) ? 'host_name' : 'service_description';
			foreach ($time_arr as $mnr => $dates) {
				$report_class = new Reports_Model();
				foreach (self::$options as $var => $new_var) {
					if (!$report_class->set_option($new_var, arr::search($_REQUEST, $var))) {
						$this->err_msg .= sprintf($this->translate->_("Could not set option '%s' to '%s'"), $new_var, arr::search($_REQUEST, $var));
					}
				}
				foreach (self::$dep_vars as $check => $set) {
					if (isset($_REQUEST[$check]) && !empty($_REQUEST[$check])) {
						foreach ($set as $dep => $key) {
							if (!$report_class->set_option($key, $_REQUEST[$dep])) {
								$this->err_msg .= sprintf($this->translate->_("Could not set option '%s' to '%s'"), $key, $_REQUEST[$dep]);
							}
						}
					}
				}
				$report_class->set_option($option_name, $objects);
				$data_tmp = $report_class->get_uptime(false, false, $dates['start'], $dates['end']);

				# The next line extracts _GROUPWIDE STATES_, discards individual member info (numeric indices)
				$data[$mnr] = array(
					'source' => $data_tmp['source'],
					'states' => $data_tmp['states'],
					'tot_time' => $data_tmp['tot_time'],
					'groupname' => $data_tmp['groupname']
				);
				unset($report_class);
			}
			$report_data = $this->_sla_object_data($data);
		}
		return $report_data;
	}

	/**
	*	Mangle SLA data for host(s) or service(s)
	*/
	public function _sla_object_data($sla_data = false)
	{
		$report_data = false;
		foreach ($sla_data as $months_key => $period_data) {
			$sourcename = $this->_get_sla_group_name($period_data);
			if (array_key_exists($months_key, $this->in_months)) {
				if (arr::search($period_data, 'states')) {
					$real_val = $period_data['states'][self::$sla_field_names[$this->report_options['report_type']]];

					# control colour of bar depending on value
					# true = green, false = red
					$sla_ok = $this->in_months[$months_key] > $real_val ? true : false;
				} else {
					$sla_ok = false;
					$real_val = 0;
				}
				$data[$this->abbr_month_names[$months_key-1]] = array($real_val, $this->in_months[$months_key], $sla_ok);
				if ($this->scheduled_downtime_as_uptime == 2)
					$table_data[$sourcename][$this->abbr_month_names[$months_key-1]][] = array($real_val, $this->in_months[$months_key], $period_data['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP']);
				else
					$table_data[$sourcename][$this->abbr_month_names[$months_key-1]][] = array($real_val, $this->in_months[$months_key]);
			}
		}

		$data_str 		= base64_encode(serialize($data));
		$member_links 	= array();
		$avail_links 	= false;
		if(strpos($sourcename, ',') !== false) {
			$members = explode(',', $sourcename);
			foreach($members as $member) {
				$member_links[] = $this->_generate_sla_member_link($member, $this->object_varname);
			}
			$avail_links = $this->_generate_avail_member_link($members, $this->object_varname);
		} else {
			$avail_links = $this->_generate_avail_member_link($sourcename, $this->object_varname);
		}

		$report_data = array(array
		(
			'data' => $data,
			'source'=>$sourcename,
			'data_str' => $data_str,
			'table_data'=>$table_data,
			'group_title'=>false,
			'member_links'=>$member_links,
			'avail_links' => $avail_links
		));
		return $report_data;
	}

	/**
	*	Mangle SLA data for host- and servicegroups
	*/
	public function _sla_group_data($sla_data = false)
	{
		if (empty($sla_data))
			return false;
		$report_data = false;
		foreach ($sla_data as $source => $period_data) {
			$members = null;
			$sourcename = $this->_get_sla_group_name($period_data);

			// loop over whole period for current group
			foreach ($period_data as $key => $tmp_data) {
				// 'jan' => array(99.8, 99.6), (real, sla)
				$months_key = ($key - 1);
				if (array_key_exists($key, $this->in_months)) {
					if (arr::search($tmp_data, 'states')) {

						# eg: $tmp_data['states']['PERCENT_TOTAL_TIME_UP']
						$real_val = $tmp_data['states'][self::$sla_field_names[$this->report_options['report_type']]];

						# control colour of bar depending on value
						# true = green, false = red
						$sla_ok = $this->in_months[$key] > $real_val ? true : false;


					} else {
						// create empty 'real' values
						$sla_ok = false;
						$real_val = 0;
					}

					# eg: $data['Jan'] = array(99.99999, 99.5)
					$data[$this->abbr_month_names[$months_key]] = array($real_val, $this->in_months[$key], $sla_ok);
					# eg: $table_data['groupnameX']['Jan'] = array(98,342342, 98)
					if ($this->scheduled_downtime_as_uptime == 2)
						$table_data[$sourcename][$this->abbr_month_names[$months_key]][] = array($real_val, $this->in_months[$key], $tmp_data['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP']);
					else
						$table_data[$sourcename][$this->abbr_month_names[$months_key]][] = array($real_val, $this->in_months[$key]);
				}

				if (is_null($members) && arr::search($tmp_data, 'states')) {
					if(isset($tmp_data['states']['SERVICE_DESCRIPTION']))
						$members = $tmp_data['states']['SERVICE_DESCRIPTION'];
					else
						$members = $tmp_data['states']['HOST_NAME'];
				}
			}

			$data_str = base64_encode(serialize($data));

			$member_links = array();
			foreach($members as $member) {
				$member_links[] = $this->_generate_sla_member_link($member, $this->object_varname);
			}

			$report_data[] = array(
				'data' => $data,
				'table_data' => $table_data,
				'data_str' => $data_str,
				'source' => $sourcename,
				'group_title'=>$sourcename,
				'member_links' => $member_links,
				'avail_links' => $this->_generate_avail_member_link($members, $this->object_varname)
			);
		}
		return $report_data;
	}

	/**
	 * Discovers name of a report data object
	 *
	 * @param array $sla_data
	 * @return mixed String name of object if found, false else.
	 */
	public function _get_sla_group_name(&$sla_data)
	{
		if (empty($sla_data)) return false;

		$first_elem = each($sla_data);
		if(is_numeric($first_elem['key']))
			$sla_entry = current($sla_data) != false ? current($sla_data) : $first_elem['value'];
		else
			$sla_entry =& $sla_data;

		// hostgroup or servicegroup
		if(!empty($sla_entry['groupname']))
			return $sla_entry['groupname'];

		// custom group
		if(strpos($sla_entry['source'], ',') !== false)
		{
			return $sla_entry['source'];
		}

		// single service
		if(arr::search($sla_entry['states'], 'SERVICE_DESCRIPTION'))
			// concatenate with host since lib_reports return service without that part
			return $sla_entry['states']['HOST_NAME'].';'.$sla_entry['states']['SERVICE_DESCRIPTION'];

		// single host
		return $sla_entry['states']['HOST_NAME'];
	}

	/**
	 * @param string $member
	 * @return array Links to SLA report for individual members
	 */
	private function _generate_sla_member_link($member)
	{
		$return = '<a href="'.url::site().Kohana::config('reports.reports_link').'/generate?type=sla&amp;'.$this->object_varname.'[]='.$member;
		foreach($this->report_options as $key => $val) {
			switch ($key) {
				case 'report_type':
					$val = array_search($this->object_varname, self::$map_type_field);
					if($val === FALSE)  // if custom group
						$val = $this->object_varname;
					break;
				case 'start_time': case 'end_time':
					if (is_numeric($val)) {
						$val = date('Y-m-d H:i', $val);
					}
					break;
			}
			$return .= "&amp;$key=$val";
		}
		foreach($this->in_months as $month => $sla) {
			$return .= '&amp;month_'.$month.'='.$sla;
		}
		$host_alias = '';
		$service_description = '';
		$host_name = '';
		if (array_key_exists('use_alias', $this->report_options) && $this->report_options['use_alias'] == 1) {
			# use alias with host_name
			if (strstr($member, ';')) {
				# we have host_name;service_description so we neeed to split this
				$member_parts = explode(';', $member);
				if (is_array($member_parts) && sizeof($member_parts)==2) {
					$host_name = $member_parts[0];
					$host_alias = $this->_get_host_alias($host_name);
					$service_description = $member_parts[1];
					$member = sprintf($this->translate->_('%s on %s(%s)'), $service_description, $host_alias, $host_alias);
				}
			} else {
				$host_alias = $this->_get_host_alias($member);
				$member = $host_alias.' (' . $member . ')';
			}
		}
		$return .= '">'.$member.'</a>';

		return $return;
	}

	/**
	 * @param 	string $members
	 * @return 	array Links to Availability report for individual members
	 */
	private function _generate_avail_member_link($members)
	{
		$objects = '';
		$return = url::site().Kohana::config('reports.reports_link').'/generate?type=avail&amp;';
		if (is_array($members)) {
			$objects .= implode('&amp;'.$this->object_varname.'[]=',$members);
		} else {
			$objects = $members;
		}
		$return .= $this->object_varname.'[]='.$objects;
		foreach($this->report_options as $key => $val) {
			switch ($key) {
				case 'report_type':
					$val = array_search($this->object_varname, self::$map_type_field);
					if($val === false)  // if custom group
						$val = $this->object_varname;
					break;
				case 'start_time': case 'end_time':
					if (is_numeric($val)) {
						$val = date('Y-m-d H:i', $val);
					}
					break;
			}
			$return .= "&amp;$key=$val";
		}
		return $return;
	}

	/**
	*	@desc  Splits a span of unixtime(start_time, end_time) into slices for every month number in $months.
	*	@param $months array - DEPRECATED. the months to calculate for.
	*	@param $start_time int start timestamp of the first month
	*	@param $end_time int end timestamp of the last month
	*	@return array of start/end timestamps for every timestamp gives the start and end of the month
	*/
	public function _split_month_data($months=false, $start_time=false, $end_time=false)
	{
		if (empty($months) || empty($start_time) || empty($end_time)) {
			return false;
		}
		$date = $start_time;
		while ($date < $end_time) {
			$end = strtotime('+1 month', $date);
			$return[date('n', $date)] = array('start' => $date, 'end' => $end);
			$date = $end;
		}
		return $return;
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
		$helptexts = array(
			'report-type' => $translate->_("Select the preferred report type. Hostgroup, Host, Servicegroup or Service. ".
				"To include objects of the given type in the report, select the objects from the left list and click on ".
				"the right pointing arrow. To exclude objects from the report, select the objects from the right list ".
				"and click on the left pointing arrow."),
			'report_time_period' => $translate->_("What time should the report be created for. Tip: This can be used for SLA reporting."),
			'scheduled_downtime' => $translate->_("Select if downtime that occurred during scheduled downtime should be counted as the actual state, as uptime, or if it should be counted as uptime but also showing the difference that makes."),
			'initial_states' => sprintf($translate->_("Whether to assume logging of initial states or not. Default values are YES. ".
				"%sFor advanced users the value can be modified by editing the nagios.cfg config file located in the %s directory."), '<br /><br />', $nagios_etc_path),
			'first_assumed_host' => $translate->_("If there is no information about the host or service in the current log file, ".
				"the status of the host/service will be assumed. Default value is &quot;First Real State&quot;."),
			'first_assumed_service' => $translate->_("If there is no information about the host or service in the current log file, ".
				"the status of the host/service will be assumed. Default value is &quot;First Real State&quot;."),
			'stated_during_downtime' => $translate->_("If the application is not running for some time during a report period we can by this ".
				"option decide to assume states for hosts and services during the downtime. Default value is YES."),
			'include_soft_states' => $translate->_("A problem is classified as a SOFT problem until the number of checks has reached the ".
				"configured max_check_attempts value. When max_check_attempts is reached the problem is reclassified as HARD."),
			'use_average' => sprintf($translate->_("What calculation method to use for the report. %s".
				"Traditional Availability reports are based on group availability (worst case). An alternative way is to use average values for ".
				"the group or object in question. Note that using average values are by some, considered %s not %s to be actual SLA."), '<br /><br />', '<b>', '</b>'),
			'use_alias' => $translate->_("Select if you would like to see host aliases in the generated reports instead of just the host_name"),
			'csv_format' => $translate->_("The CSV (comma-separated values) format is a file format that stores tabular data. This format is supported ".
				"by many applications such as MS Excel, OpenOffice and Google Spreadsheets."),
			'save_report' => $translate->_("Check this box if you want to save the configured report for later use."),
			'reporting_period' => $translate->_("Choose from a set of predefined report periods or choose &quot;CUSTOM REPORT PERIOD&quot; ".
				"to manually specify Start and End date."),
			'enter-sla' => $translate->_("Enter the selected SLA values for each month. Percent values (0.00-100.00) are assumed."),
			'report_settings_sml' => $translate->_("Here you can modify the report settings for the report you are currently viewing."),
			'cluster_mode' => $translate->_("When creating a report in cluster mode, the group logic is reversed so that the OK/UP time is calculated using the most positive service/host state of the selected objects."),
			'log_entries' => $translate->_("Shows the actual log messages that this report was created of."),
			'hostgroup_breakdown' => $translate->_("Here you have a list of all hosts that are member of this hostgroup and their states."),
			'servicegroup_breakdown' => $translate->_("Here you have a list of all services that are member of this servicegroup and their states."),
			'average_and_sla' => $translate->_("Shows the Average and SLA values for all selected services above."), // text ok?
			'availability' => $translate->_("This table shows a breakdown of the different states. How much time that was ok, warning, unknown, critical or undetermined in both actual time and percent. Time is also divied between uncheduled and scheduled which helps you to separate unplanned and planned events."),
			'piechart' => $translate->_("Pie chart that displays how much time in percent that was ok, warning, unknown, critical or undetermined."),
			'sla_graph' => $translate->_("Graphical report of the SLA. Green bars meens that the SLA was fulfilled and red that it was not fulfilled."),
			'sla_breakdown' => $translate->_("Breakdown of the SLA report in actual figures."),
			'sla_group_members' => $translate->_("Members of the selected group that the report is generated for. All members are links to individual reports."),
			'trends' => $translate->_("Shows trends during selected report period"),
			'saved_reports' => $translate->_("A list of all your saved reports. To load them, select the report you wish to generate and click select."),
			'use-sla-values' => $translate->_("Load SLA-values from previously saved reports. Just select a report in the list and it will autoload."),

			// new scheduled report
			'report-type-save' => $translate->_("Select what type of report you would like to schedule the creation of"),
			'select-report' => $translate->_("Select which report you want to you want to schedule"), // text ok?
			'report' => $translate->_("Select the saved report to schedule"),
			'interval' => $translate->_("Select how often the report is to be produced and delivered"),
			'recipents' => $translate->_("Enter the email addresses of the recipients of the report. To enter multiple addresses, separate them by commas"),
			'filename' => $translate->_("This field lets you select a custom filename for the report. If the name ends in <strong>.csv</strong>, a CSV file will be generated - otherwise a PDF will be generated."),
			'description' => $translate->_("Add a description to this schedule. This may be any information that could be of interest when editing the report at a later time. (optional)"),
			'start-date' => $translate->_("Enter the start date for the report (or use the pop-up calendar)."),
			'end-date' => $translate->_("Enter the end date for the report (or use the pop-up calendar)."),
			'local_persistent_filepath' => '<p>'.$translate->_("Specify an absolute path on the local disk, where you want the report to be saved in PDF format.").'</p><p>'.$translate->_("This should be the location of a folder, for example /var/log").'</p>'
		);
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else
			echo sprintf($translate->_("This helptext ('%s') is yet not translated"), $id);
	}

	public function _convert_special_chars($str=false) {
		$str = trim($str);
		if (empty($str)) return false;
		$return_str = '';
		$str = trim($str);
		$str = str_replace(' ', '_', $str);
		$str = str_replace('"', '', $str);
		$str = str_replace('/', '_', $str);
		$return_str = iconv('utf-8', 'us-ascii//TRANSLIT', $str);
		// If your system is buggy, you'll just get to keep your utf-8
		// Don't want it? Don't put it there!
		if ($return_str === false)
			$return_str = $str;
		return $return_str;
	}

	public function _check_filename($str=false)
	{
		$str = trim($str);
		$str = str_replace(',', '_', $str);
		if (empty($str)) return false;
		$extensions = array('pdf', 'csv');
		$extension = 'pdf'; // default
		if (strstr($str, '.')) {
			$parts = explode('.', $str);
			if (is_array($parts)) {
				$str = '';
				for ($i=0;$i<(sizeof($parts)-1);$i++) {
					$str .= $parts[$i];
				}
				$wanted_extension = end($parts);
				if(in_array($wanted_extension, $extensions)) {
					$extension = $wanted_extension;
				}
				$str .= '.'.$extension;
			}
		} else {
			$str .= '.'.$extension;
		}
		return $str;
	}

	private function _get_element_parts($str=false)
	{
		if (empty($str)) return false;
		if (!strstr($str, '-')) return false;
		// check for report_name since it has '.' as element id
		if (strstr($str, '.')) {
			$dotparts = explode('.', $str);
			if (is_array($dotparts)) {
				$str = '';
				for ($i=1;$i<sizeof($dotparts);$i++) {
					$str .= $dotparts[$i];
				}
			}
		}
		$parts = explode('-', $str);
			if (is_array($parts)) {
				return $parts;
			}
		return false;
	}

	/**
	*	Save single item (key, value) from .editable
	*	fields regarding scheduled reports.
	*/
	public function save_schedule_item()
	{
		$this->auto_render = false;
		$field = false;
		$report_id = false;
		$new_value = arr::search($_REQUEST, 'newvalue');
		$tmp_parts = arr::search($_REQUEST, 'elementid');

		if (!$tmp_parts) {
			# @@@FIXME: inform user via jGrowl and echo old value somehow?
			echo $this->translate->_("Required data is missing, unable to save changes");
			return false;
		}

		$parts = $this->_get_element_parts($tmp_parts);
		if (!empty($parts)) {
			$field 		= $parts[0];
			$report_id 	= (int)$parts[1];
		}

		// check some fields a little extra
		switch ($field) {
			case 'local_persistent_filepath':
				if(!is_writable(rtrim($new_value, '/').'/')) {
					echo $this->translate->_("Can't write to '$new_value'. Provide another path.")."<br />";
					return;
				}
				break;
			case 'recipients': // convert ';' to ','
				$new_value = str_replace(';', ',', $new_value);
				$rec_arr = explode(',', $new_value);
				$recipients = false;
				if (!empty($rec_arr)) {
					foreach ($rec_arr as $recipient) {
						if (trim($recipient)!='') {
							$recipients[] = trim($recipient);
						}
					}
					if (!empty($recipients)) {
						$new_value = implode(',', $recipients);
						$new_value = $this->_convert_special_chars($new_value);
					}
				}
				// check for required email field, rather lame check
				// but it's better than nothing...
				$recipient = explode(",", $new_value);
				if (is_array($recipient) && !empty($recipient)) {
					foreach ($recipient as $recip) {
						if (strlen($recip) < 6 || !preg_match("/.+@.+/", $recip)) {
							echo '<a title="'.$this->translate->_('Fetch saved value').'" href="#" onclick="fetch_field_value(\''.$field.'\', '.$report_id.', \''.$_REQUEST['elementid'].'\');">';
							echo sprintf($this->translate->_("'%s' is not a valid email address.%sClick here to restore saved value."), $recip, '<br />')."\n</a>";
							return;
						}
					}
				}
				break;
			case 'filename': // remove spaces
				if (strlen($new_value)>40) {
					echo sprintf($this->translate->_('The entered value is too long. Only 40 chars allowed for filename.%sValue %s not %s modified!'), '<br />', '<strong>', '</strong>').'<br />' .
						$this->translate->_('Please').' <a title="'.$this->translate->_('Fetch saved value').'" href="#" onclick="fetch_field_value(\''.$field.'\', '.$report_id.', \''.$_REQUEST['elementid'].'\');">'.$this->translate->_('click here').'</a> '.$this->translate->_('to view saved value').'.';
					exit;
				}
				$new_value = $this->_convert_special_chars($new_value);
				$new_value = $this->_check_filename($new_value);
				break;
		}

		$ok = Scheduled_reports_Model::update_report_field($report_id, $field, $new_value);

		if ($ok!==true) {
			echo $this->translate->_('An error occurred')."<br />";
			return;
		}
		/*
		# decide how to interpret field and value, since we
		# should print the correct value back.
		# If the value is an integer it should indicate that
		# we need to make a lookup in database to fetch correct value
		# Let's say we have 'periodname' as field, then value is an
		# integer and the return value should be Weekly/Monthly
		# if we get a string we should return that string
		# The problem is that all values will be passed as strings
		#
		#	Possible input values:
		#	* report_id
		#	* period_id
		#	* recipients		no changes needed
		#	* filename			no changes needed
		#	* description/info	no changes needed
		#
		*/
		switch ($field) {
			case 'report_id':
				$report_type = Scheduled_reports_Model::get_typeof_report($report_id);
				if (!$report_type) {
					echo $this->translate->_("Unable to determine type for selected report");
				} else {
					$saved_reports = Saved_reports_Model::get_saved_reports($report_type);
					if (count($saved_reports)!=0) {
						foreach ($saved_reports as $report) {
							if ($report->id == $new_value) {
								echo $report_type == 'avail' || $report_type == 'summary'
									? $report->report_name
									: $report->sla_name;
								break;
							}
						}
					} else {
						echo $this->translate->_("Unable to fetch list of saved reports");
					}
				}
				break;
			case 'period_id':
				$period = false;
				$periods = Scheduled_reports_Model::get_available_report_periods();
				if ($periods !== false) {
					foreach ($periods as $row) {
						$period[$row->id] = $row->periodname;
					}
					echo (is_array($period) && array_key_exists($new_value, $period))
						? $period[$new_value]
						: '';
				}
				break;
			case 'recipients':
				$new_value = str_replace(',', ', ', $new_value);
				echo $new_value;
				break;
			default:
				echo $new_value;
		}
	}

	/**
	*	Delete a schedule through ajax call
	*/
	public function delete_schedule()
	{
		$this->auto_render = false;
		$id = $this->input->get('id');
		if (Scheduled_reports_Model::delete_scheduled_report($id)) {
			echo "OK";
		} else {
			echo $this->translate->_('An error occurred - unable to delete selected schedule');
		}
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

		$request['create_pdf'] = 1;
		$request['new_report_setup'] = 1;
		$this->pdf_filename = $report_data['filename'];
		$this->pdf_recipients = $report_data['recipients'];
		$this->pdf_local_persistent_filepath = $report_data['local_persistent_filepath'];
		$type = isset($report_data['sla_name']) ? 'sla' : 'avail';
		foreach ($this->setup_keys as $k) {
			if ($type === 'sla' && $k === 'report_name')
				$k = 'sla_name';
			if ($k != 'host_filter_status' && $k != 'service_filter_status')
				$request[$k] = $report_data[$k];
		}

		if (!empty($report_data['objects'])) {
			$var_name = self::$map_type_field[$report_data['report_type']];
			foreach ($report_data['objects'] as $obj) {
				$request[$var_name][] = $obj;
			}
		}

		if ($type === 'sla' && isset($report_data['month']) && !empty($report_data['month'])) {
			foreach ($report_data['month'] as $k => $v) {
				$request[$k] = $v;
			}
		}

		if (PHP_SAPI === "cli") {
			# set current user to the owner of the report
			# this should only be done when called through PHP CLI
			Auth::instance()->force_login($report_data[Saved_reports_Model::USERFIELD]);
		}
		return $request;
	}

	/**
	*	Receive call from cron to check for scheduled reports
	*/
	public function cron($period_str=false)
	{
		if (PHP_SAPI !== "cli") {
			die("illegal call\n");
		}
		$this->auto_render=false;

		$res = Scheduled_reports_Model::get_period_schedules($period_str);
		if ($res === false) {
			return false;
		}

		$return = false;
		foreach ($res as $row) {
			if ($row->identifier == 'summary') {
				$summary = new Summary_Controller();
				$return[] = $summary->generate($row->id);
			} else {
				$report = new Reports_Controller();
				$return[] = $report->generate($row->identifier, $row->id);
				unset($report);
			}
		}

		# remove temp files
		$tmpfiles = glob('/tmp/report*', GLOB_ONLYDIR);
		if (is_array($tmpfiles) && !empty($tmpfiles)) {
			foreach ($tmpfiles as $file) {
				# only remove files older than or equal to 1 day
				if (time() - filemtime($file) >= 86400) {
					exec('rm -rf '.$file);
				}
			}
		}
		return $return;
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
		echo "<br /><table class=\"host_alerts\"><tr>\n";
		echo "<caption style=\"margin-top: 15px\">".$topic.' '.$t->_('for').' '.$name."</caption>".$spacer;
		echo "<th class=\"headerNone\">" . $t->_('State') . "</th>\n";
		echo "<th class=\"headerNone\">" . $t->_('Soft Alerts') . "</th>\n";
		echo "<th class=\"headerNone\">" . $t->_('Hard Alerts') . "</th>\n";
		echo "<th class=\"headerNone\">" . $t->_('Total Alerts') . "</th>\n";
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

	/**
	*	Fetch specific field value for a scheduled report
	*/
	public function fetch_field_value()
	{
		$this->auto_render=false;
		$id = arr::search($_REQUEST, 'id');
		$type = arr::search($_REQUEST, 'type');
		if (empty($id) || empty($type))
			return false;
		$data = Scheduled_reports_Model::fetch_scheduled_field_value($type, $id);
		if (!empty($data)) {
			echo $data;
		} else {
			echo 'error';
		}
	}

	public function _create_filter_array($array, $type = 'host') {

		$new_array = false;

		if ($type == 'host') {
			$new_array['up'] = (!array_key_exists(0, $array) || $array[0] == 0)  ? 0 : 1;
			$new_array['down'] = (!array_key_exists(1, $array) || $array[1] == 0)? 0 : 1;
			$new_array['unreachable'] = (!array_key_exists(2, $array) || $array[2] == 0) ? 0 : 1;
			$new_array['undetermined'] = (!array_key_exists(3, $array) || $array[3] == 0) ? 0 : 1;
		} else {
			$new_array['ok'] = (!array_key_exists(0, $array) || $array[0] == 0) ? 0 : 1;
			$new_array['warning'] = (!array_key_exists(1, $array) || $array[1] == 0) ? 0 : 1;
			$new_array['unknown'] = (!array_key_exists(2, $array) || $array[2] == 0) ? 0 : 1;
			$new_array['critical'] = (!array_key_exists(3, $array) || $array[3] == 0) ? 0 : 1;
			$new_array['pending'] = (!array_key_exists(4, $array) || $array[4] == 0) ? 0 : 1;
		}

		return $new_array;
	}
}

