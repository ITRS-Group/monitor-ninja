#!/usr/bin/php -q
<?php
/**
 * Import existing authorization settings from cgi.cfg to
 * mysql database for all users.
 */
$argv = isset($argv) ? $argv : $GLOBALS['argv'];
$prefix = isset($argv[1]) ? $argv[1] : '';

if (PHP_SAPI !== 'cli') {
	$msg = "This program must be run from the command-line\n";
	die($msg);
}

/**
 * Import users from cgi.cfg into ninja/merlin database
 * using the htpasswd_importer class to properly insert
 * hashed user passwords from htpasswd.
 */
class ninja_auth_import
{
	private $db_type = false;
	private $db_host = false;
	private $db_user = false;
	private $db_pass = false;
	private $db_database = false;
	public $prefix = false;
	private $merlin_conf_file = false;
	private $merlin_path = '/opt/monitor/op5/merlin';	# where to find merlin files
	private $nagios_cfg_path = '/opt/monitor/etc';		# path to nagios cfg files
														# no trailing slash
	public $DEBUG = true;
	public static $auth_fields = array(
				'system_information',
				'configuration_information',
				'system_commands',
				'all_services',
				'all_hosts',
				'all_service_commands',
				'all_host_commands'
			);
	public $db = false;

	/**
	*	Initialize object with database settings from merlin
	*/
	public function __construct()
	{
		$this->merlin_conf_file = $this->merlin_path.'/import.php';

		# find db config settings from merlin
		exec("/bin/grep -m1 'imp->db_type' ".$this->merlin_conf_file."|/bin/awk -F = {'print $2'}", $db_type, $retval);
		exec("/bin/grep -m1 'imp->db_host' ".$this->merlin_conf_file."|/bin/awk -F = {'print $2'}", $db_host, $retval);
		exec("/bin/grep -m1 'imp->db_user' ".$this->merlin_conf_file."|/bin/awk -F = {'print $2'}", $db_user, $retval);
		exec("/bin/grep -m1 'imp->db_pass' ".$this->merlin_conf_file."|/bin/awk -F = {'print $2'}", $db_pass, $retval);
		exec("/bin/grep -m1 'imp->db_database' ".$this->merlin_conf_file."|/bin/awk -F = {'print $2'}", $db_database, $retval);

		$this->db_type = !empty($db_type) ? $this->clean_str($db_type[0]) : false;
		$this->db_host = !empty($db_host) ? $this->clean_str($db_host[0]) : false;
		$this->db_user = !empty($db_user) ? $this->clean_str($db_user[0]) : false;
		$this->db_pass = !empty($db_pass) ? $this->clean_str($db_pass[0]) : false;
		$this->db_database = !empty($db_database) ? $this->clean_str($db_database[0]) : false;

		# verify that we have all database info
		# assuming pass might be empty
		if (empty($this->db_type) || empty($this->db_host) ||
			empty($this->db_user) || empty($this->db_database))
		{
			echo "ERROR: Unable to connect to database - some information is missing\n";
			if($this->DEBUG) echo "db_type: ".print_r($db_type)."\ndb_host: $db_host\ndb_user: $db_user\ndb_database: $db_database\n";
			exit(1);
		}
		$this->db_connect();
	}

	/**
	*	Clean a parsed string, ie trim and remove "'" + ;
	*/
	public function clean_str($str)
	{
		if (empty($str))
			return false;
		$str = trim($str);
		$str = str_replace("'", "", $str);
		$str = str_replace(";", "", $str);
		return $str;
	}

	/**
	 * Connect to database
	 */
	public function db_connect()
	{
		if($this->db_type !== 'mysql') {
			die("Only mysql is supported as of yet.\n");
		}

		$this->db = mysql_connect
			($this->db_host, $this->db_user, $this->db_pass);

		if ($this->db === false)
			return(false);

		return mysql_select_db($this->db_database);
	}

	# fetch a single row to indexed array
	public function sql_fetch_row($resource)
	{
		return(mysql_fetch_row($resource));
	}

	# fetch a single row to associative array
	public function sql_fetch_array($resource)
	{
		return(mysql_fetch_array($resource, MYSQL_ASSOC));
	}

	/**
	*	Fetch a single row as an object
	*/
	public function sql_fetch_object($resource=false)
	{
		return mysql_fetch_object($resource);
	}

	/**
	* Return nr of rows returned from a query
	*/
	public function sql_num_rows($resource=false)
	{
		return mysql_num_rows($resource);
	}

	public function sql_escape_string($string)
	{
		return mysql_real_escape_string($string);
	}

