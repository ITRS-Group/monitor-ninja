<?php
class htpasswd_importer
{
	private $htpasswd_file = "/opt/monitor/etc/htpasswd.users";
	public $overwrite = false;
	public $passwd_ary = array();
	private $existing_ary = array();
	private $db_user = "merlin";
	private $db_pass = "merlin";
	private $db_name = "merlin";
	private $db_port = 3306;
	private $db_host = "localhost";
	private $db_table = "users";
	private $db_type = "mysql";
	private $db = false;
	private $DEBUG = false;

	public function __construct($htpasswd_file = false)
	{
		$this->htpasswd_file = $htpasswd_file;
		$this->parse_file($this->htpasswd_file);
	}

	public function set_option($k, $v)
	{
		if (!isset($this->$k))
			return false;

		$this->$k = $v;
		return true;
	}

	private function get_existing_users()
	{
		if (!$this->db) {
			$this->db_connect();
		}

		$query = 'SELECT username, password_algo, password ' .
			'FROM ' . $this->db_table;
		$result = $this->sql_exec_query($query);
		while ($ary = $this->sql_fetch_array($result)) {
			$this->existing_ary[$ary['username']] = array
				('hash' => $ary['password'], 'algo' => $ary['password_algo']);
		}
	}

	# connects to and selects database. false on error, true on success
	public function db_connect()
	{
		switch ($this->db_type)
		{
			case 'mysql':
				$this->db = mysql_connect
					($this->db_host, $this->db_user, $this->db_pass);

				if ($this->db === false)
					return(false);

				return mysql_select_db($this->db_name);
				break;
			case 'pgsql':
				$this->db = pg_connect('host='.$this->db_host.' dbname='.$this->db_name.' user='.$this->db_user.' password='.$this->db_pass);
				if ($this->db === false)
					return(false);
				return $this->db;
				break;
			default:
				die("Only mysql and postgres are supported as of yet.<br />\n");
		}
	}

	# fetch a single row to associative array
	public function sql_fetch_array($resource) {
		switch ($this->db_type)
		{
			case 'mysql':
				return(mysql_fetch_array($resource, MYSQL_ASSOC));
				break;
			case 'pgsql':
				return pg_fetch_assoc($resource);
				break;
			default: return false;
		}
	}

	# execute an SQL query with error handling
	public function sql_exec_query($query)
	{
		if(empty($query))
			return(false);

		# workaround for now
		if($this->db === false) {
			$this->db_connect();
		}
		$error = false;
		switch ($this->db_type)
		{
			case 'mysql':
				$result = mysql_query($query, $this->db);
				$error = mysql_error();
				break;
			case 'pgsql':
				$result = pg_query($this->db, $query);
				$error = pg_last_error();
				break;
			default: return false;
		}
		if($result === false) {
			echo "SQL query failed with the following error message:<br />\n" .
				$error . "<br />\n";
			if($this->DEBUG) echo "Query was:<br />\n<b>$query</b><br />\n";
		}

		return($result);
	}

	public function sql_escape_string($string)
	{
		switch ($this->db_type)
		{
			case 'mysql':
				return mysql_real_escape_string($string);
				break;
			case 'pgsql':
				return pg_escape_string($string);
				break;
			default: return false;
		}
	}

	public function write_hashes_to_db()
	{
		$this->get_existing_users();

		if (!$this->db) {
			$this->db_connect();
		}

		foreach ($this->passwd_ary as $user => $ary) {
			$hash = $ary['hash'];
			$algo = $ary['algo'];
			$is_new = false; 	# keep track if user is new and should be assigned
								# the login role

			# if we're not supposed to overwrite user's passwords
			# and this user already exist, just move along
			if (isset($this->existing_ary[$user])) {
				if (!$this->overwrite)
					continue;
				if ($hash == $this->existing_ary[$user]['hash'] &&
					$algo == $this->existing_ary[$user]['algo'])
				{
					continue;
				}

				$query = "UPDATE $this->db_table SET " .
					"password_algo = '" . $this->sql_escape_string($algo) . "', " .
					"password = '" . $this->sql_escape_string($hash) . "' " .
					"WHERE username = '" . $this->sql_escape_string($user) . "'";
			} else {
				$query = 'INSERT INTO ' . $this->db_table .
					'(username, password_algo, password) VALUES(' .
					"'" . $this->sql_escape_string($user) . "', '" .
					$this->sql_escape_string($algo) . "', '" .
					$this->sql_escape_string($hash) . "')";
					$is_new = true; # mark this as new user
			}

			$result = $this->sql_exec_query($query);
			if ($result !== false) {
				$this->add_user_role($this->sql_insert_id($result));
			}
		}

		# check for users that has been removed
		foreach ($this->existing_ary as $old => $skip) {
			if (!array_key_exists($old, $this->passwd_ary)) {
				# delete this user as it is no longer available in
				# the received list of users
				$this->sql_exec_query("DELETE FROM ".$this->db_table.
					" WHERE username='".$this->sql_escape_string($old)."'");
			}
		}
	}

