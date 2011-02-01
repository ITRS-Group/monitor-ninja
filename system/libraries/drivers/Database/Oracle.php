<?php defined('SYSPATH') or die('No direct access allowed.');

class Database_Oracle_Driver extends Database_Driver {
	protected $link;

	public function __construct($config)
	{
		$this->db_config = $config;

		Kohana::log('debug', 'PDO Oracle driver initialized');
	}

	public function connect()
	{
		if (is_resource($this->link))
			return $this->link;

		extract($this->db_config['connection']);

		if (!$dsn)
			throw new Kohana_Database_Exception('database.error',
				"This driver (".__CLASS__.") requires the dsn property to be set.");
		// Do not even try to set properties in the last PDO argument - PDO_OCI
		// is busted.
		try {
			$this->link = oci_connect($user, $pass, "//$host/$database");

			if( ($charset = $this->db_config['character_set']) )
			{
				$this->set_charset($charset);
			}
		}
		catch (PDOException $e) {
			throw new Kohana_Database_Exception('database.error', $e->getMessage());
		}
		// Clear password after successful connect
		$this->db_config['connection']['pass'] = NULL;

		return $this->link;
	}

	public function query($sql)
	{
		$sth = oci_parse($this->link, $sql);
		return new Oracle_Result($sth, $this->link, $this->db_config['object'], $sql);
	}

	public function set_charset($charset)
	{
		// TODO: this should be implemented...
	}

	public function mylimit($sql, $limit, $offset = 0)
	{
		$limit = $limit + $offset;
		$sql = "SELECT *, rownum AS rnum FROM ($sql) WHERE rnum <= $limit";
		if ($offset != 0) {
			$sql = "SELECT * FROM ($sql) WHERE rnum > $offset";
		}
		return $sql;
	}

	public function escape_str($str)
	{
		if (!$this->db_config['escape'])
			return $str;

		return str_replace("'", "''", $str);
	}

	public function list_tables(Database $db)
	{
		$sql = 'SELECT * FROM ALL_TABLES';
		try {
			$res = $db->query($sql);
			$list = array();
			foreach ($res->result(FALSE) as $row) {
				$list[] = current($row);
			}

			unset($res);
			$tables = $list;
			return $tables;
		} catch (PDOException $e) {
			throw new Kohana_Database_Exception('database.error', $e->getMessage());
		}
	}

	public function show_error()
	{
		$err = oci_error();
		return isset($err['message']) ? $err['message'] : 'Unknown error';
	}

	# unimplemented an unused
	public function escape_table($table)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function escape_column($column)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function limit($limit, $offset = 0)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function compile_select($database)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function list_fields($table)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function field_data($table)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}
}

class Oracle_Result extends Database_Result {
	protected $fetch_array = false;
	protected $latest_row = null;

	public function current()
	{
		$obj = new StdClass();
		$vars = get_object_vars($this->latest_row);
		if ($vars) foreach($vars as $key => $var) {
			$name = strtolower($key);
			if (is_object($var)) {
				$val = $var->load();
				$var->close();
				$var = $val;
			}
			$obj->$name = $var;
		}
		if ($this->fetch_array)
			return get_object_vars($obj);
		return $obj;
	}

	public function next()
	{
		$this->latest_row = oci_fetch_object($this->result);
		$this->current_row++;
		return $this;
	}

	public function valid()
	{
		return ($this->current_row < $this->total_rows);
	}

	protected function pdo_row_count()
	{
		$count = 0;
		while (oci_fetch_row($this->result)) {
			$count++;
		}

		// The query must be re-fetched now.
		oci_execute($this->result, OCI_COMMIT_ON_SUCCESS);
		return $count;
	}

	public function __construct($result, $link, $object=true, $sql)
	{
		// Rewrite LIMIT/OFFSET to oracle compatible thingies
		$matches = false;
		if (preg_match('/(.*) LIMIT (\d+)( OFFSET (\d+))?$/', $sql, $matches)) {
			$query = trim($matches[1]);
			$offset = isset($matches[4]) ? $matches[4] : 0;
			$limit = $matches[2] + $offset;
			if ($limit) {
				$sql = "SELECT foo.*, rownum AS rnum FROM ({$matches[1]}) AS foo WHERE rownum <= $limit";
				if ($offset)
					$sql = "SELECT bar.* FROM ($sql) bar WHERE rnum > $offset";
			}
			$sql = $query;
		}
		// Rewrite UNIX_TIMESTAMP
		$sql = preg_replace('/UNIX_TIMESTAMP\(\)/', "((sysdate - to_date('01-JAN-1970', 'DD-MON-YYYY')) * 86400)", $sql);

		if ($result = oci_parse($link, $sql)) {
			if (!@oci_execute($result, OCI_COMMIT_ON_SUCCESS)) {
				$e = oci_error($result);
				// code 923 means no FROM found
				// this workaround sometimes works
				if ($e['code'] == 923) {
					$sql .= "\nFROM DUAL";
					$result = oci_parse($link, $sql);
					if (!@oci_execute($result, OCI_COMMIT_ON_SUCCESS))
						throw new Kohana_Database_Exception('database.error', $e->getMessage());

				}
				else {
					throw new Kohana_Database_Exception('database.error', $e['message'].' - SQL=['.$sql.']');
				}
			}

			if (preg_match('/^(SHOW|DESCRIBE|SELECT|PRAGMA|EXPLAIN)/i', $sql)) {
				$this->result = $result;
				$this->current_row = 0;

				$this->total_rows = $this->pdo_row_count();

				$this->fetch_type = ($object === TRUE) ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;
				if ($this->valid())
					$this->latest_row = oci_fetch_object($this->result);
			} elseif (preg_match('/^(DELETE|INSERT|UPDATE)/i', $sql)) {
				# completely broken, but I don't care
				$this->insert_id  = 0;
			}
		} else {
			// SQL error
			$err = oci_error();
			throw new Kohana_Database_Exception
				('database.error', $err['message'].' - SQL=['.$sql.']');
		}

		$this->sql = $sql;
	}

	public function result($object = true, $type = false)
	{
		if ($object == false)
			$this->fetch_array = true;
		return $this;
	}

	# unimplemented and unused
	public function result_array($object = null, $type = false)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function list_fields()
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function seek($offset)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}
}
