<?php
$testcase = array (
  'description' => 'count scheduled service downtime as uptime',
  'logfile' => 'scheddownasup_service.log',
  'global_vars' => 
  array (
    'includesoftstates' => '0',
  ),
  'scheduled service downtime as uptime' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost;PING',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_OK_SCHEDULED' => '3600',
      'TIME_OK_UNSCHEDULED' => '75600',
      'TIME_WARNING_SCHEDULED' => '0',
      'TIME_WARNING_UNSCHEDULED' => '7200',
    ),
  ),
  'host in scheduled downtime, service as uptime' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost2;PING',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_OK_SCHEDULED' => '3600',
      'TIME_OK_UNSCHEDULED' => '75600',
      'TIME_WARNING_SCHEDULED' => '0',
      'TIME_WARNING_UNSCHEDULED' => '7200',
    ),
  ),
  'host in scheduled downtime, service as uptime, 2 services' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost;PING',
      1 => 'testhost2;PING',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_OK_SCHEDULED' => '3500',
      'TIME_OK_UNSCHEDULED' => '75600',
      'TIME_WARNING_SCHEDULED' => '0',
      'TIME_WARNING_UNSCHEDULED' => '7300',
      'subs' => 
      array (
        'testhost;PING' => 
        array (
          'TIME_OK_SCHEDULED' => '3600',
          'TIME_OK_UNSCHEDULED' => '75600',
          'TIME_WARNING_SCHEDULED' => '0',
          'TIME_WARNING_UNSCHEDULED' => '7200',
        ),
        'testhost2;PING' => 
        array (
          'TIME_OK_SCHEDULED' => '3600',
          'TIME_OK_UNSCHEDULED' => '75600',
          'TIME_WARNING_SCHEDULED' => '0',
          'TIME_WARNING_UNSCHEDULED' => '7200',
        ),
      ),
    ),
  ),
  'host in dt before report_period starts, service never in dt' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost3;PING',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_OK_SCHEDULED' => '2100',
      'TIME_CRITICAL_UNSCHEDULED' => '84300',
    ),
  ),
  'host with two services' => 
  array (
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost2;PING',
      1 => 'testhost2;PING2',
    ),
    'scheduleddowntimeasuptime' => '1',
    'correct' => 
    array (
      'TIME_OK_SCHEDULED' => '3600',
      'TIME_OK_UNSCHEDULED' => '75600',
      'TIME_WARNING_SCHEDULED' => '0',
      'TIME_WARNING_UNSCHEDULED' => '7200',
      'subs' => 
      array (
        'testhost2;PING' => 
        array (
          'TIME_OK_SCHEDULED' => '3600',
          'TIME_OK_UNSCHEDULED' => '75600',
          'TIME_WARNING_SCHEDULED' => '0',
          'TIME_WARNING_UNSCHEDULED' => '7200',
        ),
        'testhost2;PING2' => 
        array (
          'TIME_OK_SCHEDULED' => '3600',
          'TIME_OK_UNSCHEDULED' => '75600',
          'TIME_WARNING_SCHEDULED' => '0',
          'TIME_WARNING_UNSCHEDULED' => '7200',
        ),
      ),
    ),
  ),
);
