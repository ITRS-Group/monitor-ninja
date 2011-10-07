<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['comment'] = '';
$config['duration'] = '2.0'; // hours
$config['fixed'] = true;
$config['services-too'] = true;
$config['host-too'] = false;
$config['sticky'] = true;
$config['notify'] = true;
$config['persistent'] = true;
$config['force'] = true;
$config['check_attempts'] = 5;
$config['check_interval'] = 300;
$config['delete'] = true;

$cgi_config = System_Model::parse_config_file('cgi.cfg');
$config['notes_url_target'] = isset($cgi_config['notes_url_target']) ? $cgi_config['notes_url_target'] : '_blank';
$config['action_url_target'] = isset($cgi_config['action_url_target']) ? $cgi_config['action_url_target'] : '_blank';
$config['available_targets'] = array('_blank' => '_blank', '_self' => '_self', '_top' => '_top', '_parent' => '_parent');

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
