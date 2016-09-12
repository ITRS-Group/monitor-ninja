<?php
/**
 * This file is the so called Test Suite. It takes the data from test_suite.json
 * and builds a list of QUnit tests.
 *
 * For each test you are supposed to add the relevant data in test_suite.json,
 * "js_deps" for external dependencies, "file_to_test" is the javascript file
 * which you like to test the functionality on and "test" is the js where you
 * write the tests.
 *
 * You can run the tests in your browser by just typing the URL to this file,
 * i.e. ninja/test/qunit/test_suite.php.
 *
 * You can also run tests in a console typing "make test/qunit/test_suite.html"
 * from your Ninja repo. directory in your VM (or equivalent). Note that
 * node-qunit-phantomjs (installed via NPM) is needed for this to work.
 */
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width">
		<title>QUnit Test Suite</title>
		<link rel="stylesheet" href="https://code.jquery.com/qunit/qunit-2.0.1.css">
	</head>
	<body>
		<div id="qunit"></div>
		<div id="qunit-fixture"></div>
		<script src="https://code.jquery.com/qunit/qunit-2.0.1.js"></script>
		<?php
		$settings = json_decode(file_get_contents(__DIR__ . '/test_suite.json'), true);
		foreach($settings as $s) {

			// Dependencies, if any.
			foreach($s['js_deps'] as $url) {
				echo "<script src=\"$url\"></script>";
			}

			// File with the functionality that needs to be tested.
			// There might be none.
			if (!empty($s['file_to_test'])) {
				echo "<script src=\"{$s['file_to_test']}\"></script>";
			}

			// The file with the actual tests.
			echo "<script src=\"{$s['test']}\"></script>";
		}
		?>
	</body>
</html>
