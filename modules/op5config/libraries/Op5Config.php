<?php defined('SYSPATH') OR die('No direct access allowed.');

class Op5Config_Core {
	protected $config = false;

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
	
	
	protected function getPathForNamespace( $namespace )
	{
		return '/opt/op5sys/etc/' . $namespace . '.json';
	}
	
	public function getConfig( $namespace )
	{
		/* FIXME: Configurable paths */
		
		$path   = $this->getPathForNamespace( $namespace );
		$file   = file_get_contents( $path );
		$object = json_decode( $file );
		/* TODO: Error handling */
		if( $object === null ) {
			$this->handle_error();
		}
		
		return $object;
	}
	
	public function setConfig( $namespace, $object )
	{
		/* FIXME: Configurable paths */
		$path   = $this->getPathForNamespace( $namespace );
		file_put_contents( $path, json_encode( $object ) );
	}

	private function handle_error()
	{
		$messages = array(
			JSON_ERROR_NONE           => 'JSON: No error',
			JSON_ERROR_DEPTH          => 'JSON: Maximum stack depth exceeded',
			JSON_ERROR_STATE_MISMATCH => 'JSON: Underflow or the modes mismatch',
			JSON_ERROR_CTRL_CHAR      => 'JSON: Unexpected control character found',
			JSON_ERROR_SYNTAX         => 'JSON: Syntax error, malformed JSON',
			JSON_ERROR_UTF8           => 'JSON: Malformed UTF-8 characters, possibly incorrectly encoded',
		 );
		 
		 $err = json_last_error();
		 if( !isset( $messages[ $err ] ) ) {
		 	$msg = 'JSON: Unknown error: ' . $err;
		 } else {
		 	$msg = $messages[ $err ];
		 }
		 throw new Exception( $msg );
	}
}

