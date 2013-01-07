<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controller to handle Recurring Downtime Schedules
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
class recurring_downtime_Controller extends Authenticated_Controller {

	public static $week = array('sun','mon','tue','wed','thu','fri','sat');
	private $abbr_month_names = false;
	private $month_names = false;
	private $day_names = false;
	private $abbr_day_names = false;
	private $downtime_commands = false;
	private $downtime_types = false;
	private $schedule_id = false;
	private $first_day_of_week = 1;

	public function __construct()
	{
		parent::__construct();
		if (PHP_SAPI != 'cli') {
			$auth = new Nagios_auth_Model();
			if (!$auth->view_hosts_root && Router::$method !== 'unauthorized') {
				url::redirect('recurring_downtime/unauthorized');
			}
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

		$this->downtime_types = array(
			'hosts' => $this->translate->_('Host'),
			'services' => $this->translate->_('Service'),
			'hostgroups' => $this->translate->_('Hostgroup'),
			'servicegroups' => $this->translate->_('Servicegroup'),
		);
	}



	/**
	*	Setup/Edit schedules
	*/
	public function index($id=false)
	{
		$this->template->disable_refresh = true;

		$this->template->title = $this->translate->_('Monitoring » Scheduled downtime » Recurring downtime');

		$this->template->content = $this->add_view('recurring_downtime/setup');
		$template = $this->template->content;

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min';

		$this->xtra_js[] = 'application/media/js/jquery.datePicker';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker';
		$this->xtra_js[] = $this->add_path('reports/js/move_options');
		$this->xtra_js[] = $this->add_path('reports/js/common');
		$this->xtra_js[] = $this->add_path('recurring_downtime/js/recurring_downtime');

		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox';
		$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->xtra_css[] = $this->add_path('css/default/reports');
		$this->template->css_header->css = $this->xtra_css;

		$date_format = cal::get_calendar_format(true);

		$this->schedule_id = arr::search($_REQUEST, 'schedule_id', $id);

		$schedule_info = false;
		$data = false;
		$current_dt_type = 'host'; # default
		if ($this->schedule_id) {
			# fetch info on current schedule
			$schedule_res = ScheduleDate_Model::get_schedule_data($this->schedule_id);
			if ($schedule_res !== false) {
				$row = $schedule_res->current();
				$schedule_info['id'] = $row->id;
				$schedule_info['author'] = $row->author;
				$schedule_info['downtime_type'] = $row->downtime_type;
				$schedule_info['last_update'] = date($date_format, $row->last_update);
				$data = i18n::unserialize($row->data);
				if (!isset($data['fixed'])) {
					$data['fixed'] = 1;
				}
				switch ($schedule_info['downtime_type']) {
					case 'hosts': case 'hostgroups':
						$current_dt_type = 'host';
						break;
					case 'services': case 'servicegroups':
						$current_dt_type = 'service';
						break;
				}

				$this->inline_js .= "set_initial_state('report_type', '".$data['report_type']."');\n";

				if (isset($data['fixed'])) {
					# show triggered dropdown if not fixed
					$this->inline_js .= "set_initial_state('triggered_row', '".$data['fixed']."');\n";
				}
				$this->inline_js .= "set_selection('".$data['report_type']."');\n";
				$schedule_info = array_merge($schedule_info, $data);
				$json_info = json::encode($schedule_info);
			}
		} else {
			$data['fixed'] = 1;
		}

		$saved_info = false;

		$downtime_types = array_keys($this->downtime_types);
		foreach ($downtime_types as $type) {
			$saved_schedules = ScheduleDate_Model::get_schedule_data(false, $type);
			if ($saved_schedules !== false) {
				foreach ($saved_schedules as $row) {
					$saved_data['id'] = $row->id;
					$saved_data['author'] = $row->author;
					$saved_data['downtime_type'] = $row->downtime_type;
					$saved_data['last_update'] = date($date_format, $row->last_update);
					$schedule_data = i18n::unserialize($row->data);
					unset($data['report_type']);
					$saved_data['data'] = $schedule_data;

					if (!isset($schedule_data['fixed'])) {
						$saved_data['data']['fixed'] = 1;
					}

					$saved_info[$type][] = $saved_data;
				}
			}
		}

		$t = $this->translate;
		$objfields = array(
			'hosts' => 'host_name',
			'hostgroups' => 'hostgroup',
			'servicegroups' => 'servicegroup',
			'services' => 'service_description'
		);

		$js_month_names = "Date.monthNames = ".json::encode($this->month_names).";";
		$js_abbr_month_names = 'Date.abbrMonthNames = '.json::encode($this->abbr_month_names).';';
		$js_day_names = 'Date.dayNames = '.json::encode($this->day_names).';';
		$js_abbr_day_names = 'Date.abbrDayNames = '.json::encode($this->abbr_day_names).';';
		$js_day_of_week = 'Date.firstDayOfWeek = '.$this->first_day_of_week.';';
		$js_date_format = "Date.format = '".cal::get_calendar_format()."';";
		$js_start_date = "_start_date = '".date($date_format, mktime(0,0,0,1, 1, 1996))."';";
		$this->inline_js .= "\n".$js_month_names."\n";
		$this->inline_js .= $js_abbr_month_names."\n";
		$this->inline_js .= $js_day_names."\n";
		$this->inline_js .= $js_abbr_day_names."\n";
		$this->inline_js .= $js_day_of_week."\n";
		$this->js_strings .= $js_date_format."\n";
		$this->inline_js .= $js_start_date."\n";
		$this->inline_js .= "$('#time_input, #duration').timePicker();\n";
		if ($this->schedule_id) {
			$this->inline_js .= "expand_and_populate(" . $json_info . ");\n";
		} else {
			$this->inline_js .= "set_selection(document.getElementsByName('report_type').item(0).value);\n";
		}
		$this->js_strings .= reports::js_strings();

		$this->js_strings .= "var _reports_err_str_noobjects = '".sprintf($t->_("Please select objects by moving them from %s the left selectbox to the right selectbox"), '<br />')."';\n";
		$this->js_strings .= "var _form_err_empty_fields = '".$t->_("Please Enter valid values in all required fields (marked by *) ")."';\n";
		$this->js_strings .= "var _form_err_bad_timeformat = '".$t->_("Please Enter a valid %s value (hh:mm)")."';\n";
		$this->js_strings .= "var _form_err_no_trigger_id = '".$t->_("Please select an object to trigger your flexible downtime by.")."';\n";
		$this->js_strings .= "var _schedule_error = '".$t->_("An error occurred when trying to delete this schedule")."';\n";

		$this->js_strings .= "var _schedule_delete_ok = '".$t->_("OK")."';\n";
		$this->js_strings .= "var _schedule_delete_success = '".$t->_("The schedule was successfully removed")."';\n";

		$this->js_strings .= "var _confirm_delete_schedule = '".$t->_('Are you sure that you would like to delete this schedule.\nPlease note that already scheuled downtime won\"t be affected by this and will have to be deleted manually.\nThis action can\"t be undone.')."';\n";
		$this->js_strings .= "var _form_field_time = '".$t->_("time")."';\n";
		$this->js_strings .= "var _form_field_duration = '".$t->_("duration")."';\n";

		$template->label_select = $t->_('Select');
		$template->label_hostgroups = $t->_('Hostgroups');
		$template->label_hosts = $t->_('Hosts');
		$template->label_servicegroups = $t->_('Servicegroups');
		$template->label_services = $t->_('Services');
		$template->label_available = $t->_('Available');
		$template->label_selected = $t->_('Selected');
		$template->label_add_schedule = $t->_('Add Schedule');
		$template->label_update_schedule = $t->_('Update Schedule');
		$template->label_comment = $t->_('Comment');
		$template->label_time = $t->_('Start Time');
		$template->label_fixed = $t->_('Fixed');
		$template->label_triggered_by = $t->_('Triggered By');
		$template->label_duration = $t->_('Duration');
		$template->label_days_of_week = $t->_('Days of week');
		$template->label_months = $t->_('Months');
		$template->day_names = $this->day_names;
		$template->day_index = array(1, 2, 3, 4, 5, 6, 0);
		$template->abbr_day_names = $this->abbr_day_names;
		$template->month_names = $this->month_names;
		$template->abbr_month_names = $this->abbr_month_names;
		$template->current_dt_type = $current_dt_type;

		$template->schedule_id = $this->schedule_id;
		$template->schedule_info = $schedule_info;
		$template->saved_info = $saved_info;
		$template->downtime_types = $this->downtime_types;
		$template->objfields = $objfields;
		$template->comment = isset($data) && $this->schedule_id ? $data['comment'] : '';
		$template->duration = isset($data) && $this->schedule_id ? $data['duration'] : '2:00';
		$template->fixed = isset($data) && $this->schedule_id && isset($data['fixed'])? (int)$data['fixed'] : 1;
		$template->triggered_by = isset($data) && $this->schedule_id && isset($data['triggered_by'])? $data['triggered_by'] : 0;
		$template->time = isset($data) && $this->schedule_id ? $data['time'] : '12:00';

		# fetch info on existing downtime to be used when using flexible downtime
		$command_model = new Command_Model();
		$host_downtime_ids = $command_model->get_command_info('SCHEDULE_HOST_DOWNTIME', array('SCHEDULE_HOST_DOWNTIME'));
		$svc_downtime_ids = $command_model->get_command_info('SCHEDULE_SVC_DOWNTIME', array('SCHEDULE_SVC_DOWNTIME'));
		$template->host_downtime_ids = $host_downtime_ids['params']['trigger_id']['options'];
		$template->svc_downtime_ids = $svc_downtime_ids['params']['trigger_id']['options'];
		$this->js_strings .= "var host_downtime_ids = " . json::encode($template->host_downtime_ids) . ";\n";
		$this->js_strings .= "var svc_downtime_ids = " . json::encode($template->svc_downtime_ids) . ";\n";

		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
	}


	/**
	*	Create the schedule
	*/
	public function generate()
	{
		$valid_fields = array(
			'report_type',
			'host_name',
			'hostgroup',
			'service_description',
			'servicegroup',
			'comment',
			'time',
			'duration',
			'fixed',
			'triggered_by',
			'recurring_day',
			'recurring_month'
		);

		$data = false;
		foreach ($valid_fields as $field) {
			$val = arr::search($_REQUEST, $field, null);
			if (is_null($val) && $field != 'fixed') {
				continue;
			}
			$data[$field] = arr::search($_REQUEST, $field);
		}

		$id = arr::search($_REQUEST, 'schedule_id');

		$ok = ScheduleDate_Model::edit_schedule($data, $id);

		url::redirect(Router::$controller);
	}

	/**
	 * Check if there's something new to schedule
	 *
	 * @param $id int = false
	 * @throws Exception
	 * @return void (redirection, die or nothing, pick your poision.....)
	 */
	public function check_schedules($id=false, $timestamp=false)
	{
		if (PHP_SAPI !== "cli") {
			url::redirect(Router::$controller);
		}

		$this->auto_render=false;

		// Check if a date was injected
		if (!$timestamp) {
			// No date was injected, $timestamp is current timestamp instead.
			$timestamp = time();
		}

		$scheduled = ScheduleDate_Model::schedule_downtime($id, $timestamp);
		if ($scheduled) {
			// asdfg
		}
	}

	/**
	*	Delete a schedule
	*/
	public function delete()
	{
		$this->auto_render=false;
		$this->schedule_id = $this->input->post('schedule_id', false);

		if (!$this->schedule_id) {
			echo 'ERROR';
			return false;
		}

		if (ScheduleDate_Model::delete_schedule($this->schedule_id) !== false) {
			echo "OK";
		} else {
			echo "Not authorized to delete schedule or it doesn't exist.";
		}
	}

	public function unauthorized()
	{
		$this->template->content = $this->add_view('unauthorized');
		$this->template->disable_refresh = true;

		$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to scheduled recurring downtimes');
		$this->template->content->error_description = $this->translate->_('If you believe this is an error, check the HTTP server authentication requirements for accessing this page and check the authorization options in your CGI configuration file.');
	}
}
