<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Core
 *
 * Sets the default route
 */
$config['_default'] = 'default';

# default route when user is logged in
$config['logged_in_default'] = 'tac/index';

# route to login_form
$config['log_in_form'] = 'default/show_login';

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
