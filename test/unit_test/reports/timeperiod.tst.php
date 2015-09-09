<?php
$testcase = array (
  'description' => 'Reports using timeperiods',
  'logfile' => 'softstates.log',
  'global_vars' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'testhost',
    ),
    'timeperiod' => 
    array (
      'timeperiod_name' => 'an_exclude',
      '2008-02-11' => '22:00-23:30',
    ),
  ),
  'monday lunchtime 12:00-13:00' => 
  array (
    'timeperiod' => 
    array (
      'monday' => '12:00-13:00',
    ),
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '3600',
      'TIME_INACTIVE' => '82800',
    ),
  ),
  'simple timeperiod' => 
  array (
    'timeperiod' => 
    array (
      'monday' => '02:15-03:25',
    ),
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '4200',
      'TIME_INACTIVE' => '82200',
      'TOTAL_TIME_ACTIVE' => '4200',
    ),
  ),
  'slightly tricksier timeperiod' => 
  array (
    'timeperiod' => 
    array (
      'monday' => '02:15-03:25,04:15-05:25',
    ),
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '8400',
      'TIME_INACTIVE' => '78000',
      'TOTAL_TIME_ACTIVE' => '8400',
    ),
  ),
  'tricksy timeperiod indeed' => 
  array (
    'timeperiod' => 
    array (
      'monday' => '00:15-00:25,01:15-01:25,02:15-02:25,03:15-03:25,04:00-05:00',
    ),
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '6000',
      'TIME_INACTIVE' => '80400',
      'TOTAL_TIME_ACTIVE' => '6000',
    ),
  ),
  'timeperiod with simple exception' => 
  array (
    'timeperiod' => 
    array (
      'monday' => '00:00-24:00',
      '2008-02-11' => '22:00-23:30',
    ),
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '4200',
      'TIME_DOWN_UNSCHEDULED' => '1200',
      'TIME_INACTIVE' => '81000',
    ),
  ),
  'timeperiod with simple exception and three days' => 
  array (
    'start_time' => '1202598000',
    'end_time' => '1202857200',
    'timeperiod' => 
    array (
      'monday' => '00:00-24:00',
      '2008-02-11' => '22:00-23:30',
    ),
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '4200',
      'TIME_DOWN_UNSCHEDULED' => '1200',
      'TIME_INACTIVE' => '253800',
    ),
  ),
  'timeperiod with simple excludes' => 
  array (
    'timeperiod' => 
    array (
      'monday' => '00:00-24:00',
      'excludes' => 
      array (
        0 => 'an_exclude',
      ),
    ),
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '79200',
      'TIME_DOWN_UNSCHEDULED' => '1800',
      'TIME_INACTIVE' => '5400',
    ),
  ),
  'another timeperiod' => 
  array (
    'timeperiod' => 
    array (
      'monday' => '23:00-23:30',
    ),
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '600',
      'TIME_DOWN_UNSCHEDULED' => '1200',
      'TIME_INACTIVE' => '84600',
    ),
  ),
);
