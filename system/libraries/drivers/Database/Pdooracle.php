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
		parent::__construct($result, $link, $object=true, $sql);
		if ($this->valid())
			$this->latest_row = $this->result->fetch($this->fetch_type);
	}
}
