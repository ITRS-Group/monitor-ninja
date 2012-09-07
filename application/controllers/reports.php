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
class Reports_Controller extends Base_reports_Controller
{
	private $status_link = "status/host/";
	private $trend_link = "trends/generate";
	private $history_link = "showlog/alert_history";
	private $notifications_link = "notifications/host";

	protected $reports_model = false;
	protected $trends_graph_model = false;

	/**
	*	Display report selection/setup page
	*/
	public function index($input=false)
	{
		$this->setup_options_obj($input);
		$this->reports_model = new Reports_Model($this->options);
		$this->trends_graph_model = new Trends_graph_Model();

		# check if we have all required parts installed
		if (!$this->reports_model->_self_check()) {
			url::redirect(Router::$controller.'/invalid_setup');
		}

		if(isset($_SESSION['report_err_msg'])) {
			$this->err_msg = $_SESSION['report_err_msg'];
			unset($_SESSION['report_err_msg']);
		}

		# reset current_report_params and main_report_params
		# just to be sure they're not left behind
		Session::instance()->set('current_report_params', null);
		Session::instance()->set('main_report_params', null);

		$old_config_names = Saved_reports_Model::get_all_report_names($this->type);
		$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", $old_config_names)."');";
		$type_str = $this->type == 'avail'
			? _('availability')
			: _('SLA');
		if($this->err_msg) {
			// @todo make this work work, only handled by js and a very silent redirect
			// now since the following message never gets printed:
			$error_msg = $this->err_msg;
			$this->template->error = $this->add_view('reports/error');
		}
		$this->template->content = $this->add_view('reports/setup');
		$template = $this->template->content;

		# we should set the required js-files
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';

		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = 'application/media/js/move_options.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('reports/js/reports.js');

		# this makes anything in application/media be imported before
		# application/views before modules/whatever, so op5reports can
		# put random crap here as well.

		# I apologize
		sort($this->xtra_js);
		$this->xtra_js = array_unique($this->xtra_js);

		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox.css';
		$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->xtra_css[] = $this->add_path('css/default/reports.css');
		$this->template->css_header->css = $this->xtra_css;

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

		$scheduled_info = false;
		if ($this->options['report_id']) {
			$scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->options['report_id']);
			$template->is_scheduled = empty($scheduled_info) ? false: true;
		}
		$template->scheduled_info = $scheduled_info;

		$label_avail = _('Availability');

		$label_sla = _('SLA');
		$label_switch_to = _('Switch to');

		$label_report = _('report');
		$template->label_report = $label_report;

		if ($this->options['report_id']) {
			$this->inline_js .= "expand_and_populate(" . $this->options->as_json() . ");\n";
		}
		else {
			$this->inline_js .= "set_selection(document.getElementsByName('report_type').item(0).value);\n";
		}

		if($this->options['includesoftstates'])
			$this->inline_js .= "toggle_label_weight(true, 'include_softstates');\n";
		if($this->options['assumestatesduringnotrunning'])
			$this->inline_js .= "toggle_label_weight(true, 'assume_progdown');\n";
		if($this->options['csv_output'])
			$this->inline_js .= "toggle_label_weight(true, 'csvout');\n";
		$this->inline_js .= "invalid_report_names = ".$old_config_names_js .";\n";

		$this->js_strings .= "var _edit_str = '"._('edit')."';\n";
		$this->js_strings .= "var _hide_str = '"._('hide')."';\n";
		$this->js_strings .= "var _label_avail = '".$label_avail."';\n";
		$this->js_strings .= "var _label_sla = '".$label_sla."';\n";
		$this->js_strings .= "var _label_switch_to = '".$label_switch_to."';\n";
		$this->js_strings .= "var _label_report = '".$label_report."';\n";
		$this->js_strings .= "var nr_of_scheduled_instances = ". (!empty($scheduled_info) ? sizeof($scheduled_info) : 0).";\n";
		$this->js_strings .= "var _reports_edit_information = '"._('Double click to edit')."';\n";
		$this->js_strings .= "var _reports_propagate = '"._('Would you like to propagate this value to all months')."';\n";
		$this->js_strings .= "var _reports_propagate_remove = '"._("Would you like to remove all values from all months")."';\n";
		$this->js_strings .= "var _schedule_change_filename = \""._('Would you like to change the filename based on your selections?')."\";\n";

		$this->js_strings .= reports::js_strings();

		$this->js_strings .= "var _reports_name_empty = '"._("Please give your report a meaningful name.")."';\n";
		$this->js_strings .= "var _reports_error_name_exists = '"._("You have entered a name for your report that already exists. <br />Please select a new name")."';\n";
		$this->js_strings .= "var _reports_error_name_exists_replace = \""._("The entered name already exists. Press 'Ok' to replace the entry with this name")."\";\n";
		$this->js_strings .= "var _reports_missing_objects = \""._("Some items in your saved report doesn't exist anymore and has been removed")."\";\n";
		$this->js_strings .= "var _reports_missing_objects_pleaseremove = '"._('Please modify the objects to include in your report below and then save it.')."';\n";

		$this->template->inline_js = $this->inline_js;

		$template->type = $this->type;
		$template->new_saved_title = sprintf(_('Create new saved %s report'), $type_str);
		$template->label_create_new = $this->type == 'avail' ? _('Availability report') : _('SLA report');
		$template->reporting_periods = $this->_get_reporting_periods();

