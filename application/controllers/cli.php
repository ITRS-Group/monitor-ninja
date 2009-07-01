<?php defined('SYSPATH') OR die('No direct access allowed.');

class Cli_Controller extends Authenticated_Controller {

	public function __construct()
	{
		# Only grant permission for cli access or if
		# user has been given the ADMIN role
		if (PHP_SAPI !== "cli" &&
			!Auth::instance()->logged_in(Ninja_Controller::ADMIN)) {
			url::redirect('default/index');
		}
	}

	/**
	*	Takes input from commandline import of cgi.cfg
	*/
	public function _edit_user_authorization($username=false, $options=false)
	{
		if (empty($username) || empty($options)) {
			return false;
		}
		$result = User_Model::user_auth_data($username, $options);
		return $result;
	}

	/**
	 * Parse input data from commandline and stores in an array
	 * An equivalent to getopt() but easier for us in this environment
	 */
	public function _parse_parameters($noopt = array())
	{
		$result = array();
		$params = $GLOBALS['argv'];
		// could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly
		reset($params);
		while (list($tmp, $p) = each($params)) {
			if ($p{0} == '-') {
				$pname = substr($p, 1);
				$value = true;
				if ($pname{0} == '-') {
					// long-opt (--<param>)
					$pname = substr($pname, 1);
					if (strpos($p, '=') !== false) {
						// value specified inline (--<param>=<value>)
						list($pname, $value) = explode('=', substr($p, 2), 2);
					}
				}
				// check if next parameter is a descriptor or a value
				$nextparm = current($params);
				if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-')
					list($tmp, $value) = each($params);
				$result[$pname] = $value;
			} else {
			// param doesn't belong to any option
			$result[] = $p;
			}
		}
		return $result;
	}

	/**
	 * fetch data from cgi.cfg and return to calling script
	 */
	public function get_cgi_config()
	{
		$auth_data = System_Model::parse_config_file('cgi.cfg');
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
	 * Insert user data from cgi.cfg into db
	 */
	public function insert_user_data()
	{
		# first import new users from cgi.cfg if there is any
		$path = realpath(APPPATH."../cli-helpers/htpasswd-import.php");
		$no_auto_import = true;
		require_once($path);
		$passwd_import = new htpasswd_importer();
		$base_path = System_Model::get_nagios_base_path();
		$etc_path = Kohana::config('config.nagios_etc_path') ? Kohana::config('config.nagios_etc_path') : $base_path.'/etc';
		if (substr($etc_path, -1, 1) != '/') {
			$etc_path .= '/';
		}
		$passwd_import->import_hashes($etc_path.'htpasswd.users');

		$config_data = self::get_cgi_config();

		# All db fields that should be set
		# according to data in cgi.cfg
		$auth_fields = Ninja_user_authorization_Model::$auth_fields;

		if (empty($config_data['user_list'])) {
			return false;
		}
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
				self::_edit_user_authorization($user, $auth_data);
			}
		}
	}

}