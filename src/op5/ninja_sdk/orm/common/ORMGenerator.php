<?php

class ORMGeneratorException extends Exception {}

abstract class ORMGenerator extends class_generator {
	/**
	 * Name of the object class, like Host_Model
	 *
	 * Note that it's not the base class, but the wrapper class. Use this name
	 * for instancing an object or access static variables and methods in the
	 * object.
	 *
	 * @var string
	 */
	protected $obj_class;

	/**
	 * Name of the set class, like HostSet_Model
	 *
	 * Note that it's not the base class, but the wrapper class. Use this name
	 * for instancing an set or access static variables and methods in the set.
	 *
	 * @var string
	 */
	protected $set_class;

	/**
	 * Name of the pool class, like HostPool_Model
	 *
	 * Note that it's not the base class, but the wrapper class. Use this name
	 * to access static variables and methods in the pools.
	 *
	 * @var string
	 */
	protected $pool_class;

	/**
	 * Name of the table
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The structure definition of the current table, as defined in the structure
	 * file
	 *
	 * @var array
	 */
	protected $structure;

	/**
	 * The entire structure, as defined in the structure file, making it possible
	 * to access other tables structures within the same module.
	 *
	 * @var array
	 */
	protected $full_structure;

	/**
	 * Array of the names of the primary key columns, used to uniquely identify a
	 * specific object in the table.
	 *
	 * @var array
	 */
	protected $key;

	/**
	 * If the model should enable write support.
	 *
	 * @var bool
	 */
	protected $writable;


	/**
	 * The name of the parent class.
	 *
	 * @var string
	 */
	protected $parent_class;

	public function __construct( $name, $full_structure ) {
		$this->set_model();

		$this->name = $name;
		$this->structure = $full_structure[$name];
		$this->full_structure = $full_structure;

		$this->obj_class = $this->structure['class'] . self::$model_suffix;
		$this->set_class = $this->structure['class'] . 'Set' . self::$model_suffix;
		$this->pool_class = $this->structure['class'] . 'Pool' . self::$model_suffix;

		$this->key = $this->structure['key'];

		$this->writable = false;
		if(isset($this->structure['writable']))
			$this->writable = $this->structure['writable'];
	}

	protected function generate_common() {
		$this->init_function('class_obj');
		$this->write("return %s;", $this->obj_class);
		$this->finish_function();

		$this->init_function('class_set');
		$this->write("return %s;", $this->set_class);
		$this->finish_function();

		$this->init_function('class_pool');
		$this->write("return %s;", $this->pool_class);
		$this->finish_function();

		$this->init_function('key_columns');
		$this->write("return %s;", $this->structure['key']);
		$this->finish_function();

		$this->init_function('get_table');
		$this->write("return %s;", $this->name);
		$this->finish_function();
	}
}
