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
