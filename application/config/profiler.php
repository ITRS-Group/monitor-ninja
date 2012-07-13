<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Profiler
 *
 * Array of section names to display in the Profiler, TRUE to display all of them.
 * Built in sections are benchmarks, database, session, post and cookies, custom sections can be used too.
 */
$config['show'] = false;

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
