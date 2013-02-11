<?php

require_once("op5/auth/Auth.php");
require_once('op5/config.php');

/**
 * Custom exception for Livestatus errors
 */
class op5LivestatusException extends Exception {}

class op5Livestatus {
	static private $instance = null;
	
	static public function instance($config = null)
	{
		if( self::$instance !== null )
			return self::$instance;
		self::$instance = new self($config);
		return self::$instance;
	}
	
	private $connection      = null;
	private $config          = false;

	/* constructor */
	public function __construct() {
		$this->config     = op5config::instance()->getConfig('livestatus');
		$this->connection = new op5livestatus_connection(array('path' => $this->config['path']));
	}


	/********************************************************
	 * INTERNAL FUNCTIONS
	 *******************************************************/
	public function stats_single($table, $filter, $stats) {
		$columns = array();
		foreach( $stats as $name => $query ) {
			$columns[] = $name;
			$filter .= $query;
		}
		list($res_columns,$objects,$res_count) = $this->query($table,$filter,array());
		
		return array_combine($columns, $objects[0]);
	}
	
	public function query($table, $filter, $columns, $options = array() ) {
		$query  = "GET $table\n";
		$query .= "OutputFormat: wrapped_json\n";
		$query .= "KeepAlive: on\n";
		$query .= "ResponseHeader: fixed16\n";
		if( isset($options['auth']) && $options['auth'] == false ) {
			$query .= $this->auth($table);
		}
		
		if(is_array( $columns )) {
			$column_txt = "";
			$fetch_columns = array();
			foreach($columns as $column ) {
				$parts = explode('.',$column);
				$colname = implode('_',array_slice($parts, -2)); /* service.host.name is not possible... host.name in livestatus... */
				$column_txt .= " ".$colname;
				$fetch_columns[] = $colname;
			}
			$columns = $fetch_columns;
			$query .= "Columns: $column_txt\n";
		}

		if( is_array($filter) ) {
			$filter = implode("\n", array_map( 'trim', $filter ))."\n";
		}

		$query .= $filter;
		$query .= "\n";
		
		$start   = microtime(true);
		$rc      = $this->connection->writeSocket($query);;
		$head    = $this->connection->readSocket(16);
		$status  = substr($head, 0, 3);
		$len     = intval(trim(substr($head, 4, 15)));
		$body    = $this->connection->readSocket($len);
		if(empty($body))
			throw new op5LivestatusException("empty body for query: <pre>".$query."</pre>");
		if($status != 200)
			throw new op5LivestatusException("Invalid request: ".trim($body));
		
		$result = json_decode(utf8_encode($body), true);
		
		$objects = $result['data'];
		$count = $result['total_count'];
		
		if( !is_array($columns) ) {
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
	
	private function auth($table) {
		$user = op5auth::instance()->get_user();
		/* If table is defined, attach AuthUser, unless any of the permissions in the array is avalible for the user */
		$table_permissions = array(
			'commands'      => array('command_view_all'),
			'comments'      => array('host_view_all','service_view_all'),
			'contacts'      => array('contact_view_all'),
			'downtime'      => array('host_view_all','service_view_all'),
			'hosts'         => array('host_view_all'),
			'hostgroups'    => array('hostgroups_view_all'),
			'services'      => array('service_view_all'),
			'servicegroups' => array('serviegroups_view_all'),
			'status'        => array('system_information'),
			'timeperiods'   => array('timeperiods_view_all')
			);
		
		/* Tables not handling AuthUser in livestatus... if limited, filter by "Filter: col = username" or "Or: 0" */
		$table_noauth = array(
			'contacts' => 'name',
			'commands' => true,
			'status' => true,
			'timeperiods' => true
			);
		
		if( !isset( $table_permissions[$table] ) ) {
			return "";
		}
		foreach( $table_permissions[$table] as $perm ) {
			if( $user->authorized_for($perm) ) {
				return "";
			}
		}
		if( isset($table_noauth[$table] ) ) {
			if( is_string($table_noauth[$table]) ) {
				return "Filter: ".$table_noauth[$table]. " = ".$user->username."\n";
			} else {
				return "Or: 0\n";
			}
		}
		return "AuthUser: ".$user->username."\n";
	}
	
	private function formatQueryForDebug( $query ) {
		$querylines = explode( "\n", $query );
		$result = array();
		$stats = array();
		$filter = array();
		
		$result[] = array_shift( $querylines ); /* GET-line */
		foreach( $querylines as $line ) {
			if( empty( $line ) ) continue;
			$fields = explode( ":", $line, 2 );
			if( count($fields) != 2 ) {
				$result[] = $line;
				continue;
			}
			$header = trim($fields[0]);
			$param  = trim($fields[1]);
			switch( $header ) {
				case 'Filter':
					$filter[] = $param;
					break;
				case 'And':
					$merge = array();
					for( $i=0; $i<intval($param); $i++ ) {
						$merge[] = array_pop($filter);
					}
					$filter[] = '(' . implode(' and ', $merge) . ')';
					break;
				case 'Or':
					$merge = array();
					for( $i=0; $i<intval($param); $i++ ) {
						$merge[] = array_pop($filter);
					}
					$filter[] = '(' . implode(' or ', $merge) . ')';
					break;
				case 'Stats':
					$stats[] = $param;
					break;
				case 'StatsAnd':
					$merge = array();
					for( $i=0; $i<intval($param); $i++ ) {
						$merge[] = array_pop($stats);
					}
					$stats[] = '(' . implode(' and ', $merge) . ')';
					break;
				case 'StatsOr':
					$merge = array();
					for( $i=0; $i<intval($param); $i++ ) {
						$merge[] = array_pop($stats);
					}
					$stats[] = '(' . implode(' or ', $merge) . ')';
					break;
				default:
					$result[] = "$header: $param";
			}
		}
		if( count($filter) )
			$result[] = "Filter: ".implode(' and ', $filter);
		foreach( $stats as $statline ) {
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

	public function __construct($options) {
		$this->connectionString = $options['path'];
		return $this;
	}

	public function __destruct() {
		$this->close();
	}

	public function connect() {
		$parts = explode(':', $this->connectionString, 2);
		if( count($parts) == 2 ) {
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
				throw new op5LivestatusException("connection failed, make sure $address exists\n");
			}
			$this->connection = @fsockopen('unix:'.$address, NULL, $errno, $errstr, $this->timeout);
			if (!$this->connection)
				throw new op5LivestatusException("connection failed, make sure $address exists\n");
		}
		else {
			throw new op5LivestatusException("unknown connection type: '$type', valid types are 'tcp' and 'unix'\n");
		}

		if(!$this->connection) {
			throw new op5LivestatusException("connection ".$this->connectionString." failed: ".$errstr);
		}
	}

	public function close() {
		if($this->connection != null) {
			fclose($this->connection);
			$this->connection = null;
		}
	}

	public function writeSocket($str) {
		if ($this->connection === null)
			$this->connect();
		$out = @fwrite($this->connection, $str);
		if ($out === false)
			throw new op5LivestatusException("Couldn't write to livestatus socket");
	}

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
