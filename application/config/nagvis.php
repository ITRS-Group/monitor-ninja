<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['nagvis_real_path'] = '/opt/monitor/op5/nagvis_ls/';
$config['nagvis_path'] = '/monitor/op5/nagvis_ls/';

if( !file_exists($config['nagvis_real_path'].'etc/nagvis.ini.php') ) {
	unset( $config['nagvis_real_path'] );
	unset( $config['nagvis_path'] );
}