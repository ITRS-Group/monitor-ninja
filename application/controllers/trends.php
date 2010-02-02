<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Trends controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Trends_Controller extends Authenticated_Controller {
	public static $setup_keys = array(
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
		'includesoftstates'
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
	private $pdf_data = false;

	private $assume_initial_states = true;
	private $initial_assumed_host_state = -3;
	private $initial_assumed_service_state = -3;

	private $use_average = 0;

	private $type = false;
	private $xajax = false;
	private $report_id = false;
	private $data_arr = false;
	private $report_type = false;
	private $object_varname = false;

	private $status_link = "status/host/";
	private $trend_link = "trends/index";
	private $histogram_link = "histogram/index";
	private $history_link = "history/index";
	private $notifications_link = "notifications/index";

	private $reports_model = false;
	public $start_date = false;
	public $end_date = false;
	private $report_options = false;
	private $in_months = false;

	public function __construct()
	{
		parent::__construct();

		$this->reports_model = new Reports_Model();

		$this->xajax = get_xajax::instance();
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
	}

	/**
	*	Display report selection/setup page
	*/
	public function index()
	{
		$this->template->disable_refresh = true;

		$scheduled_downtime_as_uptime_checked  =
			arr::search($_REQUEST, 'scheduleddowntimeasuptime', $this->scheduled_downtime_as_uptime) ? 'checked="checked"' : '';
		$cluster_mode_checked =
			arr::search($_REQUEST, 'cluster_mode', $this->cluster_mode) ? 'checked="checked"' : '';
		$assume_initial_states_checked =
			arr::search($_REQUEST, 'assumeinitialstates', $this->assume_initial_states) ? 'checked="checked"' : '';
		$assume_states_during_not_running_checked =
			arr::search($_REQUEST, 'assumestatesduringnotrunning', $this->assume_states_during_not_running) ? 'checked="checked"' : '';
		$include_soft_states_checked = 'checked="checked"';
		$old_config_names = false; #Saved_reports_Model::get_all_report_names($this->type);
		$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", $old_config_names)."');";
		$this->report_id =
			arr::search($_REQUEST, 'report_id', false);
		$initial_assumed_host_state_selected =
			arr::search($_REQUEST, 'initialassumedhoststate', $this->initial_assumed_host_state);
		$initial_assumed_service_state_selected =
			arr::search($_REQUEST, 'initialassumedservicestate', $this->initial_assumed_service_state);
		$csv_output_checked =
			arr::search($_REQUEST, 'csvoutput', $this->csv_output) ? 'checked="checked"' : '';

		$use_average_yes_selected = $use_average_no_selected = '';
		if(arr::search($_REQUEST, 'use_average', $this->use_average) == 1)
			$use_average_yes_selected = 'selected="selected"';
		else
			$use_average_no_selected = 'selected="selected"';

		$xajax = $this->xajax;
		#$filters = 1;

		$this->xajax->registerFunction(array('get_group_member',$this,'_get_group_member'));
		$this->xajax->registerFunction(array('get_report_periods',$this,'_get_report_periods'));
		$this->xajax->registerFunction(array('get_saved_reports',$this,'_get_saved_reports'));
		#$xajax->registerFunction('fetch_scheduled_field_value');
		#$xajax->registerFunction('delete_schedule_ajax');
		$this->xajax->processRequest();

		$this->template->content = $this->add_view('trends/setup');
		$template = $this->template->content;

		# we should set the required js-files
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker';
		$this->xtra_js[] = $this->add_path('reports/js/json');
		$this->xtra_js[] = $this->add_path('reports/js/move_options');
		$this->xtra_js[] = $this->add_path('trends/js/common');
		$this->xtra_js[] = 'application/media/js/jquery.fancybox-1.2.6.min';


		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker');
		#$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->xtra_css[] = $this->add_path('trends/css/trends');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox-1.2.6';
		$this->template->css_header->css = $this->xtra_css;

		$t = $this->translate;

		$saved_reports = null;
		$scheduled_ids = array();
		$scheduled_periods = null;
		$report_info = false;
		$scheduled_label = $t->_('Scheduled');
		$label_report = $this->translate->_('report');

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
		if($scheduled_downtime_as_uptime_checked)
			$this->inline_js .= "toggle_label_weight(true, 'sched_downt');\n";
		if($include_soft_states_checked)
			$this->inline_js .= "toggle_label_weight(true, 'include_softstates');\n";
		if($assume_states_during_not_running_checked)
			$this->inline_js .= "toggle_label_weight(true, 'assume_progdown');\n";
		if($csv_output_checked)
			$this->inline_js .= "toggle_label_weight(true, 'csvout');\n";
		$this->inline_js .= "invalid_report_names = ".$old_config_names_js .";\n";
		$this->inline_js .= "uncheck('save_report_settings', 'report_form');\n";
		$this->inline_js .= "$('#report_save_information').hide();\n";

		$this->js_strings .= "var _ok_str = '".$t->_('OK')."';\n";
		$this->js_strings .= "var _cancel_str = '".$t->_('Cancel')."';\n";
		$this->js_strings .= "var _edit_str = '".$t->_('edit')."';\n";
		$this->js_strings .= "var _hide_str = '".$t->_('hide')."';\n";
		$this->js_strings .= "var _scheduled_label = '".$scheduled_label."';\n";
		$this->js_strings .= "var _label_report = '".$label_report."';\n";
		$this->js_strings .= "var nr_of_scheduled_instances = ". (!empty($scheduled_info) ? sizeof($scheduled_info) : 0).";\n";
		$this->js_strings .= "var _reports_edit_information = '".$t->_('Double click to edit')."';\n";
		$this->js_strings .= "var _reports_schedule_deleted = '".$t->_('Your schedule has been deleted')."';\n";
		$this->js_strings .= "var _reports_propagate = '".$t->_('Would you like to propagate this value to all months')."';\n";
		$this->js_strings .= "var _reports_propagate_remove = '".$t->_("Would you like to remove all values from all months")."';\n";
		$this->js_strings .= "var _reports_invalid_startdate = \"".$t->_("You haven't entered a valid Start date")."\";\n";
		$this->js_strings .= "var _reports_invalid_enddate = \"".$t->_("You haven't entered a valid End date")."\";\n";
		$this->js_strings .= "var _schedule_change_filename = \"".$t->_('Would you like to change the filename based on your selections?')."\";\n";
		$this->js_strings .= "var _reports_enddate_infuture = '".sprintf($t->_("You have entered an End date in the future.%sClick OK to change this to current time or cancel to modify."), '\n')."';\n";
		$this->js_strings .= "var _reports_err_str_noobjects = '".sprintf($t->_("Please select what objects to base the report on by moving %sobjects from the left selectbox to the right selectbox"), '<br />')."';\n";
		$this->js_strings .= "var _reports_error_name_exists = '".sprintf($t->_("You have entered a name for your report that already exists. %sPlease select a new name"), '<br />')."';\n";
		$this->js_strings .= "var _reports_error_name_exists_replace = \"".$t->_("The entered name already exists. Press 'Ok' to replace the entry with this name")."\";\n";
		$this->js_strings .= "var _reports_missing_objects = \"".$t->_("Some items in your saved report doesn't exist anymore and has been removed")."\";\n";
		$this->js_strings .= "var _reports_missing_objects_pleaseremove = '".$t->_('Please modify the objects to include in your report below and then save it.')."';\n";
		$this->js_strings .= "var _reports_confirm_delete = '".$t->_("Are you really sure that you would like to remove this saved report?")."';\n";
		$this->js_strings .= "var _reports_confirm_delete_schedule = \"".sprintf($t->_("Do you really want to delete this schedule?%sThis action can't be undone."), '\n')."\";\n";
		$this->js_strings .= "var _reports_confirm_delete_warning = '".sprintf($t->_("Please note that this is a scheduled report and if you decide to delete it, %s" .
			"the corresponding schedule will be deleted as well.%s Are you really sure that this is what you want?"), '\n', '\n\n')."';\n";

		$this->template->inline_js = $this->inline_js;

		$this->template->xajax_js = $xajax->getJavascript(get_xajax::web_path());

		$template->type = $this->type;
		$template->scheduled_label = $scheduled_label;
		$template->title_label = $t->_('schedule');
		$template->label_select = $t->_('Select');
		$template->label_new = $t->_('New');
		$template->new_saved_title = $t->_('Create new saved trends report');
		$template->label_delete = $t->_('Delete report');
		$template->label_dblclick = $t->_('Double click to edit');
		$template->label_sch_interval = $t->_('Interval');
		$template->label_sch_recipients = $t->_('Recipients');
		$template->label_sch_filename = $t->_('Filename');
		$template->label_sch_description = $t->_('Description');
		$template->label_create_new = $t->_('Trends report');
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
		$template->label_enddate = $t->_('End date');
		$template->label_startdate_selector = $t->_('Date Start selector');
		$template->label_enddate_selector = $t->_('Date End selector');
		$template->label_click_calendar = $t->_('Click calendar to select date');
		$template->label_initialassumedhoststate = $t->_('First assumed host state');
		$template->label_initialassumedservicestate = $t->_('First assumed service state');
		$template->label_assumestatesduringnotrunning = $t->_('Assume states during program downtime');
		$template->label_assumeinitialstates = $t->_('Assume initial states');
		#$template->label_cluster_mode = $t->_('Cluster mode');
		$template->label_propagate = $t->_('Click to propagate this value to all months');
		$template->label_enter_sla = $t->_('Enter SLA');
		$template->reporting_periods = Reports_Controller::_get_reporting_periods();
		$template->scheduled_downtime_as_uptime_checked = $scheduled_downtime_as_uptime_checked;
		$template->cluster_mode_checked = $cluster_mode_checked;
		$template->assume_initial_states_checked = $assume_initial_states_checked;
		$template->initial_assumed_host_states = self::$initial_assumed_host_states;
		$template->initial_assumed_service_states = self::$initial_assumed_service_states;
		$template->assume_states_during_not_running_checked = $assume_states_during_not_running_checked;
		$template->include_soft_states_checked = $include_soft_states_checked;
		$template->label_includesoftstates = $t->_('Include soft states');
		$template->label_avg = $t->_('Average');
		$template->label_create_report = $t->_('Create report');
		$template->label_save_report = $t->_('Save report');
		$template->use_average_yes_selected = $use_average_yes_selected;
		$template->use_average_no_selected = $use_average_no_selected;
		$template->initial_assumed_host_state_selected = $initial_assumed_host_state_selected;
		$template->initial_assumed_service_state_selected = $initial_assumed_service_state_selected;
		$template->csv_output_checked = $csv_output_checked;
		$template->months = $this->abbr_month_names;
		$template->is_scheduled_report = $t->_('This is a scheduled report');
		$edit_str = $t->_('edit');
		$template->edit_str = $edit_str;
		$template->is_scheduled_clickstr = sprintf($t->_("This report has been scheduled. Click on '[%s]' to change settings"), $edit_str);

		$template->report_id = $this->report_id;
		$template->report_info = $report_info;
		$template->old_config_names_js = $old_config_names_js;
		$template->old_config_names = $old_config_names;
		$template->scheduled_ids = $scheduled_ids;
		$template->scheduled_periods = $scheduled_periods;
		$template->saved_reports = $saved_reports;

		# decide what report periods to print
		$report_period_strings = Reports_Controller::_report_period_strings();

		$report_periods = $report_period_strings["report_period_strings"];
		$report_periods['custom'] = "* " . $template->label_custom_period . " *";
		$template->report_periods = $report_periods;
		$template->selected = $report_period_strings["selected"];

		#$this->js_strings .= "var _report_types_json = '(".json::encode($report_types).")';\n";
		#$this->js_strings .= "var _saved_avail_reports = '(".json::encode($avail_reports_arr).")';\n";
		#$this->js_strings .= "var _saved_sla_reports = '(".json::encode($sla_reports_arr).")';\n";
		$this->js_strings .= "var _reports_successs = '".$t->_('Success')."';\n";
		$this->js_strings .= "var _reports_error = '".$t->_('Error')."';\n";
		$this->js_strings .= "var _reports_schedule_error = '".$t->_('An error occurred when saving scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_update_ok = '".$t->_('Your schedule has been successfully updated')."';\n";
		$this->js_strings .= "var _reports_schedule_create_ok = '".$t->_('Your schedule has been successfully created')."';\n";
		$this->js_strings .= "var _reports_fatal_err_str = '".$t->_('It is not possible to schedule this report since some vital information is missing.')."';\n";

		$this->template->js_strings = $this->js_strings;

	}

	/**
	*	Generate trends report with settings from the setup page
	*/
	public function generate()
	{
		echo Kohana::debug($_REQUEST);
		die();
	}

	/**
	*	Fetch requested items for a user depending on type (host, service or groups)
	* 	Found data is returned through xajax helper to javascript function populate_options()
	*/
	public function _get_group_member($input=false, $type=false, $erase=true)
	{
		$xajax = $this->xajax;
		return get_xajax::group_member($input, $type, $erase, $xajax);
	}

	/**
	*  	Since a lot of the help texts are identical to the ones
	* 	in teh reports controller we might as well return them to
	* 	save us the extra work.
	*/
	public static function _helptexts($id)
	{
		Reports_Controller::_helptexts($id);
	}
}
