<?php

/**
 * Livetatus interaface.
 */
class LivestatusAccess {
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
	public function __construct($config = null) {
		$config           = $config ? $config : 'livestatus';
		$this->config     = Kohana::config('database.'.$config);
		$this->auth       = Auth::instance();
		$this->connection = new LivestatusConnection(array('path' => $this->config['path']));
	}


	/********************************************************
	 * INTERNAL FUNCTIONS
	 *******************************************************/
	public function query($table, $filter, $columns) {
		$query  = "GET $table\n";
		$query .= "OutputFormat: wrapped_json\n";
		$query .= "KeepAlive: on\n";
		$query .= "ResponseHeader: fixed16\n";

		/* FIXME: Auth */
		
		if(is_array( $columns )) {
			$query .= "Columns: ".implode(' ',$columns)."\n";
		}

		$query .= $filter."\n";
		
		$start   = microtime(true);
		$rc      = $this->connection->writeSocket($query);;
		$head    = $this->connection->readSocket(16);
		$status  = substr($head, 0, 3);
		$len     = intval(trim(substr($head, 4, 15)));
		$body    = $this->connection->readSocket($len);
		if(empty($body))
			throw new LivestatusException("empty body for query: <pre>".$query."</pre>");
		if($status != 200)
			throw new LivestatusException("Invalid request: $body");
		
		$body = str_replace( '"data":[,', '"data":[', $body ); /* FIXME: (after livestatus is fixed) */
		
		$result = json_decode(utf8_encode($body), true);
		
		$objects = $result['data'];
		$count = $result['total_count'];
		
		if( !is_array($columns) ) {
			$columns = $result['columns'][0]; /* FIXME */
		}
		
		$stop = microtime(true);
		if ($this->config['benchmark'] == TRUE) {
			Database::$benchmarks[] = array('query' => $this->formatQueryForDebug($query), 'time' => $stop - $start, 'rows' => $count);//count($objects));
		}
		
		if ($objects === null) {
			throw new LivestatusException("Invalid output");
		}
		return array($columns,$objects,$count);
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
class LivestatusAccessConnection {
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
		list($type, $address) = explode(':', $this->connectionString, 2);

		if($type == 'tcp') {
			list($host, $port) = explode(':', $address, 2);
			$this->connection = fsockopen($address, $port, $errno, $errstr, $this->timeout);
		}
		elseif($type == 'unix') {
			if(!file_exists($address)) {
				throw new LivestatusException("connection failed, make sure $address exists\n");
			}
			$this->connection = @fsockopen('unix:'.$address, NULL, $errno, $errstr, $this->timeout);
			if (!$this->connection)
				throw new LivestatusException("connection failed, make sure $address exists\n");
		}
		else {
			throw new LivestatusException("unknown connection type: '$type', valid types are 'tcp' and 'unix'\n");
		}

		if(!$this->connection) {
			throw new LivestatusException("connection ".$this->connectionString." failed: ".$errstr);
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
			throw new LivestatusException("Couldn't write to livestatus socket $address");
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

?>
