<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Trends controller
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
class Trends_Controller extends Base_reports_Controller {
	public $type = 'trends';
	private $data_arr = false;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	*	Display report selection/setup page
	*/
	public function index($report_id = false, $input = false)
	{
		$this->setup_options_obj($input);
		$this->template->disable_refresh = true;

		$this->template->content = $this->add_view('trends/setup');
		$template = $this->template->content;

		# we should set the required js-files
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = 'application/media/js/move_options.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('trends/js/trends.js');
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';

		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = $this->add_path('css/default/reports.css');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox.css';
		$this->template->css_header->css = $this->xtra_css;

		$label_report = _('report');

		if ($this->options['report_id']) {
			$this->inline_js .= "expand_and_populate(" . $this->options->as_json() . ");\n";
		} else {
			$this->inline_js .= "set_selection(document.getElementsByName('report_type').item(0).value);\n";
		}

		if($this->options['includesoftstates'])
			$this->inline_js .= "toggle_label_weight(true, 'include_softstates');\n";
		if($this->options['assumestatesduringnotrunning'])
			$this->inline_js .= "toggle_label_weight(true, 'assume_progdown');\n";
		$this->inline_js .= "invalid_report_names = false;\n";
		$this->inline_js .= "uncheck('save_report_settings');\n";
		$this->inline_js .= "$('#report_save_information').hide();\n";

		$this->js_strings .= "var _edit_str = '"._('edit')."';\n";
		$this->js_strings .= "var _hide_str = '"._('hide')."';\n";
		$this->js_strings .= "var _label_report = '".$label_report."';\n";
		$this->js_strings .= "var nr_of_scheduled_instances = ". (!empty($scheduled_info) ? sizeof($scheduled_info) : 0).";\n";
		$this->js_strings .= "var _reports_edit_information = '"._('Double click to edit')."';\n";
		$this->js_strings .= "var _reports_propagate = '"._('Would you like to propagate this value to all months')."';\n";
		$this->js_strings .= "var _reports_propagate_remove = '"._("Would you like to remove all values from all months")."';\n";

		$this->js_strings .= "var _schedule_change_filename = \""._('Would you like to change the filename based on your selections?')."\";\n";
		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _reports_error_name_exists = '".sprintf(_("You have entered a name for your report that already exists. %sPlease select a new name"), '<br />')."';\n";
		$this->js_strings .= "var _reports_missing_objects = \""._("Some items in your saved report doesn't exist anymore and has been removed")."\";\n";
		$this->js_strings .= "var _reports_missing_objects_pleaseremove = '"._('Please modify the objects to include in your report below and then save it.')."';\n";

		$this->template->inline_js = $this->inline_js;

		$template->type = $this->type;
		$template->reporting_periods = $this->_get_reporting_periods();

		$this->js_strings .= "var _reports_successs = '"._('Success')."';\n";
		$this->js_strings .= "var _reports_error = '"._('Error')."';\n";
		$this->js_strings .= "var _reports_schedule_error = '"._('An error occurred when saving scheduled report')."';\n";
		$this->js_strings .= "var _reports_schedule_update_ok = '"._('Your schedule has been successfully updated')."';\n";
		$this->js_strings .= "var _reports_schedule_create_ok = '"._('Your schedule has been successfully created')."';\n";
		$this->js_strings .= "var _reports_fatal_err_str = '"._('It is not possible to schedule this report since some vital information is missing.')."';\n";

		$this->template->js_strings = $this->js_strings;
		$this->template->title = _('Reporting » Trends » Setup');
	}

