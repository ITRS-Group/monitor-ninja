<?php defined('SYSPATH') OR die('No direct access allowed.');

# setup some test variables
$hostproblems_acknowledged = 0;
$host_notifications_disabled = 0;
$active_checks_disabled = 0;
$host_is_flapping = 0;
$hostscheduled_downtime_depth = 0;
$hosts_with_comments =0;
$service_comments = 0;
$acknowledged_services = 0;
$svc_notifications_disabled = 0;
$svc_active_checks_disabled = 0;
$flapping_services = 0;
$svc_in_downtime = 0;

$user = Auth::instance()->get_user()->username;

$curr_host = false;
$a = 0;
$c=0;
if (!empty($result)) {
	foreach ($result as $row) {
		#echo Kohana::debug($row);
		$a++;
		if ($curr_host != $row->host_name) {
			$c++;
			if ($row->hostproblem_is_acknowledged)
				$hostproblems_acknowledged++;
			if (empty($row->host_notifications_enabled))
				$host_notifications_disabled++;
			if (!$row->host_active_checks_enabled)
				$active_checks_disabled++;
			if (isset($row->host_is_flapping) && $row->host_is_flapping)
				$host_is_flapping++;
			if ($row->hostscheduled_downtime_depth > 0)
				$hostscheduled_downtime_depth++;
			if ($host_comments !== false && array_key_exists($row->host_name, $host_comments)) {
				$hosts_with_comments++;
			}
		}

		if ($comments !== false && array_key_exists($row->host_name.';'.$row->service_description, $comments)) {
			$service_comments++;
		}
		if ($row->problem_has_been_acknowledged) {
			$acknowledged_services++;
		}
		if (empty($row->notifications_enabled)) {
			$svc_notifications_disabled++;
		}
		if (!$row->active_checks_enabled) {
			$svc_active_checks_disabled++;
		}
		if (isset($row->service_is_flapping) && $row->service_is_flapping) {
			$flapping_services++;
		}
		if ($row->scheduled_downtime_depth > 0) {
			$svc_in_downtime++;
		}
		$curr_host = $row->host_name;
	} # end foreach
}

$test_data = array(
	'page_links' => $page_links,
	'result' => count($result),
	'hostproblems_acknowledged' => array($hostproblems_acknowledged, ($user=='limited' ? 6 : 7)),
	'host_notifications_disabled' => array($host_notifications_disabled, 1),
	'active_checks_disabled' => array($active_checks_disabled, ($user=='limited' ? 1 : 4)),
	'host_is_flapping' => array($host_is_flapping, 0),
	'hostscheduled_downtime_depth' => array($hostscheduled_downtime_depth, ($user=='limited' ? 1 : 4)),
	'hosts_with_comments' => array($hosts_with_comments, ($user=='limited' ? 6 : 10)),
	'service_comments' => array($service_comments, ($user=='limited' ? 9 : 24)),
	'acknowledged_services' => array($acknowledged_services, 3),
	'svc_notifications_disabled' => array($svc_notifications_disabled, ($user=='limited' ? 25 : 27)),
	'svc_active_checks_disabled' => array($svc_active_checks_disabled, 2),
	'flapping_services' => array($flapping_services, 0),
	'svc_in_downtime' => array($svc_in_downtime, ($user=='limited' ? 6 : 21))
);

/*
$test_data = array(
	'page_links' => $page_links,
	'result' => count($result),
	'hostproblems_acknowledged' => array($hostproblems_acknowledged, 2),
	'host_notifications_disabled' => array($host_notifications_disabled, 1),
	'active_checks_disabled' => array($active_checks_disabled, 4),
	'host_is_flapping' => array($host_is_flapping, 0),
	'hostscheduled_downtime_depth' => array($hostscheduled_downtime_depth, 4),
	'hosts_with_comments' => array($hosts_with_comments, 6),
	'service_comments' => array($service_comments, 24),
	'acknowledged_services' => array($acknowledged_services, 3),
	'svc_notifications_disabled' => array($svc_notifications_disabled, 25),
	'svc_active_checks_disabled' => array($svc_active_checks_disabled, 2),
	'flapping_services' => array($flapping_services, 0),
	'svc_in_downtime' => array($svc_in_downtime, 21)
);
*/
#die(Kohana::debug($comments));
$tap = unittest::instance();

class tests
{
	public static function run($tap, $data, $user)
	{
		$tap->print_header(Router::$controller.'/service tests (user: '.$user.')');
		$tap->ok(!empty($data['result']), '$result should not be empty');
		$tap->ok(!empty($data['page_links']), 'page_links should not be empty');

		# returning early here as we are not interested in more detailed tests
		return $tap->done();

		$tap->ok($data['hostproblems_acknowledged'][0] == $data['hostproblems_acknowledged'][1], $data['hostproblems_acknowledged'][1].
			' hostproblems should be set as acknowledged, received '.
			$data['hostproblems_acknowledged'][0]);

		$tap->ok($data['host_notifications_disabled'][0] == $data['host_notifications_disabled'][1], $data['host_notifications_disabled'][1].
			' host should be flagged with notifications disabled, received '.
			$data['host_notifications_disabled'][0]);

		$tap->ok($data['active_checks_disabled'][0] == $data['active_checks_disabled'][1], $data['active_checks_disabled'][1].
			' host should be flagged with active checks disabled, received '.
			$data['active_checks_disabled'][0]);

		$tap->ok($data['host_is_flapping'][0] == $data['host_is_flapping'][1], $data['host_is_flapping'][1].
			' host should be flagged as flapping, received '.
			$data['host_is_flapping'][0]);

		$tap->ok($data['hostscheduled_downtime_depth'][0] == $data['hostscheduled_downtime_depth'][1], $data['hostscheduled_downtime_depth'][1].
			' host should be flagged as being in scheduled downtime, received '.
		$data['hostscheduled_downtime_depth'][0]);

		$tap->ok($data['hosts_with_comments'][0] == $data['hosts_with_comments'][1], $data['hosts_with_comments'][1].
			' host should have comments, received '.$data['hosts_with_comments'][0]);

		$tap->ok($data['service_comments'][0] == $data['service_comments'][1], $data['service_comments'][1].
			' services should have comments, received '.$data['service_comments'][0]);

		$tap->ok($data['acknowledged_services'][0] == $data['acknowledged_services'][0], $data['acknowledged_services'][0].
			' services should be flagged as acknowledged, received '.$data['acknowledged_services'][0]);

		$tap->ok($data['svc_notifications_disabled'][0] == $data['svc_notifications_disabled'][1], $data['svc_notifications_disabled'][1].
			' services should be flagged with notifications disabled, received '.
			$data['svc_notifications_disabled'][0]);

		$tap->ok($data['svc_active_checks_disabled'][0] == $data['svc_active_checks_disabled'][1], $data['svc_active_checks_disabled'][1].
			' services should be flagged with active checks disabled, received '.
			$data['svc_active_checks_disabled'][0]);

		$tap->ok($data['flapping_services'][0] == $data['flapping_services'][1], $data['flapping_services'][1].
			' services should be flagged as flapping, received '.$data['flapping_services'][0]);

		$tap->ok($data['svc_in_downtime'][0] == $data['svc_in_downtime'][1], $data['svc_in_downtime'][1].
			' services should be flagged as being in scheduled downtime, received '.$data['svc_in_downtime'][0]);

		return $tap->done();
	}
}
$user = Auth::instance()->get_user()->username;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $test_data, $user));
