<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $data, $user)
	{
		$tap->print_header(Router::$controller.'/generate tests (user: '.$user.')');

		$tap->ok(!empty($data), '$object_data should not be empty');

		return $tap->done();
	}
}
$user = Auth::instance()->get_user()->username;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $object_data, $user));
