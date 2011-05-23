<?php defined('SYSPATH') or die('No direct access allowed.');

require_once( dirname(__FILE__)."/merlin_get_kohana_db_field_metadata.php" );

class Database_Oracle_Driver extends Database_Driver {
	protected $link;

	public function __construct($config)
	{
		$this->db_config = $config;

		Kohana::log('debug', 'Oracle driver initialized');
	}

	public function connect()
	{
		if (is_resource($this->link))
			return $this->link;

		extract($this->db_config['connection']);

		$connect = ($this->db_config['persistent'] == TRUE) ? 'oci_pconnect' : 'oci_connect';

		if ($conn_str)
			$this->link = $connect($user, $pass, $conn_str);
		else
			$this->link = $connect($user, $pass, "//$host".($port?":$port":'')."/$database");

		if( ($charset = $this->db_config['character_set']) )
		{
			$this->set_charset($charset);
		}

		if (!$this->link) {
			throw new Kohana_Database_Exception('database.error', "Couldn't connect to database");
		} else if (($e = oci_error($this->link))) {
			throw new Kohana_Database_Exception('database.error', $e['message']);
		}
		// Clear password after successful connect
		$this->db_config['connection']['pass'] = NULL;

		return $this->link;
	}

	public function query($sql)
	{
		// Rewrite LIMIT/OFFSET to oracle compatible thingies
		$matches = false;
		if (preg_match('/(.*) LIMIT (\d+)( OFFSET (\d+))?$/s', $sql, $matches)) {
			$sql = trim($matches[1]);
			$offset = isset($matches[4]) ? $matches[4] : 0;
			$limit = $matches[2] + $offset;
			if ($limit) {
				$sql = "SELECT foo.*, rownum AS rnum FROM ({$matches[1]}) foo WHERE rownum <= $limit";
				if ($offset)
					$sql = "SELECT bar.* FROM ($sql) bar WHERE rnum > $offset";
			}
		}
		$sql = str_replace('NOW()', 'SYSDATE', $sql);
		// Rewrite UNIX_TIMESTAMP
		$sql = str_replace('UNIX_TIMESTAMP()', "((sysdate - to_date('01-JAN-1970', 'DD-MON-YYYY')) * 86400)", $sql);

		// LCASE is called LOWER
		$sql = str_replace('LCASE', 'LOWER', $sql);

		return new Oracle_Result(false, $this->link, $this->db_config['object'], $sql);
	}

	public function set_charset($charset)
	{
		// TODO: this should be implemented...
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
		$res = $db->query($sql);
		$list = array();
		foreach ($res->result(FALSE) as $row) {
			$list[] = current($row);
		}

		unset($res);
		$tables = $list;
		return $tables;
	}

	// Most errors are catched and thrown in Oracle_Result, so this will
	// probably fail most of the time. It also appears to be unused, though.
	public function show_error()
	{
		$err = oci_error();
		return isset($err['message']) ? $err['message'] : 'Unknown error';
	}

	public function list_fields($table)
	{
		static $tables = array();

		if (empty($tables[$table]))
		{
			foreach ($this->field_data($table) as $row)
			{
				// Make an associative array
				$tables[$table][$row->Field] = $this->sql_type($row->Type);

				if ($row->Key === 'PRI' AND $row->Extra === 'auto_increment') {
					// For sequenced (AUTO_INCREMENT) tables
					$tables[$table][$row->Field]['sequenced'] = TRUE;
				}

				if ($row->Null === 'YES') {
					// Set NULL status
					$tables[$table][$row->Field]['null'] = TRUE;
				}
			}
		}

		if (!isset($tables[$table]))
			throw new Kohana_Database_Exception('database.table_not_found', $table);

		return $tables[$table];
	}

	public function field_data($table)
	{
		# UGLY FUGLY HACK: return pre-generated field data.
		$columns = merlin_get_kohana_db_field_metadata();
		return $columns[$table];
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

	public function stmt_prepare($sql = '')
	{
		is_object($this->link) or $this->connect();
		return new Kohana_Oracle_Statement($sql, $this->link);
	}
}

/**
 * Kohana's support for prepared statements seems to be less-than-well defined.
 * This tries to stick to what Mysqli does (which is also what Pgsql does).
 *
 * Because I'm lazy, there's no support for binding output parameters, only
 * input.
 */
class Kohana_Oracle_Statement {
	protected $link = null;
	protected $stmt;
	protected $var_names = array();
	protected $var_values = array();

	public function __construct($sql, $link)
	{
		$this->link = $link;
		$this->stmt = oci_parse($sql);
	}

	/**
	 * The first param is for a "type hint string" used in other drivers
	 * ("si" means "a string and an int"). We don't give a crap about that.
	 */
	public function bind_params($unused, $params)
	{
		$this->var_names = array_keys($params);
		$this->var_values = array_values($params);
		foreach ($params as $key => $val) {
			oci_bind_by_name($this->stmt, $key, $params[$key]);
		}
	}

	public function execute()
	{
		$this->stmt->execute();
		return $this->stmt;
	}
}

class Oracle_Result extends Database_Result {
	protected $fetch_array = false;
	protected $latest_row = null;

	public function current()
	{
		$obj = new StdClass();
		if ($this->latest_row)
			foreach($this->latest_row as $key => $var) {
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
		$this->latest_row = oci_fetch_assoc($this->result);
		$this->current_row++;
		return $this;
	}

	public function rewind()
	{
		if ($this->total_rows && $this->current_row > 0) {
			$this->current_row=0;
			oci_execute($this->result, OCI_COMMIT_ON_SUCCESS);
			$this->latest_row = oci_fetch_assoc($this->result);
		}
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
		if ($result = oci_parse($link, $sql)) {
			if (!@oci_execute($result, OCI_COMMIT_ON_SUCCESS)) {
				$e = oci_error($result);
				// code 923 means no FROM found
				// this workaround sometimes works
				if ($e['code'] == 923) {
					$sql .= "\nFROM DUAL";
					$result = oci_parse($link, $sql);
					if (!@oci_execute($result, OCI_COMMIT_ON_SUCCESS))
						throw new Kohana_Database_Exception('database.error', $e['message']);

				}
				else {
					throw new Kohana_Database_Exception('database.error', $e['message'].' - SQL=['.$sql.']');
				}
			}

			if (preg_match('/^\s*(SHOW|DESCRIBE|SELECT|PRAGMA|EXPLAIN)/is', $sql)) {
				$this->result = $result;
				$this->current_row = 0;

				$this->total_rows = $this->pdo_row_count();

				if ($this->valid())
					$this->latest_row = oci_fetch_assoc($this->result);
			}
			elseif (preg_match('/^\s*INSERT +INTO +([^ (,]+)/is', $sql, $match)) {
				$tblname = $match[1].'_id_SEQ';
				$tblname = substr($tblname, 0, 30);
				try {
					$rowcount = new Oracle_Result(false, $link, $object, "SELECT $tblname.CURRVAL AS ID FROM DUAL");
					$this->insert_id = (int)$rowcount->current()->id;
				} catch (Kohana_Database_Exception $e) {
					$this->insert_id = 0;
				}
			}
			elseif (preg_match('/^\s*(DELETE|INSERT|UPDATE)/is', $sql)) {
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
