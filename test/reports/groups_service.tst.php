<?php
$testcase = array (
  'description' => 'Group availability for services',
  'logfile' => 'groups_service.log',
  'global_vars' => 
  array (
    'includesoftstates' => '1',
  ),
  'Group availability including soft states (servicegroups)' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'report_type' => 'servicegroups',
    'objects' => 
    array (
      'group1' => 
      array (
        0 => 'testhost;PING',
      ),
      'group2' => 
      array (
        0 => 'testhost2;PING',
      ),
    ),
    'correct' => 
    array (
      'TIME_OK_UNSCHEDULED' => '82800',
      'TIME_WARNING_UNSCHEDULED' => '3600',
    ),
  ),
  'Group availability including soft states (services)' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost;PING',
      1 => 'testhost2;PING',
    ),
    'correct' => 
    array (
      'TIME_OK_UNSCHEDULED' => '82800',
      'TIME_WARNING_UNSCHEDULED' => '3600',
    ),
  ),
  'Group availability including soft states, reversed host order (services)' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost2;PING',
      1 => 'testhost;PING',
    ),
    'correct' => 
    array (
      'TIME_OK_UNSCHEDULED' => '82800',
      'TIME_WARNING_UNSCHEDULED' => '3600',
    ),
  ),
  'Group availability excluding soft states (services)' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'includesoftstates' => '0',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost;PING',
      1 => 'testhost2;PING',
    ),
    'correct' => 
    array (
      'TIME_OK_UNSCHEDULED' => '83400',
      'TIME_WARNING_UNSCHEDULED' => '3000',
    ),
  ),
  'Group availability excluding soft states, reversed host order (services)' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'includesoftstates' => '0',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost2;PING',
      1 => 'testhost;PING',
    ),
    'correct' => 
    array (
      'TIME_OK_UNSCHEDULED' => '83400',
      'TIME_WARNING_UNSCHEDULED' => '3000',
    ),
  ),
  'Cluster mode availability including soft states (services)' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'sla_mode' => '2',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost;PING',
      1 => 'testhost2;PING',
    ),
    'correct' => 
    array (
      'TIME_OK_UNSCHEDULED' => '86400',
    ),
  ),
  'Cluster mode availability including soft states, reversed host order (services)' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'sla_mode' => '2',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost2;PING',
      1 => 'testhost;PING',
    ),
    'correct' => 
    array (
      'TIME_OK_UNSCHEDULED' => '86400',
    ),
  ),
  'Cluster mode availability excluding soft states (services)' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'includesoftstates' => '0',
    'sla_mode' => '2',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost;PING',
      1 => 'testhost2;PING',
    ),
    'correct' => 
    array (
      'TIME_OK_UNSCHEDULED' => '86400',
    ),
  ),
  'Cluster mode availability excluding soft states, reversed host order (services)' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'includesoftstates' => '0',
    'sla_mode' => '2',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost2;PING',
      1 => 'testhost;PING',
    ),
    'correct' => 
    array (
      'TIME_OK_UNSCHEDULED' => '86400',
    ),
  ),
);
