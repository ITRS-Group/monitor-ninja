<?php defined('SYSPATH') OR die('No direct access allowed.');

$tap = unittest::instance();

class tests
{
	public static function run($tap, $data)
	{
		$tap->print_header(Router::$controller.'/unauthorized');

		$tap->pass(!empty($data), '$error_description should not be empty');

		return $tap->done();
	}
}

exit(tests::run($tap, $error_description));