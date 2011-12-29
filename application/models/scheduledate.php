<?php defined('SYSPATH') OR die('No direct access allowed.');

/*
 * @$Header: /var/cvsroot/scheduledate/class.scheduledate.php,v 1.7 2010/04/07 07:58:03 cvs Exp $
 */
/*
   A simple class to schedule a command by date

   Copyright (C) 2009-2010 Giuseppe Lucarelli <giu.lucarelli@gmail.com>
   Copyright (C) 2010- op5 AB

   This program is free software; you can redistribute it and/or modify
   it under the terms of version 2 of the GNU General Public License as
   published by the Free Software Foundation.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * @author Giuseppe Lucarelli <giu.lucarelli@gmail.com>
 * @see http://www.phpclasses.org
 */
class ScheduleDate_Model extends Model
{
	private $week = array('sun','mon','tue','wed','thu','fri','sat');
	private $dateArray = array('year'=>0,'month'=>0,'day'=>0,'hour'=>0,'minute'=>0);
	public $date = null; /**< A ISO-8601 date and time representation (with space, not T, that is) of the date */
	public $time = null; /**< A UNIX timestamp representation of of the date */
	private $firstRun = null;
	private $lastRun = null;
	private $pattern = null;
	private $dayPattern = null;
	private $matches = null;
	private $now = null;

	/**
	 * Given something that looks like a date, load up the internal dateArray
	 *
	 * @param $date A date in a format parsable by strtotime
	 */
	public function BuildDateArray($date) {
		$t = explode(',',strftime("%Y,%m,%d,%H,%M", strtotime($date)));
		$this->dateArray['year'] = $t[0];
		$this->dateArray['month'] = $t[1];
		$this->dateArray['day'] = $t[2];
		$this->dateArray['hour'] = $t[3];
		$this->dateArray['minute'] = $t[4];
	}

	/**
	 * Generate useful formats from the internal dateArray
	 * "Useful" is defined as "both unix timestamp and iso-8601"
	 *
	 * @returns The unix timestamp represented by the dateArray
	 */
	public function BuildDate() {
		$this->date = $this->dateArray['year'].'-'.$this->dateArray['month'].'-'.$this->dateArray['day'].' '.
			$this->dateArray['hour'].':'.$this->dateArray['minute'];
		$this->time = strtotime($this->date);
		return $this->time;
	}

	/**
	 * Given a string (confusingly called pattern), find date representations therein
	 * @param $pattern The subject to search
	 * @param $matches A somewhat rewritten version of the match of a gigantic complicated regex
	 * @return true if there was a match, false otherwise
	 */
	public function Parse(& $pattern, & $matches) {
		preg_match("/([\*0-9]{1,4})(|[\-,].*)[[:blank:]\/\-]+([\*0-9]{1,2})(|[\-,].*)[[:blank:]\/\-]([\*0-9a-z]{1,3}|)(|[\*\-,].*)[[:blank:]\/]([\*0-9]{1,2})(|[\-,].*)[[:blank:]:\-]([\*0-9]{1,2})(|[\-,].*)[[:blank:]:\-\r\n\t$]/i", $pattern."\n", $matches, PREG_OFFSET_CAPTURE);
		if(!@$matches || !@$matches[0][0]) {
			return false;
		}
		$pattern = $matches[0][0];
		for($i = 2; $i <= 10; $i+=2) {
			if(strlen($matches[$i][0]) > 0) {
				if($matches[$i][0][0] == '-' || $matches[$i][0][0] == ',') {
					$matches[$i][0] = $matches[$i-1][0].$matches[$i][0];
				}
			} else if(!strcmp($matches[$i-1][0],'*')) {
				$matches[$i][0] = $matches[$i-1][0];
			}
		}
		if(preg_match("/[a-z]/i",$matches[5][0])) {
			if(strlen($matches[6][0]) == 0) {
				$matches[6][0] = $matches[5][0];
			}
			$matches[5][0] = 5;
		} else if(preg_match("/[a-z]/i",$matches[6][0])) {
			if(strlen($matches[5][0]) <= 0) {
				$matches[5][0] = 5;   // if max week days is not specified, set it to max (maybe 6?)
			}
		}
		return true;
	}

