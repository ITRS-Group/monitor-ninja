<?php

require_once( __DIR__.'/spyc.php' );
require_once( __DIR__.'/objstore.php' );

class op5config {
	private $basepath     = '/etc/op5/';
	private $apc_enabled = false; /* Sets to true if apc_fetch exists */
	private $apc_ttl     = 10;


	/**
	 * Create an instance of op5Config.
	 *
	 * @param options an arrary of options
	 * @return  object
	 */
	public static function factory($options=false)
	{
		return new self($options);
	}

	/**
	 * Return a static instance of op5Config.
	 *
	 * @return  op5Config instance of config object
	 */
	public static function instance($options=false)
	{
		return op5objstore::instance()->obj_instance(__CLASS__, $options);
	}

	/**
	 * __contruct
	 *
	 * @param $options array
	 * @return void
	 **/
	public function __construct($options=false)
	{
		$basepath = false;
		if (isset($options['basepath']))
			$basepath = $options['basepath'];
		else if (getenv('OP5LIBCFG')) {
			$basepath = getenv('OP5LIBCFG');
		}
		if ($basepath) {
			if ($basepath[strlen($basepath)-1] != '/')
				$basepath .= '/';
			$this->basepath = $basepath;
		}

		$this->apc_enabled = function_exists( 'apc_fetch' );
	}

	/**
	 * Get config for supplied namespace
	 *
	 * @param $parameter mixed
	 * @return array
	 **/
	public function getConfig( $parameter )
	{
		return $this->getConfigVar( explode('.',$parameter), $this->basepath );
	}

	/**
	 * Set config for supplied namespace
	 *
	 * @param $parameter string
	 * @param $array array
	 * @return void
 	 * @throws RuntimeException if file is unwritable
	 */
	public function setConfig( $parameter, $array )
	{
		$path = $this->getPathForNamespace( $parameter );
		if(false === $this->setConfigFile( $path, $array )) {
			throw new RuntimeException("Could not write to $path");
		}
	}

	/**
	 * Get individual config parameters from yml-file
	 *
	 * @param $parameter array
	 * @param $path string
	 * @return mixed
	 **/
	protected function getConfigVar( $parameter, $path )
	{
		/* Parameter array is empty; fetch tree */
		if( count( $parameter ) == 0 ) {
			/* Path is a dir, list dir an build array */
			if( is_dir( $path ) ) {
				$value = array();
				foreach( scandir( $path ) as $entry ) {
					if( $entry[0] == '.' ) {
						continue;
					}
					if( substr( $entry, -4 ) == '.yml' && is_file($path . '/' . $entry ) ) {
						$value[substr($entry,0,-4)] = $this->getConfigFile( $path . '/' . $entry );
					}
					if( is_dir( $path . '/' . $entry ) ) {
						$value[$entry] = $this->getConfigVar(array($entry), $path);
					}
				}
				return $value;

			/* Path is an yml, fetch yml file */
			} else if( is_file( $path .'.yml' ) ) {
				return $this->getConfigFile($path.'.yml');
			}

		/* Parameter tree isn't empty, step into */
		}
		else {
			$head = array_shift( $parameter );

			/* head is a dir, step into, and recurse */
			if( is_dir($path . '/' . $head ) ) {
				return $this->getConfigVar( $parameter, $path . '/' . $head );
			}

			if( is_file( $path . '/' . $head . '.yml') ) {
				/* head is an yml file, just fetch the parameter without recursion and exit */
				$value = $this->getConfigFile( $path . '/' . $head . '.yml' );
				while( count( $parameter ) ) {
					$head = array_shift( $parameter );
					if( isset( $value[$head] ) ) {
						$value = $value[$head];
					} else {
						return null;
					}
				}
				return $value;
			} else {
				return null;
			}
		}
	}

	/**
	 * Returns the path to supplied namespace
	 *
	 * @param $namespace string
	 * @return string
	 **/
	protected function getPathForNamespace( $namespace )
	{
		return $this->basepath . $namespace . '.yml';
	}

	/**
	 * Returns content of yaml config file as array
	 *
	 * @param $path string
	 * @return array
	 **/
	protected function getConfigFile( $path )
	{
		if( $this->apc_enabled ) {
			$array = apc_fetch( $this->apc_tag_for_path( $path ), $success );
			if( $success ) {
				return $array;
			}
		}

		$array = Spyc::YAMLLoad( $path );

		if( $this->apc_enabled ) {
			apc_store( $this->apc_tag_for_path( $path ), $array, (int) $this->apc_ttl );
		}
		return $array;
	}

	/**
	 * Writes array to yaml config file
	 *
	 * @param $path string
	 * @param $array array
	 * @return boolean
	 **/
	protected function setConfigFile( $path, $array )
	{
		if( $this->apc_enabled ) {
			/* TODO: Use store instead... but I want to verify that it's stored correctly */
			apc_delete( $this->apc_tag_for_path( $path ) );
		}

		$yaml = Spyc::YAMLDump( $array );
		return file_put_contents($path, $yaml) !== false;
	}

	/**
	 * Hashes path tag for apc
	 *
	 * @param $path string
	 * @return string
	 **/
	protected function apc_tag_for_path( $path ) {
		/* Fix path with realpath before hashing. Fixes double slashes and relative paths */
		$tag = 'op5_config_' . md5( realpath($path) );
		return $tag;
	}
}

