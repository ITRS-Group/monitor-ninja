<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package reports
 *
 * Sender of reports
 */
$config['from'] = 'Ninja';

$mail_sender_address = '';

if (!empty($mail_sender_address)) {
	$from_email = $mail_sender_address;
} else {
	$hostname = exec('hostname --long');
	if (empty($hostname) && $hostname != '(none)') {
		// unable to get a valid hostname
		$from_email = $config['from'] . '@localhost';
	} else {
		$from_email = $config['from'] . '@'.$hostname;
	}
}

$config['from_email'] = $from_email;

/**
*	Path to showlog executable
*/
$config['showlog_path'] = '/opt/monitor/op5/reports/module/showlog';

