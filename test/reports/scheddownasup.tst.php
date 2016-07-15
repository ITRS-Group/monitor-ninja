<?php
$testcase = array (
  'description' => 'count scheduled downtime as uptime',
  'logfile' => 'scheddownasup.log',
  'global_vars' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'includesoftstates' => '1',
  ),
  'up_down_dtstart_dtend_up: normal' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'up_down_dtstart_dtend_up',
    ),
    'scheduleddowntimeasuptime' => '0',
    'correct' => 
    array (
      'TIME_UP_SCHEDULED' => '0',
      'TIME_UP_UNSCHEDULED' => '75600',
      'TIME_DOWN_SCHEDULED' => '7200',
      'TIME_DOWN_UNSCHEDULED' => '3600',
      'TOTAL_TIME_SCHEDULED' => '7200',
      'TOTAL_TIME_UNSCHEDULED' => '79200',
      'TOTAL_TIME_ACTIVE' => '86400',
      'TOTAL_TIME_KNOWN' => '86400',
    ),
  ),
  'up_down_dtstart_dtend_up: scheduled downtime as uptime' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'up_down_dtstart_dtend_up',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_UP_SCHEDULED' => '7200',
      'TIME_UP_UNSCHEDULED' => '75600',
      'TIME_DOWN_SCHEDULED' => '0',
      'TIME_DOWN_UNSCHEDULED' => '3600',
      'TOTAL_TIME_SCHEDULED' => '7200',
      'TOTAL_TIME_UNSCHEDULED' => '79200',
      'TOTAL_TIME_ACTIVE' => '86400',
      'TOTAL_TIME_KNOWN' => '86400',
    ),
  ),
  'down_dtstart_up_dtend: normal' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'down_dtstart_up_dtend',
    ),
    'scheduleddowntimeasuptime' => '0',
    'correct' => 
    array (
      'TIME_UP_SCHEDULED' => '3600',
      'TIME_UP_UNSCHEDULED' => '75600',
      'TIME_DOWN_SCHEDULED' => '3600',
      'TIME_DOWN_UNSCHEDULED' => '3600',
      'TOTAL_TIME_SCHEDULED' => '7200',
      'TOTAL_TIME_UNSCHEDULED' => '79200',
      'TOTAL_TIME_ACTIVE' => '86400',
      'TOTAL_TIME_KNOWN' => '86400',
    ),
  ),
  'down_dtstart_up_dtend: scheduled downtime as uptime' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'down_dtstart_up_dtend',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_UP_SCHEDULED' => '7200',
      'TIME_UP_UNSCHEDULED' => '75600',
      'TIME_DOWN_SCHEDULED' => '0',
      'TIME_DOWN_UNSCHEDULED' => '3600',
      'TOTAL_TIME_SCHEDULED' => '7200',
      'TOTAL_TIME_UNSCHEDULED' => '79200',
      'TOTAL_TIME_ACTIVE' => '86400',
      'TOTAL_TIME_KNOWN' => '86400',
    ),
  ),
  'up_dtstart_down_dtend: normal' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'up_dtstart_down_dtend',
    ),
    'scheduleddowntimeasuptime' => '0',
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '3600',
      'TIME_UP_SCHEDULED' => '3600',
      'TIME_DOWN_SCHEDULED' => '3600',
      'TIME_DOWN_UNSCHEDULED' => '75600',
      'TOTAL_TIME_SCHEDULED' => '7200',
      'TOTAL_TIME_UNSCHEDULED' => '79200',
      'TOTAL_TIME_ACTIVE' => '86400',
      'TOTAL_TIME_KNOWN' => '86400',
    ),
  ),
  'up_dtstart_down_dtend: scheduled downtime as uptime' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'up_dtstart_down_dtend',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_UP_SCHEDULED' => '7200',
      'TIME_UP_UNSCHEDULED' => '3600',
      'TIME_DOWN_SCHEDULED' => '0',
      'TIME_DOWN_UNSCHEDULED' => '75600',
      'TOTAL_TIME_SCHEDULED' => '7200',
      'TOTAL_TIME_UNSCHEDULED' => '79200',
      'TOTAL_TIME_ACTIVE' => '86400',
      'TOTAL_TIME_KNOWN' => '86400',
    ),
  ),
  'up_dtstart_down_up_down_dtend: normal' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'up_dtstart_down_up_down_dtend',
    ),
    'scheduleddowntimeasuptime' => '0',
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '3600',
      'TIME_UP_SCHEDULED' => '3600',
      'TIME_DOWN_SCHEDULED' => '3600',
      'TIME_DOWN_UNSCHEDULED' => '75600',
      'TOTAL_TIME_SCHEDULED' => '7200',
      'TOTAL_TIME_UNSCHEDULED' => '79200',
      'TOTAL_TIME_ACTIVE' => '86400',
      'TOTAL_TIME_KNOWN' => '86400',
    ),
  ),
  'up_dtstart_down_up_down_dtend: scheduled downtime as uptime' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'up_dtstart_down_up_down_dtend',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '3600',
      'TIME_UP_SCHEDULED' => '7200',
      'TIME_DOWN_SCHEDULED' => '0',
      'TIME_DOWN_UNSCHEDULED' => '75600',
    ),
  ),
  'group avail: normal' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'up_down_dtstart_dtend_up',
      1 => 'up_dtstart_down_dtend',
    ),
    'scheduleddowntimeasuptime' => '0',
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '3600',
      'TIME_UP_SCHEDULED' => '0',
      'TIME_DOWN_SCHEDULED' => '7200',
      'TIME_DOWN_UNSCHEDULED' => '75600',
    ),
  ),
  'group avail: scheduled downtime as uptime' => 
  array (
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'up_down_dtstart_dtend_up',
      1 => 'up_dtstart_down_dtend',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '3600',
      'TIME_UP_SCHEDULED' => '7200',
      'TIME_DOWN_SCHEDULED' => '0',
      'TIME_DOWN_UNSCHEDULED' => '75600',
    ),
  ),
);
