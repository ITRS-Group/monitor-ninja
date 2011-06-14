<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $data, $user)
	{
		$tap->print_header(Router::$controller.'/reports test (single host user: '.$user.')');

		$tap->ok(!empty($data), '$avail_data should not be empty');

		return $tap->done();
	}
}
$user = Auth::instance()->get_user()->username;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $avail_data, $user));