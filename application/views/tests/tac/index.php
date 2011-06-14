<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $widget_order, $widgets, $user)
	{
		$tap->print_header(Router::$controller.'/index tests (user: '.$user.')');
		$tap->ok(!empty($widget_order), '$widget_order should not be empty');
		$tap->ok(!empty($widgets), '$widgets should not be empty');

		return $tap->done();
	}
}
$user = Auth::instance()->get_user()->username;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $widget_order, $widgets, $user));
