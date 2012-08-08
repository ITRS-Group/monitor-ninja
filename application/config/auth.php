<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Max allowed login attempts
 * Set to false to ignore
 */
$config['max_attempts'] = false;

/**
 * Setting this to TRUE will allow you to access any page by
 * appending ?username=<username>&password=<password> to the URL.
 *
 * Warning: this is insecure! Do know what you're doing!
 */
$config['use_get_auth'] = false;

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
