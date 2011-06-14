<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $multiple_states, $user)
	{
		$tap->print_header(Router::$controller.'/reports test (multiple service states, user:'.$user.')');

		$tap->ok(!empty($multiple_states), '$multiple_states should not be empty');

		return $tap->done();
	}
}
$user = Auth::instance()->get_user()->username;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $multiple_states, $user));
