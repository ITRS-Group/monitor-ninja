<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
* Use this flag to change the default behaviour for how
* to view passive checks.
*
* By settings this flag to true, all passive checks will be treated
* as active in tactical overview.
*
* Please note that this value is cached as a session variable so
* you need to logout and login again for changes to take effect
*
* Default value: false
*/
$config['show_passive_as_active'] = false;
