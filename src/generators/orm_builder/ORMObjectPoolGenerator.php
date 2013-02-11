<?php

class ORMObjectPoolGenerator extends class_generator {
	private $name;
	private $structure;
	private $objectclass;
	
	public function __construct( $name, $descr ) {
		$this->name = $name;
		$this->structure = $descr;
		$this->objectclass = $descr[$name]['class'].self::$model_suffix;
		$this->classname = 'Base'.$descr[$name]['class'].'Pool';
		$this->set_model();
	}
	
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( 'ObjectPool', array('abstract') );
		$this->variable('table',$this->name,'protected');
		$this->generate_pool();
		$this->generate_table_for_field();
		$this->generate_setbuilder_all();
		$this->generate_setbuilder_none();
		$this->finish_class();
	}
	
	private function generate_pool() {
		$this->init_function( 'pool', array('name'), 'static' );
		$this->write( 'if( $name === false ) return new self();');
		$this->write( 'return parent::pool($name);' );
		$this->finish_function();
	}
	
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

	private function lookup_class( $class ) {
		foreach( $this->structure as $table => $struct ) {
			if( $struct['class'] == $class ) {
				return $table;
			}
		}
		return false;
	}
	
	private function generate_setbuilder_all() {
		$this->init_function( 'all', array(), 'static' );
		$this->write('return new '.$this->structure[$this->name]['class'].'Set'.self::$model_suffix.'(new LivestatusFilterAnd());');
		$this->finish_function();
	}
	
	private function generate_setbuilder_none() {
		$this->init_function( 'none', array(), 'static' );
		$this->write('return new '.$this->structure[$this->name]['class'].'Set'.self::$model_suffix.'(new LivestatusFilterOr());');
		$this->finish_function();
	}
}