	# execute an SQL query with error handling
	public function sql_exec_query($query)
	{
		if(empty($query))
			return(false);

		# workaround for now
		if($this->db === false) {
			$this->gui_db_connect();
		}

		$result = mysql_query($query, $this->db);
		if($result === false) {
			echo "SQL query failed with the following error message;<br />\n" .
			mysql_error() . "<br />\n";
			if($this->DEBUG) echo "Query was;<br />\n<b>$query</b><br />\n";
		}

		return($result);
	}

	/**
	 * Reads a configuration file in the format variable=value
	 * and returns it in an array.
	 * lines beginning with # are considered to be comments
	 * @param $config_file The configuration file to parse
	 * @return Array of key => value type on success, false on errors
	 */
	public function parse_config_file($config_file) {
		$config_file = trim($config_file);
		if (empty($config_file)) {
			return false;
		}

		if (!strstr($config_file, $this->nagios_cfg_path)) {
			$config_file = $this->nagios_cfg_path.'/'.$config_file;
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
		$cgi_config_file = $this->nagios_cfg_path."/cgi.cfg";
		$user_data = false;
		$user_list = array();
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
				if (!in_array($user, $user_list)) {
					$user_list[] = $user;
				}
			}
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
		$path = realpath($this->prefix."/cli-helpers/htpasswd-import.php");
		if (!file_exists($path)) {
			die("Unable to find htpasswd-import class so this script will now terminate\n");
			exit(1);
		}
		$no_auto_import = true;
		require_once($path);
		$passwd_import = new htpasswd_importer();
		$passwd_import->import_hashes($this->nagios_cfg_path.'/htpasswd.users');

		$config_data = $this->fetch_nagios_users();

		# All db fields that should be set
		# according to data in cgi.cfg
		$auth_fields = self::$auth_fields;

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
				$this->edit_user_data($user, $auth_data);
			}
		}
	}

	/**
	*	Check if user exists and if so we pass the supplied
	* 	$options data to insert_user_auth_data() to let
	* 	it decide if to update or insert.
	*/
	public function edit_user_data($username=false, $options=false)
	{
		if (empty($username) || empty($options))
			return false;
		$username = trim($username);
		$result = false;

		$auth_fields = self::$auth_fields;

		# authorization data fields and order
		$auth_options = false;

		# check that we have the correct number of auth options
		# return false otherwise
		if (count($options) != count($auth_fields)) {
			return false;
		}

		# merge the two arrays into one with auth_fields as key
		for ($i=0;$i<count($options);$i++) {
			$auth_options[$auth_fields[$i]] = $options[$i];
		}

		$sql = "SELECT * FROM ".$this->db_database.".users WHERE";
		$sql .= " username='".$this->sql_escape_string($username)."'";
		$res = $this->sql_exec_query($sql);
		if ($res !== false && $this->sql_num_rows($res)) {
			# user found in db
			# does authorization data exist for this user?
			$user = $this->sql_fetch_object($res);
			$result = $this->insert_user_auth_data($user->id, $auth_options);
		} else {
			# this should never happen
			$result = "Tried to save authorization data for a non existing user.\n";
		}

		return array($result);
	}

	/**
	*	This method is what actually takes care of the
	* 	insert/update of the auth_data.
	* 	If authorization credentials has changed for an existing
	* 	user, the data will be updated
	*/
	public function insert_user_auth_data($user_id=false, $options=false)
	{
		if (empty($user_id) || empty($options))
			return false;
		$user_id = (int)$user_id;

		# check if we already have any data
		$sql = "SELECT * FROM ".$this->db_database.".ninja_user_authorization";
		$sql .= " WHERE user_id=".(int)$user_id;
		$res = $this->sql_exec_query($sql);
		if ($res !== false && $this->sql_num_rows($res)) {
			# user exists, update authorization data
			$sql = "UPDATE ".$this->db_database.".ninja_user_authorization SET ";
			$sql_upd = false;
			foreach	($options as $field => $value) {
				$sql_upd[] = $field .' = '.(int)$value . ' ';
			}
			if (!empty($sql_upd)) {
				$sql .= implode(', ', $sql_upd);
				$sql .= " WHERE user_id = ".$user_id;
			} else {
				return false;
			}
		} else {
			# create new record
			$sql = "INSERT INTO ".$this->db_database.".ninja_user_authorization";
			$sql .= " (user_id, `".implode('`,`', array_keys($options))."`) ";
			$values = array_values($options);
			$ok_values = false;
			foreach ($values as $val) {
				$ok_values[] = (int)$val;
			}
			$sql .= "VALUES(".$user_id.", ".implode(',', $ok_values).")";
		}

		# done, save it
		return $this->sql_exec_query($sql);
	}
}

$import = new ninja_auth_import();
$import->prefix = $prefix;
$import->insert_user_data();
?>