	/**
	*	Generate trends report with settings from the setup page
	*/
	public function generate($input = false)
	{
		$this->setup_options_obj($input);
		$this->options['keep_logs'] = true;
		$this->options['keep_sub_logs'] = true;
		$report_class = new Reports_Model($this->options);
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = 'application/media/js/move_options';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('trends/js/trends.js');

		$this->template->js_header->js = $this->xtra_js;

		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = $this->add_path('css/default/reports.css');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox.css';
		$this->template->css_header = $this->add_view('css_header');
		$this->template->css_header->css = $this->xtra_css;

		$this->template->content = $this->add_view('trends/index'); # base template with placeholders for all parts
		$template = $this->template->content;

		if('custom' == $this->options['report_period'])
			$report_time_formatted  = sprintf(_("%s to %s"), $str_start_date, $str_end_date);
		else
			$report_time_formatted  = $this->options->get_value('report_period');

		if($this->options['rpttimeperiod'] != '')
			$report_time_formatted .= " - {$this->options['rpttimeperiod']}";

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
				url::redirect(Router::$controller.'/index');
		}
		$var = $this->options->get_value('report_type');
		$objects = false;
		$mon_auth = Nagios_auth_Model::instance();
		foreach ($this->options[$var] as $obj) {
			if ($mon_auth->{'is_authorized_for_'.substr($this->options['report_type'], 0, -1)}($obj))
				$objects[] = $obj;
		}
		$selected_objects = '&'.$var.'[]='.implode('&'.$var.'[]=', $objects);

		$get_vars = $this->options->as_keyval_string();

		$this->data_arr = $is_group
			? $this->_expand_group_request($objects, $this->options->get_value('report_type'))
			: $report_class->get_uptime();

