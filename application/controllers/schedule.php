<?php defined('SYSPATH') OR die('No direct access allowed.');

class Schedule_Controller extends Authenticated_Controller
{
	public function __construct() {
		parent::__construct();
		$this->template->disable_refresh = true;
	}
	/**
	 * List all scheduled reports
	 */
	public function show()
	{
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = $this->add_path('schedule/js/schedule.js');
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->template->js_header->js = $this->xtra_js;

		$this->template->content = $this->add_view('schedule/schedules');
		$available_schedules = $this->template->content;

		$new_schedule = $this->add_view('schedule/new_schedule');
		$new_schedule->available_schedule_periods = Scheduled_reports_Model::get_available_report_periods();

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
						$defined_report_types[$rpt_type->identifier] = _('Availability Report');
						break;
					case 'sla':
						$defined_report_types[$rpt_type->identifier] = _('SLA Report');
						break;
					case 'summary':
						$defined_report_types[$rpt_type->identifier] = _('Alert Summary Report');
						break;
				}
			}
		}

		$new_schedule->defined_report_types = $defined_report_types;
		$avail_reports = Saved_reports_Model::get_saved_reports('avail');
		$sla_reports = Saved_reports_Model::get_saved_reports('sla');
		$summary_reports = Saved_reports_Model::get_saved_reports('summary');


		# fetch ALL schedules (avail + SLA + Alert Summary)
		$avail_schedules = Scheduled_reports_Model::get_scheduled_reports('avail')->result_array(false);

		foreach ($avail_reports as $idx => $report) {
			foreach ($avail_schedules as $schedule) {
				if ($schedule['report_id'] == $report->id) {
					$avail_reports[$idx]->report_name .= ' ( *'._('Scheduled').'* )';
					continue 2;
				}
			}
		}
		$new_schedule->saved_reports = $avail_reports;

		# add new schedule template to available_schedules template
		$available_schedules->new_schedule = $new_schedule;

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
			$sla_reports_arr[$rep->id] = $rep->report_name;
		}

		$scheduled_label = _('Scheduled');
		$this->js_strings .= "var _report_types_json = ".json_encode($report_types).";\n";
		$this->js_strings .= "var _saved_avail_reports = ".json_encode($avail_reports_arr).";\n";
		$this->js_strings .= "var _saved_sla_reports = ".json_encode($sla_reports_arr).";\n";
		$this->js_strings .= "var _saved_summary_reports = ".json_encode($summary_reports_arr).";\n";
		$this->js_strings .= "var _scheduled_reports = ".json_encode(array('avail' => $avail_schedules, 'sla' => Scheduled_reports_Model::get_scheduled_reports('sla')->result_array(false), 'summary' => Scheduled_reports_Model::get_scheduled_reports('summary')->result_array(false))).";\n";
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
		$this->js_strings .= "var _reports_edit_information = '"._('Double click to edit')."';\n";
		$this->js_strings .= "var _scheduled_label = '".$scheduled_label."';\n";

		$this->js_strings .= reports::js_strings();

		$this->template->js_strings = $this->js_strings;

		$this->template->title = _('Reporting » Schedule');
	}

	/**
	 * Kills request with headers and content à la json
	 *
	 * @param $type string
	 */
	public function list_by_type($type)
	{
		$this->auto_render = false;
		$scheduled_reports = Saved_reports_Model::get_saved_reports($type);
		if(!$scheduled_reports) {
			return json::fail(_("No reports found for that type"));
		}
		return json::ok($scheduled_reports);
	}

	/**
	*	Schedule a report
	*/
	public function schedule()
	{
		$this->auto_render=false;
		// collect input values
		$report_id = arr::search($_REQUEST, 'report_id'); // scheduled ID
		$rep_type = arr::search($_REQUEST, 'type');
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
			return json::fail(sprintf(_("An error occurred when saving scheduled report (%s)"), $ok));
		}
		return json::ok(array('id' => $ok));
	}

	public function send_now($schedule_id) {
		$this->auto_render = false;
		$type = Scheduled_reports_Model::get_typeof_report($schedule_id);
		$opt_obj = Scheduled_reports_Model::get_scheduled_data($schedule_id);
		$opts = $opt_obj->as_keyval_string();
		$pipe_desc = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'));
		$pipes = false;
		$cmd = 'php '.DOCROOT.KOHANA.' '.escapeshellarg($type.'/generate?'.$opts).' '.escapeshellarg(Auth::instance()->get_user()->username);
		$process = proc_open($cmd, $pipe_desc, $pipes, DOCROOT);
		if (is_resource($process)) {
			fwrite($pipes[0], "\n");
			fclose($pipes[0]);
			$out = stream_get_contents($pipes[1]);
			$err = stream_get_contents($pipes[2]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			$code = proc_close($process);
		}
		else {
			$err = "Couldn't execute cmd";
			$code = -128;
		}
		$save = false;
		$mail = false;
		$months = date::abbr_month_names();
		$month = $months[date('m')-1]; // January is [0]
		$filename = preg_replace("~\.pdf$~", null, $opt_obj['filename'])."_".date("Y_").$month.date("_d").'.pdf';
		if ($code != 0) {
			if (request::is_ajax()) {
				return json::fail(sprintf(_("Failed to run %s: %s"), $cmd, $out));
			}
			else {
				Kohana::log('error', "Couldn't generate report for {$opt_obj['report_name']}: $out");
				return false;
			}
		}
		if ($opt_obj['local_persistent_filepath']) {
			persist_pdf::save($out, $opt_obj['local_persistent_filepath'].'/'.basename($opt_obj['filename']));
			$save = true;
		}
		if ($opt_obj['recipients']) {
			Send_report_Model::send($out, $filename, $opt_obj['output_format'], $opt_obj['recipients']);
			$mail = true;
		}
		if (request::is_ajax()) {
			if ($save && $mail)
				$msg = _('Report was saved and emailed');
			else if ($save)
				$msg = _('Report was saved');
			else if ($mail)
				$msg = _('Report was emailed');
			else
				$msg = _('Nothing to do');
			return json::ok($msg);
		} else {
			return true;
		}
	}

	/**
	 * Receive call from cron to check for scheduled reports
	 */
	public function cron($period_str=false)
	{
		if (PHP_SAPI !== "cli") {
			die("illegal call\n");
		}
		if (!$period_str)
			die("Missing period string");
		$this->auto_render=false;

		$res = Scheduled_reports_Model::get_period_schedules($period_str);
		if ($res === false) {
			return false;
		}

		$return = false;
		foreach ($res as $row) {
			$this->send_now($row->id);
		}
		return $return;
	}

	/**
	*	Save single item (key, value) from .editable
	*	fields regarding scheduled reports.
	*
	*	(that is, edit schedule)
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
			echo _("Required data is missing, unable to save changes");
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
				$new_value = trim($new_value);
				if(!empty($new_value) && !is_writable(rtrim($new_value, '/').'/')) {
					echo _("Can't write to '$new_value'. Provide another path.")."<br />";
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
							echo '<a title="'._('Fetch saved value').'" href="#" onclick="fetch_field_value(\''.$field.'\', '.$report_id.', \''.$_REQUEST['elementid'].'\');">';
							echo sprintf(_("'%s' is not a valid email address.%sClick here to restore saved value."), $recip, '<br />')."\n</a>";
							return;
						}
					}
				}
				break;
			case 'filename': // remove spaces
				if (strlen($new_value)>40) {
					echo sprintf(_('The entered value is too long. Only 40 chars allowed for filename.%sValue %s not %s modified!'), '<br />', '<strong>', '</strong>').'<br />' .
						_('Please').' <a title="'._('Fetch saved value').'" href="#" onclick="fetch_field_value(\''.$field.'\', '.$report_id.', \''.$_REQUEST['elementid'].'\');">'._('click here').'</a> '._('to view saved value').'.';
					exit;
				}
				$new_value = $this->_convert_special_chars($new_value);
				$new_value = $this->_check_filename($new_value);
				break;
		}

		$ok = Scheduled_reports_Model::update_report_field($report_id, $field, $new_value);

		if ($ok!==true) {
			echo _('An error occurred')."<br />";
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
					echo _("Unable to determine type for selected report");
					return;
				}
				$saved_reports = Saved_reports_Model::get_saved_reports($report_type);
				if (!count($saved_reports)) {
					echo _("Unable to fetch list of saved reports");
					return;
				}
				foreach ($saved_reports as $report) {
					if ($report->id == $new_value) {
						echo $report->report_name;
						return;
					}
				}
			case 'period_id':
				$periods = Scheduled_reports_Model::get_available_report_periods();
				echo is_array($periods) && array_key_exists($new_value, $periods)
					? $periods[$new_value]
					: '';
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
		$id = $this->input->post('id');
		if (Scheduled_reports_Model::delete_scheduled_report($id)) {
			return json::ok(_("Schedule deleted"));
		} else {
			return json::fail(_('An error occurred - unable to delete selected schedule'));
		}
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

	private function _convert_special_chars($str=false) {
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

	private function _check_filename($str=false)
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
}
