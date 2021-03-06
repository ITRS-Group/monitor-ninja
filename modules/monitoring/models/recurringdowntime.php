<?php

/**
 * Autogenerated class RecurringDowntime_Model
 *
 * @todo: documentation
 */
class RecurringDowntime_Model extends BaseRecurringDowntime_Model {
	public function get_weekdays()
	{
		$weekdays = parent::get_weekdays();
		if (is_string($weekdays))
			$weekdays = unserialize($weekdays);
		if (!$weekdays)
			$weekdays = array();
		return $weekdays;
	}

	public function get_months()
	{
		$months = parent::get_months();
		if (is_string($months))
			$months = unserialize($months);
		if (!$months)
			$months = array();
		return $months;
	}

	/**
	 * Get the start time ,end time or duration, but format it the way times are usually
	 *
	 */
	public function get_time_string($time, $duration){
		if($duration){
			$data['day'] = (int)floor($time/(3600*24));
			$data['hours'] = (int)floor($time%(3600*24) / 3600);
			$data['minutes'] = (int)floor(($time%3600)/60);
			$data['seconds'] = (int)($time%60);
		}else{
			$data['hours'] = (int)floor($time / 3600);
			$data['minutes'] = (int)floor(($time%3600)/60);
			$data['seconds'] = (int)($time%60);
		}
		return $data;
	}

	/**
	 * Get the start time, but format it the way times are usually
	 * formatted: hh:mm:ss
	 *
	 * @ninja orm depend[] start_time
	 */
	public function get_start_time_string()
	{
		$start_time = $this->get_start_time();
		$data = $this->get_time_string($start_time,false);
		return sprintf("%02d:%02d", $data['hours'], $data['minutes']);
	}

	/**
	 * Get the end time, but format it the way times are usually
	 * formatted: hh:mm:ss
	 *
	 * @ninja orm depend[] end_time
	 */
	public function get_end_time_string()
	{
		$end_time = $this->get_end_time();
		$data = $this->get_time_string($end_time,false);
		return sprintf("%02d:%02d", $data['hours'], $data['minutes']);
	}

	/**
	 * Get the duration, but format it the way times are usually
	 * formatted: hh:mm:ss, or an empty string if the downtime is 'fixed'.
	 *
	 * @ninja orm depend[] duration
	 * @ninja orm depend[] fixed
	 */
	public function get_duration_string()
	{
		if($this->get_fixed()) {
			return "";
		}
		$duration = $this->get_duration();
		$data = $this->get_time_string($duration,true);
		return sprintf("%dd %dh %dm", $data['day'], $data['hours'], $data['minutes']);
	}

	/**
	 * Get the duration
	 * Not formated return array
	 *
	 * @ninja orm depend[] duration
	 * @ninja orm depend[] fixed
	 */
	public function get_duration_arr()
	{
		if($this->get_fixed()) {
			return "";
		}
		$duration = $this->get_duration();
		$data = $this->get_time_string($duration,true);
		return $data;
	}

	/**
	 * Get all objects in this schedule as a list
	 *
	 * @ninja orm depend[] id
	 */
	public function get_objects()
	{
		$ret = array();
		$id = $this->get_id();
		if ($id) {
			$db = Database::instance();
			$res = $db->query('SELECT object_name from recurring_downtime_objects WHERE recurring_downtime_id = '.$id);
			foreach ($res->result(false) as $row) {
				$ret[] = $row['object_name'];
			}
		}
		return $ret;
	}


	/**
	* Adding the suffix to date/day
	*
	*/
	public function format_date($date){
		$suffix = '';
		switch($date) {
			case 1: case 21: case 31: $suffix = 'st'; break;
			case 2: case 22: $suffix = 'nd'; break;
			case 3: case 23: $suffix = 'rd'; break;
			default: $suffix = 'th';
		}
		return $date.''.$suffix;
	}
	
