<?php

require_once(__DIR__.'/objstore.php');

class op5queryhandler_Exception extends Exception {
	public function __construct($msg, $data=false) {
		if($data !== false) {
			$msg .= " (data: ".$data.")";
		}
		parent::__construct($msg);
	}
}

class op5queryhandler {
	static public function instance()
	{
		return op5objstore::instance()->obj_instance(__CLASS__);
	}

	private $path;
	private $timeout = 5;

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	public function __construct() {
		$this->path = op5config::instance()->getConfig("queryhandler.socket_path");
	}

	public function json_call($channel, $command, $args, $conv_hash=true, $node = false) {
		/* Just a wrapper, because of the old json-syntax. */
		return $this->kvvec_call($channel, $command, $args, $conv_hash, $node);
	}

	public function kvvec_call($channel, $command, $args, $conv_hash=true, $node = false) {
		$data = $this->call($channel, $command, $args, $node);
		$expanded = $this->unpack_args(trim($data));
		if($expanded === NULL) {
			throw new op5queryhandler_Exception('Unknown response', $data);
		}
		if(empty($expanded)) {
			throw new op5queryhandler_Exception('Empty result', $data);
		}
		if($conv_hash) {
			$expanded = array_combine(
					array_map(function($k){return $k[0];},$expanded),
					array_map(function($k){return $k[1];},$expanded)
					);
		}
		return $expanded;
	}

	public function call($channel, $command, $args, $node = false) {
		if(is_array($args)) {
			$args = $this->pack_args($args);
		}

		return $this->raw_call("@$channel $command $args\0", $node);
	}

	public function raw_call($command, $node = false) {
		if($node !== false) {
			return $this->raw_remote_call($command, $node);
		}
		$sock = @fsockopen('unix://'.$this->path, NULL, $errno, $errstr, $this->timeout);
		if ($sock === false)
			throw new op5queryhandler_Exception("Request failed: $errstr");
		if ($errno)
			throw new op5queryhandler_Exception("Request failed: $errstr");

		for ($written = 0; $written < strlen($command); $written += $len) {
			$len = @fwrite($sock, substr($command, $written));
			if ($len === false)
				throw new op5queryhandler_Exception("Request failed: couldn't write query");
		}

		$content = "";
		while(($c = fread($sock,1))!==false){
			if($c === "" || $c === "\0")
				break;
			$content.=$c;
		}
		@fclose($sock);
		return $content;
	}

	private function raw_remote_call($command, $node) {
		/* Ehum... this has potential to be made better... It works for now... (TODO) */
		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);
		$process = proc_open('asmonitor ssh '.escapeshellarg($node).' "unixcat ' . $this->path . '"', $descriptorspec, $pipes);
		if($process === false)
			return false; /* TODO: Error handling */

		/* stdin */
		fwrite($pipes[0], $command);
		fflush($pipes[0]);
		/* stdout */
		$content = "";
		while(($c = fread($pipes[1],1))!==false){
			if($c === "" || $c === "\0")
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
	private function pack_args($args) {
		$escstr = ";=\n\0\\";
		$result = array();
		foreach($args as $k => $v) {
			if(is_array($v)) {
				foreach($v as $vx) {
					$result[] = addcslashes($k,$escstr) . '=' . addcslashes($vx,$escstr);
				}
			} else {
				$result[] = addcslashes($k,$escstr) . '=' . addcslashes($v,$escstr);
			}
		}
		return implode(';', $result);
	}

	/**
	 * Converts an associative array of arguments to a string using kvvec syntax with delimiters = and ;
	 *
	 * If value is an array, it's treated as a set of lines with identical keys
	 */
	private function unpack_args($args) {
		$pairs = array();

		do {
			$match = false;
			$key_raw = preg_match('/^((?:[^;=\\\\]|\\\\.)*?)=/', $args, $matches);
			if($key_raw) {
				$key = stripcslashes($matches[1]);
				$args = substr($args,strlen($matches[0]));
				$value_raw = preg_match('/^((?:[^;=\\\\]|\\\\.)*?)(?:;|\z)/', $args, $matches);
				if($value_raw) {
					$value = stripcslashes($matches[1]);
					$args = substr($args,strlen($matches[1])+1);
					$pairs[] = array($key, $value);
					$match = true;
				}
			}
		} while($match);

		return $pairs;
	}
}
