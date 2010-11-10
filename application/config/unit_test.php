<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Unit_Test
 *
 * Default paths to scan for tests.
 */
$config['paths'] = array
(
	MODPATH.'unit_test/tests',
);

/**
 * Set to TRUE if you want to hide passed tests from the report.
 */
$config['hide_passed'] = FALSE;

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
