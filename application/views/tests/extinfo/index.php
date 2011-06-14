<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $data, $host_address, $user)
	{
		$tap->print_header(Router::$controller.'/host tests (user: '.$user.')');

		$tap->ok(!empty($data), '$data (commands) should not be empty');
		$tap->ok(!empty($host_address), '$host_address should not be empty');

		return $tap->done();
	}
}
$user = Auth::instance()->get_user()->username;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $commands, $host_address, $user));
