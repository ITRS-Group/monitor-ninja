<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Histogram controller
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
class Histogram_Controller extends Authenticated_Controller
{
	const RECENT_ALERTS = 1;
	const ALERT_TOTALS = 2;
	const TOP_ALERT_PRODUCERS = 3;
	const ALERT_TOTALS_HG = 4;
	const ALERT_TOTALS_HOST = 5;
	const ALERT_TOTALS_SERVICE = 6;
	const ALERT_TOTALS_SG = 7;

	private $xajax = false;
	private $reports_model = false;
	private $abbr_month_names = false;
	private $month_names = false;
	private $day_names = false;
	private $abbr_day_names = false;
	public $first_day_of_week = 1; # 1 = monday, 7 = sunday
	public $statetypes = false;
	public $hoststates = false;
	public $servicestates = false;
	public $breakdown = false;
	public $state_names = false;
	public $report_type = false;
	public $selected_breakdown = false;
	public $data = false;
	public $min = false;
	public $max = false;
	public $avg = false;
	public $sum = false;
	private $labels = false;

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
			$this->translate->_('Mon'),
			$this->translate->_('Tue'),
			$this->translate->_('Wed'),
			$this->translate->_('Thu'),
			$this->translate->_('Fri'),
			$this->translate->_('Sat'),
			$this->translate->_('Sun')
		);

		$this->day_names = array(
			$this->translate->_('Monday'),
			$this->translate->_('Tuesday'),
			$this->translate->_('Wednesday'),
			$this->translate->_('Thursday'),
			$this->translate->_('Friday'),
			$this->translate->_('Saturday'),
			$this->translate->_('Sunday')
		);

		$this->breakdown = array(
			'monthly' => $this->translate->_('Month'),
			'dayofmonth' => $this->translate->_('Day of the Month'),
			'dayofweek' => $this->translate->_('Day of the Week'),
			'hourly' => $this->translate->_('Hour of the Day')
		);

		#statetypes
		$this->statetypes = array(
			3 => $this->translate->_("Hard and Soft States"),
			2 => $this->translate->_("Hard States"),
			1 => $this->translate->_("Soft States")
		);

		#hoststates
		$this->hoststates = array(
			7 => $this->translate->_("All Host Events"),
			6 => $this->translate->_("Host Problem Events"),
			1 => $this->translate->_("Host Up Events"),
			2 => $this->translate->_("Host Down Events"),
			4 => $this->translate->_("Host Unreachable Events")
		);

		#servicestates
		$this->servicestates = array(
			15 => $this->translate->_("All Service Events"),
			14 => $this->translate->_("Service Problem Events"),
			1 => $this->translate->_("Service Ok Events"),
			2 => $this->translate->_("Service Warning Events"),
			4 => $this->translate->_("Service Critical Events"),
			8 => $this->translate->_("Service Unknown Events"),
		);

	}

	/**
	*	Setup options for histogram report
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
		$this->template->content = $this->add_view('histogram/setup');
		$template = $this->template->content;

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min';
		$this->xtra_js[] = $this->add_path('histogram/js/move_options');
		$this->xtra_js[] = $this->add_path('reports/js/common');
		$this->xtra_js[] = $this->add_path('histogram/js/histogram');

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker');
		$this->xtra_css[] = $this->add_path('histogram/css/histogram');
		#$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->template->css_header->css = $this->xtra_css;

		$this->js_strings .= reports::js_strings();

		$template->label_create_new = $this->translate->_('Event History Report');
		$template->label_standardreport = $this->translate->_('Standard Reports');
		$template->label_report_mode = $this->translate->_('Report Mode');
		$template->label_report_mode_standard = $this->translate->_('Standard');
		$template->label_report_mode_custom = $this->translate->_('Custom');

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
		$this->inline_js .= "set_selection($('#report_type').val());\n";

		$template->label_rpttimeperiod = $t->_('Report Period');
		$template->label_startdate = $t->_('Start Date');
		$template->label_enddate = $t->_('End Date');
		$template->label_events_to_graph = $t->_('Events To Graph');
		$template->label_breakdown = $t->_('Statistics Breakdown');
		$template->label_newstatesonly = $t->_('Ignore Repeated States');
		$template->label_statetypes_to_graph = $t->_('State Types To Graph');
		$template->label_create_report = $t->_('Create Report!');
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
		$label_custom_period = $t->_('CUSTOM REPORT PERIOD');

		# timeperiod
		$report_period_strings = Reports_Controller::_report_period_strings();
		$report_periods = $report_period_strings["report_period_strings"];
		$report_periods['custom'] = "* " . $label_custom_period . " *";
		$template->selected_report_period = $report_period_strings["selected"];

		$template->report_periods = $report_periods;

		$template->statetypes = $this->statetypes;
		$template->hoststates = $this->hoststates;
		$template->servicestates = $this->servicestates;
		$template->breakdown = $this->breakdown;

		$this->template->xajax_js = $xajax->getJavascript(get_xajax::web_path());
		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
	}

	/**
	 * Generate the event history report
	 */
	public function generate()
	{
		#die(Kohana::debug($_REQUEST));
		$this->report_type = arr::search($_REQUEST, 'report_type');
		if (empty($this->report_type)) {
			url::redirect(Router::$controller.'/index');
		}

		$valid_options = array(
			'state_types',
			'host_states',
			'service_states',
			'start_time',
			'end_time',
			'report_period',
			'host_name',
			'service_description',
			'hostgroup',
			'servicegroup'
		);

		$t = $this->translate;
		$this->template->disable_refresh = true;
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_js[] = 'application/media/js/jquery.flot.min';
		$this->xtra_js[] = 'application/media/js/date';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min';

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

		$this->xtra_css[] = 'application/media/css/jquery.fancybox';
		$this->xtra_js[] = $this->add_path('reports/js/common');
		$this->xtra_js[] = $this->add_path('histogram/js/histogram');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker');
		$this->xtra_css[] = $this->add_path('histogram/css/histogram');
		$this->template->css_header->css = $this->xtra_css;
		$rpt = new Reports_Model();

		$options = $_REQUEST;
		$used_options = array();
		foreach ($valid_options as $opt) {
			if (!empty($options[$opt])) {
				if ($rpt->set_option($opt, $options[$opt]) !== false) {
					$used_options[$opt] = $options[$opt];
				}
			}
		}

		$report_period = arr::search($_REQUEST, 'timeperiod') ? arr::search($_REQUEST, 'timeperiod') : arr::search($_REQUEST, 'report_period');
		$start_time = arr::search($_REQUEST, 't1') ? arr::search($_REQUEST, 't1') : arr::search($_REQUEST, 'start_time');
		$end_time = arr::search($_REQUEST, 't2') ? arr::search($_REQUEST, 't2') : arr::search($_REQUEST, 'end_time');
		$rpttimeperiod = arr::search($_REQUEST, 'rpttimeperiod', 'last24hours');
		$in_host = arr::search($_REQUEST, 'host', false);
		$selected_state_types = arr::search($_REQUEST, 'state_types', false);
		if ($in_host === false)
			$in_host 		= arr::search($_REQUEST, 'host_name', false);
		$in_service 		= arr::search($_REQUEST, 'service', array());
		if (empty($in_service))
			$in_service 	= arr::search($_REQUEST, 'service_description', array());

		$in_hostgroup 		= arr::search($_REQUEST, 'hostgroup', array());
		$in_servicegroup	= arr::search($_REQUEST, 'servicegroup', array());
		$selected_objects = "";
		$hostgroup			= false;
		$hostname			= false;
		$servicegroup		= false;
		$service			= false;

		$group_name = false;
		$title = $t->_('Event history for ');
		switch ($this->report_type) {
			case 'hostgroups':
				$sub_type = "host";
				$hostgroup = $in_hostgroup;
				$group_name = $hostgroup;
				$title .= $t->_('Hostgroup(s): ');
				if (is_array($hostgroup)) {
					$title .= implode(',', $hostgroup);
				} else {
					$title .= $hostgroup;
				}
				$this->object_varname = 'host_name';
				break;
			case 'servicegroups':
				$sub_type = "service";
				$servicegroup = $in_servicegroup;
				$group_name = $servicegroup;
				$title .= $t->_('Servicegroup(s): ');
				if (is_array($servicegroup)) {
					$title .= implode(', ', $servicegroup);
				} else {
					$title .= $servicegroup;
				}
				$this->object_varname = 'service_description';
				break;
			case 'hosts':
				$sub_type = "host";
				$hostname = $in_host;
				$title .= $t->_('Host(s): ');
				if (is_array($hostname)) {
					$title .= implode(', ', $hostname);
				} else {
					$title .= $hostname;
				}
				$this->object_varname = 'host_name';
				break;
			case 'services':
				$sub_type = "service";
				$service = $in_service;
				$title .= $t->_('Service(s): ');
				$tmp_obj = false;
				if (is_array($service)) {
					foreach ($service as $s) {
						if (strstr($s, ';')) {
							$tmp = explode(';', $s);
							$tmp_obj[] = "'".$tmp[1]."' ".$t->_('On Host')." '".$tmp[0]."' ";
						}
					}
					if (!empty($tmp_obj)) {
						$title .= implode(', ', $tmp_obj);
					}
				} else {
					if (strstr($s, ';')) {
						$tmp = explode(';', $s);
						$title .= "'".$tmp[1]."' ".$t->_('On Host')." '".$tmp[0]."' ";
					} else {
						$title .= $service;
					}
				}
				$this->object_varname = 'service_description';
				break;
			default:
				url::redirect(Router::$controller.'/index');
		}

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
		$html_options[] = array('hidden', 'report_type', $this->report_type);
		$html_options[] = array('hidden', 'rpttimeperiod', $rpttimeperiod);
		$newstatesonly = arr::search($_REQUEST, 'newstatesonly');
		$this->selected_breakdown = arr::search($_REQUEST, 'breakdown', 'hourly');
		$histogram_options = array(
			'breakdown' => $this->selected_breakdown,
			'newstatesonly' => $newstatesonly,
			'report_type' => $this->report_type
			);
		$breakdown_keys = false;
		switch ($this->selected_breakdown) {
			case 'monthly':
				for ($i = 1;$i<=12;$i++) $breakdown_keys[] = $i;
				break;
			case 'dayofmonth':
				# build day numbers 1-31 (always 31 slots for each month as in histogram.c)
				for ($i = 1;$i<=31;$i++) $breakdown_keys[] = $i;
				break;
			case 'dayofweek':
				# using integer equivalent to date('N')
				$breakdown_keys = array(1, 2, 3, 4, 5, 6, 7);
				break;
			case 'hourly':
				# build hour strings like '00', '01' etc
				for ($i=0;$i<=24;$i++) $breakdown_keys[] = substr('00'.$i, -2);
				break;
		}
		$histogram_data = $rpt->alert_history($histogram_options, $breakdown_keys);

		if (!empty($histogram_data)) {
			# pull the data from the returned array
			$this->data = isset($histogram_data['data']) ? $histogram_data['data'] : false;
			$this->min = isset($histogram_data['min']) ? $histogram_data['min'] : false;
			$this->max = isset($histogram_data['max']) ? $histogram_data['max'] : false;
			$this->avg = isset($histogram_data['avg']) ? $histogram_data['avg'] : false;
			$this->sum = isset($histogram_data['sum']) ? $histogram_data['sum'] : false;
		}

		$sub_type = false;
		switch ($this->report_type) {
			case 'hosts': case 'hostgroups':
				$this->state_names = array(
					Reports_Model::HOST_UP => $t->_('UP'),
					Reports_Model::HOST_DOWN => $t->_('DOWN'),
					Reports_Model::HOST_UNREACHABLE => $t->_('UNREACHABLE')
				);
				$sub_type = 'host';
				break;
			case 'services': case 'servicegroups':
				$this->state_names = array(
					Reports_Model::SERVICE_OK => $t->_('OK'),
					Reports_Model::SERVICE_WARNING => $t->_('WARNING'),
					Reports_Model::SERVICE_CRITICAL => $t->_('CRITICAL'),
					Reports_Model::SERVICE_UNKNOWN => $t->_('UNKNOWN')
				);
				$sub_type = 'service';
				break;
		}

		$this->inline_js .= "var graph_options = {legend: {show: true,container: $('#overviewLegend')},xaxis:{ticks:".$this->_get_xaxis_ticks()."},bars:{align:'center'}, grid: { hoverable: true, clickable: true }, yaxis:{min:0}};";
		$this->js_strings .= "var graph_xlables = new Array(".implode(',', $this->labels).");";

		$this->js_strings .= reports::js_strings();

		$data = $this->_prepare_graph_data();
		$datasets = array();
		$this->inline_js .= "var datasets = {";

		$states = array_keys($this->state_names);
		foreach ($data as $key => $val) {
			$datasets[] = "'".ucfirst(strtolower($this->state_names[$key]))."': {label: '".ucfirst(strtolower($this->state_names[$key]))."', data: [".implode(',', $val)."], color:'".Trends_Controller::_state_colors($sub_type, $states[$key])."', bars: { show: true}}";
		}

		$this->inline_js .= implode(',', $datasets).'};';

		$this->inline_js .= "var choiceContainer = $('#choices');
		$.each(datasets, function(key, val) {
			choiceContainer.append('<br/><input type=\"checkbox\" name=\"' + key +
			'\" checked=\"checked\" id=\"id' + key + '\">' +
			'<label for=\"id' + key + '\">'
			+ val.label + '</label>');
		});
		choiceContainer.find(\"input\").click(plotAccordingToChoices);

		function plotAccordingToChoices() {
			var data = [];

			choiceContainer.find(\"input:checked\").each(function () {
				var key = $(this).attr(\"name\");
				if (key && datasets[key])
					data.push(datasets[key]);
			});

			if (data.length > 0)
				$.plot($('#histogram_graph'), data, graph_options);
		}

	    plotAccordingToChoices();";

		$this->template->content = $this->add_view('histogram/index');

		$base = $this->template->content;
		$base->content = $this->add_view("histogram/histogram");
		$content = $base->content;
		$content->state_names = $this->state_names;

		$content->min = $this->min;
		$content->label_min = $t->_('MIN');
		$content->max = $this->max;
		$content->label_max = $t->_('MAX');
		$content->avg = $this->avg;
		$content->label_avg = $t->_('AVG');
		$content->sum = $this->sum;
		$content->label_sum = $t->_('SUM');
		$content->label_eventtype = $t->_('EVENT TYPE');
		$content->states = $this->state_names;
		$content->available_states = array_keys($this->min);
		$content->title = $title;
		$content->objects = $objects;
		$timeformat_str = nagstat::date_format();
		$content->report_time = date($timeformat_str, $rpt->start_time).' '.$t->_('to').' '.date($timeformat_str, $rpt->end_time);

		$this->template->content->report_options = $this->add_view('histogram/options');
		$tpl_options = $this->template->content->report_options;

		$tpl_options->label_report_period = $t->_('Reporting period');;
		$label_custom_period = $t->_('CUSTOM REPORT PERIOD');
		$report_period_strings = Reports_Controller::_report_period_strings();
		$report_periods = $report_period_strings["report_period_strings"];
		$report_periods['custom'] = "* " . $label_custom_period . " *";

		$tpl_options->report_periods = $report_periods;
		$tpl_options->selected = $report_period;
		$tpl_options->label_settings = $t->_('Report settings');
		$tpl_options->label_startdate = $t->_('Start date');
		$tpl_options->label_enddate = $t->_('End date');
		$tpl_options->label_show_event_duration = $t->_('Show event duration');
		$tpl_options->label_startdate_selector = $t->_('Date Start selector');
		$tpl_options->label_enddate_selector = $t->_('Date End selector');
		$tpl_options->label_click_calendar = $t->_('Click calendar to select date');
		$tpl_options->label_events_to_graph = $t->_('Events To Graph');
		$tpl_options->label_breakdown = $t->_('Statistics Breakdown');
		$tpl_options->label_newstatesonly = $t->_('Ignore Repeated States');
		$tpl_options->label_statetypes_to_graph = $t->_('State Types To Graph');
		$tpl_options->hoststates = $this->hoststates;
		$tpl_options->servicestates = $this->servicestates;
		$tpl_options->breakdown = $this->breakdown;
		$tpl_options->label_statetypes_to_graph = $t->_('State Types To Graph');
		$tpl_options->statetypes = $this->statetypes;
		$tpl_options->selected_state_types = $selected_state_types;
		$tpl_options->selected_breakdown = $this->selected_breakdown;
		$tpl_options->selected_host_state = arr::search($_REQUEST, 'host_states');
		$tpl_options->selected_service_state = arr::search($_REQUEST, 'service_states');
		$tpl_options->selected_newstatesonly = $newstatesonly;
		$tpl_options->sub_type = $sub_type;

		$tpl_options->label_update = $t->_('Update report');
		$tpl_options->label_save = $t->_('Save');
		$tpl_options->label_clear = $t->_('Clear');
		$tpl_options->html_options = $html_options;
		$tpl_options->label_edit_settings = $t->_('edit settings');
		$tpl_options->start_time = $start_time;
		$tpl_options->end_time = $end_time;
		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
	}

	/**
	*	Replace all integer indicies with proper
	* 	translated strings
	*/
	public function _get_xaxis_ticks()
	{
		if (empty($this->data)) {
			return false;
		}

		$return = false;
		$i = 0;
		foreach ($this->data as $key => $data) {
			switch ($this->selected_breakdown) {
				case 'dayofmonth':
					$return[] = '['.$i.', '.$key.']';
					$this->labels[] = "'".$key."'";
					break;
				case 'monthly':
					$return[] = '['.$i.', "'.$this->month_names[$key-1].'"]';
					$this->labels[] = "'".$this->month_names[$key-1]."'";
					break;
				case 'dayofweek':
					$return[] = '['.$i.', "'.$this->day_names[$key-1].'"]';
					$this->labels[] = "'".$this->day_names[$key-1]."'";
					break;
				case 'hourly':
					$return[] = '['.$i.', "'.$key.':00'.'"]';
					$this->labels[] = "'".$key.':00'."'";
					break;
			}
			$i++;
		}
		return '['.implode(',', $return).']';
	}

	/**
	*	Prepare data structore for use in histogram
	*/
	public function _prepare_graph_data($data=false)
	{
		if (empty($this->data)) {
			return false;
		}

		$return = false;
		$i = 0; # graph data needs to have 0 indicies
		foreach ($this->data as $key => $data) {
			foreach ($data as $k => $v) {
				$return[$k][] = '['.$i.','.$v.']';
			}
			$i++;
		}
		return $return;

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
		# filter
		$translate = zend::instance('Registry')->get('Zend_Translate');

		$nagios_etc_path = Kohana::config('config.nagios_etc_path');
		$nagios_etc_path = $nagios_etc_path !== false ? $nagios_etc_path : Kohana::config('config.nagios_base_path').'/etc';

		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array();
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else
			return Reports_Controller::_helptexts($id);

	}


	/**
	*	Accept direct link from extinfo and redirect
	*/
	public function host($host_name=false)
	{
		$host_name = arr::search($_REQUEST, 'host_name', $host_name);
		if (empty($host_name)) {
			die($this->translate->_('ERROR: No host name found'));
		}
		$service = arr::search($_REQUEST, 'service');
		$report_type = empty($service) ? 'hosts' : 'services';
		$breakdown = arr::search($_REQUEST, 'breakdown', 'hourly');
		$link = empty($service) ? 'host_name[]='.$host_name : 'service_description[]='.$host_name.';'.$service;

		url::redirect(Router::$controller.'/generate?'.$link.'&report_type='.$report_type.'&breakdown='.$breakdown);
	}
}
