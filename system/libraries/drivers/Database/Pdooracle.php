<?php defined('SYSPATH') or die('No direct access allowed.');

class Database_Pdooracle_Driver extends Database_Pdogeneric_Driver {
	public function __construct($config)
	{
		parent::__construct($config);
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
		$this->dsn = $dsn;
		// Do not even try to set properties in the last PDO argument - PDO_OCI
		// is busted.
		try {
			$this->link = new PDO($this->dsn, $user, $pass);
			$this->link->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
			$this->link->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
			$this->link->setAttribute(PDO::ATTR_ORACLE_NULLS, TRUE);
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
		// FIXME: add caching
		try
		{
			$sth = $this->link->prepare($sql);
			return new Pdooracle_Result($sth, $this->link, $this->db_config['object'], $sql);
		}
		catch (PDOException $e)
		{
			throw new Kohana_Database_Exception('database.error',
			                                    $e->getMessage());
		}
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
}

class Pdooracle_Result extends Pdogeneric_Result {
	public function current()
	{
		// evil hack to force oracle to not have a bunch of open [BC]LOBs, as
		// those create segfaults
		// FIXME: I belong in PDB-OCI - will someone help me move?
		$vars = get_object_vars($this->latest_row);
		if ($vars) foreach ($vars as $key => $val) {
			if (is_resource($val)) {
				$this->latest_row->$key = stream_get_contents($val);
				fclose($val);
			}
		}
		return $this->latest_row;
	}

	public function next()
	{
		$this->latest_row = $this->result->fetch($this->fetch_type);
		$this->current_row++;
		return $this;
	}

	public function valid()
	{
		if ($this->current_row >= $this->total_rows)
			return false;
		return true;
	}

	public function __construct($result, $link, $object=true, $sql)
	{
		// Rewrite LIMIT/OFFSET to oracle compatible thingies
		$matches = false;
		if (preg_match('/(.*) LIMIT (\d+)( OFFSET (\d+))?$/', $sql, $matches)) {
			$offset = isset($matches[4]) ? $matches[4] : 0;
			$limit = $matches[2] + $offset;
			if ($limit) {
				$sql = "SELECT foo.*, rownum rnum FROM ({$matches[1]}) foo WHERE rownum <= $limit";
				if ($offset)
					$sql = "SELECT bar.* FROM ($sql) bar WHERE rnum > $offset";
			}
		}
		// Rewrite UNIX_TIMESTAMP
		$sql = preg_replace('/UNIX_TIMESTAMP\(\)/', "((sysdate - to_date('01-JAN-1970', 'DD-MON-YYYY')) * 86400)", $sql);

		if (is_object($result) OR $result = $link->prepare($sql)) {
			try {
				$result->execute();
			} catch (PDOException $e) {
				$info = $link->errorInfo();
				// code 923 means no FROM found
				// this workaround sometimes works
				if (isset($info[1]) && $info[1] == 923) {
					$sql .= ' FROM dual';
					try {
						$result->execute();
					} catch (PDOException $e) {
						throw new Kohana_Database_Exception('database.error', $e->getMessage());
					}

				}
			}

			if (preg_match('/^(SHOW|DESCRIBE|SELECT|PRAGMA|EXPLAIN)/i', $sql)) {
				$this->result = $result;
				$this->current_row = 0;

				$this->total_rows = $this->pdo_row_count();

				$this->fetch_type = ($object === TRUE) ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;
				if ($this->valid())
					$this->latest_row = $this->result->fetch($this->fetch_type);
			} elseif (preg_match('/^(DELETE|INSERT|UPDATE)/i', $sql)) {
				# completely broken, but I don't care
				$this->insert_id  = 0;
			}
		} else {
			// SQL error
			$err = $link->errorInfo();
			throw new Kohana_Database_Exception
				('database.error', $err[2].' - SQL=['.$sql.']');
		}

		$this->result($object);
		$this->sql = $sql;
	}
}
