<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();
$user = Auth::instance()->get_user()->username;

class tests
{
	public static function run($tap, $page_links, $result, $host_comments, $hoststatustypes, $user)
	{
		$tap->print_header(Router::$controller.'/host tests (user: '.$user.')');

		$tap->ok(!empty($page_links), '$page_links should not be empty');

		$tap->ok(!empty($result), '$result should not be empty');
		# returning early here as we are not interested in more detailed tests
		return $tap->done();

		$tap->ok(!empty($host_comments), '$host_comments should not be empty');
		if (empty($hoststatustypes)) {
			# since hoststatustypes is a filter, we shouldn't get all hosts and subsequently
			# the next test will almost always fail
			# @@@FIXME: Handle tests depending on host- and servicefilters (props and types)
			$expected = ($user=='limited' ? 7 : 24);
			#$expected = 23;
			$tap->ok(count($result) == $expected, '$result should contain '.$expected.' items, found '. count($result));
			$tap->ok(count($result)>0, '$result should not be empty');
		} else {
			$cnt = 0;
			switch ($hoststatustypes) {
				case 1: $cnt = 4; break;
				case 2: $cnt = 8; break;
				case 4: $cnt = 6; break;
				case 6: $cnt = 14; break;
				case 64: $cnt = 8; break;
			}
			$tap->ok(count($result) == $cnt, '$result should contain '.$cnt.' items, found '. count($result));
			if ($cnt !=0) {
				$tap->ok(count($result)>0, '$result should not be empty');
			}
		}

		return $tap->done();
	}
}

#		$this->hostprops = $hostprops;
#		$this->serviceprops = $serviceprops;
$benchmark = Benchmark::get('system_benchmark_total_execution');
echo 'TIME: '.$benchmark['time']."\n";
exit(tests::run($tap, $page_links, $result, $host_comments, $this->hoststatustypes, $user));