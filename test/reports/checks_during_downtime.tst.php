<?php
$testcase = array (
  'description' => 'count scheduled downtime after previous downtime',
  'sqlfile' => 'test_during_downtimes.sql',
  'global_vars' =>
  array (
    'start_time' => '1202688001',
    'end_time' => '1202695201',
    'includesoftstates' => '1',
  ),
  'host1: normal' =>
  array (
    'report_type' => 'hosts',
    'objects' =>
    array (
      0 => 'host1',
    ),
    'scheduleddowntimeasuptime' => '0',
    'correct' =>
    array (
      'TIME_DOWN_UNSCHEDULED' => '0',
      'TIME_DOWN_SCHEDULED' => '7200',
    ),
  ),
  'host1: scheduled downtime as uptime' =>
  array (
    'report_type' => 'hosts',
    'objects' =>
    array (
      0 => 'host1',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' =>
    array (
      'TIME_UP_SCHEDULED' => '7200',
      'TIME_DOWN_UNSCHEDULED' => '0',
    ),
  ),
);
