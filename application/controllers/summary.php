<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Alert Summary controller
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
class Summary_Controller extends Authenticated_Controller
{
	private $xajax = false;
	private $reports_model = false;
	private $abbr_month_names = false;
	private $month_names = false;
	private $day_names = false;
	private $abbr_day_names = false;
	private $first_day_of_week = 1;


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

		$xajax = $this->xajax;

		$this->xajax->registerFunction(array('get_group_member',$this,'_get_group_member'));

		$this->xajax->processRequest();

		$this->template->disable_refresh = true;
		$t = $this->translate;
		$this->template->content = $this->add_view('summary/setup');
		$template = $this->template->content;

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker';
		#$this->xtra_js[] = 'application/media/js/jquery.timePicker';
		#$this->xtra_js[] = $this->add_path('summary/js/json');
		$this->xtra_js[] = $this->add_path('summary/js/move_options');
		$this->xtra_js[] = $this->add_path('summary/js/common');

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('summary/css/datePicker');
		$this->xtra_css[] = $this->add_path('summary/css/summary');
		#$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->template->css_header->css = $this->xtra_css;

		$this->js_strings .= "var _ok_str = '".$t->_('OK')."';\n";
		$this->js_strings .= "var _cancel_str = '".$t->_('Cancel')."';\n";

		$template->label_create_new = $this->translate->_('Alert Summary Report');
		$template->label_standardreport = $this->translate->_('Standard Reports');
		$template->label_reporttype = $this->translate->_('Report Type');
		$template->label_report_mode = $this->translate->_('Report Mode');
		$template->label_report_mode_standard = $this->translate->_('Standard');
		$template->label_report_mode_custom = $this->translate->_('Custom');

		$this->inline_js .= "set_selection($('#report_type').val());\n";
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

		$template->standardreport = array(
			1 => $t->_("Most Recent Hard Alerts"),
			2 => $t->_("Most Recent Hard Host Alerts"),
			3 => $t->_("Most Recent Hard Service Alerts"),
			4 => $t->_("Top Hard Host Alert Producers"),
			5 => $t->_("Top Hard Service Alert Producers")
		);
		$template->label_show_items = $t->_('Items to show');
		$template->label_default_show_items = 25;
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


		# displaytype
		$template->report_types = array(
			1 => $t->_("Most Recent Alerts"),
			2 => $t->_("Alert Totals"),
			4 => $t->_("Alert Totals By Hostgroup"),
			5 => $t->_("Alert Totals By Host"),
			7 => $t->_("Alert Totals By Servicegroup"),
			6 => $t->_("Alert Totals By Service"),
			3 => $t->_("Top Alert Producers")
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
			3 => $t->_("Host Problem States"),
			4 => $t->_("Host Up States"),
			1 => $t->_("Host Down States"),
			2 => $t->_("Host Unreachable States")
		);

		#servicestates
		$template->servicestates = array(
			120 => $t->_("All Service States"),
			56 => $t->_("Service Problem States"),
			64 => $t->_("Service Ok States"),
			8 => $t->_("Service Warning States"),
			16 => $t->_("Service Unknown States"),
			32 => $t->_("Service Critical States")
		);

		$this->template->xajax_js = $xajax->getJavascript(get_xajax::web_path());
		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
	}

	/**
	*
	*
	*/
	public function generate()
	{
		# stub
		echo Kohana::debug($_POST);
		die();
	}

	/**
	*	Fetch requested items for a user depending on type (host, service or groups)
	* 	Found data is returned through xajax helper to javascript function populate_options()
	*/
	public function _get_group_member($input=false, $type=false, $erase=true)
	{
		$xajax = $this->xajax;
		return get_xajax_Core::group_member($input, $type, $erase, $xajax);
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


}