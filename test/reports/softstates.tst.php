<?php
$testcase = array (
  'description' => 'Include/exclude soft states',
  'logfile' => 'softstates.log',
  'Include soft states' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'includesoftstates' => '1',
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'testhost',
    ),
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '82800',
      'TIME_DOWN_UNSCHEDULED' => '3600',
    ),
  ),
  'Exclude soft states' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'includesoftstates' => '0',
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'testhost',
    ),
    'correct' => 
    array (
      'TIME_UP_UNSCHEDULED' => '83400',
      'TIME_DOWN_UNSCHEDULED' => '3000',
    ),
  ),
);
