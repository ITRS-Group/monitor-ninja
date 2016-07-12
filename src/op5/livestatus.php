<?php

require_once(__DIR__.'/auth/Auth.php');
require_once(__DIR__.'/config.php');
require_once(__DIR__.'/objstore.php');

/**
 * Custom exceptions for livestatus
 *
 **/
class op5LivestatusException extends Exception {
	/**
	 * Plain message for exceptions
	 *
	 * @var $plain_message string
	 **/
	private $plain_message;

	/**
	 * Executed query
	 *
	 * @var $query string
	 **/
	private $query;

	/**
	 * Construct
	 *
	 * @param $plain_message string
	 * @param $query string
	 * @return void
	 **/
	public function __construct($plain_message, $query = false) {
		$this->query = $query;
		$this->plain_message = $plain_message;

		$message = $plain_message;
		if($query) {
			$message .= ' <pre>'.$query.'</pre>';
		}
		parent::__construct($message);
	}

	/**
	 * Gets plain message
	 *
	 * @return string
	 **/
	public function getPlainMessage() {
		return $this->plain_message;
	}

	/**
	 * Gets query
	 *
	 * @return string
	 **/
	public function getQuery() {
		return $this->query;
	}
}

/**
 * Connects to and queries livestatus
 *
 **/
class op5Livestatus {
	/**
	 * Creates an instance of livestatus
	 *
	 * @param $config array
	 * @return object
	 **/
	static public function instance($config = null)
	{
		return op5objstore::instance()->obj_instance(__CLASS__, $config);
	}

	private $connection      = null;
	private $config          = false;

	/**
	 * Constructor
	 *
	 * @throws op5LivestatusException
	 **/
	public function __construct($config = null) {
		if(!$config) {
			$this->config = op5config::instance()->getConfig('livestatus');
		} else {
			$this->config = $config;
		}
		$connection_class = "op5livestatus_connection";

		if( isset($this->config['connection_class']) ) {
			$connection_class = $this->config['connection_class'];
		}

		if(!isset($this->config['path'])) {
			throw new op5LivestatusException(
				"No path to livestatus  configured. Please ".
				"check /etc/op5/livestatus.yml and see that the ".
				"value for 'path' points at the livestatus ".
				"socket");
		}
		$this->connection = new $connection_class($this->config['path']);
	}


	/********************************************************
	 * INTERNAL FUNCTIONS
	 *******************************************************/
	/**
	 * Performs a filtered query
	 *
	 * @param $table string
	 * @param $filter string
	 * @param $stats array
	 * @return array
	 **/
	public function stats_single($table, $filter, $stats) {
		$columns = array();
		foreach($stats as $name => $query) {
			$columns[] = $name;
			$filter .= $query;
		}
		list($res_columns,$objects,$res_count) = $this->query($table,$filter,array());

		return array_combine($columns, $objects[0]);
	}

