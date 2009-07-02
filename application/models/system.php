<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Reads various nagios-related configuration files
 */
class System_Model extends Model
{

	/**
	*	Fetch nagios base path as configured in config file
	*	@return string 'config.nagios_base_path'
	*/
	public static function get_nagios_base_path()
	{
		return Kohana::config('config.nagios_base_path');
	}

	/**
	 * Reads a configuration file in the format variable=value
	 * and returns it in an array.
	 * lines beginning with # are considered to be comments
	 * @param $config_file The configuration file to parse
	 * @return Array of key => value type on success, false on errors
	 */
	public static function parse_config_file($config_file) {
		$config_file = trim($config_file);
		if (empty($config_file)) {
			return false;
		}

		# check if we have a full path as input
		if (!file_exists($config_file)) {
			$base_path = self::get_nagios_base_path();
			$base_path = Kohana::config('config.nagios_etc_path') ? Kohana::config('config.nagios_etc_path') : $base_path.'/etc';
			$etc = strstr($base_path, '/etc') ? '' : '/etc/';
			# check that we have a trailing slash in path
			if (substr($base_path.$etc, -1, 1) != '/') {
				$etc .= '/';
			}
			$config_file = $base_path.$etc.$config_file;
		}

		if (!file_exists($config_file)) {
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
	 * Call parse_config_file() with cgi.cfg
	 * and fetch user configuration options (authorized_for)
	 */
	public function fetch_nagios_users()
	{
		$cgi_config = false;
		$base_path = self::get_nagios_base_path();
		$etc_path = Kohana::config('config.nagios_etc_path') ? Kohana::config('config.nagios_etc_path') : $base_path.'/etc';
		$cgi_config_file = $etc_path."/cgi.cfg";
		$user_data = false;
		$access_levels = array('authorized_for_system_information',
						'authorized_for_configuration_information',
						'authorized_for_system_commands',
						'authorized_for_all_services',
						'authorized_for_all_hosts',
						'authorized_for_all_service_commands',
						'authorized_for_all_host_commands');

		$cgi_config = self::parse_config_file($cgi_config_file);
		if(empty($cgi_config)) {
			return false;
		}

		foreach($cgi_config as $k => $v) {
			if(substr($k, 0, 14) === 'authorized_for') {
				$cgi_config[$k] = explode(',', $v);
			}
		}

		# fetch defined access data for users
		foreach ($access_levels as $level) {
			$users = $cgi_config[$level];
			foreach ($users as $user) {
				$user_data[$level][] = $user;
			}
		}
		return $user_data;
	}

	/**
	 * Fetch authentication information
	 * for a named user.
	 * Use cached authorization data from session if available.
	 */
	public function nagios_access($username=false)
	{
		$access = Session::instance()->get('nagios_access', false);
		if (empty($access)) {
			$access = Ninja_user_authorization_Model::get_auth_data($username);
			Session::instance()->set('nagios_access', $access);
		}
		return $access;
	}

	/**
	 * Fetch info on installed rpm packages
	 * @param $filter A regular expression passed to 'grep'
	 * @return array or false
	 */
	public function rpm_info($filter = 'op5')
	{
		$filter = escapeshellarg(trim($filter));
		$rpm_info = false;
		$exec_str = '/bin/rpm -qa';
		$exec_str .= !empty($filter) ? '|grep '.$filter.'|sort' : '|sort';
		exec($exec_str, $output, $retval);
		if ($retval==0 && !empty($output)) {
			foreach ($output as $rpm) {
				$rpm_info .= $rpm."<br />";
			}
			if (!empty($rpm_info)) {
				return $rpm_info;
			}
		}
		return false;
	}
}
