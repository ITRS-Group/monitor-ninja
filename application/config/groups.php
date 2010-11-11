<?php defined('SYSPATH') OR die('No direct access allowed.');

# control if you need to be authorized for
# all hosts to see a hostgroup or not
# Default is false
$config['see_partial_hostgroups'] = false;

# control if you need to be authorized for
# all services to see a servicegroup or not
# Default is false
$config['see_partial_servicegroups'] = false;

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
