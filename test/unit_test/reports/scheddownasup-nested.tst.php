<?php
$testcase = array (
  'description' => 'count scheduled downtime as uptime, nested',
  'sqlfile' => 'test_nested_downtimes.sql',
  'global_vars' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'includesoftstates' => '1',
  ),
  'down_dtstart_dtstart_dtend_dtend: normal' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'down_dtstart_dtstart_dtend_dtend',
    ),
    'scheduleddowntimeasuptime' => '0',
    'correct' => 
    array (
      'TIME_DOWN_UNSCHEDULED' => '78600',
      'TIME_DOWN_SCHEDULED' => '7800',
    ),
  ),
  'down_dtstart_dtstart_dtend_dtend: scheduled downtime as uptime' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'down_dtstart_dtstart_dtend_dtend',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_UP_SCHEDULED' => '7800',
      'TIME_DOWN_UNSCHEDULED' => '78600',
    ),
  ),
);
