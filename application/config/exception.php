<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Commands to include output of in exception
 */
$config['shell_commands'] = array(
	"whoami",
	"groups",
	"df -h",
	"date",
	"rpm -q op5-monitor-release"
);

$config['extra_info'] = array(
	'Monitor user' => user::session('username')
);
