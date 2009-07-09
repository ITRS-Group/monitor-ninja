#!/usr/bin/php -q
<?php
/**
 * Import existing authorization settings from cgi.cfg to database
 * for all users.
 */
$err_str = "\nAn error occurred and this script will terminate. Too bad...\n";
$retval = false;
$cli_access = false;
$argv = isset($argv) ? $argv : $GLOBALS['argv'];
$prefix = isset($argv[1]) ? $argv[1] : '';

exec('/usr/bin/php '.$prefix.'/index.php default/get_cli_status ', $cli_access, $retval);

if (!empty($retval)) {
	echo $err_str;
	exit(1);
}

if (empty($cli_access)) {
	echo "\nCLI access seems to be disabled so it's impossible to import\n";
	echo "authorization settings from cgi.cfg to Ninja.\n";
	echo "Try specifying a valid (existing) user under 'cli_access' in config/config.php \n";
	echo "or try setting the value to true and try running this \nscript ('" . getcwd() . '/' .
		basename($_SERVER['PHP_SELF']) . "') again.\n";
	exit(1);
}

$user = false;
if ($cli_access[0] == 1) {
	# cli_access is set to true which means we should try to find a valid user
	exec('/usr/bin/php ../index.php default/get_a_user ', $user, $retval);
	if (!empty($retval)) {
		echo $err_str;
		exit(1);
	}
	$user = $user[0];
} else {
	$user = $cli_access[0];
}

exec('/usr/bin/php ../index.php cli/insert_user_data '.$user.' ', $result, $retval);

if (!empty($retval)) {
	echo $err_str;
	exit(1);
}

exit(0);

?>