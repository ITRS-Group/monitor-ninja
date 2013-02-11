<?php

class ORMRootPoolGenerator extends class_generator {
	
	private $structure;
	private $objectclass;
	
	public function __construct() {
		$this->classname = 'BaseObjectPool';
		$this->set_model();
	}
	
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( false, array('abstract') );
		$this->variable('table',false,'protected');
		$this->variable('table_classes',false,'private static');
		$this->generate_pool();
		$this->generate_load_table_classes();
		$this->finish_class();
	}
	
	private function generate_pool() {
		$this->init_function( 'pool', array('name'), 'static' );
		
		$this->write( 'if( self::$table_classes === false ) {' );
		$this->write( 'self::$table_classes = static::load_table_classes();' );
		$this->write( '}' );
		
		$this->write( 'if( isset(self::$table_classes[$name]) ) {' );
		$this->write(     'return new self::$table_classes[$name]["pool"]();' );
		$this->write( '}' );
		
		$this->write( 'throw new Exception("Unknown table ".$name);' );
		$this->finish_function();
	}
	private function generate_load_table_classes() {
		$this->init_function( 'load_table_classes', array('name'), 'static' );
		$this->write( 'return array();' );
		$this->finish_function();
	}
}
