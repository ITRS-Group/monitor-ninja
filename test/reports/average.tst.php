<?php
$testcase = array (
  'description' => 'Test SLA with average and group availability for two hosts',
  'logfile' => 'average.log',
  'global_vars' => 
  array (
    'start_time' => '1199200000',
    'end_time' => '1199230000',
    'report_type' => 'hosts',
    'objects' => 
    array (
      0 => 'testhost',
      1 => 'testhost2',
    ),
  ),
  'test 1: using SLA - Group availability' => 
  array (
    'sla_mode' => '0',
    'correct' => 
    array (
      'TOTAL_TIME_UP' => '20000',
      'TOTAL_TIME_DOWN' => '10000',
    ),
  ),
  'test 1: using SLA - average' => 
  array (
    'sla_mode' => '1',
    'correct' => 
    array (
      'TOTAL_TIME_UP' => '25000',
      'TOTAL_TIME_DOWN' => '5000',
    ),
  ),
);
