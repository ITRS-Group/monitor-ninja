<?php defined('SYSPATH') OR die('No direct access allowed.');


class NinjaDateTime extends DateTime {
    public $dow; // day of week
    public $dom; // day of month
    public $month_ord; // month ordinal (e.g. January)
    public $week, $month, $year, $dom_last;

    /** Monitor/Ninja DateTime type for extra convenience.
     * @param $date_str
     * @throws Exception
     */
    function __construct($date_str) {
        parent::__construct($date_str);

        $this->dom = (int)$this->format('d');
        $this->dom_last = (int)$this->format('t');
        $this->dow = (int)$this->format('N');
        $this->week = (int)$this->format('W');
        $this->month = (int)$this->format('m');
        $this->month_ord = (string)$this->format('F');
        $this->year = (int)$this->format('Y');
    }

    public function last_dom() {
        return $this->dom_last === $this->dom;
    }
}

class Downtime {
    public $model, $start, $end;

    /** Provides a helpers for working with scheduled downtime.
     *
     * @param $model object
     * @throws Exception
     */
    function __construct($model) {
        $this->model = $model;
        $this->start = $this->get_scheduled_start();
        $this->end = $this->get_scheduled_end();
    }

    /** Returns number ordinal
     *
     * @param $number int|mixed
     * @return string number ordinal
     * @throws Exception
     */
    public function number_to_ordinal($number) {
        $number -= 1; // 1 becomes idx 0 of $ordinals.
        $ordinals = ['first', 'second', 'third', 'fourth'];
        if(!array_key_exists($number, $ordinals)) {
            throw new Exception(
                sprintf('Missing ordinal map for number %s', $number)
            );
        }
        return $ordinals[$number];
    }

    /** Returns day-of-week ordinal
     * @param $number int
     * @return string dow ordinal
     * @throws Exception
     */
    public function dow_to_ordinal($number) {
        $ordinals = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        if(!array_key_exists($number, $ordinals)) {
            throw new Exception(
                sprintf('Missing ordinal map for day %s', $number)
            );
        }
        return $ordinals[$number];
    }

    /** Creates a DateTime object using the given date and time
     *
     * @param $date_str string as Y-m-d
     * @param $time_str string as H:i:s
     * @return NinjaDateTime
     * @throws Exception
     */
    private function dt_from_str($date_str, $time_str) {
        $dt_str = sprintf('%s %s', $date_str, $time_str);
        return new NinjaDateTime($dt_str);
    }

    /** Returns MonDateTime given start date and time strings
     *
     * @return NinjaDateTime
     * @throws Exception
     */
    public function get_scheduled_start() {
        $date_str = $this->model->get_start_date();
        $time_str = $this->model->get_start_time_string();
        return $this->dt_from_str($date_str, $time_str);
    }

    /** Returns MonDateTime given end date and time strings
     *
     * @return NinjaDateTime
     * @throws Exception
     */
    public function get_scheduled_end() {
        $date_str = $this->model->get_end_date();
        $time_str = $this->model->get_end_time_string();
        return $this->dt_from_str($date_str, $time_str);
    }
}


class RecurringDowntime extends Downtime {
    public $recur, $on;

    /** Provides a helpers for working with recurring downtime.
     *
     * @param $model object
     * @throws Exception
     */
    function __construct($model) {
        parent::__construct($model);

        $this->recur = $this->get_recurrence($model);
        $this->on = json_decode($model->get_recurrence_on(), true);
    }

    /** Returns recurrence for schedule.
     *
     * @param $sched object
     * @return object
     * @throws Exception
     */
    private function get_recurrence($sched) {
        $recur = json_decode($sched->get_recurrence());
        if(!$recur) {
            throw new Exception('RecurringSchedule.schedule must have a recurrence');
        }

        return $recur;
    }


    /** Returns the day-of-month for a relative occurrence using the given month ordinal.
     *
     * Note: day_no is inconsistent; it's either ordinal or number.
     *
     * @param $month_ord string month ordinal
     * @return int day-of-month
     * @throws Exception
     */
    public function get_dom_occurrence($month_ord) {
        if($this->on['day_no'] === 'last') {
            $occurrence = 'last';
        } else {
            $occurrence = $this->number_to_ordinal($this->on['day_no']);
        }

        $dow = $this->dow_to_ordinal($this->on['day']);

        $dt = new DateTime(
        // relative dt; e.g. "first monday of january"
            sprintf('%s %s of %s', $occurrence, $dow, $month_ord)
        );

        return (int)$dt->format('d');
    }

