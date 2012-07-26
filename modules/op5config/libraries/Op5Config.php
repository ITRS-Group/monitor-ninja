<?php defined('SYSPATH') OR die('No direct access allowed.');

class Op5Config_Core {
	/**
	 * Create an instance of Auth.
	 *
	 * @return  object
	 */
	public static function factory($config = array())
	{
		return new self( $config );
	}

	/**
	 * Return a static instance of Auth.
	 *
	 * @return  object
	 */
	public static function instance($config = array())
	{
		static $instance;
		
		// Load the Auth instance
		if (empty($instance)) {
			$instance = self::factory($config);
		}

		return $instance;
	}


	public function __construct( $config )
	{
		$this->config = $config;
	}
	
	
	
	public function getConfig( $namespace )
	{
		/* FIXME: Configurable paths */
		
		$path   = '/opt/op5sys/etc/' . $namespace . '.json';
		$file   = file_get_contents( $path );
		$object = json_decode( $file );
		/* TODO: Error handling */
		if( $object === null ) {
			$this->handle_error();
		}
		
		return $object;
	}

	private function handle_error()
	{
		switch (json_last_error()) {
			case JSON_ERROR_NONE:           $msg = 'JSON: No error';                                                 break;
			case JSON_ERROR_DEPTH:          $msg = 'JSON: Maximum stack depth exceeded';                             break;
			case JSON_ERROR_STATE_MISMATCH: $msg = 'JSON: Underflow or the modes mismatch';                          break;
			case JSON_ERROR_CTRL_CHAR:      $msg = 'JSON: Unexpected control character found';                       break;
			case JSON_ERROR_SYNTAX:         $msg = 'JSON: Syntax error, malformed JSON';                             break;
			case JSON_ERROR_UTF8:           $msg = 'JSON: Malformed UTF-8 characters, possibly incorrectly encoded'; break;
			default:                        $msg = 'JSON: Unknown error';                                            break;
		 }
		 throw new Exception( $msg );
	}
}