	/**
	 * FIXME: really, no idea
	 */
	public function Explode(& $pattern, $rif, & $field, $needle = ',') {
		$retval = false;

		$list = explode($needle,$pattern);
		$field = false;
		$first = false;
		for($i=0; $i < sizeof($list); $i++) {
			if($pos=strpos($list[$i],'-')) {
				$from=substr($list[$i],0,$pos);                     // $from=trim(substr($list[$i],0,$pos),"()");
				if($first === false) $first = $from;
				$to=substr($list[$i],$pos+1);                       // $to=trim(substr($list[$i],$pos+1),"()");
				if($rif <= $from) {
					$field = $from;
					$retval = true;
					break;
				} else if($rif <= $to) {
					$field = $rif;
					$retval = true;
					break;
				}
			} else if($rif <= $list[$i]) {                          // } else if($rif <= trim($list[$i],"()")) {
				$field = $list[$i];                                 //     $field = trim($list[$i],"()");
			$retval = true;
			break;
		}
		if($first === false) $first = $list[$i];                // if($first === false) $first = trim($list[$i],"()");
		}
		// if no matches, start from first value
		if($field === false)
			$field = $first;
		//echo "Exiting [$field]...................\n";
		return $retval;
	}

	/**
	 * FIXME: no idea
	 */
	public function GetFirstWeekDay($day) {
		$retval = false;
		$daycounter = array();

		for($i=1; $i <= 31; $i++) {
			$this->dateArray['day'] = $i;
			$d=date("w",strtotime(
						$this->dateArray['year'].'-'.$this->dateArray['month'].'-'.$i.' '.
						$this->dateArray['hour'].':'.$this->dateArray['minute']));
			if(!preg_match("/\b".$d."\b/",$this->dayPattern)) {
				continue;
			}
			if(!@$daycounter[$d]) {
				$daycounter[$d] = 1;
			} else {
				$daycounter[$d]++;
			}
			if($daycounter[$d] > $this->matches[5][0]) {
				break;
			}
			if($i >= $day) {
				$retval = $i;
				break;
			}
		}
		return $retval;
	}

	/**
	 * FIXME: no idea
	 */
	public function GetLastWeekDay($year,$month,$day,$hour,$minute) {
		$retval = strtotime($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':00');
		$daycounter = array();

		for($i=31; $i >= 28; $i--) {
			if(checkdate(trim($month,' *,-'),$i,trim($year,' *,-'))) {
				break;
			}
		}
		if($i <= $day) {
			$retval=strtotime("$year-$month-$i $hour:$minute:00");
		}
		if($this->dayPattern === null) {
			return $retval;
		}
		$day = $i;    // max month's day
		for($i=1; $i <= $day; $i++) {
			$d=date("w",strtotime("$year-$month-$i $hour:$minute:00"));
			if(preg_match("/\b".$d."\b/",$this->dayPattern)) {
				if(!@$daycounter[$d]) {
					$daycounter[$d] = 1;
				} else {
					$daycounter[$d]++;
				}
				if($daycounter[$d] > $this->matches[5][0]) {
					continue;
				}
				$retval=strtotime("$year-$month-$i $hour:$minute:00");
			}
		}
		return $retval;
	}

	/**
	 * FIXME: no idea
	 */
	public function GetLastToken($field, $pattern, $max) {
		if(preg_match("/[a-z]/i",$pattern)) {
			$this->TransformWeek($pattern);
			$pattern = $this->dayPattern;
		}
		if(!strcmp($field,'*')) {
			$pattern = $max;
		}
		//if($pattern && !strcmp($pattern,'*')) {
		//$pattern = $max;
		//} else {
		//$pattern = $field.$pattern;
		//}
		$token = preg_split('/[,-]/',(strlen($pattern) > 0 ? $pattern : $field));
		for($i=sizeof($token)-1; $i >= 0; $i--) {
			if(strlen($token[$i]) > 0) {
				return $token[$i];
			}
		}
	}

