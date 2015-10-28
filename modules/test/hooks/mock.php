<?php
if (!isset($_SERVER['HTTP_X_OP5_MOCK'])) return;

$mock_data_path = $_SERVER['HTTP_X_OP5_MOCK'];

$log = op5log::instance('test');
if (!is_readable($mock_data_path)) {
	$log->log("warning", "Can not read mock data from '$mock_data_path'");
	return;
}

Event::add("system.ready", function() use ($mock_data_path, $log) {
	op5objstore::instance()->mock_add('op5config',
		new MockConfig(
			array('auth' =>
			array(
				'common' => array(
					'session_key' => 'auth_user',
					'default_auth' => 'Default'
				),
				'Default' => array(
					'driver' => 'default'
				)
			)
		)
	));

	op5objstore::instance()->mock_add('op5auth',
		new MockAuth());

	op5objstore::instance()->mock_add('op5MayI',
		new MockMayI(
		)
	);

	$json_str = file_get_contents($mock_data_path);

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
	foreach ($json_conf as $driver => $tables) {
		op5objstore::instance()->mock_add($driver, new ORMDriverNative($tables));
	}
});

