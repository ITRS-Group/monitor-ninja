<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package reports
 *
 * Sender of reports
 */
$config['from'] = false;

$mail_sender_address = '';

if (!empty($mail_sender_address)) {
	$from_email = $mail_sender_address;
} else {
	$hostname = exec('hostname --long');
	$from = !empty($config['from']) ? $config['from'] : Kohana::config('config.product_name');
	$from = str_replace(' ', '', trim($from));
	if (empty($hostname) && $hostname != '(none)') {
		// unable to get a valid hostname
		$from_email = $from . '@localhost';
	} else {
		$from_email = $from . '@'.$hostname;
	}
}

$config['from_email'] = $from_email;

/**
*	Path to showlog executable
*/
$config['showlog_path'] = '/opt/monitor/op5/reports/module/showlog';

