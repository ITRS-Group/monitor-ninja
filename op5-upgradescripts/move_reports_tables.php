<?php

/**
 * Move old monitor_reports data (avail_config, sla_config etc)
 * into merlin.
 */
$argv = isset($argv) ? $argv : $GLOBALS['argv'];
$db_opt['type'] = 'mysql'; # mysql is the only one supported for now.
$db_opt['host'] = 'localhost';
$db_opt['user'] = isset($argv[2]) ? $argv[2] : false;
$db_opt['passwd'] = isset($argv[3]) ? $argv[3] : false;
$db_opt['old_database'] = 'monitor_reports';
$db_opt['new_database'] = 'merlin';
$db_opt['persistent'] = true;	# set to false if you're using php-cgi

$DEBUG = false;

$prefix = isset($argv[1]) ? $argv[1] : false;

if (empty($db_opt['user'])) {
	echo "No database user for monitor_reports supplied - exiting\n";
	exit(1);
}

# connects to and selects database. false on error, true on success
class old_reports
{
	public $db_opt = false;
	public $dbh = false;
	public $tables_to_convert = array(
		'avail_config',
		'avail_config_objects',
		'avail_db_version',
		'scheduled_report_periods',
		'scheduled_report_types',
		'scheduled_reports',
		'scheduled_reports_db_version',
		'sla_config',
		'sla_config_objects',
		'sla_db_version',
		'sla_periods',
		'summary_config'
	);


	public function __construct($db_opt=false)
	{
		if (empty($db_opt)) {
			echo "Missing input - exiting\n";
			exit(1);
		}
		$this->db_opt = $db_opt;
		$this->dbh = $this->db_connect();
	}

	public function db_connect() {
		$db_opt = $this->db_opt;

		if($db_opt['type'] !== 'mysql') {
			die("Only mysql is supported as of yet.<br />\n");
		}

		if(!empty($db_opt['persistent'])) {
			# use persistent connections
			$dbh = mysql_pconnect($db_opt['host'],
									  $db_opt['user'],
									  $db_opt['passwd']);
		} else {
			$dbh = mysql_connect($db_opt['host'],
									  $db_opt['user'],
									  $db_opt['passwd']);
		}

		if($dbh === false) return(false);

		return(mysql_select_db($db_opt['old_database']));
	}


	# fetch a single row to associative array
	public function sql_fetch_array($resource) {
		return(mysql_fetch_array($resource, MYSQL_ASSOC));
	}

	public function sql_escape_string($string)
	{
		return mysql_real_escape_string($string);
	}

	# execute an SQL query with error handling
	public function sql_exec_query($query) {
		if(empty($query)) return(false);

		if($this->dbh === false) {
			$this->db_connect();
		}

		$result = mysql_query($query);
		if($result === false) {
			echo "SQL query failed with the following error message;\n" .
			  mysql_error() . "\n";
			if($DEBUG) echo "Query was:\n".$query."\n";
		}

		return $result && mysql_num_rows($result) ? $result : false;
	}
}

class ninja_report_import
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
	public $DEBUG = false;
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

		if ($this->DEBUG) echo "  Successfully connected to database\n";
		return mysql_select_db($this->db_database);
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

		$result = mysql_query($query);
		if($result === false) {
			echo "SQL query failed with the following error message;<br />\n" .
			mysql_error() . "<br />\n";
			if($this->DEBUG) echo "Query was;<br />\n<b>$query</b><br />\n";
		}

		return($result);
	}
}

$old_reports = new old_reports($db_opt);
echo "Moving data from monitor_reports to merlin\n";
foreach ($old_reports->tables_to_convert as $table) {
	$sql = "SELECT * FROM ".$db_opt['old_database'].".".$table;
	$old_res = $old_reports->sql_exec_query($sql);

	if ($old_res !== false) {
		$sql = false;
		while ($row = $old_reports->sql_fetch_array($old_res)) {
			$sql[] = "INSERT INTO ".$db_opt['new_database'].".".$table." (".implode(',', array_keys($row)).") VALUES ('".implode("', '", array_values($row))."')";
		}
		unset($old_reports);
		if (!empty($sql)) {
			$merlin = new ninja_report_import();
			$merlin->prefix = $prefix;
			echo "Moving data for $table\n";
			$merlin->sql_exec_query("TRUNCATE $table");
			foreach ($sql as $query) {
				$merlin->sql_exec_query($query);
			}
			unset($merlin);
		}
		$old_reports = new old_reports($db_opt);
	}
}

unset($old_reports);
echo "Done moving data from monitor_reports to merlin\n";
?>