		if ((empty($this->data_arr) || (sizeof($this->data_arr)==1 && empty($this->data_arr[0])))) {
			# error!
			# what objects were submitted?
			$template->report_header = _('Empty report');

			$template->error = $this->add_view('reports/error');

			$template->error->error_msg = sprintf(_("The selected objects for this %s report doesn't seem to exist anymore.%s
			The reason for this is most likely that they have been removed or renamed in your configuration."), ucfirst($this->get_value('report_type')), '<br />');
			if (!empty($objects)) {
				$template->error->missing_objects = $objects;
			}
			return;
		} else {
			# ==========================================
			# ========= REPORT STARTS HERE =============
			# ==========================================

			# decide what report periods to print
			$this->template->content->report_options = $this->add_view('trends/options');
			$tpl_options = $this->template->content->report_options;

			$this->inline_js .= "set_initial_state('assumestatesduringnotrunning', '".$this->options['assumestatesduringnotrunning']."');\n";
			$this->inline_js .= "show_calendar('".$this->options['report_period']."');\n";
			$this->js_strings .= reports::js_strings();
			$this->js_strings .= "var assumestatesduringnotrunning = '".$this->options['assumestatesduringnotrunning']."';\n";
			$this->js_strings .= "var report_period = '".$this->options['report_period']."';\n";

			$avail_data = false;
			$raw_trends_data = false;
			$multiple_items = false; # structure of avail_data

			if (isset($this->data_arr[0])) {
				$avail_template = $this->add_view('trends/multiple_'.$sub_type.'_states');
				$avail_template->hide_host = false;
				$avail_template->get_vars = $get_vars;
				$avail_template->report_type = $this->options['report_type'];
				$avail_template->selected_objects = $selected_objects;

				# prepare avail data
				if ($is_group) { # {host,service}group
					foreach ($this->data_arr as $data) {
						if (empty($data))
							continue;
						array_multisort($data);
						$avail_data[] = $this->_get_multiple_state_info($data, $sub_type, $get_vars, $this->options['start_date'], $this->options['end_date'], $this->type);
					}
				} else { # custom group
					array_multisort($this->data_arr);
					$avail_data[] = $this->_get_multiple_state_info($this->data_arr, $sub_type, $get_vars, $this->options['start_date'], $this->options['end_date'], $this->type);
				}

				if (!empty($avail_data) && count($avail_data))
					for($i=0,$num_groups=count($avail_data)  ; $i<$num_groups ; $i++) {
						$this->_reorder_by_host_and_service($avail_data[$i], $this->options['report_type']);
					}

				$multiple_items = true;
				$avail_template->multiple_states = $avail_data;

				# hostgroups / servicegroups or >= 2 hosts or services
				$i=0;
				foreach ($this->data_arr as $key => $data) {
					# >= 2 hosts or services won't have the extra
					# depth in the array, so we break out early
					if (empty($data['log']) || !is_array($data['log'])) {
						$raw_trends_data = $this->data_arr['log'];
						break;
					}

					# $data is the outer array (with, source, log,
					# states etc)
					if (empty($raw_trends_data)) {
						$raw_trends_data = $data['log'];
					} else {
						$raw_trends_data = array_merge($data['log'], $raw_trends_data);
					}
				} # end foreach
			} else {
				$avail_data = $this_print_state_breakdowns($this->data_arr['source'], $this->data_arr['states'], $this->options['report_type']);
				$avail_template = $this->add_view('trends/avail');
				$avail_template->avail_data = $avail_data;
				$avail_template->source = $this->data_arr['source'];
				$avail_template->header_string = _("Service state breakdown");
				$trend_links = false;
				$notification_link = 'notifications/host/';

				$host_name = $avail_data['values']['HOST_NAME'];
				$avail_link = '/reports/generate?type=avail'.
				"&host_name[]=". $host_name .
				'&start_time=' . $this->options['start_date'] . '&end_time=' . $this->options['end_date'] .'&' . $get_vars;
				$avail_link_icon = 'availability';
				$notification_icon = 'notify';
				$status_icon = 'hoststatus';
				$histogram_icon = 'histogram';
				$alerthistory_icon = 'alert-history';

				if (isset($avail_data['values']['SERVICE_DESCRIPTION']) ) {
					$service_description = $avail_data['values']['SERVICE_DESCRIPTION'];
					$avail_link .= '&service_description[]=' . "$host_name;$service_description&report_type=services";
					$avail_link_name = _('Availability report for this service');

					$notification_link_name = _('Notifications for this service');
					$notification_link .= $host_name.'?service='.$service_description;

					$histogram_link_name = _('View alert histogram for this service');
					$histogram_link = 'histogram/generate?host='.$host_name.'?service='.$service_description;

					$trend_links[_('View trends for this host')] = array('trends/host/'.$host_name, 'trends');

					$alerthistory_link = 'showlog/alert_history/'.$host_name.';'.$service_description;
					$alerthistory_link_name = _('View alert history for this host');
				} else {
					$service_description = false;
					$avail_link_name = _('Availability report for this host');
					$avail_link .= "&report_type=hosts";

					$statuslink = 'status/service?name='.$host_name;
					$trend_links[_('Status detail for this host')] = array($statuslink, $status_icon);

					$notification_link_name = _('Notifications for this host');
					$notification_link .= $host_name;

					$histogram_link_name = _('View alert histogram for this host');
					$histogram_link = 'histogram/generate?host='.$host_name;

					$alerthistory_link = 'showlog/alert_history/'.$host_name;
					$alerthistory_link_name = _('View alert history for this host');
				}
				$trend_links[$avail_link_name] = array($avail_link, $avail_link_icon);
				$trend_links[$notification_link_name] = array($notification_link, $notification_icon);
				$trend_links[$histogram_link_name] = array($histogram_link, $histogram_icon);
				$trend_links[$alerthistory_link_name] = array($alerthistory_link, $alerthistory_icon);

				$avail_template->trend_links = $trend_links;
				$avail_template->state_values = $this->state_values;

				# hosts or services
				if (isset($this->data_arr['log'])) {
					$raw_trends_data = $this->data_arr['log'];
				}
			}
		}

		$container = array();
		if (is_array($raw_trends_data) && !empty($raw_trends_data)) {
			$container = $raw_trends_data;
		}

		unset($raw_trends_data);

		$this->template->content->content = $this->add_view('trends/new_report');
		$content = $this->template->content->content;
		$trends_graph_model = new Trends_graph_Model();
		$content->graph_image_source = $trends_graph_model->get_graph_src_for_data(
			$container,
			$this->options['start_time'],
			$this->options['end_time'],
			sprintf(
				_('State History for %s'.PHP_EOL.' (%s   to   %s)'),
				$this->options['report_type'],
				date(nagstat::date_format(), $this->options['start_time']),
				date(nagstat::date_format(), $this->options['end_time'])
			)
		);
		$content->avail_template = $avail_template;
		$content->date_format_str = nagstat::date_format();

		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
		$this->template->title = _('Reporting » Trends » Report');
	}

	/**
	*  	Since a lot of the help texts are identical to the ones
	* 	in teh reports controller we might as well return them to
	* 	save us the extra work.
	*/
	public static function _helptexts($id)
	{
		# filter
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
}
