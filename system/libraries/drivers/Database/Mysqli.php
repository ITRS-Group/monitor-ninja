<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * MySQLi Database Driver
 *
 * $Id: Mysqli.php 3917 2009-01-21 03:06:22Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Mysqli_Driver extends Database_Driver {

	// Database connection link
	protected $link;
	protected $db_config;
	protected $statements = array();

	/**
	 * Sets the config for the class.
	 *
	 * @param  array  database configuration
	 */
	public function __construct($config)
	{
		$this->db_config = $config;

		Kohana::log('debug', 'MySQLi Database Driver Initialized');
	}

	/**
	 * Closes the database connection.
	 */
	public function __destruct()
	{
		is_object($this->link) and $this->link->close();
	}

	public function connect()
	{
		// Check if link already exists
		if (is_object($this->link))
			return $this->link;

		// Import the connect variables
		extract($this->db_config['connection']);

		// Build the connection info
		$host = isset($host) ? $host : $socket;

		// Make the connection and select the database
		if ($this->link = new mysqli($host, $user, $pass, $database, $port))
		{
			if ($charset = $this->db_config['character_set'])
			{
				$this->set_charset($charset);
			}

			// Clear password after successful connect
			$this->config['connection']['pass'] = NULL;

			return $this->link;
		}

		return FALSE;
	}

	public function query($sql)
	{
		// Only cache if it's turned on, and only cache if it's not a write statement
		if ($this->db_config['cache'] AND ! preg_match('#\b(?:INSERT|UPDATE|REPLACE|SET)\b#i', $sql))
		{
			$hash = $this->query_hash($sql);

			if ( ! isset(self::$query_cache[$hash]))
			{
				// Set the cached object
				self::$query_cache[$hash] = new Kohana_Mysqli_Result($this->link, $this->db_config['object'], $sql);
			}

			// Return the cached query
			return self::$query_cache[$hash];
		}

		return new Kohana_Mysqli_Result($this->link, $this->db_config['object'], $sql);
	}

	public function set_charset($charset)
	{
		if ($this->link->set_charset($charset) === FALSE)
			throw new Kohana_Database_Exception('database.error', $this->show_error());
	}

	public function escape_table($table)
	{
		if (!$this->db_config['escape'])
			return $table;

		if (stripos($table, ' AS ') !== FALSE)
		{
			// Force 'AS' to uppercase
			$table = str_ireplace(' AS ', ' AS ', $table);

			// Runs escape_table on both sides of an AS statement
			$table = array_map(array($this, __FUNCTION__), explode(' AS ', $table));

			// Re-create the AS statement
			return implode(' AS ', $table);
		}
		return '`'.str_replace('.', '`.`', $table).'`';
	}

	public function escape_column($column)
	{
		if (!$this->db_config['escape'])
			return $column;

		if (strtolower($column) == 'count(*)' OR $column == '*')
			return $column;

		// This matches any modifiers we support to SELECT.
		if ( ! preg_match('/\b(?:rand|all|distinct(?:row)?|high_priority|sql_(?:small_result|b(?:ig_result|uffer_result)|no_cache|ca(?:che|lc_found_rows)))\s/i', $column))
		{
			if (stripos($column, ' AS ') !== FALSE)
			{
				// Force 'AS' to uppercase
				$column = str_ireplace(' AS ', ' AS ', $column);

				// Runs escape_column on both sides of an AS statement
				$column = array_map(array($this, __FUNCTION__), explode(' AS ', $column));

				// Re-create the AS statement
				return implode(' AS ', $column);
			}

			return preg_replace('/[^.*]+/', '`$0`', $column);
		}

		$parts = explode(' ', $column);
		$column = '';

		for ($i = 0, $c = count($parts); $i < $c; $i++)
		{
			// The column is always last
			if ($i == ($c - 1))
			{
				$column .= preg_replace('/[^.*]+/', '`$0`', $parts[$i]);
			}
			else // otherwise, it's a modifier
			{
				$column .= $parts[$i].' ';
			}
		}
		return $column;
	}	

	public function stmt_prepare($sql = '')
	{
		is_object($this->link) or $this->connect();
		return new Kohana_Mysqli_Statement($sql, $this->link);
	}

	public function escape_str($str)
	{
		if (!$this->db_config['escape'])
			return $str;

		is_object($this->link) or $this->connect();

		return $this->link->real_escape_string($str);
	}


	public function limit($limit, $offset = 0)
	{
		return 'LIMIT '.$offset.', '.$limit;
	}

	public function compile_select($database)
	{
		$sql = ($database['distinct'] == TRUE) ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';

		if (count($database['from']) > 0)
		{
			// Escape the tables
			$froms = array();
			foreach ($database['from'] as $from)
			{
				$froms[] = $this->escape_column($from);
			}
			$sql .= "\nFROM ";
			$sql .= implode(', ', $froms);
		}

		if (count($database['join']) > 0)
		{
			foreach($database['join'] AS $join)
			{
				$sql .= "\n".$join['type'].'JOIN '.implode(', ', $join['tables']).' ON '.$join['conditions'];
			}
		}

		if (count($database['where']) > 0)
		{
			$sql .= "\nWHERE ";
		}

		$sql .= implode("\n", $database['where']);

		if (count($database['groupby']) > 0)
		{
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $database['groupby']);
		}

		if (count($database['having']) > 0)
		{
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $database['having']);
		}

		if (count($database['orderby']) > 0)
		{
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $database['orderby']);
		}

		if (is_numeric($database['limit']))
		{
			$sql .= "\n";
			$sql .= $this->limit($database['limit'], $database['offset']);
		}

		return $sql;
	}

	public function list_tables(Database $db)
	{
		static $tables;

		if (empty($tables) AND $query = $db->query('SHOW TABLES FROM '.$this->escape_table($this->db_config['connection']['database'])))
		{
			foreach ($query->result(FALSE) as $row)
			{
				$tables[] = current($row);
			}
		}

		return $tables;
	}

	public function show_error()
	{
		return $this->link->error;
	}

	public function list_fields($table)
	{
		static $tables;

		if (empty($tables[$table]))
		{
			foreach ($this->field_data($table) as $row)
			{
				// Make an associative array
				$tables[$table][$row->Field] = $this->sql_type($row->Type);

				if ($row->Key === 'PRI' AND $row->Extra === 'auto_increment')
				{
					// For sequenced (AUTO_INCREMENT) tables
					$tables[$table][$row->Field]['sequenced'] = TRUE;
				}

				if ($row->Null === 'YES')
				{
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
		$columns = array();
		$query = $this->link->query('SHOW COLUMNS FROM '.$this->escape_table($table));

		if (is_object($query))
		{
			while ($row = $query->fetch_object())
			{
				$columns[] = $row;
			}
		}

		return $columns;
	}

} // End Database_Mysqli_Driver Class

/**
 * MySQLi Result
 */
class Kohana_Mysqli_Result extends Database_Result {

	// Database connection
	protected $link;

	// Data fetching types
	protected $fetch_type  = 'mysqli_fetch_object';
	protected $return_type = MYSQLI_ASSOC;

	/**
	 * Sets up the result variables.
	 *
	 * @param  object    database link
	 * @param  boolean   return objects or arrays
	 * @param  string    SQL query that was run
	 */
	public function __construct($link, $object = TRUE, $sql)
	{
		$this->link = $link;

		if ( ! $this->link->multi_query($sql))
		{
			// SQL error
			throw new Kohana_Database_Exception('database.error', $this->link->error.' - '.$sql);
		}
		else
		{
			$this->result = $this->link->store_result();

			// If the query is an object, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
			if (is_object($this->result))
			{
				$this->current_row = 0;
				$this->total_rows  = $this->result->num_rows;
				$this->fetch_type = ($object === TRUE) ? 'fetch_object' : 'fetch_array';
			}
			elseif ($this->link->error)
			{
				// SQL error
				throw new Kohana_Database_Exception('database.error', $this->link->error.' - '.$sql);
			}
			else
			{
				// Its an DELETE, INSERT, REPLACE, or UPDATE query
				$this->insert_id  = $this->link->insert_id;
				$this->total_rows = $this->link->affected_rows;
			}
		}

		// Set result type
		$this->result($object);

		// Store the SQL
		$this->sql = $sql;
	}

	/**
	 * Magic __destruct function, frees the result.
	 */
	public function __destruct()
	{
		if (is_object($this->result))
		{
			$this->result->free_result();

			// this is kinda useless, but needs to be done to avoid the "Commands out of sync; you
			// can't run this command now" error. Basically, we get all results after the first one
			// (the one we actually need) and free them.
			if (is_resource($this->link) AND $this->link->more_results())
			{
				do
				{
					if ($result = $this->link->store_result())
					{
						$result->free_result();
					}
				} while ($this->link->next_result());
			}
		}
	}

	public function result($object = TRUE, $type = MYSQLI_ASSOC)
	{
		$this->fetch_type = ((bool) $object) ? 'fetch_object' : 'fetch_array';

		// This check has to be outside the previous statement, because we do not
		// know the state of fetch_type when $object = NULL
		// NOTE - The class set by $type must be defined before fetching the result,
		// autoloading is disabled to save a lot of stupid overhead.
		if ($this->fetch_type == 'fetch_object')
		{
			$this->return_type = (is_string($type) AND class_exists($type, true)) ? $type : 'stdClass';
		}
		else
		{
			$this->return_type = $type;
		}

		return $this;
	}

	public function as_array($object = NULL, $type = MYSQLI_ASSOC)
	{
		return $this->result_array($object, $type);
	}

	public function result_array($object = NULL, $type = MYSQLI_ASSOC)
	{
		$rows = array();

		if (is_string($object))
		{
			$fetch = $object;
		}
		elseif (is_bool($object))
		{
			if ($object === TRUE)
			{
				$fetch = 'fetch_object';

				// NOTE - The class set by $type must be defined before fetching the result,
				// autoloading is disabled to save a lot of stupid overhead.
				$type = (is_string($type) AND class_exists($type, true)) ? $type : 'stdClass';
			}
			else
			{
				$fetch = 'fetch_array';
			}
		}
		else
		{
			// Use the default config values
			$fetch = $this->fetch_type;

			if ($fetch == 'fetch_object')
			{
				$type = (is_string($type) AND class_exists($type, true)) ? $type : 'stdClass';
			}
		}

		if ($this->result->num_rows)
		{
			// Reset the pointer location to make sure things work properly
			$this->result->data_seek(0);

			while ($row = $this->result->$fetch($type))
			{
				$rows[] = $row;
			}
		}

		return isset($rows) ? $rows : array();
	}

	public function list_fields($with_table_names = false)
	{
		$field_names = array();
		while ($field = $this->result->fetch_field())
		{
			$name = "";
			if($with_table_names)
			{
				$name .= $field->table . '.';
			}
			$name .= $field->name;
			$field_names[] = $name;
		}

		return $field_names;
	}

	public function seek($offset)
	{
		if ( ! $this->offsetExists($offset))
			return FALSE;

		$this->result->data_seek($offset);

		return TRUE;
	}

	public function offsetGet($offset)
	{
		if ( ! $this->seek($offset))
			return FALSE;

		// Return the row
		$fetch = $this->fetch_type;
		return $this->result->$fetch($this->return_type);
	}

} // End Mysqli_Result Class

/**
 * MySQLi Prepared Statement (experimental)
 */
class Kohana_Mysqli_Statement {

	protected $link = NULL;
	protected $stmt;
	protected $var_names = array();
	protected $var_values = array();

	public function __construct($sql, $link)
	{
		$this->link = $link;

		$this->stmt = $this->link->prepare($sql);

		return $this;
	}

	public function __destruct()
	{
		$this->stmt->close();
	}

	// Sets the bind parameters
	public function bind_params($param_types, $params)
	{
		$this->var_names = array_keys($params);
		$this->var_values = array_values($params);
		call_user_func_array(array($this->stmt, 'bind_param'), array_merge($param_types, $var_names));

		return $this;
	}

	public function bind_result($params)
	{
		call_user_func_array(array($this->stmt, 'bind_result'), $params);
	}

	// Runs the statement
	public function execute()
	{
		foreach ($this->var_names as $key => $name)
		{
			$$name = $this->var_values[$key];
		}
		$this->stmt->execute();
		return $this->stmt;
	}
}
