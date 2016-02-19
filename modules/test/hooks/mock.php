<?php
if (!isset($_SERVER['HTTP_X_OP5_MOCK'])) return;

$mock_data_path = $_SERVER['HTTP_X_OP5_MOCK'];

$log = op5log::instance('test');
if (!is_readable($mock_data_path)) {
	$log->log("warning", "Can not read mock data from '$mock_data_path'");
	return;
}

Event::add("system.ready", function() use ($mock_data_path, $log) {
	try {
		$json_str = file_get_contents($mock_data_path);
	}
	catch (Exception $e) {
		// Handle exception as error below.
		$json_str = false;
	}

	if (!$json_str) {
		// "error" since the user is a dev; or an elite supreme hacker
		// reached the mother core, which we also want to hint about
		// even with low log threshold
		$log->log("error", "Could not read mock data from '$mock_data_path'");
		return;
	}
	$json_conf = json_decode($json_str, true);

	if ($json_conf === null) {
		$log->log("error", "Could not decode mock data from '$mock_data_path'. Invalid JSON?");
		return;
	}

	if (array_key_exists("MockedClasses", $json_conf)) {
		$mocked_classes = $json_conf["MockedClasses"];
		unset($json_conf["MockedClasses"]);

		foreach ($mocked_classes as $mock_spec) {
			$mock_class = $mock_spec["mock_class"];
			op5objstore::instance()->mock_add($mock_spec["real_class"],
				new $mock_class($mock_spec["args"]));
		}
	}

	foreach ($json_conf as $driver => $tables) {
		op5objstore::instance()->mock_add($driver, new ORMDriverNative($tables, $mock_data_path, $driver));
	}
});

