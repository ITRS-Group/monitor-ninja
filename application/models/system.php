<?php defined('SYSPATH') OR die('No direct access allowed.');

class System_Model extends Model {

	public $base_path = '';
	public function __construct()
	{
		parent::__construct();
		$this->base_path = Kohana::config('config.nagios_base_path');
	}

	/**
	 * 	@name 	parse_config_file
	* 	@desc 	Reads a configuration file in the format variable=value
	*			and returns it in an array.
	*			Lines beginning with # are considered to be comments
	* 	@param  string $config_file path to file to parse
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
	*	@name	fetch_nagios_users
	*	@desc	Call parse_config_file() with cgi.cfg
	* 			and fetch user configuration options (authorized_for)
	*
	*/
	public function fetch_nagios_users()
	{
		$cgi_config = false;
		$cgi_config_file = $this->base_path."/etc/cgi.cfg";
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
	*	@name 	nagios_access
	*	@desc	Fetch authentication information
	* 			for a named user
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

}