    /** Plucks values by key from a map.
     *
     * input:
     * ['day' => 1, 'day' => 2, 'day' => 3]
     *
     * output:
     * [1, 2, 3]
     *
     * @param $key string The key to pluck
     * @return array plucked items
     */
    public function pluck_recurrence($key) {
        $plucked = [];
        foreach($this->on as $item) {
            if(!array_key_exists($key, $item)) {
                continue;
            }
            array_push($plucked, $item[$key]);
        }
        return $plucked;
    }


    /** Checks if the provided $date's year matches the schedule's repeat interval.
     *
     * @param $date
     * @return bool
     * @throws Exception
     */
    public function match_year($date) {
        return $this->match_interval('y', $date);
    }

    /** Checks if the provided $date's month matches the schedule's repeat interval.
     *
     * @param $date
     * @return bool
     * @throws Exception
     */
    public function match_month($date) {
        return $this->match_interval('m', $date);
    }

    /** Checks if the provided $date's week matches the schedule's repeat interval.
     *
     * Note:
     * This method doesn't wrap `match_interval` as `DateInterval` lacks support for `weeks`.
     * (This could however be implemented by the ambitious).
     *
     * @param $date DateTime
     * @return bool
     * @throws Exception
     */
    public function match_week($date) {
        $diff = $this->start->diff($date);
        return $diff->days / 7 % $this->recur->no === 0;
    }

    /** Checks if the provided $date's day matches the schedule's repeat interval.
     *
     * @param $date
     * @return bool
     * @throws Exception
     */
    public function match_day($date) {
        return $this->match_interval('m', $date);
    }

    /** Returns true if $unit of $date coincides with the scheduled interval.
     *
     * This is done by ensuring the remainder after division of:
     * [units of time between schedule-start and given date] by [schedule interval]
     * equals 0.
     *
     * @param $unit string abbreviated unit to use in comparison
     * @param $date DateTime to compare with
     * @return bool
     * @throws Exception
     */
    private function match_interval($unit, $date) {
        $diff = $this->start->diff($date);
        return $diff->$unit % $this->recur->no === 0;
    }
}

class ScheduleDate_Model extends Model {
    /**
     * Fields that a schedule include. These are all valid, and all required.
     * Mostly public for test reasons.
     */
    static public $valid_fields = array(
        'author',
        'downtime_type',
        'objects',
        'comment',
        'start_time',
        'end_time',
        'duration',
        'fixed',
        'weekdays',
        'months',
        'start_date',
        'end_date',
        'recurrence',
        'recurrence_on',
        'recurrence_ends',
        'exclude_days'
    );

    /**
     * A list of valid schedule types - same format (no underscore, trailing s)
     * as in report options.
     * Mostly public for test reasons.
     */
    static public $valid_types = array(
        'hosts',
        'services',
        'hostgroups',
        'servicegroups'
    );


    /** Returns a list of objects eligible for downtime
     * @param $sched object schedule-object
     * @return array list of downtime candidates
     */
    protected function downtime_objects($sched) {
        $objects = [];
        foreach($sched->get_objects() as $obj_name) {
            $is_scheduled = static::check_if_scheduled(
                $sched->get_downtime_type(),
                $obj_name,
                $sched->get_start_time(),
                $sched->get_end_time(),
                $sched->get_fixed()
            );

            array_push($objects, [
                'name' => $obj_name,
                'eligible' => !$is_scheduled
            ]);
        }

        return $objects;
    }

    /** Returns a list of objects with downtime eligibility
     * @param $downtime Downtime
     * @param $obj_name string
     * @return string Command format
     */
    public function downtime_command($downtime, $obj_name) {
        $model = $downtime->model;
        $downtime_type = $downtime->model->get_downtime_type();

        $command = [
            '%type' => $downtime_type,
            '%cmd' => self::get_nagios_cmd($downtime_type),
            '%obj_name' => $obj_name,
            '%start' => $downtime->start->getTimestamp(),
            '%end' => $downtime->end->getTimestamp(),
            '%is_fixed' => $model->get_fixed(),
            '%duration' => $model->get_duration(),
            '%author' => $model->get_author(),
            '%comment' => 'AUTO: ' . $model->get_comment()
        ];

        $cmd_fmt = '%cmd;%obj_name;%start;%end;%is_fixed;0;%duration;%author;%comment';
        return str_replace(array_keys($command), array_values($command), $cmd_fmt);
    }