	/**
	 * Queries livestatus
	 *
	 * @param $table string
	 * @param $filter string
	 * @param $columns array
	 * @param $options array
	 * @return array
	 **/
	public function query($table, $filter, $columns, $options = array()) {
		$query  = "GET $table\n";
		$query .= "OutputFormat: wrapped_json\n";
		$query .= "ResponseHeader: fixed16\n";
		if(isset($options['auth']) && $options['auth'] instanceof User_Model) {
			$query .= $this->auth($table, $options['auth']);
		} else if(!isset($options['auth']) || $options['auth'] != false) {
			$query .= $this->auth($table);
		}

		if(is_array($columns)) {
			$column_txt = "";
			$fetch_columns = array();
			foreach($columns as $column) {
				$parts = explode('.',$column);
				$colname = implode('_',array_slice($parts, -2)); /* service.host.name is not possible... host.name in livestatus... */
				$column_txt .= " ".$colname;
				$fetch_columns[] = $colname;
			}
			$columns = $fetch_columns;
			$query .= "Columns:$column_txt\n";
		}

		if(is_array($filter)) {
			$filter = implode("\n", array_map('trim', $filter));
		}
		$filter = trim($filter);
		if($filter) {
			$query .= $filter."\n";
		}

		$query .= "\n";

		// Connect to Livestatus. Re-trying up to three times. Sleep 0.3 seconds
		// between each try.
		$i = 0;
		while (true) {
			try {
				$start   = microtime(true);
				$rc      = $this->connection->writeSocket($query);;
				$head    = $this->connection->readSocket(16);
				$status  = substr($head, 0, 3);
				$len     = intval(trim(substr($head, 4, 15)));
				$body    = $this->connection->readSocket($len);
				if(empty($body))
					throw new op5LivestatusException(
						"Invalid query, livestatus response was empty", $query
					);
				if($status != 200)
					throw new op5LivestatusException(trim($body), $query);
				$this->connection->close();
				break;
			} catch (op5LivestatusException $e) {
				$ninja_log = op5Log::instance('ninja');
				// After three failing attempts the exception is thrown upwards.
				if ($i == 3) {
					$ninja_log->debug('Livestatus down. Last attempt failed.');
					throw $e;
				}
				usleep(300000);
				$i++;
				$msg = "Livestatus down. Attempt $i of 3 failed. Trying again.";
				$ninja_log->debug($msg);
			}
		}

		$result = json_decode(utf8_encode($body), true);

		$objects = $result['data'];
		$count = $result['total_count'];

		if(!is_array($columns)) {
			$columns = $result['columns'][0]; /* FIXME */
		}

		$stop = microtime(true);

		/* TODO: benchmarks log non-kohana dependent...
		if (isset($this->config['benchmark']) && $this->config['benchmark']) {
			Database::$benchmarks[] = array('query' => $this->formatQueryForDebug($query), 'time' => $stop - $start, 'rows' => $count);//count($objects));
		}
		*/

		if ($objects === null) {
			throw new op5LivestatusException("Invalid output");
		}
		return array($columns,$objects,$count);
	}

	/**
	 * Attaches username to query if the user hasn't got full permission for queried table.
	 *
	 * @param $table string
	 * @param $user object
	 * @return string
	 **/
	private function auth($table, User_Model $user = null) {
		if(!$user) {
			$user = op5auth::instance()->get_user();
		}
		/* List all tables available, and how to handle the permissions for the
		 * table.
		 *
		 * Each definition contains two parts: how to handle the permission if
		 * limited access, and who have full access to the table.
		 *
		 * For the limited access:
		 * if false - send AuthUser header
		 * if true - don't allow any access, which means, send filter Or: 0
		 * if string - represents a column name, and if that matches exactly the
		 *             username, add Filter: column = username
		 *
		 * The second parameter is a list of permissions flags, if a user has
		 * any of those, give full access to the user, and don't send ANY auth
		 * header.
		 */
		$table_permissions = array(
			'commands'      => array( true,   array('command_view_all') ),
			'comments'      => array( false,  array('host_view_all','service_view_all') ),
			'contactgroups' => array( false,  array('contactgroup_view_all') ),
			'contacts'      => array( 'name', array('contact_view_all') ),
			'downtimes'     => array( false,  array('host_view_all','service_view_all') ),
			'hosts'         => array( false,  array('host_view_all') ),
			'hostgroups'    => array( false,  array('hostgroup_view_all') ),
			'services'      => array( false,  array('service_view_all') ),
			'servicegroups' => array( false,  array('servicegroup_view_all') ),
			'status'        => array( true,   array('system_information') ),
			'timeperiods'   => array( true,   array('timeperiod_view_all') )
			);

		/* If not defined, we don't know the table, and thus can not authenticate */
		if(!isset($table_permissions[$table])) {
			throw new op5LivestatusException('Unknown table '.$table);
		}

		list( $if_limited, $for_full_perm ) = $table_permissions[$table];

		foreach($for_full_perm as $perm) {
			if($user->authorized_for($perm)) {
				return "";
			}
		}
		if(is_string($if_limited)) {
			return "Filter: ".$if_limited. " = ".$user->get_username() ."\n";
		} else if($if_limited === true) {
			return "Or: 0\n";
		} else if($if_limited === false) {
			return "AuthUser: ".$user->get_username()."\n";
		}
		throw new op5LivestatusException('Internal error in livestatus auth');
	}

