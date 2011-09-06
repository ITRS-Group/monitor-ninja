<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 *	Specify the path to the hyperapplet jar file
 * 	Download from http://hypergraph.sourceforge.net/download.html
 * 	Assuming vendor/hypergraph/ in ninja/application
 */
$config['hyperapplet_path'] = false;

if (!is_file(APPPATH.$config['hyperapplet_path'])) {
	$config['hyperapplet_path'] = false;
}

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}