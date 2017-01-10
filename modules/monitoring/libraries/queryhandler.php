<?php

require_once('op5/objstore.php');

/**
 * op5Queryhandler exception
 */
class op5queryhandler_Exception extends Exception {

	/**
	 * Exception constructor
	 */
	public function __construct($msg, $data=false) {
		if($data !== false) {
			$msg .= " (data: ".$data.")";
		}
		parent::__construct($msg);
	}
}

/**
 * op5Queryhandler class
 */
class op5queryhandler {

	/**
	 * Return singleton instance
	 */
	static public function instance() {
		return op5objstore::instance()->obj_instance(__CLASS__);
	}

	private $path;
	private $timeout = 5;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->path = op5config::instance()->getConfig("queryhandler.socket_path");
	}

	/**
	 * Dummy documentation
	 */
	public function json_call($channel, $command, $args, $node = false) {
		/* Just a wrapper, because of the old json-syntax. */
		return $this->kvvec_call($channel, $command, $args, $node);
	}

	/**
	 * Dummy documentation
	 */
	public function kvvec_call($channel, $command, $args, $node = false) {
		$data = $this->call($channel, $command, $args, $node);
		$expanded = self::kvvec2array(trim($data));
		if(empty($expanded)) {
			throw new op5queryhandler_Exception('Empty result', $data);
		}
		return $expanded;
	}

	/**
	 * Dummy documentation
	 */
	public function call($channel, $command, $args, $node = false) {
		if(is_array($args)) {
			$args = $this->array2kvvec($args);
		}
		return $this->raw_call("@$channel $command $args\0", $node);
	}

	/**
	 * Dummy documentation
	 */
	public function raw_call($command, $node = false) {
		if($node !== false) {
			return $this->raw_remote_call($command, $node);
		}
		$sock = @fsockopen('unix://'.$this->path, NULL, $errno, $errstr, $this->timeout);
		if ($sock === false)
			throw new op5queryhandler_Exception("Failed to open socket at $this->path: $errstr");
		if ($errno)
			throw new op5queryhandler_Exception("Failed to open socket at $this->path: $errstr");

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

	/**
	 * Dummy documentation
	 */
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
	private function array2kvvec($args) {
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
	 * Converts a (possibly escaped) kvvec string into an associative array.
	 * As a trivial example,
	 * kvvec2array("key=value")
	 * would return
	 * array("key" => "value")
	 *
	 * @param $kvvec string
	 * @return array()
	 * @throws InvalidArgumentException in case of syntax error
	 *
	 */
	public static function kvvec2array($kvvec) {
		$pairs = array();

		$len = strlen($kvvec);
		$escapes = array(
			'n' => "\n",
			'r' => "\r",
			't' => "\t",
			'\\' => '\\'
		);
		$pos = 0;
		while ($pos < $len) {
			$key = "";
			for (; $pos < $len; $pos++) {
				$c = $kvvec[$pos];
				if ($c == '\\') {
					$pos++;
					if ($pos == $len) //end of string
						break;
					$c = isset($escapes[$kvvec[$pos]]) ? $escapes[$kvvec[$pos]] : $kvvec[$pos];
				} else if ($c == ';') {
					throw new op5queryhandler_Exception("Syntax error in kvvec key, unescaped reserved character ';' encountered at index '$pos'");
				} else if ($c == '=') {
					$pos++;
					break;
				}

				$key .= $c;
			}
			$value = "";
			for (; $pos < $len; $pos++) {
				$c = $kvvec[$pos];
				if ($c == '\\') {
					$pos++;
					if ($pos == $len) //end of string
						break;
					$c = isset($escapes[$kvvec[$pos]]) ? $escapes[$kvvec[$pos]] : $kvvec[$pos];
				} else if ($c == ';') {
					$pos++;
					break;
				}
				else if ($c == '=') {
					throw new op5queryhandler_Exception("Syntax error in kvvec value, unescaped reserved character '=' encountered at index '$pos'");
				}
				$value .= $c;
			}
			$pairs[$key] = $value;
		}
		return $pairs;
	}
}