	/**
	 * FIXME: no idea
	 */
	public function TransformWeek($pattern) {
		if($this->dayPattern !== null)
			return;
		$token = trim($pattern,'*');
		for($i=0; $i < sizeof($this->week); $i++) {
			$token = str_replace($this->week[$i],$i,$token);
		}
		$token = preg_split('/([,-])/',$token,-1,PREG_SPLIT_DELIM_CAPTURE);
		for($i=0; $i < sizeof($token); $i++) {
			if(strcmp($token[$i],'-')) {
				$this->dayPattern .= $token[$i];
				continue;
			}
			for($x=$token[$i-1]+1; $x < $token[$i+1]; $x++) {
				$this->dayPattern .= ','.$x;
			}
			$this->dayPattern .= ',';
		}
	}

	/**
	 * FIXME: no idea
	 */
	public function CheckPattern (& $pattern, $rif, & $field, $debug = false) {
		if($debug) {
			echo "\n";
			var_dump($pattern,$rif,$field);
		}
		if(!strcmp($pattern,'*')) {
			$field = $rif;
		} else if(strlen($pattern) == 0) {
			return false;
		} else {
			if($this->Explode($pattern,$rif,$field,',') === false) {
				return false;
			}
		}
		return true;
	}

	/// minute
	public function BuildMinute() {
		if($this->BuildDate() < $this->now) {
			$d=date("i",strtotime($this->date)+60);
			$this->CheckPattern($this->matches[10][0], $d, $this->dateArray['minute']);
			//echo " < min(".date("Y-m-d H:i:s",$this->BuildDate())."); ";
		}
	}
	/// hour
	public function BuildHour() {
		if($this->BuildDate() < $this->now) {
			$d=date("H",strtotime($this->date)+60*60);
			$this->CheckPattern($this->matches[8][0], $d, $this->dateArray['hour']);
			//echo " < hour(".date("Y-m-d H:i:s",$this->BuildDate())."); ";
		}
	}
	/// day
	public function BuildDay() {
		if($this->BuildDate() < $this->now) {
			if(!preg_match("/[a-z]/i",$this->matches[6][0])) {
				$d=date("d",strtotime($this->date)+24*60*60);
				$this->CheckPattern($this->matches[6][0], $d, $this->dateArray['day']);
				if(checkdate(trim($this->dateArray['month'],' *,-'),
							trim($this->dateArray['day'],' *,-'),
							trim($this->dateArray['year'],' *,-')) != true) {
					$this->dateArray['day'] = $d - 1;
				}
			} else {
				$this->TransformWeek($this->matches[6][0]);
				$d=date("w",strtotime($this->date)) + 1;
				if($d > 6)
					$d = 0;
				$this->CheckPattern($this->dayPattern, $d, $this->dateArray['day']);
				$this->dateArray['day'] = $this->dateArray['day'] - ($d - 1);
				if($this->dateArray['day'] <= 0) {
					$this->dateArray['day'] = 7 - ($this->dateArray['day'] * -1);
				}
				$newtime = strtotime($this->date)+$this->dateArray['day']*24*60*60;
				$this->dateArray['day']=date("d",$newtime);
				// check if new week day doesn't exceed limit
				if(($this->dateArray['day']=$this->GetFirstWeekDay($this->dateArray['day'])) === false) {
					$this->dateArray['day'] = 1;
				} else {
					// if month changes, day is invalid so reset day to '1' and check for first week day after 'year' building
					if(date('m',$newtime) != $this->dateArray['month']) {
						$this->dateArray['day'] = 1;
					}
				}
			}
			//echo " < day(".date("Y-m-d H:i:s",$this->BuildDate())."); ";
		}
	}
	/// month
	public function BuildMonth() {
		if($this->BuildDate() < $this->now) {
			$d=date('m',$this->now)+1;
			if($d > 12) $d = 1;
			$this->CheckPattern($this->matches[4][0], $d, $this->dateArray['month']);
			if(checkdate(trim($this->dateArray['month'],' *,-'),
						trim($this->dateArray['day'],' *,-'),
						trim($this->dateArray['year'],' *,-')) != true) {
				$this->dateArray['month'] = '1';
			}
			//echo " < mon(".date("Y-m-d H:i:s",$this->BuildDate())."); ";
		}
	}
	/// year
	public function BuildYear() {
		if($this->BuildDate() < $this->now) {
			$d = date('Y',$this->now)+1;
			if($this->CheckPattern($this->matches[2][0], $d, $this->dateArray['year']) === false) {
				$this->date = false;
			}
			//echo " < year(".date("Y-m-d H:i:s",$this->BuildDate())."); ";
		}
	}

