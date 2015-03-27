<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Commands to include output of in exception
 */
$config['shell_commands'] = array(
	"id",
	"df -hP",
	"df -iP",
	"date",
	"locale",
	"rpm -qf /etc/op5-monitor-release",
	"uname -a",
	"grep -D skip . /etc/*-release",
	"mon node status",
	"mon node show",
	"free -m",
	"top -bcn1 | head -n17",
	"last -Fax | egrep -e 'system boot' -e '^(reboot|shutdown|wtmp begins) ' -e '- (crash|gone) '",
	"pstree -p 1 | grep -Po '(?<=\()[0-9]+(?=\))' | xargs ps uf -p",
	"grep ^model.name /proc/cpuinfo | uniq -c",
	"lspci | sed -r 's/^[^ ]+ //' | sort | uniq -c"
);

$config['extra_info'] = array(
	'Monitor user' => user::session('username')
);
