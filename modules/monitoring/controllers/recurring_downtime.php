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
	/**
	 * Setup/Edit schedules
	 */
	public function index($id = false) 
	{
		$recurring_downtime_error = "";
		if (!empty($_POST)) {
			$data = array();
			$missing = array();
			foreach (ScheduleDate_Model::$valid_fields as $field) {
				if (!isset($_REQUEST[$field])) {
					if ($field === 'fixed') {
						$data[$field] = true;
					}
					else if ($field === 'author') {
						$data[$field] = Auth::instance()->get_user()->get_username();
					}
					else if ($field === 'recurrence'){
						$recurrence = $_REQUEST['recurrence_select'];
						if($recurrence == 'no'){
							$data[$field] = 0;
						}
						else if ($recurrence == 'custom'){
							$no = $_REQUEST['recurrence_no'];
							$text = $_REQUEST['recurrence_text'];
							$data[$field]['label'] = "custom";
                            $data[$field]['no'] = $no;
                            $data[$field]['text'] = $text;
							if($text == 'week'){
								$data['recurrence_on'] = json_decode($_REQUEST['week_on']);
							}else if($text == 'month'){
								$data['recurrence_on'] = json_decode($_REQUEST['month_on']);
							}else if($text == 'year'){
								$data['recurrence_on'] = json_decode($_REQUEST['year_on']);
							}
							if($_REQUEST['ends'] == 'finite_ends'){
								$data['recurrence_ends'] = $_REQUEST['finite_ends_value'];	
							}
						}else{
							$recurrence_quick = json_decode($recurrence); 
							$data[$field] = $recurrence_quick->recur;
							$data['recurrence_on'] = $recurrence_quick->on;
						}
						$data[$field] = json_encode($data[$field]);
					} 
					else if($field == 'recurrence_on' or $field == 'recurrence_ends'){
						if(!isset($data['recurrence_on'])){
							$data['recurrence_on'] = 0;
						}
						if(!isset($data['recurrence_ends'])){
							$data['recurrence_ends'] = 0;
						}
						if($field == 'recurrence_on'){
							$data[$field] = json_encode($data[$field]);
						}
					}
					else {
						$missing[] = $field;
					}
				}
				else {
					$data[$field] = $_REQUEST[$field];
				}
			}
			if ($missing) {
				$recurring_downtime_error = 'Missing required fields: ' . implode(', ', $missing);
			} else {

				$data['duration'] = (($data['duration'][0]*24) + $data['duration'][1]).":".$data['duration'][2];
				$id = arr::search($_REQUEST, 'schedule_id');
				$sd = new ScheduleDate_Model();
				try {
					$sd->edit_schedule($data, $id);
					return url::redirect(LinkProvider::factory()->get_url('listview', null, array('q' => '[recurring_downtimes] all')));
				} catch(Exception $e) {
					$recurring_downtime_error = $e->getMessage();
				}
			}
		}
		$this->template->disable_refresh = true;
		$this->template->title = _('Monitoring » Scheduled downtime » Recurring downtime');
		$this->template->content = $this->add_view('recurring_downtime/setup');
		$template = $this->template->content;
		$this->template->css[] = $this->add_path('reports/css/datePicker.css');
		$this->template->css[] = 'application/media/css/jquery.filterable.css';
		$date_format = cal::get_calendar_format(true);
		$schedule_id = arr::search($_REQUEST, 'schedule_id', $id);
		$this->template->content->error = $recurring_downtime_error;
		$this->template->toolbar = new Toolbar_Controller('Recurring scheduled downtimes');
		$this->template->toolbar->button('<span class="icon-16 x16-schedulereports"></span>' . _('Schedules'), array('href' => url::base(true) . 'listview?q=[recurring_downtimes]%20all'));
		$data = false;
		$schedule_info = array(
			'start_time' => 12 * 3600,
			'end_time' => 14 * 3600,
			'duration' => 2 * 3600,
			'fixed' => true,
			'comment' => '',
		);
		if (!empty($_POST)) {
			$schedule_info = array_merge($schedule_info, $_POST);
			$schedule_info = new RecurringDowntime_Model($schedule_info, '', false);
		}
		else if ($schedule_id) {
			$set = RecurringDowntimePool_Model::get_by_query('[recurring_downtimes] id = ' . $schedule_id);
			$schedule_info = $set->it(array('id', 'downtime_type', 'objects', 'start_time', 'end_time', 'duration', 'fixed', 'weekdays', 'months', 'comment', 'start_date', 'end_date', 'recurrence', 'recurrence_on', 'recurrence_ends'))->current();
		} else {
			$schedule_info = new RecurringDowntime_Model($schedule_info, '', false);
		}

		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _reports_err_str_noobjects = '"._("Please select objects from the left selectbox")."';\n";
		$this->js_strings .= "var _form_err_empty_fields = '"._("Please enter valid values in all required fields (marked by *) ")."';\n";
		$this->js_strings .= "var _form_err_bad_timeformat = '"._("Please enter a valid {field} value (hh:mm[:ss])")."';\n";
		$this->js_strings .= "var _schedule_error = '"._("An error occurred when trying to delete this schedule")."';\n";
		$this->js_strings .= "var _schedule_delete_ok = '"._("OK")."';\n";
		$this->js_strings .= "var _schedule_delete_success = '"._("The schedule was successfully removed")."';\n";
		$this->js_strings .= "var _form_field_start_time = '"._("Start Time")."';\n";
		$this->js_strings .= "var _form_field_end_time = '"._("End Time")."';\n";
		$this->js_strings .= "var _form_field_duration = '"._("duration")."';\n";
		$this->js_strings .= "var _duration = '".json_encode($schedule_info->get_duration_arr())."';\n";
		$this->js_strings .= "var _fixed = '".$schedule_info->get_fixed()."';\n";
		$this->js_strings .= "var _start_time = '".$schedule_info->get_start_time_string()."';\n";
		$this->js_strings .= "var _end_time = '".$schedule_info->get_end_time_string()."';\n";
		$this->js_strings .= "var _start_date = '".$schedule_info->get_start_date()."';\n";
		$this->js_strings .= "var _end_date = '".$schedule_info->get_end_date()."';\n";
		$this->js_strings .= "var _recurrence = '".$schedule_info->get_recurrence()."';\n";
		$this->js_strings .= "var _recurrence_on = '".$schedule_info->get_recurrence_on()."';\n";
		$this->js_strings .= "var _recurrence_ends = '".$schedule_info->get_recurrence_ends()."';\n";
		$template->day_names = date::day_names();
		$template->day_index = array(1, 2, 3, 4, 5, 6, 0);
		$template->month_names = date::month_names();
		$template->schedule_id = $schedule_id;
		$template->schedule_info = $schedule_info;
		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
	}

	/**
	 * Insert a downtime
	 *
	 * @return string
	 **/
	function insert_downtimes()
	{
		$this->auto_render = false;
		$objects = $this->input->post('objects', false);
		$object_type = $this->input->post('object_type', false);
		$start_time = $this->input->post('start_time', false);
		$end_time = $this->input->post('end_time', false);
		$start_date = $this->input->post('start_date', false);
		$end_date = $this->input->post('end_date', false);
		$fixed = $this->input->post('fixed', false);
		$duration = $this->input->post('duration', false);
		$comment = $this->input->post('comment', false);
		if (!$duration) {
			$duration = '0';
		} else {
			$duration = explode(':', $duration);
			if (count($duration) === 3) {
				list($hours, $minutes, $seconds) = $duration;
			} elseif (count($duration) === 2) {
				list($hours, $minutes) = $duration;
				$seconds = 0;
			} else {
				return json::fail("Failed to schedule downtime, duration expects format HH:MM:SS (01:30:00) or HH:MM (01:30)");
			}
			$duration = 0;
			$duration += intval(ltrim($hours, '0')) * 3600;
			$duration += intval(ltrim($minutes, '0')) * 60;
			$duration += intval(ltrim($seconds, '0'));
			$duration = round($duration);
		}
		if (ScheduleDate_Model::insert_downtimes($objects, $object_type, $start_time, $end_time, $start_date, $end_date, $fixed, $duration, $comment) !== false) {
			return json::ok("Downtime successfully scheduled");
		} else {
			return json::fail("Failed to schedule downtime");
		}
	}
	
	/**
	 * Delete a schedule
	 */
	public function delete()
	{
		$this->auto_render = false;
		$schedule_id = $this->input->post('schedule_id', false);
		if (!$schedule_id) {
			return json::fail('Error: no schedule id provided');
		}
		if (ScheduleDate_Model::delete_schedule($schedule_id) !== false) {
			return json::ok("Schedule deleted");
		} else {
			return json::fail("Not authorized to delete schedule or it doesn't exist.");
		}
	}
}