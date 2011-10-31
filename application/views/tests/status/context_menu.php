<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap)
	{
		$tap->ok("Dummy file ".__FILE__." likes everybody, you too! Test passed, sir.");

		return $tap->done();
	}
}

$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap));
