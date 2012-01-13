<?php

require_once unittest::tap_path();

class Ninja_Unit_Test {
	function __construct() {
		$paths = array(MODPATH . 'unit_test');
		ob_end_clean();
		$main_tap = new phptap("Ninja unit test suite");

		foreach ($paths as $path) {
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::KEY_AS_PATHNAME)) as $path => $file) {
				// Skip files without "_Test" suffix
				if ( ! $file->isFile() OR substr($path, -9) !== '_Test'.EXT)
					continue;

				// The class name should be the same as the file name
				$class = substr($path, strrpos($path, '/') + 1, -(strlen(EXT)));

				// Skip hidden files
				if (substr($class, 0, 1) === '.')
					continue;

				// Check for duplicate test class name
				if (class_exists($class, FALSE))
					$main_tap->fail("Duplicate test class: $class in $path");

				// Include the test class
				include_once $path;
				runUnit($class, $main_tap);
			}
		}

		$exit_code = $main_tap->done(false);
		exit((int)($exit_code > 1));
	}
}