		$template->months = reports::abbr_month_names();

		$template->scheduled_ids = $scheduled_ids;
		$template->scheduled_periods = $scheduled_periods;
		$template->saved_reports = $saved_reports;

		$this->js_strings .= "var _reports_success = '"._('Success')."';\n";
		$this->js_strings .= "var _reports_error = '"._('Error')."';\n";
		$this->js_strings .= "var _reports_schedule_error = '"._('An error occurred when saving scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_send_error = '"._('An error occurred when trying to send the scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_update_ok = '"._('Your schedule has been successfully updated')."';\n";
		$this->js_strings .= "var _reports_schedule_send_ok = '"._('Your report was successfully sent')."';\n";
		$this->js_strings .= "var _reports_schedule_create_ok = '"._('Your schedule has been successfully created')."';\n";
		$this->js_strings .= "var _reports_fatal_err_str = '"._('It is not possible to schedule this report since some vital information is missing.')."';\n";

		$this->js_strings .= "var _reports_no_sla_str = '"._('Please enter at least one SLA value')."';\n";
		$this->js_strings .= "var _reports_sla_err_str = '"._('Please check SLA values in fields marked red below and try again')."';\n";

		$this->template->js_strings = $this->js_strings;

		$this->template->title = _('Reporting » ').($this->type == 'avail' ? _('Availability Report') : _('SLA Report')).(' » Setup');
	}

	/**
	 * Generate (availability) report from parameters set in index()
	 *
	 * @param array $input = false
	 */
	public function generate($input=false)
	{
		$this->setup_options_obj($input);
		if ($this->options['output_format'] == 'pdf') {
			return $this->generate_pdf($input);
		}
		$this->reports_model = new Reports_Model($this->options);
		$this->trends_graph_model = new Trends_graph_Model();

		# check if we have all required parts installed
		if (!$this->reports_model->_self_check()) {
			url::redirect(Router::$controller.'/invalid_setup');
		}

		$this->_stash_params();

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = 'application/media/js/move_options.js';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('reports/js/reports.js');

		if ($this->options['skin']) {
			if (substr($this->options['skin'], -1, 1) != '/') {
				$this->options['skin'] .= '/';
			}
			$this->template->current_skin = $this->options['skin'];
		}

		$this->template->js_header->js = $this->xtra_js;

		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = $this->add_path('css/default/reports.css');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox.css';
		$this->template->css_header = $this->add_view('css_header');

		$old_config_names = Saved_reports_Model::get_all_report_names($this->type);
		$old_config_names_js = empty($old_config_names) ? "false" : "new Array('".implode("', '", $old_config_names)."');";
		$this->inline_js .= "invalid_report_names = ".$old_config_names_js .";\n";

		$this->template->content = $this->add_view('reports/index'); # base template with placeholders for all parts
		$template = $this->template->content;

		$scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->options['report_id']);

		$sub_type = false;

		if('custom' == $this->options['report_period'])
			$report_time_formatted  = sprintf(_("%s to %s"), date(nagstat::date_format(), $this->options['start_time']), date(nagstat::date_format(), $this->options['end_time']));
		else
			$report_time_formatted  = $this->options->get_value('report_period');

		if($this->options['rpttimeperiod'] != '')
			$report_time_formatted .= " - {$this->options['rpttimeperiod']}";

		switch ($this->options['report_type']) {
			case 'hostgroups':
				$sub_type = "host";
				$object_varname = 'host_name';
				$is_group = true;
				break;
			case 'servicegroups':
				$sub_type = "service";
				$object_varname = 'service_description';
				$is_group = true;
				break;
			case 'hosts':
				$sub_type = "host";
				$object_varname = 'host_name';
				$is_group = false;
				break;
			case 'services':
				$sub_type = "service";
				$object_varname = 'service_description';
				$is_group = false;
				break;
			default:
				url::redirect(Router::$controller.'/index');
		}
		$this->template->set_global('type', $this->type);
		$var = $this->options->get_value('report_type');
		$objects = false;
		$mon_auth = Nagios_auth_Model::instance();
		foreach ($this->options[$var] as $obj) {
			if ($mon_auth->{'is_authorized_for_'.substr($this->options['report_type'], 0, -1)}($obj))
				$objects[] = $obj;
		}

		$get_vars = $this->options->as_keyval_string();

		# fetch data
		if ($this->type == 'avail') {
			$data_arr = $is_group
				? $this->_expand_group_request($objects, $this->options->get_value('report_type'))
				: $this->reports_model->get_uptime();
		} else {
			$data_arr = $this->get_sla_data($this->options['months'], $objects, $object_varname);
		}

		if ($this->options['output_format'] == 'csv') {
			csv::csv_http_headers($this->type, $this->options);
			$this->template = $this->add_view('reports/'.$this->type.'csv');
			$this->template->data_arr = $data_arr;
			return;
		}

		$template->title = $this->type == 'avail' ? _('Availability Report') : _('SLA Report');

		$template->report_time_formatted = $report_time_formatted;

