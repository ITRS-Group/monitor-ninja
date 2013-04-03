<?php

class op5queryhandler {
	static private $instance = false;
	
	static public function instance() {
		if( self::$instance === false ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private $path;
	private $timeout = 5;
	
	private function __construct() {
		$this->path = '/opt/monitor/var/rw/nagios.qh';
	}
	
	public function json_call( $channel, $command, $args, $conv_hash=true, $node = false ) {
		$data = $this->call($channel, $command, $args, $node);
		$expanded = @json_decode(trim($data));
		if( $expanded === NULL ) {
			print $data;
			return false;
		}
		if( $conv_hash ) {
			$expanded = array_combine(
					array_map(function($k){return $k[0];},$expanded),
					array_map(function($k){return $k[1];},$expanded)
					);
		}
		return $expanded;
	}
	
	public function call( $channel, $command, $args, $node = false ) {
		if( is_array( $args ) ) {
			$args = $this->pack_args($args);
		}
		
		return $this->raw_call( "@$channel $command $args\0", $node );
	}
	
	public function raw_call( $command, $node = false ) {
		if( $node !== false ) {
			return $this->raw_remote_call($command, $node);
		}
		$sock = @fsockopen('unix://'.$this->path, NULL, $errno, $errstr, $this->timeout);
		if ($sock === false)
			return "Request failed: $errstr";
		if ($errno)
			return "Request failed: $errstr";

		for ($written = 0; $written < strlen($command); $written += $len) {
			$len = @fwrite($fp, substr($string, $written));
			if ($len === false)
				return "Request failed: couldn't write query";
		}

		$content = "";
		while(($c = @fread($sock,1)) !== "\0"){
			if($c === false)
				return "Request failed: couldn't read response";
			$content .= $c;
		}
		@fclose($sock);
		return $content;
	}
	
	private function raw_remote_call( $command, $node ) {
		/* Ehum... this has potential to be made better... It works for now... (TODO) */
		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);
		$process = proc_open('asmonitor ssh '.escapeshellarg($node).' "unixcat /opt/monitor/var/rw/nagios.qh"', $descriptorspec, $pipes);
		if( $process === false )
			return false; /* TODO: Error handling */
		
		/* stdin */
		fwrite($pipes[0], $command);
		fflush($pipes[0]);
		/* stdout */
		$content = "";
		while(($c = fread($pipes[1],1))!==false){
			if( $c === "" || $c === "\0" )
				break;
			$content.=$c;
		}
		fclose($pipes[0]);
		fclose($pipes[1]);
		
		/* stderr */
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		
		proc_close($process);
		
		if(!empty($stderr)) {
			print $stderr; /* FIXME: better logging */
		}
		
		return $content;
	}
	
	/**
	 * Converts an associative array of arguments to a string using kvvec syntax with delimiters = and ;
	 * 
	 * If value is an array, it's treated as a set of lines with identical keys
	 */
	private function pack_args( $args ) {
		$result = array();
		foreach( $args as $k => $v ) {
			if( is_array( $v ) ) {
				foreach( $v as $vx ) {
					$result[] = $k . '=' . $vx;
				}
			} else {
				$result[] = $k . '=' . $v;
			}
		}
		return implode(';', $result);
	}
}