	/**
	 * FIXME: no idea
	 */
	public function GetFirstRun($pattern,$now) {
		$this->pattern = $pattern;
		if(!$this->matches) {
			$this->Parse($pattern,$this->matches);
		}
		if(@$this->CheckPattern($this->matches[2][0], date('Y',$now), $year) !== true) {
			$year = ($this->matches[1][0] ? $this->matches[1][0] : date('Y',$now));
		}
		if($this->CheckPattern($this->matches[4][0], 1, $month) !== true) {
			$month = ($this->matches[3][0] ? $this->matches[3][0] : 1);
		}
		if(!preg_match("/[a-z]/i",$this->matches[6][0])) {
			if($this->CheckPattern($this->matches[6][0], 1, $day) !== true) {
				$day = ($this->matches[5][0] ? $this->matches[5][0] : 1);
			}
		} else {
			$this->dateArray['year'] = $year;
			$this->dateArray['month'] = $month;
			$this->TransformWeek($this->matches[6][0]);
			$day=$this->GetFirstWeekDay(1);
		}
		if($this->CheckPattern($this->matches[8][0], 0, $hour) !== true) {
			$hour = ($this->matches[7][0] ? $this->matches[7][0] : 0);
		}
		if($this->CheckPattern($this->matches[10][0], 0, $minute) !== true) {
			$minute = ($this->matches[9][0] ? $this->matches[9][0] : 0);
		}
		$this->firstRun = strtotime($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':00');
		return $this->firstRun;
	}

	/**
	 * FIXME: no idea
	 */
	public function GetLastRun() {
		return $this->GetLastWeekDay(
				$this->GetLastToken($this->matches[1][0], $this->matches[2][0], 2019),
				$this->GetLastToken($this->matches[3][0], $this->matches[4][0], 12),
				$this->GetLastToken($this->matches[5][0], $this->matches[6][0], 31),
				$this->GetLastToken($this->matches[7][0], $this->matches[8][0], 23),
				$this->GetLastToken($this->matches[9][0], $this->matches[10][0], 59));
	}

	/**
	 * FIXME: no idea
	 */
	public function Renew($pattern, $date, $timecheck) {
		$retval = false;
		$loop = 365;

		$this->pattern = $pattern;
		//$this->now = $now;
		if(!$this->matches) {
			$this->Parse($this->pattern,$this->matches);
		}
		$this->date = $date;
		$this->BuildDateArray($this->date);
		$this->BuildDate();
		if($this->lastRun === null) {
			$this->lastRun = $this->GetLastRun();
		}
		if($this->lastRun <= $timecheck) {
			$this->date = false;
			$retval = false;
			return $retval;
		}
		while(1) {
			$this->now = strtotime($this->date) + 1; // sum 1 so now is greater then original date
			$this->BuildMinute();
			$this->BuildHour();
			$this->BuildDay();
			$this->BuildMonth();
			$this->BuildYear();
			// check for week day
			if(preg_match("/[a-z]/i",$this->matches[6][0]) && $this->dateArray['day'] == 1) {
				$this->dateArray['day'] = $this->GetFirstWeekDay($this->dateArray['day']);
				$this->BuildDate();
			}
			if($loop-- <= 0) {
				echo nl2br("\nOops! there is an internal error\n\n");
				$this->date = false;
				$retval = false;
				break;
			}
			if(date('Y',$this->time) == 1970) {
				echo "TIME ERROR ".date("Y-m-d D H:i:s",$this->time);
				break;
			}
			if($this->time > $timecheck) {
				$retval = true;
				break;
			}
		}
		if($retval !== false) {
			$retval = $this->time;
		}
		return $retval;
	}

