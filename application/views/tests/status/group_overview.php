<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $group_details, $grouptype, $user)
	{
		$tap->print_header(Router::$controller.'/'.$grouptype.'group_overview tests (user: '.$user.')');

		$tap->ok(true, 'Simple test to rule out SQL errors only');

		return $tap->done();
	}
}

$user = Auth::instance()->get_user()->username;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $group_details, $grouptype, $user));