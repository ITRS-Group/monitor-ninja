<?php
require_once(__DIR__.'/config.php');

/**
 * undocumented class
 *
 * @package default
 **/
class op5LogAccess {
	protected $log_instance = false;
	protected $namespace = false;

	public function __construct($log_instance, $namespace) {
		$this->log_instance = $log_instance;
		$this->namespace = $namespace;

	}

	/**
	 * @param $message string Proxy for $this->debug()
	 */
	public function __invoke($message) {
		return $this->debug($message);
	}

	/**
	 * undocumented function
	 *
	 * @param $level string
	 * @param $message string
	 * @return void
	 **/
	public function log($level, $message) {
		$this->log_instance->log($this->namespace, $level, $message);
	}

	/**
	 * undocumented function
	 *
	 * @param $message string
	 * @return void
	 **/
	public function debug($message) {
		$this->log_instance->log($this->namespace, 'debug', $message);
	}
	
	/**
	 * Test if correct log level
	 *
	 * @param $level string
	 * @return bool
	 */
	public function loggable_level($level) {
		return $this->log_instance->loggable_level($this->namespace, $level);
	}
}

/**
 * Logs to given namespace.
 * Logs are configured in /etc/op5/log.yml
 *
 * @package default
 **/
class op5Log {

	/**
	 * Static log levels
	 *
	 **/
	private static $levels = array(
			'error'   => 1,
			'warning' => 2,
			'notice'  => 3,
			'debug'   => 4
			);

	/**
	 * Return a static instance of Auth.
	 *
	 * @return  object
	 */
	public static function instance($namespace=false)
	{
		$log_instance = op5objstore::instance()->obj_instance(__CLASS__);

		if($namespace === false)
			return $log_instance;

		/* Return a wrapper to augment the log with a namespace field */
		return new op5LogAccess($log_instance, $namespace);
	}

	/*
	 * Instance configuration:
	 * - file: filename
	 * - reference: lookup stack to get calling class name
	 * - prefix: text to add after timestamp
	 */
	private $config = array();


	/* Temporary store for messages... reduce file access.
	 * Indexed per filename, so multiple instances can log to same file
	* */
	private $messages = array();

	/**
	 * Setup the logging class
	 */
	public function __construct()
	{
		$this->config = op5Config::instance()->getConfig('log');
/*
		if(!isset($this->config['file'])) {
			throw new Exception("Logging for namespace '$namespace' is missing file parameter");
		}

		$level = 'debug';
		if(isset($this->config['level'])) {
			$level = $this->config['level'];
		}
		if(!isset(self::$levels[$this->config['level']])) {
			throw new Exception("Unknown logging level '".self::$levels[$this->config['level']]."'for '$namespace',".
					". Logging levels available: ".implode(', ', self::$levels));
		}
		$this->config['level'] = self::$levels[$this->config['level']];

		if(!isset($this->config['prefix'])) {
			$this->config['prefix'] = $namespace;
		}*/

		/* This will be registered once per instance of op5Log. This is no
		 * problem because writeback clears the buffer after each run, and no
		 * extra file access will be generated with the buffer cleared. And the
		 * number of log namespaces is quite limited.
		 */
		register_shutdown_function(array(__CLASS__, 'writeback'));
	}

	/**
	 * @param $level string
	 * @param $message string
	 */
	public function log($namespace, $level, $message)
	{
		if(!isset($this->config[$namespace])) {
			/* Loggging disabled for this namespace */
			return;
		}
		$config = $this->config[$namespace];

		if(self::$levels[$level] > self::$levels[$config['level']]) {
			return; /* To low logging level in config... */
		}

		/*
		 * If message is an exception, format it.
		 */
		if($message instanceof Exception) {
			$ex = $message;
			$message = trim("exception: " . $ex->getMessage())."\n";
			$message .= $ex->getTraceAsString();
		}

		/*
		 * If reference is expected to the logged message, load it from the
		 * stack trace.
		 *
		 * This costs time, and is not often needed, but make debugging easier
		 * for non-informative messages
		 */
		$reference = '';
		if(isset($config['reference']) && $config['reference']) {
			$stack = debug_backtrace();
			@$reference = ' ' . $stack[1]['class'] . ' @' . $stack[1]['line'];
		}

		/*
		 * Generate filename and message. Put filename through strftime, so log
		 * files can be rotated automatically
		 */
		$filename = strftime($config['file']);
		$prefix = isset($config['prefix']) ? $config['prefix'] : $namespace;
		$line_prefix = strftime('%Y-%m-%d %H:%M:%S ') . sprintf('%-7s', $level) . ' ' . $prefix . $reference . ': ';
		$message = implode("\n", array_map(function($line) use($line_prefix) { return $line_prefix . $line; }, explode("\n",$message)));

		/*
		 * Store message to self::$message as temporary storage, to reduce disc
		 * access to one access per file and script, instead of one per line.
		 */
		if(!isset($this->messages[$filename])) {
			$this->messages[$filename] = array();
		}
		$this->messages[$filename][] = $message;
	}
	
	/**
	 * Test if correct log level
	 *
	 * @param $level string
	 * @return bool
	 */
	public function loggable_level($namespace, $level) {
		if(!isset($this->config[$namespace])) {
			/* Loggging disabled for this namespace */
			return false;
		}
		$config = $this->config[$namespace];
		if(self::$levels[$level] > self::$levels[$config['level']]) {
			return false; /* To low logging level in config... */
		}
		return true;
	}

	/**
	 * Write log files to disc.
	 *
	 * This is automatically runned through register_shutdown_function when
	 * creating the first logger instance. But can be called during the script
	 * to flush messages
	 */
	public function do_writeback()
	{

		$processUser = array('name' => 'unknown');
		if (function_exists('posix_getpwuid')) {
			$processUser = posix_getpwuid(posix_geteuid());
		}
		$user = $processUser['name'];
		foreach($this->messages as $file => $messages) {
			$dir = dirname($file);
			if(!is_dir($dir)) {
				mkdir($dir, 0775, true);
			}

			$new_file = false;
			if (!file_exists($file)) {
				$new_file = true;
			}

			$res = file_put_contents(
				$file,
				implode("\n", $messages) . "\n",
				FILE_APPEND);

			if ($new_file) {
				/* Set read-writable by owner and group.
				 * both the web server and the monitor user is a member of the web server (apache) group.
				 * This allows the monitor user to directly use the op5Log class without triggering
				 * access errors.
				 * */
				chmod ($file, 0664);
			}

			if($res === false) {
				error_log('Could not write to log file: ' . $file);
			}
			if ($user === 'root' && posix_getpwuid(fileowner($file)) === 'root') {
				exec("id monitor -gn", $p_group, $status);
				chown($file, "monitor");
				chgrp($file, $p_group[0]);
			}
		}
		$this->messages = array(); /* empty, to make it possible to add more messages afterwards */
	}

	/**
	 * Just a wrapper to be static
	 */
	public static function writeback() {
		self::instance()->do_writeback();
	}
}
