<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Commands to include output of in exception
 */
$config['shell_commands'] = array(
	"id",
	"df -hP",
	"date",
	"rpm -q op5-monitor",
	"rpm -q op5-default-appliance",
	"rpm -q op5-system-release",
	"uname -a",
	"cat /proc/version",
	"grep -D skip . /etc/*-release",
	"mon node status",
	"mon node show",
	"top -bcn1M | head",
	"grep ^model.name /proc/cpuinfo | uniq -c",
	"lspci | sed -r 's/^[^ ]+ //' | sort | uniq -c",
	"mon sysconf check"
);

$config['extra_info'] = array(
	'Monitor user' => user::session('username')
);
