<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Reports controller
 *
 * This particular reports controller is meant as a base controller for both
 * SLA and Availability reports, mostly for hysterical reasons.
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Reports_Controller extends Base_reports_Controller {
	private $status_link = "status/service/";
	private $history_link = "alert_history/generate";


	private static $sla_field_names = array(
		'hosts' => 'PERCENT_TOTAL_TIME_UP',
		'hostgroups' => 'PERCENT_TOTAL_TIME_UP',
		'services' => 'PERCENT_TOTAL_TIME_OK',
		'servicegroups' => 'PERCENT_TOTAL_TIME_OK'
	);

	/**
	 * Display report selection/setup page
	 */
	public function index($input=false) {
		$this->setup_options_obj($input);
		$reports_model = new Status_Reports_Model($this->options);

		$type_str = $this->type == 'avail'
			? _('availability')
			: _('SLA');
		$this->template->content = $this->add_view('reports/setup');
		$template = $this->template->content;

		if(isset($_SESSION['report_err_msg'])) {
			$template->error_msg = $_SESSION['report_err_msg'];
			unset($_SESSION['report_err_msg']);
		}

		# this makes anything in application/media be imported before
		# application/views before modules/whatever, so op5reports can
		# put random crap here as well.

		# I apologize
		sort($this->template->js);
		$this->template->js = array_unique($this->template->js);

		$this->template->css[] = $this->add_path('reports/css/datePicker.css');

		# what scheduled reports are there?
		$scheduled_periods = null;
		$scheduled_res = Scheduled_reports_Model::get_scheduled_reports($this->type);
		if ($scheduled_res && count($scheduled_res)!=0) {
			foreach ($scheduled_res as $sched_row) {
				$scheduled_periods[$sched_row->report_id] = $sched_row->periodname;
			}
		}

		$template->report_options = $this->add_view('reports/options');

		$this->js_strings .= "var _reports_propagate = '"._('Would you like to propagate this value to all months?')."';\n";
		$this->js_strings .= "var _reports_propagate_remove = '"._("Would you like to remove all values from all months?")."';\n";

		$this->js_strings .= reports::js_strings();

		$this->js_strings .= "var _reports_name_empty = '"._("Please give your report a meaningful name.")."';\n";

		$template->report_options->months = date::abbr_month_names();

		$template->scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->options['report_id']);

		$saved_reports = $this->options->get_all_saved();
		$template->report_options->saved_reports = $saved_reports;
		$template->saved_reports = $saved_reports;
		$template->scheduled_periods = $scheduled_periods;

		$this->js_strings .= "var _reports_no_sla_str = '"._('Please enter at least one SLA value')."';\n";
		$this->js_strings .= "var _reports_sla_err_str = '"._('Please check SLA values in fields marked red below and try again')."';\n";

		$this->template->js_strings = $this->js_strings;

		$this->template->toolbar = new Toolbar_Controller($this->type == 'avail' ? _('Availability report') : _('SLA report'));

		if ( $this->type == 'avail' ) {
			$this->template->toolbar->info( '<a id="switch_report_type" href="' . url::base(true) . 'sla' . '">' );
			$this->template->toolbar->info(
				html::image($this->add_path('icons/16x16/sla.png'), array('alt' => _('SLA'), 'title' => _('SLA'), 'ID' => 'switcher_image'))
			);
			$this->template->toolbar->info( ' &nbsp; <span id="switch_report_type_txt">' . _('Switch to SLA report') . '</span>' );
			$this->template->toolbar->info( '</a>' );
		} else {
			$this->template->toolbar->info( '<a id="switch_report_type" href="' . url::base(true) . 'avail' . '">' );
			$this->template->toolbar->info(
				html::image($this->add_path('icons/16x16/availability.png'), array('alt' => _('Availability'), 'title' => _('Availability'), 'ID' => 'switcher_image'))
			);
			$this->template->toolbar->info( ' &nbsp; <span id="switch_report_type_txt">' . _('Switch to Availability report') . '</span>' );
			$this->template->toolbar->info( '</a>' );
		}

		$this->template->title = _('Reporting » ').($this->type == 'avail' ? _('Availability Report') : _('SLA Report')).(' » Setup');
	}

	/**
	 * Create the same data as generate, but dump it as json
	 */
	public function debug($input=false) {
		$this->setup_options_obj($input);
		$reports_model = new Status_Reports_Model($this->options);
		$data_arr = $reports_model->get_uptime();
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="debug.json"');
		print json_encode($data_arr);
		exit(1);
	}

	/**
	 * Generate (availability) report from parameters set in index()
	 *
	 * @param $input array = false
	 */
	public function generate($input=false) {
		$this->setup_options_obj($input);

		$reports_model = new Status_Reports_Model($this->options);

		if ($this->options['skin']) {
			if (substr($this->options['skin'], -1, 1) != '/') {
				$this->options['skin'] .= '/';
			}
			$this->template->current_skin = $this->options['skin'];
		}

		$this->template->css[] = $this->add_path('reports/css/tgraph.css');
		$this->template->css[] = $this->add_path('reports/css/datePicker.css');

		$this->template->content = $this->add_view('reports/index'); # base template with placeholders for all parts
		$template = $this->template->content;

		$sub_type = false;

		$date_format = $this->type == 'sla' ? cal::get_calendar_format(true) : date::date_format();

		switch ($this->options['report_type']) {
			case 'hostgroups':
				$sub_type = "host";
				$is_group = true;
				break;
			case 'servicegroups':
				$sub_type = "service";
				$is_group = true;
				break;
			case 'hosts':
				$sub_type = "host";
				$is_group = false;
				break;
			case 'services':
				$sub_type = "service";
				$is_group = false;
				break;
			default:
				return url::redirect(Router::$controller.'/index');
		}

		$report_members = $this->options->get_report_members();
		if (empty($report_members)) {
			if (!$is_group)
				$_SESSION['report_err_msg'] = _("You didn't select any objects to include in the report");
			else
				$_SESSION['report_err_msg'] = sprintf(_("The groups you selected (%s) had no members, so cannot create a report from them"), implode(', ', $this->options['objects']));
			return url::redirect(Router::$controller.'/index?' . http_build_query($this->options->options));
		}

		if ($this->type == 'avail') {
			$data_arr = $reports_model->get_uptime();
		} else {
			$data_arr = $this->get_sla_data();
		}

		if ($this->options['output_format'] == 'csv') {
			csv::csv_http_headers($this->type, $this->options);
			$this->template = $this->add_view('reports/'.$this->type.'csv');
			$this->template->data_arr = $data_arr;
			return;
		}

		$template->title = $this->type == 'avail' ? _('Availability Report') : _('SLA Report');

		$template->report_options = $this->add_view('reports/options');

		$template->report_options->saved_reports = $this->options->get_all_saved();
		$template->report_options->months = date::abbr_month_names();

		$this->js_strings .= "var _reports_propagate = '"._('Would you like to propagate this value to all months?')."';\n";
		$this->js_strings .= "var _reports_propagate_remove = '"._("Would you like to remove all values from all months?")."';\n";

		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _reports_name_empty = '"._("Please give your report a meaningful name.")."';\n";

		$host_graph_items = array('TOTAL_TIME_UP' => _('Up'),
			'TOTAL_TIME_DOWN' => _('Down'),
			'TOTAL_TIME_UNREACHABLE' => _('Unreachable'),
			'TOTAL_TIME_UNDETERMINED' => _('Undetermined'),
			'TOTAL_TIME_HIDDEN' => 'EXCLUDE',
		);
		$service_graph_items = array('TOTAL_TIME_OK' => _('Ok'),
			'TOTAL_TIME_WARNING' => _('Warning'),
			'TOTAL_TIME_UNKNOWN' => _('Unknown'),
			'TOTAL_TIME_CRITICAL' => _('Critical'),
			'TOTAL_TIME_UNDETERMINED' => _('Undetermined'),
			'TOTAL_TIME_HIDDEN' => 'EXCLUDE',
		);
		$graph_filter = ${$sub_type.'_graph_items'};

		$template->header = $this->add_view('reports/header');
		$template->header->type = $this->type;
		$template->header->title = $template->title;
		$template->header->report_time_formatted = $this->format_report_time($date_format);

		if ($this->type == 'avail') {
			if ($is_group || count($this->options['objects']) > 1) {
				$template_values = array();
				$template->content = $this->add_view('reports/multiple_'.$sub_type.'_states');
				$template->content->multiple_states = $data_arr;
				$template->content->hide_host = false;

				if($is_group) # actual hostgroup/servicegroup.
					$tmp_title = ucfirst($sub_type)._('group breakdown');
				else
					$tmp_title = ucfirst($sub_type).' '._('state breakdown');
				$template->header->title = $tmp_title;

				if( $this->options['include_pie_charts'] ) {
					$template->pie = $this->add_view('reports/pie_chart');
					$data_str = array();

					foreach($data_arr as $data) { # for every group
						if (!is_array($data) || !isset($data['states']))
							continue;
						$image = array();
						foreach ($graph_filter as $key => $val) {
							if ($data['states'][$key]!=0)
								$image[strtoupper($val)] = $data['states'][$key];
						}
						$data_str[] = array(
							'img' => http_build_query($image),
							'host' => isset($data['groupname'])?implode(', ', $data['groupname']):'',
							);
					}

					$template->pie->data_str = $data_str;
				}
			} else { // single avail report
				$template->content = $this->add_view('reports/avail');

				$sources = array();
				foreach ($data_arr as $grouplist) {
					if (!is_array($grouplist) || !isset($grouplist['states']))
						continue;
					foreach ($grouplist as $host) {
						if (!is_array($host) || !isset($host['states']))
							continue;
						$sources[] = $host['source'];
					}
				}
				$avail = $template->content;
				$avail->report_data = $data_arr;

				$avail->header_string = ucfirst($this->options['report_type'])." "._('state breakdown');

				if($this->options['include_pie_charts']) {
					$pies = array();
					foreach ($data_arr as $data) {
						if (!is_array($data) || !isset($data['states']) || !is_array($data['states']))
							continue;
						$pie = $this->add_view('reports/pie_chart');
						$image_data = array();
						foreach ($graph_filter as $key => $val) {
							if ($data['states'][$key]!=0)
								$image_data[strtoupper($val)] = $data['states'][$key];
						}
						if ($image_data) {
							$data_str = http_build_query($image_data);
							$pie->data_str = $data_str;
							$pie->source = $data['source'];
						}

						$pies[] = $pie;
					}
					$avail->pies = $pies;
				}

				if ($sub_type=='host') {
					$template->svc_content = $this->_print_states_for_services($sources);
				}

				$links = array();
				# links - only for HTML reports
				if ($this->options['report_type'] == 'hosts') {
					$host = $this->options['objects'][0];
					$template->header->title = sprintf(_('Host details for %s'), $host);
					$links[$this->histogram_link . "?" . $this->options->as_keyval_string()] = _('Alert histogram');
					$links[$this->status_link.$host] = _('Status detail');
					$links[$this->history_link . '?' . $this->options->as_keyval_string()] = _('Alert history');
					$links[listview::link('notifications', array('host_name' => $host))] = _('Notifications');
				} else if ($this->options['report_type'] == 'services') {
					list($host, $service) = explode(';', $this->options['objects'][0]);
					$template->header->title = sprintf(_('Service details for %s on host %s'), $service, $host);
					$links[$this->histogram_link . "?" . $this->options->as_keyval_string()] = _('Alert histogram');
					$links[$this->history_link . "?" . $this->options->as_keyval_string()] = _('Alert history');
					$links[listview::link('notifications', array('host_name' => $host, 'service_description' => $service))] = _('Notifications');
				}

				$template->links = $links;
				$template->source = $sources;
				$template->header_string = sprintf(_("State breakdown for %s"), implode(', ', $sources));
			}
		} else { # SLA report
			$template->content = $this->add_view('reports/sla');
			$template->header->title = _('SLA breakdown');
			$sla = $template->content;
			$sla->report_data = $data_arr;
		}

		if($this->options['include_trends']) {
			$skipped = 0;
			$included = 0;
			$graph_data = array();
			foreach ($data_arr as $data) {
				if (!is_array($data) || !isset($data['states']))
					continue;
				foreach ($data as $obj) {
					if (!is_array($obj) || !isset($obj['log']))
						continue;
					if ($this->options['collapse_green_trends'] && (($sub_type == 'host' && $obj['states']['PERCENT_TOTAL_TIME_UP'] == 100) || ($sub_type == 'service' && $obj['states']['PERCENT_TOTAL_TIME_OK'] == 100))) {
						$skipped++;
						continue;
					}
					$graph_data[$obj['source']] = $obj['log'];
					$included++;
				}
			}

			$template->trends_graph = $this->add_view('trends/new_report');

			/* New JS trend graph */

			$template->trends_graph->skipped = $skipped;
			$template->trends_graph->included = $included;
			$template->trends_graph->graph_start_date = $this->options['start_time'];
			$template->trends_graph->graph_end_date = $this->options['end_time'];
			$template->trends_graph->use_scaling = $this->options['include_trends_scaling'];
			$template->trends_graph->obj_type = $sub_type;
			$template->trends_graph->graph_pure_data = Trends_graph_Model::format_graph_data($graph_data);
		}

		$this->template->js_strings = $this->js_strings;

		$this->template->title = _('Reporting » ').($this->type == 'avail' ? _('Availability Report') : _('SLA Report')).(' » Report');

		if ($this->options['include_alerts']) {
			$alrt_opts = new Alert_history_options($this->options);
			$alrt_opts['summary_items'] = 0; // we want *every* line in this time range
			$alrt_opts['include_downtime'] = true; // and we want downtime messages
			$alrt_opts['include_flapping'] = true; // and flapping messages

			$alerts = new Alert_history_Controller();
			$alerts->set_options($alrt_opts);
			$alerts->auto_render = false;
			$alerts->generate();
			$this->template->content->log_content = $alerts->template->content->content;
		}

		if(ninja::has_module('synergy') && $this->options['include_synergy_events']) {
			$synergy_report_model = new Synergy_report_Model($this->options);
			$synergy_content = $this->add_view('reports/synergy');
			$synergy_content->synergy_events = $synergy_report_model->get_data();
			$this->template->content->synergy_content = $synergy_content;
		}
		sort($this->template->js);
		$scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->options['report_id']);
		if($scheduled_info) {
			$schedule_id = $this->input->get('schedule_id', null);
			if($schedule_id) {
				$le_schedule = current(array_filter($scheduled_info, function($item) use ($schedule_id) {
					return $item['id'] == $schedule_id && $item['attach_description'] && $item['description'];
				}));
				if($le_schedule) {
					$template->header->description = $this->options['description'] ? $this->options['description']."\n".$le_schedule['description'] : $le_schedule['description'];
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
		$template->report_options = $this->add_view('reports/options');
		$template->report_options->saved_reports = $this->options->get_all_saved();
		$template->report_options->months = date::abbr_month_names();
	}

	private function _print_states_for_services($host_name=false) {
		$res = Livestatus::instance()->getServices(array('columns' => array('host_name', 'description'), 'filter' => array('host_name' => $host_name)));
		if (!empty($res)) {
			$service_arr = array();

			$classname = get_class($this->options);
			$opts = new $classname($this->options);
			$opts['service_filter_status'] = array();
			$opts['report_type'] = 'services';
			foreach ($res as $row)
				$service_arr[] = $row['host_name'] . ';' . $row['description'];
			$opts['objects'] = $service_arr;
			$report_class = new Status_Reports_Model($opts);

			$data_arr = $report_class->get_uptime();
			$content = $this->add_view('reports/multiple_service_states');
			$content->options = $opts;
			$content->header_string = _("Service state breakdown");
			$content->multiple_states = $data_arr;
			$content->hide_host = true;
			$content->source = $service_arr;
			return $content;
		}
		return false;
	}

	/**
	 * Fetch data from report_class
	 * Uses split_month_data() to split start- and end_time
	 * on months.
	 *
	 * @return array
	 */
	private function get_sla_data() {
		// OK, we have start and end but we will have to split
		// this time into parts according to sla_periods (months)
		$time_arr = $this->_split_month_data($this->options['start_time'], $this->options['end_time']);
		// only use month entered by the user regardless of start- or endtime
		$sla_data = array();
		$opts = new Avail_options($this->options);
		$opts['report_period'] = 'custom';
		foreach ($time_arr as $mnr => $dates) {
			$opts['start_time'] = $dates['start'];
			$opts['end_time'] = $dates['end'];
			$report_class = new Status_Reports_Model($opts);
			$data_tmp = $report_class->get_uptime();
			# So, all this does is to remove the summary for all reports?
			# That's anti-clever!
			foreach ($data_tmp as $group => $val) {
				if (!is_array($val) || !isset($val['states']))
					continue;
				$sla_data[$group][$mnr] = $val;
			}
		}
		if (empty($sla_data))
			return $sla_data;
		$report_data = array();
		foreach ($sla_data as $period_data) {
			$table_data = array();
			$data = array();
			$name = false;
			// loop over whole period for current group
			foreach ($period_data as $period_start => $tmp_data) {
				if (!is_array($tmp_data) || !isset($tmp_data['states']))
					continue;
				$month_idx = date('n', $period_start);
				if (array_key_exists($month_idx, $this->options['months'])) {
					if (arr::search($tmp_data, 'states')) {

						# $tmp_data['states']['PERCENT_TOTAL_TIME_{UP,OK}']
						$real_val = $tmp_data['states'][self::$sla_field_names[$this->options['report_type']]];

						# control colour of bar depending on value
						# true = green, false = red
						$sla_ok = $this->options['months'][$month_idx] > $real_val ? true : false;
					} else {
						// create empty 'real' values
						$sla_ok = false;
						$real_val = 0;
					}

					$data[date('M', $period_start)] = array($real_val, $this->options['months'][$month_idx], $sla_ok);
					if ($this->options['scheduleddowntimeasuptime'] == 2)
						$table_data[$period_start] = array($real_val, $this->options['months'][$month_idx], $tmp_data['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP']);
					else
						$table_data[$period_start] = array($real_val, $this->options['months'][$month_idx]);
				}
				$source = false;
				foreach ($tmp_data as $subobj) {
					if (!is_array($subobj) || !isset($subobj['states']))
						continue;
					$source[] = $subobj['source'];
				}
				$name = isset($tmp_data['groupname']) ? $tmp_data['groupname'] : false;
			}

			if (!$name && count($source) == 1)
				$name = $source;

			$report_data[] = array(
				'name' => $name,
				'table_data' => $table_data,
				'data_str' => http_build_query($data),
				'source' => $source,
				'avail_link' => $this->_generate_avail_member_link(),
			);
		}
		return $report_data;
	}

	/**
	 * @param string $members
	 * @return string A link to an Availability report for all members
	 */
	private function _generate_avail_member_link() {
		$objects = '';
		$return = url::site().'avail/generate?';
		$return .= $this->options->as_keyval_string(false);
		return $return;
	}

	/**
	 * Splits a span of unixtime(start_time, end_time) into slices for every month number in $months.
	 *
	 * @param $start_time int start timestamp of the first month
	 * @param $end_time int end timestamp of the last month
	 * @return array of start/end timestamps for every timestamp gives the start and end of the month
	 */
	private function _split_month_data($start_time, $end_time) {
		$months = array();
		$date = $start_time;
		while ($date < $end_time) {
			$end = strtotime('+1 month', $date);
			$months[$date] = array('start' => $date, 'end' => $end);
			$date = $end;
		}
		return $months;
	}

	/**
	 * Translated helptexts for this controller
	 */
	public static function _helptexts($id) {
		$helptexts = array(
			'scheduled_downtime' => _("Select if downtime that occurred during scheduled downtime should be counted as the actual state, as uptime, or if it should be counted as uptime but also showing the difference that makes."),
			'stated_during_downtime' => _("If the application is not running for some time during a report period we can by this ".
				"option decide to assume states for hosts and services during the downtime."),
			'sla_mode' => _("What calculation method to use for the report summary.<br/>".
				"Depending on how your network is configured, your SLA might be the group availability (worst state at any point in time) or cluster mode availability (best state at any point time). You can also choose to use average values instead for ".
				"the group or object in question.<br/>Note that calculating the summary incorrectly could mislead users."),
			'use_alias' => _("Select if you would like to see host aliases in the generated reports instead of just the host_name"),
			'csv_format' => _("The CSV (comma-separated values) format is a file format that stores tabular data. This format is supported ".
				"by many applications such as MS Excel, OpenOffice and Google Spreadsheets."),
			'save_report' => _("Check this box if you want to save the configured report for later use."),
			'enter-sla' => _("Enter the selected SLA values for each month. Percent values (0.00-100.00) are assumed."),
			'hostgroup_breakdown' => _("Here you have a list of all hosts that are member of this hostgroup and their states."),
			'servicegroup_breakdown' => _("Here you have a list of all services that are member of this servicegroup and their states."),
			'average_and_sla' => _("Shows the Average and SLA values for all selected services above."), // text ok?
			'availability' => _("This table shows a breakdown of the different states. How much time that was ok, warning, unknown, critical or undetermined in both actual time and percent. Time is also divied between uncheduled and scheduled which helps you to separate unplanned and planned events."),
			'piechart' => _("Pie chart that displays how much time in percent that was ok, warning, unknown, critical or undetermined."),
			'sla_graph' => _("Graphical report of the SLA. Green bars meens that the SLA was fulfilled and red that it was not fulfilled."),
			'sla_breakdown' => _("Breakdown of the SLA report in actual figures."),
			'sla_group_members' => _("Members of the selected group that the report is generated for. All members are links to individual reports."),
			'trends' => _("Shows trends during selected report period, lines above the main line are upscaled statechanges from the blacked out section below."),
			'trends_scaling' => _("Scale up rapid state changes into a line above the main line."),
			'collapse_green_trends' => _("Hide trends that are 100% Up/OK during the report period. This reduces visual noise to help you correlate events."),
			'use-sla-values' => _("Load SLA-values from previously saved reports. Just select a report in the list and it will autoload."),
			'include_pie_charts' => _('If you include this, your availability percentages will be graphed in pie charts'),
			"host_states" => _("Uncheck the host states you wish to hide or handle specially in the report."),
			"service_states" => _("Uncheck the service states you wish to hide or handle specially in the report."),

			// new scheduled report
			'report-type-save' => _("Select what type of report you would like to schedule the creation of"),
			'select-report' => _("Select which report you want to you want to schedule"), // text ok?
			'report' => _("Select the saved report to schedule"),
			'interval' => _("Select how often the report is to be produced and delivered"),
			'recipents' => _("Enter the email addresses of the recipients of the report. To enter multiple addresses, separate them by commas"),
			'filename' => _("This field lets you select a custom filename for the report. If the name ends in <strong>.csv</strong>, a CSV file will be generated - otherwise a PDF will be generated."),
			'start-date' => _("Enter the start date for the report (or use the pop-up calendar)."),
			'end-date' => _("Enter the end date for the report (or use the pop-up calendar)."),
			'local_persistent_filepath' => _("Specify an absolute path on the Monitor Server, where you want the report to be saved in PDF format.").'<br />'._("This should be the location of a folder, for example /tmp"),
			'attach_description' => _("Append this description inside the report's header to the general description given for the report"),
			'include_trends' => _("Check this to include a trends graph in your report.<br>Warning: This can make your reports slow!"),
			'include_trends_scaling' => _("Check this to get upscaled values on your trends graph for small segments of time that would otherwise be hidden."),
			'include_alerts' => _('Include a log of all alerts for all objects in your report.<br>Warning: This can make your reports slow!'),
			'synergy_events' => _('Include a detailed history of what happened to BSM objects'),
		);
		if (array_key_exists($id, $helptexts))
			echo $helptexts[$id];
		else
			parent::_helptexts($id);
	}
}
