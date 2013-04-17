<?php

class ORMObjectPoolGenerator extends class_generator {
	private $name;
	private $structure;
	private $objectclass;

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	public function __construct( $name, $descr ) {
		$this->name = $name;
		$this->structure = $descr;
		$this->objectclass = $descr[$name]['class'].self::$model_suffix;
		$this->classname = 'Base'.$descr[$name]['class'].'Pool';
		$this->set_model();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( 'ObjectPool', array('abstract') );
		$this->variable('table',$this->name,'protected');
		$this->generate_pool();
		$this->generate_table_for_field();
		$this->generate_setbuilder_all();
		$this->generate_setbuilder_none();
		$this->generate_set_by_key();
		$this->generate_fetch_by_key();
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

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function generate_table_for_field() {
		$this->init_function( 'get_table_for_field', array('name') );
		$this->write( 'switch($name) {' );
		foreach( $this->structure[$this->name]['structure'] as $field => $type ) {
			if( is_array( $type ) ) {
				$this->write( 'case %s:', $field );
				$this->write( 'return %s;', $this->lookup_class( $type[0] ) );
			}
		}
		$this->write( '}' );
		$this->write( 'return false;' );
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function lookup_class( $class ) {
		foreach( $this->structure as $table => $struct ) {
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
		$this->write('return new '.$this->structure[$this->name]['class'].'Set'.self::$model_suffix.'(new LivestatusFilterAnd());');
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function generate_setbuilder_none() {
		$this->init_function( 'none', array(), 'static' );
		$this->write('return new '.$this->structure[$this->name]['class'].'Set'.self::$model_suffix.'(new LivestatusFilterOr());');
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function generate_set_by_key() {
		$this->init_function( 'set_by_key', array('key'), 'static' );

		$this->write('$parts = explode(";",$key);');
		$this->write('if(count($parts) != %s) {', count($this->structure[$this->name]['key']));
		$this->write(    'return false;');
		$this->write('}');

		$set_fetcher = 'return self::all()';
		$args = array();
		foreach($this->structure[$this->name]['key'] as $i => $field) {
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
		$this->write('foreach(self::set_by_key($key) as $obj) {');
		$this->write(    'return $obj;');
		$this->write('}');
		$this->write('return false;');
		$this->finish_function();
	}
}
