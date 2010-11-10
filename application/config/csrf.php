<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Check if we always should use CSRF tokens to secure forms
 */
$config['active'] = false;

/**
 * Session key to hold CSRF token
 */
$config['csrf_token'] = 'csrf_token';

/**
 * token generation timestamp
 */
$config['csrf_timestamp'] = 'csrf_timestamp';

/**
 * csrf token lifetime in seconds
 * For how long to we trust the csrf token?
 */
$config['csrf_lifetime']  = 5400;

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
