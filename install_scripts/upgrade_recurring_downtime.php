<?php
if (PHP_SAPI !== "cli") {
	exit(1);
}

define('SKIP_KOHANA', true);
require_once __DIR__.'/../index.php';

$db = Database::instance();
$res = $db->query('SELECT * FROM recurring_downtime');
$report = array(
	'hosts' => 'host_name',
	'services' => 'service_description',
	'hostgroups' => 'hostgroup',
	'servicegroups' => 'servicegroup'
);
foreach ($res->result(false) as $row) {
	if ($row['start_time'])
		continue; // already migrated
	$data = i18n::unserialize($row['data']);
	$data['start_time'] = arr::search($data, 'time', 0);
	$end_time = ScheduleDate_Model::time_to_seconds(arr::search($data, 'time', '0')) + ScheduleDate_Model::time_to_seconds(arr::search($data, 'duration', '0'));
	$data['end_time'] = sprintf(
		'%02d:%02d:%02d',
		($end_time / 3600),
		($end_time / 60 % 60),
		($end_time % 60));
	$data['weekdays'] = arr::search($data, 'recurring_day', array());
	$data['months'] = arr::search($data, 'recurring_month', array());
	$data['downtime_type'] = arr::search($data, 'report_type', '');
	if ($data['downtime_type'])
		$data['objects'] = arr::search($data, $report[$data['report_type']], array());
	$data['author'] = $row['author'];
	$sd = new ScheduleDate_Model();
	$sd->edit_schedule($data, $row['id']);
}
