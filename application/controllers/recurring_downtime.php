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

	private $downtime_types = false;
	private $schedule_id = false;

	public function __construct()
	{
		parent::__construct();
		if (PHP_SAPI != 'cli') {
			if (!Auth::instance()->authorized_for('host_view_all') && Router::$method !== 'unauthorized') {
				return url::redirect('recurring_downtime/unauthorized');
			}
		}

		$this->downtime_types = array(
			'hosts' => _('Host'),
			'services' => _('Service'),
			'hostgroups' => _('Hostgroup'),
			'servicegroups' => _('Servicegroup'),
		);
	}


	/**
	*	Setup/Edit schedules
	*/
	public function index($id=false)
	{
		$this->template->disable_refresh = true;

		$this->template->title = _('Monitoring » Scheduled downtime » Recurring downtime');

		$this->template->content = $this->add_view('recurring_downtime/setup');
		$template = $this->template->content;

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';

		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('recurring_downtime/js/recurring_downtime.js');

		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox.css';
		$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->xtra_css[] = $this->add_path('css/default/reports.css');
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

		$objfields = array(
			'hosts' => 'host_name',
			'hostgroups' => 'hostgroup',
			'servicegroups' => 'servicegroup',
			'services' => 'service_description'
		);

		$this->inline_js .= "$('#time_input, #duration').timePicker();\n";
		if ($this->schedule_id) {
			$this->inline_js .= "expand_and_populate(" . $json_info . ");\n";
		} else {
			$this->inline_js .= "set_selection(document.getElementsByName('report_type').item(0).value);\n";
		}
		$this->js_strings .= reports::js_strings();

		$this->js_strings .= "var _reports_err_str_noobjects = '".sprintf(_("Please select objects by moving them from %s the left selectbox to the right selectbox"), '<br />')."';\n";
		$this->js_strings .= "var _form_err_empty_fields = '"._("Please Enter valid values in all required fields (marked by *) ")."';\n";
		$this->js_strings .= "var _form_err_bad_timeformat = '"._("Please Enter a valid %s value (hh:mm)")."';\n";
		$this->js_strings .= "var _form_err_no_trigger_id = '"._("Please select an object to trigger your flexible downtime by.")."';\n";
		$this->js_strings .= "var _schedule_error = '"._("An error occurred when trying to delete this schedule")."';\n";

		$this->js_strings .= "var _schedule_delete_ok = '"._("OK")."';\n";
		$this->js_strings .= "var _schedule_delete_success = '"._("The schedule was successfully removed")."';\n";

		$this->js_strings .= "var _confirm_delete_schedule = '"._('Are you sure that you would like to delete this schedule.\nPlease note that already scheuled downtime won\"t be affected by this and will have to be deleted manually.\nThis action can\"t be undone.')."';\n";
		$this->js_strings .= "var _form_field_time = '"._("time")."';\n";
		$this->js_strings .= "var _form_field_duration = '"._("duration")."';\n";

		$template->day_names = date::day_names();
		$template->day_index = array(1, 2, 3, 4, 5, 6, 0);
		$template->abbr_day_names = date::abbr_day_names();
		$template->month_names = date::month_names();
		$template->abbr_month_names = date::abbr_month_names();
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

		return url::redirect(Router::$controller);
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
			return url::redirect(Router::$controller);
		}

		$this->auto_render=false;

		// Check if a date was injected
		if (!$timestamp) {
			// No date was injected, $timestamp is current timestamp instead.
			$timestamp = time();
		}

		ScheduleDate_Model::schedule_downtime($id, $timestamp);
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

		$this->template->content->error_message = _('It appears as though you do not have permission to scheduled recurring downtimes');
		$this->template->content->error_description = _('If you believe this is an error, check the HTTP server authentication requirements for accessing this page and check the authorization options in your CGI configuration file.');
	}
}
