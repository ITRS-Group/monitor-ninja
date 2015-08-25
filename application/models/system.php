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
	*	Fetch nagios etc path as configured in config file
	*	@return string directory where nagios.cfg can be found
	*/
	public static function get_nagios_etc_path()
	{
		$etc = Kohana::config('config.nagios_etc_path') ? Kohana::config('config.nagios_etc_path') : (self::get_nagios_base_path().'/etc');
		if (substr($etc, -1, 1) != '/') {
			$etc .= '/';
		}
		return $etc;
	}

	/**
	 * 	Fetch nagios command pipe as configured by the
	 * 	nagios_pipe setting. If the pipe does not exist
	 * 	or is not writable, fetch pipe as configured by
	 * 	command_file in nagios.cfg.
	 * 	@return string path to the configured pipe
	 * */
	public static function get_pipe()
	{
		$pipe = Kohana::config('config.nagios_pipe');
		if( !file_exists($pipe) || !is_writable($pipe) ) {
			$nagconfig = self::parse_config_file("nagios.cfg");
			if (isset($nagconfig['command_file'])) {
				$pipe = $nagconfig['command_file'];
			}
		}
		return $pipe;
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
			$etc = self::get_nagios_etc_path();
			$config_file = $etc.$config_file;
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
	*	Fetch status info from nagios log file
	*/
	public static function get_status_info($file = 'status.log', $section = 'programstatus')
	{
		if (empty($file))
			return false;

		if (!file_exists($file)) {
			$base_path = self::get_nagios_base_path();
			$file = $base_path.'/var/'.$file;
		}
		if (!file_exists($file)) {
			return false;
		}

		$fh = fopen($file, "r");
		if (!$fh)
			return false;
		$found_section = false;
		while ($raw_line = fgets($fh)) {
			$line = trim($raw_line);
			if (!strlen($line) || $line{0} === '#')
				continue;

			# this routine is only ever read to get one
			# section, so we can bail early when we've
			# found it and the section is done with
			if ($found_section && $line{0} === '}')
				break;

			if (!strcmp($line, $section.' {')) {
				$found_section = true;
				continue;
			}

			if ($found_section === true) {
				$ary = explode('=', $line);
				if (is_array($ary) && sizeof($ary)==2) {
					$data[$section][$ary[0]] = $ary[1];
				}
			}
		}
		fclose($fh);

		return $data;
	}

	/**
	*	Extract values from status.log array returned from
	*	get_status_info()
	*/
	public static function extract_stat_key($key=false, &$arr=false)
	{
		if (empty($key) || empty($arr))
			return false;
		$return = false;
		if (isset($arr[$key])) {
			$tmp = explode(',', $arr[$key]);
			if (is_array($tmp) && !empty($tmp)) {
				if (count($tmp) == 1) {
					$return = $tmp[0];
				} else {
					$return = $tmp;
				}
			} else {
				$return = $arr[$key];
			}
		}
		return $return;
	}
}