	/**
	 *	Save/update a recurring schedule
	 * 	$data should be an array
	 */
	public function edit_schedule($data = false, $id=false)
	{
		if (!is_array($data)) {
			return false;
		}

		$db = Database::instance();

		$downtime_type = $data['report_type'];
		$data = serialize($data);

		if ((int)$id) {
			# update schedule
			$sql = "UPDATE recurring_downtime SET author = ".$db->escape(Auth::instance()->get_user()->username).
				", data = ".$db->escape($data).", downtime_type=".$db->escape($downtime_type).", last_update=".time().
				" WHERE id = ".(int)$id;
		} else {
			# new schedule
			$sql = "INSERT INTO recurring_downtime (author, data, downtime_type, last_update) ".
				"VALUES(".$db->escape(Auth::instance()->get_user()->username).
				", ".$db->escape($data).", ".$db->escape($downtime_type).", ".time().")";
		}

		$db->query($sql);
		return true;
	}

	/**
	 *	Fetch specific schedule from db
	 */
	public function get_schedule_data($id = false, $type=false)
	{
		$db = Database::instance();

		$sql = "SELECT * FROM recurring_downtime ";

		if (!empty($type)) {
			$sql .= " WHERE downtime_type=".$db->escape($type)." ORDER BY last_update";
		} else {
			if (!empty($id)) {
				$sql .= " WHERE id=".$id;
			} else {
				$sql .= " ORDER BY downtime_type, last_update";
			}
		}

		$res = $db->query($sql);

		return count($res) > 0 ? $res : false;
	}

	/**
	 *	Send downtime command to nagios
	 */
	public function add_downtime($data=false, $nagioscmd=false, $start_time=false)
	{
		if (empty($data) || empty($nagioscmd) || empty($start_time)) {
			return false;
		}

		$objfields = array(
				'hosts' => 'host_name',
				'hostgroups' => 'hostgroup',
				'servicegroups' => 'servicegroup',
				'services' => 'service_description'
				);

		# determine if we should loop over host_name, hostgroups etc
		$obj_arr = $data[$objfields[$data['report_type']]];
		$cmd = false;
		$duration = $data['duration'];
		$fixed = isset($data['fixed']) ? (int)$data['fixed'] : 1;
		$triggered_by = isset($data['triggered_by']) && !$fixed ? (int)$data['triggered_by'] : 0;

		if (strstr($duration, ':')) {
			# we have hh::mm
			$timeparts = explode(':', $duration);
			$duration_hours = $timeparts[0];
			$duration_minutes = $timeparts[1];

			#convert to seconds
			$duration = ($duration_hours * 3600);
			$duration += ($duration_minutes * 60);
		} else {
			$duration_hours = (int)$duration;
			$duration = ($duration_hours * 3600);
		}

		$end_time = $start_time + $duration;
		$author = $data['author'];
		$comment = $data['comment'];

		$nagios_base_path = Kohana::config('config.nagios_base_path');
		$pipe = $nagios_base_path."/var/rw/nagios.cmd";
		$nagconfig = System_Model::parse_config_file("nagios.cfg");
		if (isset($nagconfig['command_file'])) {
			$pipe = $nagconfig['command_file'];
		}

		foreach ($obj_arr as $obj) {
			# check if object already scheduled for same start time and duration?
			if (Downtime_Model::check_if_scheduled($data['report_type'], $obj, $start_time, $duration)) {
				fwrite(STDERR, "skipping");
				continue;
			}
			$tmp_cmd = "$nagioscmd;$obj;$start_time;$end_time;$fixed;$triggered_by;$duration;$author;AUTO: $comment";
			$result = nagioscmd::submit_to_nagios($tmp_cmd, $pipe);
			$cmd[] = $tmp_cmd.' :'.(int)$result;
		}

		#echo Kohana::debug($cmd);
	}

	/**
	 * Delete a scheduled recurring downtime
	 *
	 * FIXME: why is there no authorization here?
	 *
	 * @param $id ID of the downtime to delete
	 * @returns true on success, false otherwise
	 *
	 */
	public function delete_schedule($id=false)
	{
		if (empty($id) || !(int)$id) {
			return false;
		}

		$db = Database::instance();

		$sql = "DELETE FROM recurring_downtime WHERE id=".(int)$id;
		if (!$db->query($sql)) {
			return false;
		}
		return true;
	}

}