	public function import_hashes($htpasswd_file = false)
	{
		$ary = $this->parse_file($htpasswd_file);
		if ($ary === false)
			return false;

		return $this->write_hashes_to_db($ary);
	}

	public function get_algo(&$hash)
	{
		if (!strncmp($hash, "{SHA}", 5)) {
			$hash = substr($hash, 5);
			return "b64_sha1";
		}
		if (!strncmp($hash, '$apr1$', 6))
			return "apr_md5";
		if (strlen($hash) === 13)
			return "crypt";

		$hash = sha1($hash);
		return "sha1";
	}

	public function parse_file($htpasswd_file = false)
	{
		if (!$htpasswd_file)
			$htpasswd_file = $this->htpasswd_file;

		if (!$htpasswd_file || !file_exists($htpasswd_file))
			return false;

		$buf = explode("\n", file_get_contents($htpasswd_file));

		foreach ($buf as $line) {
			$line = trim($line);
			if (empty($line))
				continue;

			$ary = explode(':', $line, 2);
			$hash = $ary[1];
			$algo = $this->get_algo($hash);
			$ent = array('hash' => $hash, 'algo' => $algo);
			$this->passwd_ary[$ary[0]] = $ent;
		}

		return $this->passwd_ary;
	}

	/**
	*	Return last inserted ID
	*/
	public function sql_insert_id($resource=false)
	{
		switch ($this->db_type)
		{
			case 'mysql':
				return mysql_insert_id();
				break;
			case 'pgsql':
				$insert = pg_fetch_row($resource);
				return $insert[0];
				break;
			default: return false;
		}

	}

	/**
	*	Add role for last inserted user
	*/
	public function add_user_role($user_id=false)
	{
		$user_id = (int)$user_id;
		if (!$user_id)
			return false;
		$login_role = 1;
		$sql = "INSERT INTO roles_users (user_id, role_id) ";
		$sql .= "VALUES(".$user_id.", ".$login_role.")";
		$this->sql_exec_query($sql);
	}

}

function pw_import_usage($msg = false)
{
	global $argv;

	if ($msg) {
		echo "\n$msg\n\n";
	}

	echo "Usage: $argv[0] htpasswd-file [options]\n";
	echo "Options:\n";
	echo "  --incremental        perform incremental import\n";
	echo "  --overwrite          overwrite existing entries\n";
	echo "  --db-name=<dbname>   set database name\n";
	echo "  --db-host=<dbhost>   set database host\n";
	echo "  --db-table=<dbtable> set database table\n";
	echo "  --db-user=<dbuser>   set database user\n";
	echo "  --db-pass=<dbpass>   set database password\n";
	echo "  --db-host=<dbhost>   set database host\n";
	exit(1);
}

if (PHP_SAPI === 'cli' && !isset($no_auto_import)) {
	$pwi = new htpasswd_importer;
	$imported = 0;

	if ($argc < 2) {
		pw_import_usage("No htpasswd file passed, so nothing to do");
	}
	for ($i = 1; $i < $argc; $i++) {
		$arg = $argv[$i];

		if ($arg{0} !== '-') {
			if (!$pwi->parse_file($arg)) {
				pw_import_usage("'$arg' is not a valid/readable htpasswd file");
			}
			$imported++;
			continue;
		}

		if ($arg === '--incremental' || $arg === '-i') {
			$pwi->overwrite = false;
			continue;
		}
		if ($arg === '--overwrite' || $arg === '-o') {
			$pwi->overwrite = true;
			continue;
		}

		if (strpos($arg, '=') !== false) {
			$ary = explode('=', $arg, 2);
			$arg = $ary[0];
			$opt = $ary[1];
		} elseif ($i < $argc - 1) {
			$opt = $argv[++$i];
		} else {
			pw_import_usage("Option $arg requires an argument");
		}
		exit(0);

		$pwi_arg = str_replace("-", "_", substr($arg, 2));
		if (!$pwi->set_option($pwi_arg, $opt)) {
			pw_import_usage("Failed to set option '$pwi_arg' = '$opt'");
		}
	}

	$pwi->write_hashes_to_db();
}