	/**
	* Get recurrence and recurrence on in text format
	*
	* @ninja orm depend[] id
	*/
	public function get_recurrence_text()
	{
	$ret = '';
		$id = $this->get_id();
		if ($id) {
			$db = Database::instance();
			$res = $db->query('SELECT recurrence, recurrence_on, recurrence_ends from recurring_downtime WHERE id = '.$id);
			$valid_weekdays = array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
			$valid_months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
			foreach ($res->result(false) as $row) {
				$rec = json_decode($row['recurrence']);
				$rec_on = json_decode($row['recurrence_on']);
				$rec_ends = $row['recurrence_ends'];
				$end_text = '';
				if($rec_ends != 0){
					$end_text = ' until '.$rec_ends;
				}
				if($rec != ''){
					if($rec->label == 'no'){
						return "No recurrence";
					}
					if($rec->text == 'day'){
						if($rec->no == 1){
							$repeat_text = "daily";
						}else{
							$repeat_text = "every ".$rec->no." days";
						}
						$ret = 'Repeat '.$repeat_text.''.$end_text;
					}elseif($rec->text == 'week'){
						$all_days = '';
						$i = 0; 
						foreach($rec_on as $key => $value){
							/**
							 * recurrence_on format
							 * ^^^^^^^^^^^^^^^^^^^^
							 *
							 * The `recurrence_on` field can be either an object or an array of objects.
							 * The block below ensures compatibility with both formats.
							 */

							if(is_object($value)) {
								if(!property_exists($value, 'day')) {
									// Silently skip non-day recurrences for week
									continue;
								}
								$day_number = $value->day;
							} elseif(array_key_exists($value, $valid_weekdays)) {
								$day_number = $value;
							} else {
								throw new UnexpectedValueException(sprintf(
									"Unable to map value %s to a name", $value
								));
							}

							$day_name = ucfirst($valid_weekdays[$day_number]);

							if($i == 0){
								$all_days .= $day_name;
							}else{
								$next_i = $i+1;
								if(array_key_exists($next_i,$rec_on)){
									$all_days .= ', ';
									$all_days .= $day_name;
								}else{
									$all_days .= ' and ';
									$all_days .= $day_name;
								}
							}
							$i = $i+1;
						}
						if($rec->no == 1){
							$repeat_text = "weekly on ".$all_days;
						}else{
							$repeat_text =  "every ".$rec->no." week on ".$all_days;
						}
						$ret = 'Repeat '.$repeat_text.''.$end_text;
					}elseif($rec->text == 'month'){

						if($rec_on->day_no == "last" && $rec_on->day == "last"){
							if($rec->no == 1){
								$repeat_text = "monthly on the last day";
							}else{
								$repeat_text = "every ".$rec_no." months on the last day";
							}
						}else{
							$day_name = ucfirst($valid_weekdays[$rec_on->day]);
							if($rec->no == 1){
								$repeat_text = "monthly on the ".$this->format_date($rec_on->day_no)." ".$day_name;
							}else{
								$repeat_text = "every ".$rec->no." months on the ".$this->format_date($rec_on->day_no).' '.$day_name;
							}
						}
						$ret = 'Repeat '.$repeat_text.''.$end_text;
					}elseif($rec->text == 'year'){
						$day_name = ucfirst($valid_weekdays[$rec_on->day]);
						$month_name = ucfirst($valid_months[($rec_on->month)]);
						if($rec_on->day_no == "last"){
							$day_no="last";
						}else{
							$day_no=$this->format_date($rec_on->day_no);
						}
						if($rec->no == 1){
							$repeat_text = "yearly on the ".$day_no. ' '.$day_name.' of '.$month_name;
						}else{
							$repeat_text = "every ".$rec->no." years on the ".$day_no.' '.$day_name.' of '.$month_name;
						}
						$ret = 'Repeat '.$repeat_text.''.$end_text;
					}
				}
			}
		}
		return $ret;
	}
}
