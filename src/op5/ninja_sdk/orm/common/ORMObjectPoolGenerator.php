<?php

require_once('ORMGenerator.php');

abstract class ORMObjectPoolGenerator extends ORMGenerator {
	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	public function __construct( $name, $full_structure ) {
		parent::__construct($name, $full_structure);
		$this->classname = 'Base'.$this->structure['class'].'Pool';

		$this->parent_class = 'ObjectPool';
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( $this->parent_class, array('abstract') );

		if( isset($this->structure['default_sort']) ) {
			$this->variable('default_sort',$this->structure['default_sort'],'static protected');
		} else {
			$this->variable('default_sort',array(),'static protected');
		}

		$this->generate_common();

		$this->generate_backend_specific_functions();

		$this->generate_stats();
		$this->generate_count();
		$this->generate_it();

		if($this->writable) {
			$this->generate_insert_single();
			$this->generate_update();
			$this->generate_delete();
		}

		$this->generate_pool();
		$this->generate_get_driver();
		$this->generate_get_full_structure();
		$this->generate_table_for_field();
		$this->generate_setbuilder_all();
		$this->generate_setbuilder_none();
		$this->generate_set_by_key();
		$this->generate_fetch_by_key();
		$this->generate_get_all_columns_list();

		$this->finish_class();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function generate_pool() {
		$this->init_function( 'pool', array('name'), 'static', array('name' => false));
		$this->write( 'if( $name === false ) return new static();');
		$this->write( 'return parent::pool($name);' );
		$this->finish_function();
	}


	private function generate_get_driver() {
		$this->init_function( 'get_driver', array(), 'static');
		/**
		 * SQLDrivers currently depend on the interface
		 * of Kohana's Database class, which is a parameterized
		 * singleton type of thing. This means that we need to parameterize
		 *
		 * the driver itself in order to support switching out the driver
		 * (for example, when mocking) for one database instance at a time.
		 **/
		if (isset($this->structure['db_instance'])) {
			$driver_instance_name = "ORMDriver" . $this->structure['source'] . " " . $this->structure['db_instance'];
		}
		else {
			$driver_instance_name = "ORMDriver" . $this->structure['source'] . " default";
		}
		$this->write('return op5objstore::instance()->obj_instance_callback("' . $driver_instance_name . '", function() { return new ORMDriver' . $this->structure["source"] . '();});');
		$this->finish_function();
	}

	private function generate_get_full_structure() {
		$this->init_function( 'get_full_structure', array(), 'static');
		$this->write('return ' . var_export($this->full_structure, true) . ';');
		$this->finish_function();

	}
	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function generate_table_for_field() {
		$this->init_function( 'get_table_for_field', array('name') );
		$this->write( 'switch($name) {' );
		foreach( $this->structure['structure'] as $field => $type ) {
			if( is_array( $type ) ) {
				$this->write( 'case %s:', $field );
				$this->write( 'return %s;', $this->lookup_class( $type[0] ) );
			}
		}
		$this->write( '}' );
		$this->write( 'return false;' );
		$this->finish_function();
	}

	public function generate_get_all_columns_list() {
		$columns = array();
		$subobjs = array();
		foreach ($this->structure['structure'] as $name => $type) {
			if (is_array($type)) {
				$subobjs[$name] = $type;
			} else {
				$columns[] = $name;
			}
		}
		$this->init_function('get_all_columns_list', array('include_nested'), array('static'), array('include_nested'=>true));
		$this->write('$raw_columns = %s;', $columns);
		$this->write('$sub_columns = array();');
		$this->write('if ($include_nested) {');
		foreach ($subobjs as $name => $type) {
			$this->write('$obj_cols = '.$type[0].'Pool'.self::$model_suffix.'::get_all_columns_list(false);');
			$this->write('foreach ($obj_cols as $name) {');
			$this->write('$sub_columns[] = %s.$name;', $name.'.');
			$this->write('}');
		}
		$this->write('}');
		$this->write('$virtual_columns = array_keys('.$this->obj_class.'::rewrite_columns());');
		$this->write('return array_merge($sub_columns, $raw_columns, $virtual_columns);');
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function lookup_class( $class ) {
		foreach( $this->full_structure as $table => $struct ) {
			if( $struct['class'] == $class ) {
				return $table;
			}
		}
		return false;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function generate_setbuilder_all() {
		$this->init_function( 'all', array(), 'static' );
		$this->write('return new '.$this->set_class.'(new LivestatusFilterAnd());');
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function generate_setbuilder_none() {
		$this->init_function( 'none', array(), 'static' );
		$this->write('return new '.$this->set_class.'(new LivestatusFilterOr());');
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function generate_set_by_key() {
		$this->init_function( 'set_by_key', array('key'), 'static' );
		if(count($this->structure['key']) > 0) {
			// if count(key) == 0, explode is always 1
			$this->write('$parts = explode(";",$key);');
			$this->write('if(count($parts) != %s) {', count($this->structure['key']));
			$this->write(    'return self::none();');
			$this->write('}');
		} else {
			$this->write('if($key != "") {');
			$this->write(    'return self::none();');
			$this->write('}');
		}

		$set_fetcher = 'return self::all()';
		$args = array();
		foreach($this->structure['key'] as $i => $field) {
			$set_fetcher .= '->reduce_by(%s,$parts[%s],"=")';
			$args[] = $field;
			$args[] = $i;
		}
		array_unshift($args,$set_fetcher.';');
		call_user_func_array(array($this,'write'), $args);
		$this->finish_function();
	}

	private function generate_fetch_by_key() {
		$this->init_function( 'fetch_by_key', array('key'), 'static' );
		$this->write('$set = self::set_by_key($key);');
		$this->write('if($set === false) {');
		$this->write('return false;');
		$this->write('}');
		$this->write('foreach(self::set_by_key($key) as $obj) {');
		$this->write(    'return $obj;');
		$this->write('}');
		$this->write('return false;');
		$this->finish_function();
	}

	public function generate_update() {
		$this->init_function('update', array('filter', 'values'));
		$this->write('try {');
		$this->write(	'return self::get_driver()->update("' . $this->name . '", ' . var_export($this->structure, true) . ', $filter, $values);');
		$this->write('} catch (Exception $e) {');
		$this->write(	'throw new ORMDriverException($e->getMessage(), %s, false, $e);', $this->name);
		$this->write('}');
		$this->finish_function();
	}


	public function generate_delete() {
		$this->init_function('delete', array('filter'));
		$this->write('try {');
		$this->write(	'return self::get_driver()->delete("' . $this->name . '", ' . var_export($this->structure, true) . ', $filter);');
		$this->write('} catch (Exception $e) {');
		$this->write(	'throw new ORMDriverException($e->getMessage(), %s, false, $e);', $this->name);
		$this->write('}');
		$this->finish_function();
	}

	public function generate_insert_single() {
		$this->init_function('insert_single', array('values'));
		$this->write('try {');
		$this->write(	'return self::get_driver()->insert_single("' . $this->name . '", ' . var_export($this->structure, true) . ', $values);');
		$this->write('} catch (Exception $e) {');
		$this->write(	'throw new ORMDriverException($e->getMessage(), %s, false, $e);', $this->name);
		$this->write('}');
		$this->finish_function();
	}

	abstract public function generate_backend_specific_functions();
	/**
	 * Generate stats
	 *
	 * @return void
	 **/
	public function generate_stats() {
		$this->init_function('stats',array('filter','intersections'));
		$this->write('try {');
		$this->write(	'return self::get_driver()->stats("' . $this->name . '", ' . var_export($this->structure, true) . ', $filter, $intersections);');
		$this->write('} catch (Exception $e) {');
		$this->write(	'throw new ORMDriverException($e->getMessage(), %s, false, $e);', $this->name);
		$this->write('}');
		$this->finish_function();
	}

	/**
	 * Generate count
	 *
	 * @return void
	 **/
	public function generate_count() {
		$this->init_function('count', array('filter'));
		$this->write('try {');
		$this->write(	'return self::get_driver()->count("' . $this->name . '", ' . var_export($this->structure, true) . ', $filter);');
		$this->write('} catch (Exception $e) {');
		$this->write(	'throw new ORMDriverException($e->getMessage(), %s, false, $e);', $this->name);
		$this->write('}');
		$this->finish_function();
	}

	/**
	 * Generates set
	 *
	 * @return void
	 **/
	public function generate_it() {
		$this->init_function( 'it', array('filter','columns','order','limit','offset'), array(), array('order' => array(), 'limit'=>false, 'offset'=>false) );
		$this->write('try {');
		$this->write(	'return self::get_driver()->it("' . $this->name . '", ' . var_export($this->structure, true) . ', $filter, $columns, $order, $limit, $offset);');
		$this->write('} catch (Exception $e) {');
		$this->write(	'throw new ORMDriverException($e->getMessage(), %s, false, $e);', $this->name);
		$this->write('}');
		$this->finish_function();

	}
}
