<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Reads various nagios-related configuration files
 */
class System_Model extends Model
{
	public $base_path = ''; /** the base path where nagios is installed */
	public function __construct()
	{
		parent::__construct();
		$this->base_path = Kohana::config('config.nagios_base_path');
	}

	/**
	 * Reads a configuration file in the format variable=value
	 * and returns it in an array.
	 * Lines beginning with # are considered to be comments
	 * @param $config_file path to file to parse
	 */
	public function parse_config_file($config_file)
	{
		$options = false;
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
		$cgi_config_file = $this->base_path."/cgi.cfg";
		$user_data = false;
		$access_levels = array('authorized_for_system_information',
						'authorized_for_configuration_information',
						'authorized_for_system_commands',
						'authorized_for_all_services',
						'authorized_for_all_hosts',
						'authorized_for_all_service_commands',
						'authorized_for_all_host_commands');

		$cgi_config = $this->parse_config_file($cgi_config_file);
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
	 * for a named user
	 */
	public function nagios_access($username=false)
	{
		if (empty($username)) {
			return false;
		}

		$user_access = false;
		$data = $this->fetch_nagios_users();
		if (!empty($data)) {
			foreach ($data as $access => $users) {
				if (!empty($users) && in_array($username, $users)) {
					$user_access[] = $access;
				}
			}
		}
		return $user_access;
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
