<?php
require_once('op5/config.php');

class op5Log {
	/**
	 * Holds instances of log
	 *
	 * @var $instances string
	 **/
	static $instances = array();

	/**
	 * @param $message string Proxy for $this->debug()
	 */
	public function __invoke($message) {
		return $this->debug($message);
	}

	/**
	 * Create an instance of Auth.
	 *
	 * @return  object
	 */
	public static function factory( $namespace )
	{
		return new self( $namespace );
	}

	/**
	 * Return a static instance of Auth.
	 *
	 * @return  object
	 */
	public static function instance( $namespace )
	{
		// Load the Auth instance
		if ( !isset( self::$instances[ $namespace ] ) ) {
			self::$instances[ $namespace ] = self::factory( $namespace );
		}

		return self::$instances[ $namespace ];
	}

	/*
	 * Instance configuration:
	 * - file: filename
	 * - reference: lookup stack to get calling class name
	 * - prefix: text to add after timestamp
	 */
	private $config = array();
	/*
	 * Namespace of logging.
	 */
	private $namespace = false;


	/* Temporary store for messages... reduce file access.
	 * Indexed per filename, so multiple instances can log to same file
	* */
	private static $messages = array();
	private static $levels = array(
			'error'   => 1,
			'warning' => 2,
			'notice'  => 3,
			'debug'   => 4
			);

	/**
	 * Setup the logging class
	 */
	public function __construct( $namespace )
	{
		$this->namespace = $namespace;
		$logconfig = op5Config::instance()->getConfig( 'log' );
		if( isset( $logconfig[$namespace] ) ) {
			$this->config = $logconfig[$namespace];
		}
		else {
			/*
			 * No logging specificed for this namespace... set config to false,
			 * which indicates no logging
			 */
			$this->config = false;
			return;
		}

		if( !isset( $this->config['file'] ) ) {
			throw new Exception( "Logging for namespace '$namespace' is missing file parameter" );
		}

		$level = 'debug';
		if( isset( $this->config['level'] ) ) {
			$level = $this->config['level'];
		}
		if( !isset( self::$levels[ $this->config['level'] ] ) ) {
			throw new Exception( "Unknown logging level '".self::$levels[ $this->config['level'] ]."'for '$namespace',".
					". Logging levels available: ".implode(', ', self::$levels) );
		}
		$this->config['level'] = self::$levels[ $this->config['level'] ];

		if( !isset( $this->config['prefix'] ) ) {
			$this->config['prefix'] = $namespace;
		}

		/* This will be registered once per instance of op5Log. This is no
		 * problem because writeback clears the buffer after each run, and no
		 * extra file access will be generated with the buffer cleared. And the
		 * number of log namespaces is quite limited.
		 */
		register_shutdown_function( array( __CLASS__, 'writeback' ) );
	}

	/**
	 * @param $message string Proxy for $this->debug()
	 */
	public function debug($message) {
		return $this->log('debug', $message);
	}

	/**
	 * @param $level string
	 * @param $message string
	 */
	public function log( $level, $message )
	{
		if( $this->config === false ) {
			/* Loggging disabled for this namespace */
			return;
		}
		if( self::$levels[$level] > $this->config['level'] ) {
			return; /* To low logging level in config... */
		}

		/*
		 * If message is an exception, format it.
		 */
		if( $message instanceof Exception ) {
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
		if( isset( $this->config['reference'] ) && $this->config['reference'] ) {
			$stack = debug_backtrace();
			@$reference = ' ' . $stack[1]['class'] . ' @' . $stack[1]['line'];
		}

		/*
		 * Generate filename and message. Put filename through strftime, so log
		 * files can be rotated automatically
		 */
		$filename = strftime( $this->config['file'] );
		$line_prefix = strftime( '%Y-%m-%d %H:%M:%S ' ) . sprintf('%-7s', $level) . ' ' . $this->config['prefix'] . $reference . ': ';
		$message = implode("\n", array_map(function($line) use($line_prefix) { return $line_prefix . $line; }, explode("\n",$message)));

		/*
		 * Store message to self::$message as temporary storage, to reduce disc
		 * access to one access per file and script, instead of one per line.
		 */
		if( !isset( self::$messages[$filename] ) ) {
			self::$messages[$filename] = array();
		}
		self::$messages[$filename][] = $message;
	}

	/**
	 * Write log files to disc.
	 *
	 * This is automatically runned through register_shutdown_function when
	 * creating the first logger instance. But can be called during the script
	 * to flush messages
	 */
	public static function writeback()
	{
		$processUser = posix_getpwuid(posix_geteuid());
		$user = $processUser['name'];
		foreach( self::$messages as $file => $messages ) {
			$dir = dirname( $file );
			if(!is_dir($dir)) {
				@mkdir( $dir, 0775, true );
			}
			$res = @file_put_contents(
				$file,
				implode( "\n", $messages ) . "\n",
				FILE_APPEND );

			if( $res === false ) {
				error_log( 'Could not write to log file: ' . $file );
			}
			if ($user === 'root' && posix_getpwuid(fileowner($file)) === 'root') {
				exec("id monitor -gn", $p_group, $status);
				chown($file, "monitor");
				chgrp($file, $p_group[0]);
			}
		}
		self::$messages = array(); /* empty, to make it possible to add more messages afterwards */
	}
}
