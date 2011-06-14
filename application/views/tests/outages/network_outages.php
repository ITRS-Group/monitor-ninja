<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $outage_data, $user)
	{
		$tap->print_header(Router::$controller.'/network_outages tests (user: '.$user.')');

		$tap->ok(!empty($outage_data), '$outage_data should not be empty');

		return $tap->done();
	}
}
$user = Auth::instance()->get_user()->username;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $outage_data, $user));