    /** Schedule a downtime
     * @throws Exception
     */
    public function schedule_downtime() {
        // Get schedules from DB
        $schedules = RecurringDowntimePool_Model::all();
        $tomorrow = new NinjaDateTime('tomorrow');

        foreach ($schedules as $sched) {
            $downtime = new RecurringDowntime($sched);
            $set_downtime = false;

            switch($downtime->recur->text) {
                case 'day':
                    /**
                     * Repeats every Nth day
                     * Conditions:
                     * 1) Tomorrow's /day/ matches the scheduled day-interval
                     */
                    $set_downtime = $downtime->match_day($tomorrow);
                    break;
                case 'week':
                    /**
                     * Repeats every Nth week
                     * Conditions:
                     * 1) Tomorrow's /week/ matches the scheduled week-interval
                     * 2) Tomorrow's weekday is one-of the DOM's scheduled
                     */
                    if($downtime->match_week($tomorrow)) {
                        $days = $downtime->pluck_recurrence('day');
                        $set_downtime = in_array($tomorrow->dow, $days);
                    }
                    break;
                case 'month':
                    /**
                     * Repeats every Nth month
                     * Conditions:
                     * 1) Tomorrow's /month/ matches the scheduled month-interval
                     * 2) Tomorrow is the "<ordinal number> <ordinal day> of <month>"
                     */
                    if($downtime->match_month($tomorrow)) {
                        $sched_dom = $downtime->get_dom_occurrence($tomorrow->month_ord);
                        $set_downtime = $tomorrow->dom === $sched_dom;
                    }
                    break;
                case 'year':
                    /**
                     * Repeats every Nth year
                     * Conditions:
                     * 1) Tomorrow's /year/ matches the scheduled year-interval
                     * 2) Tomorrow is the "<ordinal number> <ordinal day> of <month>"
                     */
                    if($downtime->match_year($tomorrow)) {
                        $sched_dom = $downtime->get_dom_occurrence($tomorrow->month_ord);
                        $set_downtime = $tomorrow->dom === $sched_dom;
                    }
                    break;
                default:
                    throw new Exception(sprintf('Invalid recurrence: %s', $downtime->recur->text));
            }

            if(!$set_downtime) {
                fwrite(STDERR, "No downtime for $tomorrow\n");
                continue;
            }

            // Iterate over objects with this schedule and set downtime, unless already scheduled.
            foreach($this->downtime_objects($sched) as $object) {
                $name = $object['name'];
                if(!$object['eligible']) {
                    fwrite(STDERR, "skipping already scheduled: $name\n");
                    continue;
                }

                // Build downtime command string
                $command = $this->downtime_command($downtime, $name);
                $result[] = nagioscmd::submit_to_nagios($command);

                echo($result);
            }
        }
    }

    /**
     * Use a reasonable amount of indicators to determine whether there's
     * already a matching downtime. This prevents downtimes from being
     * scheduled more than once.
     */
    static protected function check_if_scheduled($type, $name, $start_time, $end_time, $is_fixed)
    {
        $ls = Livestatus::instance();
        switch ($type) {
            case 'hosts':
                $res = $ls->getDowntimes(array('filter' => array('is_service' => 0, 'host_name' => $name, 'start_time' => $start_time, 'fixed' => $is_fixed, 'end_time' => $end_time)));
                break;
            case 'services':
                if (!strstr($name, ';')){
                    return false;
                }
                $parts = explode(';', $name);
                $host = $parts[0];
                $service = $parts[1];
                $res = $ls->getDowntimes(array('filter' => array('is_service' => 1, 'host_name' => $host, 'service_description' => $service, 'start_time' => $start_time, 'fixed' => $is_fixed, 'end_time' => $end_time)));
                break;
            case 'hostgroups':
                $hosts = $ls->getHosts(array('filter' => array('groups' => array('>=' => $name))));
                $in_dtime = $ls->getDowntimes(array('filter' => array('is_service' => 0, 'host_groups' => array('>=' => $name), 'start_time' => $start_time, 'fixed' => $is_fixed, 'end_time' => $end_time)));
                return (count($hosts) <= count($in_dtime));
                break;

            case 'servicegroups':
                $services = $ls->getServices(array('filter' => array('groups' => array('>=' => $name))));
                $in_dtime = $ls->getDowntimes(array('filter' => array('is_service' => 1, 'service_groups' => array('>=' => $name), 'start_time' => $start_time, 'fixed' => $is_fixed, 'end_time' => $end_time)));
                return (count($services) <= count($in_dtime));
                break;
        }
        return (!empty($res));
    }