	/**
	 * Formats query for debugging purposes
	 *
	 * @param $query string
	 * @return string
	 **/
	private function formatQueryForDebug($query) {
		$querylines = explode("\n", $query);
		$result = array();
		$stats = array();
		$filter = array();

		$result[] = array_shift($querylines); /* GET-line */
		foreach($querylines as $line) {
			if(empty($line)) continue;
			$fields = explode(":", $line, 2);
			if(count($fields) != 2) {
				$result[] = $line;
				continue;
			}
			$header = trim($fields[0]);
			$param  = trim($fields[1]);
			switch($header) {
				case 'Filter':
					$filter[] = $param;
					break;
				case 'And':
					$merge = array();
					for($i=0; $i<intval($param); $i++) {
						$merge[] = array_pop($filter);
					}
					$filter[] = '(' . implode(' and ', $merge) . ')';
					break;
				case 'Or':
					$merge = array();
					for($i=0; $i<intval($param); $i++) {
						$merge[] = array_pop($filter);
					}
					$filter[] = '(' . implode(' or ', $merge) . ')';
					break;
				case 'Stats':
					$stats[] = $param;
					break;
				case 'StatsAnd':
					$merge = array();
					for($i=0; $i<intval($param); $i++) {
						$merge[] = array_pop($stats);
					}
					$stats[] = '(' . implode(' and ', $merge) . ')';
					break;
				case 'StatsOr':
					$merge = array();
					for($i=0; $i<intval($param); $i++) {
						$merge[] = array_pop($stats);
					}
					$stats[] = '(' . implode(' or ', $merge) . ')';
					break;
				default:
					$result[] = "$header: $param";
			}
		}
		if(count($filter))
			$result[] = "Filter: ".implode(' and ', $filter);
		foreach($stats as $statline) {
			$result[] = "Stats: $statline";
		}
		return implode("\n", $result);
	}
}

/*
 * Livestatus Connection Class
*/
class op5livestatus_connection {
	private $connection  = null;
	private $timeout     = 10;

	/**
	 * Constructor
	 *
	 * @param $path_to_ls_socket
	 **/
	public function __construct($path_to_ls_socket) {
		$this->connectionString = $path_to_ls_socket;
		return $this;
	}

	/**
	 * Destructor
	 *
	 * @return void
	 **/
	public function __destruct() {
		$this->close();
	}

	/**
	 * Connects to livestatus socket
	 *
	 * @return void
	 * @throws op5LivestatusException
	 **/
	public function connect() {
		$parts = explode(':', $this->connectionString, 2);
		if(count($parts) == 2) {
			$type = $parts[0];
			$address = $parts[1];
		} else {
			$type = 'unix';
			$address = '//'.$parts[0];
		}

		if($type == 'tcp') {
			list($host, $port) = explode(':', $address, 2);
			$this->connection = fsockopen($address, $port, $errno, $errstr, $this->timeout);
		}
		elseif($type == 'unix') {
			if(!file_exists($address)) {
				throw new op5LivestatusException("Cannot connect to Livestatus, '$address' is not a valid address to the Livestatus socket.");
			}
			$this->connection = @fsockopen('unix:'.$address, NULL, $errno, $errstr, $this->timeout);
		}
		else {
			throw new op5LivestatusException(
				'Cannot connect to Livestatus. Unknown connection type: ' .
				"'$type', valid types are 'tcp' and 'unix'."
			);
		}

		if(!$this->connection) {
			throw new op5LivestatusException(
				'Cannot connect to Livestatus. ' .
				'Connection ' . $this->connectionString . ' failed: ' . $errstr
			);
		}
	}

	/**
	 * Closes the socket connection
	 *
	 * @return void
	 **/
	public function close() {
		if($this->connection != null) {
			fclose($this->connection);
			$this->connection = null;
		}
	}

	/**
	 * Writes to livestatus socket
	 *
	 * @param $str string
	 * @return void
	 * @throws op5LivestatusException
	 **/
	public function writeSocket($str) {
		if ($this->connection === null)
			$this->connect();
		$out = @fwrite($this->connection, $str);
		if ($out === false)
			throw new op5LivestatusException("Couldn't write to Livestatus socket");
	}

	/**
	 * Reads from livestatus socket
	 *
	 * @param $len integer
	 * @return string
	 **/
	public function readSocket($len) {
		$offset     = 0;
		$socketData = '';

		while($offset < $len) {
			if(($data = fread($this->connection, $len - $offset)) === false) {
				return false;
			}

			if(($dataLen = strlen($data)) === 0) {
				break;
			}

			$offset     += $dataLen;
			$socketData .= $data;
		}

		return $socketData;
	}
}
