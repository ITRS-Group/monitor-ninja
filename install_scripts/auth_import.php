#!/usr/bin/php -q
<?php
/**
 * Import existing authorization settings from cgi.cfg to database
 * for all users.
 */

/**
 * Parse nagios config file
 */
function parse_file($config_file)
{
	$config_file = trim($config_file);
	if (empty($config_file)) {
		return false;
	}
	$default_cgi_path = '/usr/local/nagios/etc';
	$monitor_cgi_path = '/opt/monitor/etc';
	$base_path = false;
	if (file_exists($default_cgi_path)) {
		$base_path = $default_cgi_path;
	} else {
		if (file_exists($monitor_cgi_path)) {
			$base_path = $monitor_cgi_path;
		}
	}

	if ($base_path === false) {
		echo "Unable to locate config files. Exiting.\n";
		exit(1);
	}

	$etc = strstr($base_path, '/etc') ? '' : '/etc/';
	# check that we have a trailing slash in path
	if (substr($base_path.$etc, -1, 1) != '/') {
		$etc .= '/';
	}

	$config_file = $base_path.$etc.$config_file;
	if (!file_exists($config_file)) {
		echo "Couldn't find file ($config_file)\n";
		return false;
	}

	$buf = file_get_contents($config_file);
	if($buf === false) return(false);

	$lines = explode("\n", $buf);
	$buf = '';

	$tmp = false;
	foreach($lines as $line) {
		// skip empty lines and non-variables
		$line = trim($line);
		if(!strlen($line) || $line{0} === '#') continue;
		$str = explode('=', $line);
		if(!isset($str[1])) continue;

		// preserve all values if a variable can be specified multiple times
		if(isset($options[$str[0]]) && $options[$str[0]] !== $str[1]) {
			if(!is_array($options[$str[0]])) {
				$tmp = $options[$str[0]];
				$options[$str[0]] = array($tmp);
			}
			$options[$str[0]][] = $str[1];
			continue;
		}
		$options[$str[0]] = $str[1];
	}

	return $options;
}

/**
 * Fetch cgi.cfg and return all authorized_for_ directives,
 * page refresh_rate and all defined users.
 *
 * @return array user_data, refresh_rate, user_list
 */
function get_cgi_config()
{
	$auth_data = parse_file('cgi.cfg');
	$user_data = false;
	$user_list = array();
	$return = false;
	$access_levels = array(
		'authorized_for_system_information',
		'authorized_for_configuration_information',
		'authorized_for_system_commands',
		'authorized_for_all_services',
		'authorized_for_all_hosts',
		'authorized_for_all_service_commands',
		'authorized_for_all_host_commands'
	);

	if(empty($auth_data)) {
		return false;
	}

	foreach($auth_data as $k => $v) {
		if(substr($k, 0, 14) === 'authorized_for') {
			$auth_data[$k] = explode(',', $v);
		}
	}

	# fetch defined access data for users
	foreach ($access_levels as $level) {
		$users = $auth_data[$level];
		foreach ($users as $user) {
			$user_data[$level][] = $user;
			if (!in_array($user, $user_list)) {
				$user_list[] = $user;
			}
		}
	}
	if (array_key_exists('refresh_rate', $auth_data)) {
		$return['refresh_rate'] = $auth_data['refresh_rate'];
	}
	$return['user_data'] = $user_data;
	$return['user_list'] = $user_list;
	return $return;
}

/**
 * Insert found user data into db
 */
function insert_user_data()
{
	$config_data = get_cgi_config();

	# All db fields that should be set
	# according to data in cgi.cfg
	$auth_fields = array(
		'system_information',
		'configuration_information',
		'system_commands',
		'all_services',
		'all_hosts',
		'all_service_commands',
		'all_host_commands'
	);

	if (empty($config_data['user_list']))
		return false;
	foreach ($config_data['user_list'] as $user) {
		$auth_data = array();
		if (empty($config_data['user_data'])) {
			continue;
		}

		foreach ($auth_fields as $field) {
			if (!isset($config_data['user_data']['authorized_for_'.$field])) {
				$auth_data[] = 0;
			} else {
				if (in_array($user, $config_data['user_data']['authorized_for_'.$field])) {
					$auth_data[] = 1;
				} else {
					$auth_data[] = 0;
				}
			}
		}
		if (!empty($auth_data)) {
			system('/usr/bin/php ../index.php cli/edit_user_authorization monitor -user '.$user.' -authdata '.implode(',', $auth_data), $result);
			print_r($result);
		} else {
			echo "empty\n";
		}
	}
}

insert_user_data();

?>