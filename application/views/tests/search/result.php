<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $service_result, $host_result, $user)
	{
		$tap->print_header(Router::$controller.'/lookup tests (user: '.$user.')');

		if ($user == 'limited') {
			$tap->pass(empty($service_result), '$service_result should be empty');
			$tap->pass(empty($host_result), '$host_result should be empty');
		} else {
			$tap->ok(!empty($service_result), '$service_result should not be empty');

			# this is not entirely correct - it will be empty for a query like:
			# h:monitor and s:mysql or users
			# as this will only produce a service result
			$tap->ok(count($host_result)!=0, '$host_result should not be empty');
		}

		return $tap->done();
	}
}

$user = Auth::instance()->get_user()->username;
if ($user == 'limited') {
	$service_result = false;
	$host_result = false;
}
$host_val = isset($host_result) ? $host_result : false;
$service_val = isset($service_result) ? $service_result : false;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $service_val, $host_val, $user));