    /**
     * Returns appropriate nagios command
     *
     * @param $type string
     * @return string
     */
    static protected function get_nagios_cmd($type)
    {
        if (empty($type)) {
            return false;
        }
        $downtime_commands = array(
            'hosts' => 'SCHEDULE_HOST_DOWNTIME',
            'services' => 'SCHEDULE_SVC_DOWNTIME',
            'hostgroups' => 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME',
            'servicegroups' => 'SCHEDULE_SERVICEGROUP_SVC_DOWNTIME'
        ); # will schedule downtime for all services - not their hosts!
        return $downtime_commands[$type];
    }

    /**
     * Schedule a downtime by submitting it to nagios
     *
     * @param $objects array
     * @param $object_type string
     * @param $start_time string
     * @param $end_time string
     * @param $start_date string
     * @param $end_date string
     * @param $fixed string
     * @param $duration string
     * @param $comment string
     * @return boolean
     **/
    public static function insert_downtimes($objects, $object_type, $start_time, $end_time, $start_date, $end_date, $fixed, $duration, $comment)
    {
        $result = array();
        $nagios_cmd = self::determine_downtimetype($object_type);
        $author = op5auth::instance()->get_user()->get_username();
        $month = date('n');
        $day = date('d');
        $year = date('Y');
        $strt_d = explode('-',$start_date);
        $sy = (int)$strt_d[0];
        $sm = (int)$strt_d[1];
        $sd = (int)$strt_d[2];
        $end_d = explode('-',$end_date);
        $ey = (int)$end_d[0];
        $em = (int)$end_d[1];
        $ed = (int)$end_d[2];
        $start_time = mktime(0, 0, self::time_to_seconds($start_time), $sm, $sd, $sy);
        $end_time = mktime(0, 0, self::time_to_seconds($end_time), $em, $ed, $ey);
        foreach ($objects as $object) {
            if (static::check_if_scheduled($object_type, $object, $start_time, $end_time, $fixed)) {
                // Skip object if it is already scheduled for downtime
                continue;
            }
            $tmp_cmd = "$nagios_cmd;$object;$start_time;$end_time;$fixed;0;$duration;$author;AUTO: $comment";
            $result[] = nagioscmd::submit_to_nagios($tmp_cmd);
        }
        return !in_array(false, $result);
    }

    /**
     * Given a time-like string (hh[:mm[:ss]]),
     * return the number of seconds involved.
     */
    static public function time_to_seconds($time)
    {
        $seconds = 0;
        $parts = explode(':', $time);
        if (isset($parts[0]))
            $seconds += $parts[0] * 3600;
        if (isset($parts[1]))
            $seconds += $parts[1] * 60;
        if (isset($parts[2]))
            $seconds += $parts[2];
        return $seconds;
    }

    /**
     * Returns appropriate nagios command
     *
     * @param $report_type string
     * @return string
     */
    static protected function determine_downtimetype($report_type=false)
    {
        if (empty($report_type)) {
            return false;
        }
        $downtime_commands = array(
            'hosts' => 'SCHEDULE_HOST_DOWNTIME',
            'services' => 'SCHEDULE_SVC_DOWNTIME',
            'hostgroups' => 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME',
            'servicegroups' => 'SCHEDULE_SERVICEGROUP_SVC_DOWNTIME'
        ); # will schedule downtime for all services - not their hosts!
        return $downtime_commands[$report_type];
    }

