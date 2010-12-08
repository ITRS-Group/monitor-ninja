<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package reports
 *
 * Sender of reports
 * Enter name of sender of reports here or it will be
 * something like <product_name>@<hostname>
 */
$config['from'] = false;

/**
 * email of sender
 */
$config['from_email'] = '';

/**
*	Path to showlog executable
*/
$config['showlog_path'] = '/opt/monitor/op5/merlin/showlog';

$config['reports_link'] = "reports";

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
