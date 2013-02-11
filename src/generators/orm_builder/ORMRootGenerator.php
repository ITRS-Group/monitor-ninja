<?php

class ORMRootGenerator extends class_generator {
	
	private $structure;
	
	public function __construct() {
		$this->classname = 'BaseObject';
		$this->set_model();
	}
	
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class();
		$this->variable( '_table', null, 'protected' );
		$this->variable( 'export', array('key'), 'protected' );
		$this->variable( 'rewrite_columns', array(), 'static public' );
		$this->generate_export();
		$this->generate_construct();
		$this->finish_class();
	}

	private function generate_construct() {
		$this->init_function( "__construct", array( 'values', 'prefix' ) );
		$this->finish_function();
	}
	
	private function generate_export() {
		$this->init_function('export');
		$this->write( '$result=array("_table" => $this->_table);');
		$this->write( 'foreach( $this->export as $field) {' );
		$this->write(     '$value = $this->{"get_$field"}();');
		$this->write(     'if( $value instanceof Object'.self::$model_suffix.' ) {');
		$this->write(          '$value = $value->export();');
		$this->write(     '}');
		$this->write(     '$result[$field] = $value;');
		$this->write( '}');
		$this->write( 'return $result;');
		$this->finish_function();
	}
}
