<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $group_details, $grouptype, $user)
	{
		$tap->print_header(Router::$controller.'/'.$grouptype.'group grid tests (user: '.$user.')');

		if ($user == 'limited') {
			$tap->ok(count($group_details)==1, '$group_details should be empty:'.count($group_details));
		} else {
			$tap->ok(!empty($group_details), '$group_details should not be empty');

			if (count($group_details) && !empty($group_details)) {
				foreach ($group_details as $details) {
					$result = Group_Model::get_group_info($grouptype, $details->{$grouptype.'group_name'});
					$tap->ok(count($result) && !empty($result), '$group_details should not be empty');
				}
			}
		}

		return $tap->done();
	}
}

$user = Auth::instance()->get_user()->username;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $group_details, $grouptype, $user));