		# AVAIL REPORT
		if ($this->type == 'avail' && (empty($data_arr)
			|| (sizeof($data_arr)==1 && empty($data_arr[0]))
			|| (!isset($data_arr['source']) && empty($data_arr[0][0]['source']) ))) {
			# avail report is empty

			# what objects were submitted?
			$template->report_header = _('Empty report');

			$template->error = $this->add_view('reports/error');

			$template->error->error_msg = sprintf(_("The selected objects for this %s report doesn't seem to exist anymore.%s
			The reason for this is most likely that they have been removed or renamed in your configuration."), ucfirst(substr($this->options['report_type'], 0, strlen($this->options['report_type'])-1)), '<br />');
			if (!empty($objects)) {
				$template->error->missing_objects = $objects;
			}
		} else {
			# ==========================================
			# ========= REPORT STARTS HERE =============
			# ==========================================
			$this->template->content->report_options = $this->add_view('reports/options');

			$tpl_options = $this->template->content->report_options;

			$available_schedule_periods = false;
			$schedule_periods = Scheduled_reports_Model::get_available_report_periods();
			if ($schedule_periods !== false && !empty($schedule_periods)) {
				foreach ($schedule_periods as $s) {
					$available_schedule_periods[$s->id] = $s->periodname;
				}
			}
			$tpl_options->available_schedule_periods = $available_schedule_periods;
			$tpl_options->scheduled_info = $scheduled_info;
			if ($this->type == 'avail') {
				$this->inline_js .= "set_initial_state('scheduleddowntimeasuptime', '".$this->options['scheduleddowntimeasuptime']."');\n";
				$this->inline_js .= "set_initial_state('report_period', '".$this->options['report_period']."');\n";
				$this->inline_js .= "show_calendar('".$this->options['report_period']."');\n";
			}

			$this->js_strings .= "var cluster_mode = '".(int)$this->options['cluster_mode']."';\n";
			$this->js_strings .= "var scheduleddowntimeasuptime = '".$this->options['scheduleddowntimeasuptime']."';\n";

			$this->js_strings .= "var _reports_success = '"._('Success')."';\n";
			$this->js_strings .= "var _reports_error = '"._('Error')."';\n";
			$this->js_strings .= "var _reports_schedule_send_ok = '"._('Your report was successfully sent')."';\n";
			$this->js_strings .= "var nr_of_scheduled_instances = ". (!empty($scheduled_info) ? sizeof($scheduled_info) : 0).";\n";
			$this->js_strings .= "var _reports_fatal_err_str = '"._('It is not possible to schedule this report since some vital information is missing.')."';\n";
			$this->js_strings .= "var _reports_schedule_interval_error = '"._(' -Please select a schedule interval')."';\n";
			$this->js_strings .= "var _reports_schedule_recipient_error = '"._(' -Please enter at least one recipient')."';\n";
			$this->js_strings .= "var _edit_str = '"._('edit')."';\n";
			$this->js_strings .= "var _hide_str = '"._('hide')."';\n";
			$this->js_strings .= "var _reports_schedule_error = '"._('An error occurred when saving scheduled report')."';\n";
			$this->js_strings .= "var _reports_schedule_update_ok = '"._('Your schedule has been successfully updated')."';\n";
			$this->js_strings .= "var _reports_schedule_create_ok = '"._('Your schedule has been successfully created')."';\n";
			$this->js_strings .= "var _reports_view_schedule = '"._('View schedule')."';\n";
			$this->js_strings .= "var _reports_edit_information = '"._('Double click to edit')."';\n";
			$this->js_strings .= "var _reports_errors_found = '"._('Found the following error(s)')."';\n";
			$this->js_strings .= "var _reports_please_correct = '"._('Please correct this and try again')."';\n";

			$this->js_strings .= "var _reports_error_name_exists = '"._("You have entered a name for your report that already exists. <br />Please select a new name")."';\n";
			$this->js_strings .= reports::js_strings();
			$this->js_strings .= "var _reports_name_empty = '"._("Please give your report a meaningful name.")."';\n";
			$this->js_strings .= "var _reports_error_name_exists_replace = \""._("The entered name already exists. Press 'Ok' to replace the entry with this name")."\";\n";

			$csv_link = $this->_get_csv_link();
			$tpl_options->csv_link = $csv_link;
			$pdf_link = $this->_get_pdf_link($this->type);
			$tpl_options->pdf_link = $pdf_link;

			$host_graph_items = array('TOTAL_TIME_UP' => _('Up'),
					'TOTAL_TIME_DOWN' => _('Down'),
					'TOTAL_TIME_UNREACHABLE' => _('Unreachable'),
					'TOTAL_TIME_UNDETERMINED' => _('Undetermined'));
			$service_graph_items = array('TOTAL_TIME_OK' => _('Ok'),
					'TOTAL_TIME_WARNING' => _('Warning'),
					'TOTAL_TIME_UNKNOWN' => _('Unknown'),
					'TOTAL_TIME_CRITICAL' => _('Critical'),
					'TOTAL_TIME_UNDETERMINED' => _('Undetermined'));
			$graph_filter = ${$sub_type.'_graph_items'};

			# hostgroups / servicegroups
			if ($this->type == 'avail' && isset($data_arr[0])) {

				$template->header = $this->add_view('reports/header');
				$template->header->report_time_formatted = $report_time_formatted;
				$template->header->csv_link = $csv_link;
				$template->header->pdf_link = $pdf_link;

				if ($is_group) {
					foreach ($data_arr as $data) {
						if (empty($data))
							continue;
						array_multisort($data);
						$template_values[] = $this->_get_multiple_state_info($data, $sub_type, $get_vars, $this->options['start_time'], $this->options['end_time'], $this->type);
					}
				} else {
					array_multisort($data_arr);
					$template_values[] = $this->_get_multiple_state_info($data_arr, $sub_type, $get_vars, $this->options['start_time'], $this->options['end_time'], $this->type);
				}

				if (!empty($template_values) && count($template_values))
					for($i=0,$num_groups=count($template_values)  ; $i<$num_groups ; $i++) {
						$this->_reorder_by_host_and_service($template_values[$i], $this->options['report_type']);
					}

				if($this->options['include_trends']) {
					if($is_group) {
						// Copy-pasted from controllers/trends.php
						foreach ($data_arr as $key => $data) {
							# >= 2 hosts or services won't have the extra
							# depth in the array, so we break out early
							if (empty($data['log']) || !is_array($data['log'])) {
								if(isset($data_arr['log'])) {
									$graph_data = $data_arr['log'];
								} elseif(isset($data_arr[0]['log'])) {
									// fixes the case of multiple groups when at least one of them
									// has a '/' in its name
									$graph_data = $data_arr[0]['log'];
								}
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
						$graph_data = $data_arr['log'];
					}

					$template->trends_graph = $this->add_view('trends/new_report');
					$template->trends_graph->graph_image_source = $this->trends_graph_model->get_graph_src_for_data(
						$graph_data,
						$this->options['start_time'],
						$this->options['end_time'],
						$template->title
					);
					$template->trends_graph->is_avail = true;
				}

				$template->content = $this->add_view('reports/multiple_'.$sub_type.'_states');
				$template->content->multiple_states = $template_values;
				$template->content->hide_host = false;
				$template->content->service_filter_status_show = true;
				$template->content->report_time_formatted = $report_time_formatted;

				$template->pie = $this->add_view('reports/pie_chart');

				// ===== SETUP PIECHART VALUES =====
				$image_data = array();
				foreach($graph_filter as $key => $val) { $image_data[strtoupper($val)] = 0; }

				# We've either got
				# 1) custom group
				# 2) hostgroup / servicegroup

				$groups_added = 0;
				$pie_groupname = false;
				if(!isset($data_arr['groupname'])) { # actual hostgroup/servicegroup.
					$tmp_title = ucfirst($sub_type)._('group breakdown');
					$template->header->title = $tmp_title;
					foreach($data_arr as $data) { # for every group
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
					$tmp_title = ucfirst($sub_type).' '._('state breakdown');
					$template->header->title = $tmp_title;
					if (is_array($data_arr['states'])) {
						foreach ($graph_filter as $key => $val) {
							if ($data_arr['states'][$key]!=0)
							{
								if (isset($image_data[0][strtoupper($val)])) {
									$image_data[0][strtoupper($val)] += $data_arr['states'][$key];
								} else {
									$image_data[0][strtoupper($val)] = $data_arr['states'][$key];
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
				}
			} else { # host/services
				$image_data = false;
				$data_str = '';
				if (!empty($data_arr)) {
					$data = $data_arr;
					$template->content = $this->add_view('reports/'.$this->type);
					$template->content->options = $this->options;

					$template->header = $this->add_view('reports/header');
					$template->header->report_time_formatted = $report_time_formatted;
					$template->header->csv_link = $this->type == 'avail' ? $csv_link : false;
					$template->header->pdf_link = $pdf_link;

					if ($this->type == 'avail') {
						$avail_data = $this->_print_state_breakdowns($data['source'], $data['states'], $this->options['report_type']);
						$avail = $template->content;
						$avail->state_values = $this->state_values;

						$avail->avail_data = $avail_data;
						$avail->source = $data['source'];
						$avail->report_time_formatted = $report_time_formatted;

						$avail->header_string = ucfirst($this->options['report_type'])." "._('state breakdown');

						$this->xtra_css[] = $this->add_path('css/default/reports.css');
						if($this->options['include_trends']) {
							$trends_data = false;
							if (isset($data['log']) && isset($data['source']) && !empty($data['source'])) {
								$trends_data = $data['log'];
							}

							if($is_group) {
								// Copy-pasted from controllers/trends.php
								foreach ($data_arr as $key => $data) {
									# >= 2 hosts or services won't have the extra
									# depth in the array, so we break out early
									if (empty($data['log']) || !is_array($data['log'])) {
										$graph_data = $data_arr['log'];
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
								$graph_data = $data_arr['log'];
							}

							$template->trends_graph = $this->add_view('trends/new_report');
							$template->trends_graph->graph_image_source = $this->trends_graph_model->get_graph_src_for_data(
								$graph_data,
								$this->options['start_time'],
								$this->options['end_time'],
								$template->title
							);
							$template->trends_graph->report_time_formatted = $report_time_formatted;
							$this->xtra_js[] = $this->add_path('trends/js/trends.js');
						}

						$avail->pie = $this->add_view('reports/pie_chart');
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
							$avail->pie->data_str = $data_str;
							$avail->pie->source = $data['source'];
						}

						if ($sub_type=='host') {
							$service_states = $this->_print_states_for_services($data_arr['source'], $this->options['start_time'], $this->options['end_time'], $this->options['report_type']);

							if ($service_states !== false) {
								$template_values[] = $this->_get_multiple_state_info($service_states, 'service', $get_vars, $this->options['start_time'], $this->options['end_time'], $this->type);
								$header_str = _("Service state breakdown");
								$template->svc_content = $this->add_view('reports/multiple_service_states');
								$content = $template->svc_content;
								$content->header_string = $header_str;
								$content->multiple_states = $template_values;
								$content->hide_host = true;
								$content->service_filter_status_show = false;
								$content->source = $data['source'];
								$content->report_time_formatted = $report_time_formatted;
							}
						}

						// fetch and display log messages
						$log = arr::search($data, 'log');
						if ($log !== false) {
							$template->log_content = $this->add_view('reports/log');
							$log_template = $template->log_content;
							$log_template->log = array_shift($log);
							$log_template->type = $sub_type;
							$log_template->source = $data['source'];
							$log_template->report_time_formatted = $report_time_formatted;
							$log_template->date_format_str = nagstat::date_format();
						}

						$t1 = $this->options['start_time'];
						$t2 = $this->options['start_time'];

						# assume default values for the following
						$backtrack = 1;

						$links = array();
						$trends_img_params = '';
						$trends_link_params = '';
						$downtime       = $this->options['scheduleddowntimeasuptime'];
						$not_running    = $this->options['assumestatesduringnotrunning'];
						$soft_states    = $this->options['includesoftstates'];

						# links - only for HTML reports
						switch($this->options['report_type']) {
							case 'hosts':
								# only meaningful to print these links if only one host selected
								if(count($hostname) != 1)
									break;

								$host = $hostname[0];
								$template->header->title = ucfirst($this->options['report_type']).' '._('details for').': '.ucfirst($host);
								$all_avail_params = "report_type=".$this->options['report_type'].
									 "&amp;host_name=all".
									 "&amp;report_period={$this->options['report_period']}".
									 "&amp;rpttimeperiod={$this->options['rpttimeperiod']}".
									 "&amp;start_time=".$this->options['start_time'].
									 "&amp;end_time=".$this->options['end_time'];

								if($downtime)			$all_avail_params .= "&amp;scheduleddowntimeasuptime=$downtime";
								if($not_running)		$all_avail_params .= "&amp;assumestatesduringnotrunning=$not_running";
								if($soft_states)		$all_avail_params .= "&amp;includesoftstates=$soft_states";

								$links[Router::$controller.'/'.Router::$method."?".$all_avail_params] = _('Availability report for all hosts');

								$trends_params = "host=$host".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;includesoftstates=".$soft_states.
									"&amp;assumestatesduringnotrunning=".$this->options['assumestatesduringnotrunning'].
									"&amp;backtrack=$backtrack";

								$trends_img_params = $this->trend_link."?".
									"host=$host".
									"&amp;createimage&amp;smallimage".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;includesoftstates=".$soft_states.
									"&amp;assumestatesduringnotrunning=".$this->options['assumestatesduringnotrunning'].
									"&amp;backtrack=$backtrack";

								$trends_link_params = $this->trend_link."?".
									"host=$host".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;includesoftstates=".$soft_states.
									"&amp;assumestatesduringnotrunning=".$this->options['assumestatesduringnotrunning'].
									"&amp;backtrack=$backtrack";



								$links[$this->trend_link."?".$trends_params] = _('Trends');

								$histogram_params = "host=$host&amp;t1=$t1&amp;t2=$t2";

								$links[$this->histogram_link . "?" . $histogram_params] = _('Alert histogram');

								$links[$this->status_link.$host] = _('Status detail');

								$links[$this->history_link . "/" .$host] = _('Alert history');
								$links[$this->notifications_link . "/" . $host] = _('Notifications');
								break;

							case 'services':

								list($host, $service) = explode(';',$service[0]);

								$template->header->title = ucfirst($this->options['report_type']).' '._('details for').': '.ucfirst($service).' '._('on host').': '.ucfirst($host);
								if (isset($template->content)) {
									$template->content->host = $host;
									$template->content->service = $service;
								}
								$avail_params = "&show_log_entries".
									 "&amp;t1=$t1".
									 "&amp;t2=$t2".
									 "&amp;report_period=".$this->options['report_period'].
									 "&amp;rpttimeperiod=".$this->options['rpttimeperiod'].
									 "&amp;backtrack=$backtrack".
									 "&amp;assumestatesduringnotrunning=".$this->_convert_yesno_int($not_running, false).
									 "&amp;show_log_entries".
									 "&amp;showscheduleddowntime=yes";


								if($downtime)			$avail_params .= "&amp;scheduleddowntimeasuptime=$downtime";
								if($not_running)		$avail_params .= "&amp;assumestatesduringnotrunning=$not_running";
								if($soft_states)		$avail_params .= "&amp;includesoftstates=$soft_states";

								$trends_params = "host=$host".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;includesoftstates=".$soft_states.
									"&amp;assumestatesduringnotrunning=".$this->options['assumestatesduringnotrunning'].
									"&amp;backtrack=$backtrack";

								$trends_img_params = $this->trend_link."?".
									"host=$host".
									"&amp;service=$service".
									"&amp;createimage&amp;smallimage".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;includesoftstates=".$soft_states.
									"&amp;assumestatesduringnotrunning=".$this->options['assumestatesduringnotrunning'].
									"&amp;backtrack=$backtrack";

								$trends_link_params = $this->trend_link."?".
									"host=$host".
									"&amp;service=$service".
									"&amp;t1=$t1".
									"&amp;t2=$t2".
									"&amp;includesoftstates=".$soft_states.
									"&amp;assumestatesduringnotrunning=".$this->options['assumestatesduringnotrunning'].
									"&amp;backtrack=$backtrack";

								$histogram_params     = "host=$host&amp;service=$service&amp;t1=$t1&amp;t2=$t2";
								$history_params       = "host=$host&amp;service=$service";
								$notifications_params = "host=$host&amp;service=$service";


								$links[Router::$controller.'/'.Router::$method."?host=$host$avail_params"] 			= _('Availability report for this host');
								$links[Router::$controller.'/'.Router::$method."?host=null&amp;service=all$avail_params"] = _('Availability report for all services');
								$links[$this->trend_link . "?" . $trends_params . "&amp;service_description=".$host.';'.$service] = _('Trends');
								$links[$this->histogram_link . "?" . $histogram_params] 		= _('Alert histogram');
								$links[$this->history_link . "?" . $history_params] 			= _('Alert history');
								$links[$this->notifications_link . "?" . $notifications_params] = _('Notifications');

								break;
						}

						$template->links = $links;
						$template->trends = $trends_img_params;
						$template->trends_link = $trends_link_params;
						$template->source = $data['source'];
						$template->header_string = sprintf(_("State breakdown for %s"), $data['source']);
					} else {
						# SLA report
						$template->header->title = _('SLA breakdown');
						$sla = $template->content;
						$sla->report_data = $data_arr;
					}

				} # end if not empty. Display message to user?
			}

			$this->template->inline_js = $this->inline_js;
			$this->template->js_strings = $this->js_strings;
			$this->template->css_header->css = $this->xtra_css;

		}
		$this->template->title = _('Reporting » ').($this->type == 'avail' ? _('Availability Report') : _('SLA Report')).(' » Report');
		return $template;
	}

	/**
	*	Stash parameters in session from setup form to be used
	*	for re-generating report.
	*/
	public function _stash_params()
	{
		Session::instance()->set('current_report_params', null);

		if (!empty($data)) {
			if (array_key_exists('ew_report_setup', $input)) {
				# directly from setup form - keep data for backlink
				Session::instance()->set('main_report_params', $this->options->as_keyval_string(false));
			}

			Session::instance()->set('current_report_params', $this->options->as_keyval_string(false));
		}
	}

	/**
	*	Save a report via ajax call
	* 	Called from reports.js (trigger_ajax_save())
	* 	@return JSON string
	*/
	public function save($report_id = false)
	{
		if(!request::is_ajax()) {
			$msg = _('Only Ajax calls are supported here');
			die($msg);
		}

		$this->auto_render=false;

		# 	Fetch the input variable 'type' from
		#	either $_GET or $_POST and use default
		# 	method param if nothing found

		$obj_field = $this->options->get_val('report_type');
		$obj_value = $this->options[$obj_field];

		$save_report_settings = arr::search($_REQUEST, 'save_report_settings');
		$return = false;
		if ($save_report_settings && $this->options['report_name'] !== false && !empty($obj_value)) {
			$this->report_id = Saved_reports_Model::edit_report_info($this->type, $this->report_id, $report_options, $obj_value, $this->options['months']);
			$status_msg = $this->report_id ? _("Report was successfully saved") : "";
			$msg_type = $this->report_id ? "ok" : "";
			$return = array('status' => $msg_type, 'status_msg' => $status_msg, 'report_id' => $this->report_id);
		} else {
			$return = array('status' => '', 'status_msg' => _('Unable to save this report.'));
		}
		echo json::encode($return);
	}

	/**
	*	Print message to user about invalid setup.
	*	This could be because of missing database or
	* 	reports module
	*/
	public function invalid_setup()
	{
		$this->template->content = $this->add_view('reports/'.$this->report_prefix.'reports_module');
		$template = $this->template->content;
		$template->error_msg  = _('Some parts in your setup is apparently missing.');
		$template->info = _("make sure you install the latest version of merlin");
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
		$err_msg = $this->err_msg;

		$host_name = trim($host_name);
		if (empty($host_name)) {
			return false;
		}
		$host_model = new Host_Model();
		$res = $host_model->get_services($host_name);
		if (!empty($res)) {
			$service_arr = array();

			$classname = get_class($this->options);
			$opts = new $classname($this->options);
			$opts['host_name'] = $host_name;
			foreach ($res as $row)
				$service_arr[] = $row->service_description;
			$opts['service_description'] = $service_arr;
			$report_class = new Reports_Model($opts);

			$data_arr = $report_class->get_uptime();
			return $data_arr;
		}
		return false;
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
		$return = form::open($this->type.'/generate', array('style' => 'display:block; position: absolute; top: 0px; right: 71px'));
		$return .= "<div>\n";
		$url_params = '';
		$url_params_to_skip = array('js_start_time', 'js_end_time', 's1'); # params that just f--k up things

		foreach($this->options as $key => $val)
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
		$label = _('Download report as CSV');
		$return .= "<input type='image' src='".$this->add_path('icons/32x32/page-csv.png').
			"' alt='".$label."' title='".$label."' style='border: 0px; width: 32px; height: 32px; margin-top: 13px; background: none; margin-right: 7px' /></div></form>\n";
		return $return;
	}

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
		$pdf_img_alt = _('Show as pdf');

		$default_filename = 'report.pdf';
		$default_options = array
		(
			'create_pdf' => true
		);
		$default_action_url = $this->type.'/generate';

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
		foreach($this->options as $key => $val)
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

		$form .= '<input type="image" src="'.$pdf_img_src.'" title="'.$pdf_img_alt.'" '
			.'value="'.$pdf_img_alt.'"  alt="'.$pdf_img_alt.'" style="border: 0px; width: 32px; height: 32px; margin-top: 14px; background: none" />';


		$form .= '</div>';
		$form .= "</form>";

		return $form;
	}

	/**
	 * Fetch data from report_class
	 * Uses split_month_data() to split start- and end_time
	 * on months.
	 *
	 * @param $months = false
	 * @param $objects = false
	 * @return array
	 */
	public function get_sla_data($months, $objects, $object_varname)
	{
		if (empty($months) || empty($objects)) {
			return false;
		}

		$report_data = false;

		// OK, we have start and end but we will have to split
		// this time into parts according to sla_periods (months)
		$time_arr = $this->_split_month_data($months, $this->options['start_time'], $this->options['end_time']);
		// only use month entered by the user regardless of start- or endtime
		$data = false;
		$optclass = get_class($this->options);
		$opts = new $optclass($this->options);
		$opts[$this->options->get_value('report_type')] = $objects;
		unset($opts['report_period']);
		switch ($this->options['report_type']) {
		 case 'hostgroups':
		 case 'servicegroups':
			foreach ($time_arr as $mnr => $dates) {
				$opts['start_time'] = $dates['start'];
				$opts['end_time'] = $dates['end'];
				$data_tmp = $this->_expand_group_request($objects, $this->options->get_value('report_type'), $opts);
				if (!empty($data_tmp))
					foreach ($data_tmp as $group => $val) {
						if ($val !== false) {
							$data[$group][$mnr] = array(
								'source' => $val['source'],
								'states' => $val['states'],
								'tot_time' => $val['tot_time'],
								'groupname' => $val['groupname']
								);
						}
					}
			}
			$report_data = $this->_sla_group_data($data, $object_varname);
			break;
		 case 'hosts':
		 case 'services':
			foreach ($time_arr as $mnr => $dates) {
				$opts['start_time'] = $dates['start'];
				$opts['end_time'] = $dates['end'];
				$report_class = new Reports_Model($opts);
				$data_tmp = $report_class->get_uptime();

				# The next line extracts _GROUPWIDE STATES_, discards individual member info (numeric indices)
				$data[0][$mnr] = array(
					'source' => $data_tmp['source'],
					'states' => $data_tmp['states'],
					'tot_time' => $data_tmp['tot_time'],
					'groupname' => $data_tmp['groupname']
				);
				unset($report_class);
			}
			$report_data = $this->_sla_group_data($data, $object_varname);
			break;
		 default:
			die("ooops, didn't see {$this->options['report_type']} comming");
		}
		return $report_data;
	}

	/**
	*	Mangle SLA data for host- and servicegroups
	*/
	public function _sla_group_data($sla_data, $object_varname)
	{
		if (empty($sla_data))
			return false;
		$report_data = false;
		foreach ($sla_data as $group_idx => $period_data) {
			$table_data = false;
			$data = false;
			$name = false;
			$members = false;
			// loop over whole period for current group
			foreach ($period_data as $period_start => $tmp_data) {
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
				// the same for all iterations of this loop, but we need it after
				$source = $tmp_data['source'];
				$name = $tmp_data['groupname'];
			}

			if (!$name && count($source) == 1)
				$name = $source;

			$data_str = base64_encode(serialize($data));

			$member_links = array();
			foreach($source as $member) {
				$member_links[] = $this->_generate_sla_member_link($member, $object_varname);
			}

			$report_data[] = array(
				'name' => $name,
				'table_data' => $table_data,
				'data_str' => $data_str,
				'source' => $source,
				'member_links' => $member_links,
				'avail_link' => $this->_generate_avail_member_link(),
			);
		}
		return $report_data;
	}

	/**
	 * @param string $member
	 * @return array HTML containing a link to SLA report for the one member
	 */
	private function _generate_sla_member_link($member, $varname)
	{
		$return = '<a href="'.url::site().'sla/generate?'.$varname.'[]='.$member;
		foreach($this->options as $key => $val) {
			switch ($key) {
				case 'start_time': case 'end_time':
					if (is_numeric($val)) {
						$val = date('Y-m-d H:i', $val);
					}
					break;
			}
			$return .= "&amp;$key=$val";
		}
		foreach($this->options['months'] as $month => $sla) {
			$return .= '&amp;month_'.$month.'='.$sla;
		}
		$host_alias = '';
		$service_description = '';
		$host_name = '';
		if ($this->options['use_alias']) {
			# use alias with host_name
			if (strstr($member, ';')) {
				# we have host_name;service_description so we neeed to split this
				$member_parts = explode(';', $member);
				if (is_array($member_parts) && sizeof($member_parts)==2) {
					$host_name = $member_parts[0];
					$host_alias = $this->_get_host_alias($host_name);
					$service_description = $member_parts[1];
					$member = sprintf(_('%s on %s(%s)'), $service_description, $host_alias, $host_alias);
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
	 * @param string $members
	 * @return string A link to an Availability report for all members
	 */
	private function _generate_avail_member_link()
	{
		$objects = '';
		$return = url::site().'avail/generate?';
		$return .= $this->options->as_keyval_string(false);
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
			$return[$date] = array('start' => $date, 'end' => $end);
			$date = $end;
		}
		return $return;
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
		$helptexts = array(
			'filter' => _("Free text search, matching the objects in the left list below"),
			'report-type' => _("Select the preferred report type. Hostgroup, Host, Servicegroup or Service. ".
				"To include objects of the given type in the report, select the objects from the left list and click on ".
				"the right pointing arrow. To exclude objects from the report, select the objects from the right list ".
				"and click on the left pointing arrow."),
			'report_time_period' => _("What time should the report be created for. Tip: This can be used for SLA reporting."),
			'scheduled_downtime' => _("Select if downtime that occurred during scheduled downtime should be counted as the actual state, as uptime, or if it should be counted as uptime but also showing the difference that makes."),
			'initial_states' => sprintf(_("Whether to assume logging of initial states or not. Default values are YES. ".
				"%sFor advanced users the value can be modified by editing the nagios.cfg config file located in the %s directory."), '<br /><br />', $nagios_etc_path),
			'first_assumed_host' => _("If there is no information about the host or service in the current log file, ".
				"the status of the host/service will be assumed. Default value is &quot;First Real State&quot;."),
			'first_assumed_service' => _("If there is no information about the host or service in the current log file, ".
				"the status of the host/service will be assumed. Default value is &quot;First Real State&quot;."),
			'stated_during_downtime' => _("If the application is not running for some time during a report period we can by this ".
				"option decide to assume states for hosts and services during the downtime. Default value is YES."),
			'includesoftstates' => _("A problem is classified as a SOFT problem until the number of checks has reached the ".
				"configured max_check_attempts value. When max_check_attempts is reached the problem is reclassified as HARD."),
			'use_average' => sprintf(_("What calculation method to use for the report. %s".
				"Traditional Availability reports are based on group availability (worst case). An alternative way is to use average values for ".
				"the group or object in question. Note that using average values are by some, considered %s not %s to be actual SLA."), '<br /><br />', '<b>', '</b>'),
			'use_alias' => _("Select if you would like to see host aliases in the generated reports instead of just the host_name"),
			'csv_format' => _("The CSV (comma-separated values) format is a file format that stores tabular data. This format is supported ".
				"by many applications such as MS Excel, OpenOffice and Google Spreadsheets."),
			'save_report' => _("Check this box if you want to save the configured report for later use."),
			'reporting_period' => _("Choose from a set of predefined report periods or choose &quot;CUSTOM REPORT PERIOD&quot; ".
				"to manually specify Start and End date."),
			'enter-sla' => _("Enter the selected SLA values for each month. Percent values (0.00-100.00) are assumed."),
			'report_settings_sml' => _("Here you can modify the report settings for the report you are currently viewing."),
			'cluster_mode' => _("When creating a report in cluster mode, the group logic is reversed so that the OK/UP time is calculated using the most positive service/host state of the selected objects."),
			'log_entries' => _("Shows the actual log messages that this report was created of."),
			'hostgroup_breakdown' => _("Here you have a list of all hosts that are member of this hostgroup and their states."),
			'servicegroup_breakdown' => _("Here you have a list of all services that are member of this servicegroup and their states."),
			'average_and_sla' => _("Shows the Average and SLA values for all selected services above."), // text ok?
			'availability' => _("This table shows a breakdown of the different states. How much time that was ok, warning, unknown, critical or undetermined in both actual time and percent. Time is also divied between uncheduled and scheduled which helps you to separate unplanned and planned events."),
			'piechart' => _("Pie chart that displays how much time in percent that was ok, warning, unknown, critical or undetermined."),
			'sla_graph' => _("Graphical report of the SLA. Green bars meens that the SLA was fulfilled and red that it was not fulfilled."),
			'sla_breakdown' => _("Breakdown of the SLA report in actual figures."),
			'sla_group_members' => _("Members of the selected group that the report is generated for. All members are links to individual reports."),
			'trends' => _("Shows trends during selected report period"),
			'saved_reports' => _("A list of all your saved reports. To load them, select the report you wish to generate and click select."),
			'use-sla-values' => _("Load SLA-values from previously saved reports. Just select a report in the list and it will autoload."),

			// new scheduled report
			'report-type-save' => _("Select what type of report you would like to schedule the creation of"),
			'select-report' => _("Select which report you want to you want to schedule"), // text ok?
			'report' => _("Select the saved report to schedule"),
			'interval' => _("Select how often the report is to be produced and delivered"),
			'recipents' => _("Enter the email addresses of the recipients of the report. To enter multiple addresses, separate them by commas"),
			'filename' => _("This field lets you select a custom filename for the report. If the name ends in <strong>.csv</strong>, a CSV file will be generated - otherwise a PDF will be generated."),
			'description' => _("Add a description to this schedule. This may be any information that could be of interest when editing the report at a later time. (optional)"),
			'start-date' => _("Enter the start date for the report (or use the pop-up calendar)."),
			'end-date' => _("Enter the end date for the report (or use the pop-up calendar)."),
			'local_persistent_filepath' => _("Specify an absolute path on the local disk, where you want the report to be saved in PDF format.").'<br />'._("This should be the location of a folder, for example /tmp"),
			'include_trends' => _("Check this to include a trends graph in your report."),
			'status_to_display' => _('Uncheck a status to exclude log entries of that kind from the report.')
		);
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else
			echo sprintf(_("This helptext ('%s') is yet not translated"), $id);
	}
}

