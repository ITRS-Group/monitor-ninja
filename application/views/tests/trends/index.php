<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $data, $user)
	{
		$tap->print_header(Router::$controller.'/index (template) tests (user: '.$user.')');

		$tap->ok(true, 'Simple test to rule out SQL errors only');

		return $tap->done();
	}
}
$user = Auth::instance()->get_user()->username;
$content = !isset($content) ? false : $content;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $content, $user));
