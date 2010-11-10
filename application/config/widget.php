<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Name of widgets directory
 */
$config['dirname'] = 'widgets/';

/**
 * define base path for widgets
 */
$config['path'] = 'application/';

/**
 * Name of custom widgets directory
 */
$config['custom_dirname'] = 'custom_widgets/';

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
