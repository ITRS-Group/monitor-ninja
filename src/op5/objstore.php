<?php

/**
 * This is a provider for singleton objects in op5lib. This tracks the loaded
 * singletons, and makes it possible to request a module of one kind.
 *
 * This is mostly useful to make the objects testable, because it is possilbe
 * through this interface to preload objects, and mock objects if nessecary.
 *
 * This class is the entrypoint for singletons in op5lib, which makes this, by
 * obvious reasons, a singleton itself.
 *
 * Objects should wrap this in an instance-method
 */
class op5objstore {
	/**********************************
	 * Singleton behaviour
	**********************************/
	protected static $instance = false;

	/**
	 *
	 * @return op5objstore
	 */
	public static function instance() {
		if( self::$instance === false ) {
			self::$instance = self::factory();
		}
		return self::$instance;
	}

	public static function factory() {
		return new self();
	}

	public function __construct() {
		$this->mock_clear();
		$this->clear();
	}


	/**********************************
	 * Mock environment
	**********************************/

	protected $mock_objects;

	public function mock_clear() {
		$this->mock_objects = array();
	}

	public function mock_add( $name, $object ) {
		$name = strtolower( $name );
		$this->mock_objects[$name] = $object;
	}

	public function mock_del( $name ) {
		$name = strtolower( $name );
		unset( $this->mock_objects[$name] );
	}

	/**********************************
	 * Object storage
	**********************************/

	protected $objects;

	public function clear() {
		$this->objects = array();
	}

	public function unload( $name ) {
		unset( $this->objects[$name] );
	}

	public function obj_instance( $name, $arg=false ) {
		$name = strtolower( $name );
		return $this->obj_instance_callback($name,
				function() use ($name, $arg) {
					return new $name( $arg );
				}
		);
	}

	public function obj_instance_callback( $name, $callback ) {
		$name = strtolower( $name );
		/* If mocking an object, use that instead */
		if( isset($this->mock_objects[$name]))
			return $this->mock_objects[$name];

		/* If not loaded, load an instance */
		if( !isset($this->objects[$name]) )
			$this->objects[$name] = $callback();

		/* Return the instance */
		return $this->objects[$name];
	}


}