    /**
     * Save/update a recurring schedule
     *
     * @param $data array
     * @param $id int
     * @throws Exception
     */
    public function edit_schedule($data, &$id = false) {
        if (!$data) {
            throw new Exception("Missing data for editing a scheduled downtime");
        }
        foreach (static::$valid_fields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing field $field");
            }
        }
        $db = Database::instance();
        $downtime_type = $data['downtime_type'];
        if (!in_array($downtime_type, static::$valid_types, true)) {
            throw new Exception("Downtime type $downtime_type is invalid");
        }
        $type = substr($data['downtime_type'], 0, -1);
        if (!op5auth::instance()->authorized_for($type.'_edit_contact') && !op5auth::instance()->authorized_for($type.'_edit_all')) {
            throw new Exception("Not authorized for editing $type objects");
        }

        $start_time = static::time_to_seconds($data['start_time']);
        $end_time = static::time_to_seconds($data['end_time']);
        $duration = static::time_to_seconds($data['duration']);

        if ((int)$id) {
            $set = RecurringDowntimePool_Model::get_by_query('[recurring_downtimes] id = '.(int)$id);
            if (!count($set)) {
                throw new Exception("Schedule was supposed to be for an existing recurring downtime, but none could be found for $id");
            }
            $db->query("DELETE FROM recurring_downtime_objects WHERE recurring_downtime_id = ".(int)$id);
            # update schedule
            $sql = "UPDATE recurring_downtime SET author = %s," .
                " downtime_type = %s, last_update = %s, comment = %s," .
                " start_time = %s, end_time = %s, duration = %s, fixed = %s," .
                " weekdays = %s, months = %s, start_date = %s, end_date = %s," .
                " recurrence = %s, recurrence_on = %s, recurrence_ends = %s, exclude_days = %s  WHERE id = ".(int)$id;
        } else {
            # new schedule
            $sql = "INSERT INTO recurring_downtime (author, downtime_type," .
                " last_update, comment, start_time, end_time, duration," .
                " fixed, weekdays, months, start_date, end_date, recurrence, recurrence_on, recurrence_ends, exclude_days) VALUES (%s, %s, %s, %s, %s, %s," .
                " %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)";
        }
        $res = $db->query(sprintf($sql, $db->escape($data['author']),
            $db->escape($data['downtime_type']), $db->escape(time()),
            $db->escape($data['comment']), $db->escape($start_time),
            $db->escape($end_time), $db->escape($duration),
            $db->escape($data['fixed']),
            $db->escape(serialize($data['weekdays'])),
            $db->escape(serialize($data['months'])),
            $db->escape($data['start_date']),
            $db->escape($data['end_date']),
            $db->escape($data['recurrence']),
            $db->escape($data['recurrence_on']),
            $db->escape($data['recurrence_ends']),
            $db->escape($data['exclude_days'])
        ));
        if (!$id)
            $id = $res->insert_id();
        foreach ($data['objects'] as $object) {
            $db->query("INSERT INTO recurring_downtime_objects" .
                " (recurring_downtime_id, object_name) VALUES (" .
                (int)$id.", ".$db->escape($object).")");
        }
    }

    /**
     * Delete a scheduled recurring downtime
     *
     * @param $id ID of the downtime to delete
     * @returns true on success, false otherwise
     */
    public function delete_schedule($id)
    {
        $set = RecurringDowntimePool_Model::get_by_query('[recurring_downtimes] id = '.(int)$id);
        if (!count($set))
            return false;

        $obj = $set->it(array('downtime_type'))->current();
        $type = substr($obj->get_downtime_type(), 0, -1);

        // *_add_delete is for the objects, and because this manipulates the
        // state of an existing object, *_add_delete is not required. OK?
        if (!op5auth::instance()->authorized_for($type.'_edit_contact') && !op5auth::instance()->authorized_for($type.'_edit_all'))
            return false;

        $db = Database::instance();

        $sql = "DELETE FROM recurring_downtime WHERE id=".(int)$id;
        if (!$db->query($sql)) {
            return false;
        }
        $sql = "DELETE FROM recurring_downtime_objects WHERE recurring_downtime_id=".(int)$id;
        if (!$db->query($sql)) {
            return false;
        }
        return true;
    }
}
