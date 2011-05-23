<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $data, $type, $user)
	{
		$ignored = array('service_dependencies', 'service_escalations', 'host_dependencies', 'host_escalations');
		$tap->print_header(Router::$controller.' ('.$type.') tests (user: '.$user.')');

		if (in_array($type, $ignored) && empty($data)) {
			$tap->pass('It is OK in this config if this type ('.$type.') is empty.', TAP_TODO);
		} else {
			$tap->ok(!empty($data), '$data should not be empty');
		}

		return $tap->done();
	}
}
$user = Auth::instance()->get_user()->username;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $data, $type, $user));
