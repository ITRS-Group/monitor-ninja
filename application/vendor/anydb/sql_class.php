<?php
require_once (dirname( __FILE__) . '/anyDB.php');

class sql_class extends BasicDB {
	var $database 	= false;
	var $user 		= false;
	var $password 	= false;
	var $port 		= 3360; // not used
	var $host 		= false;
	var $_dbType	= false;
	var $db 		= false;

	function sql_class($db='', $user='', $passwd=false, $port=0, $host='', $type='mysql')
	{
		$database 	= trim($db);
		$host 		= trim($host);
		$user 		= trim($user);
		$password 	= $passwd;
		$db_type 	= trim($type);

		$this->database = !empty($database) ? $database : $this->database;
		$this->user 	= !empty($user) ? $user : $this->user;
		$this->password = $password===false ? $this->password : $password;
		$this->port 	= !empty($port) ? $port : $this->port;
		$this->host 	= !empty($host) ? $host : $this->host;
		$this->_dbType 	= !empty($db_type) ? $db_type : $this->_dbType;
		$this->db = $this->connect_db();
	}

	function connect_db()
	{
		$db = anyDB::getLayer(strtoupper($this->_dbType),'', $this->_dbType);
		if (!$db->connect($this->host, $this->database, $this->user, $this->password)) {
			echo "ERROR: Unable to connect to ".$this->host."<br />\n";
			echo $db->error;
			return false;
		}
		return $db;
	}
}

?>