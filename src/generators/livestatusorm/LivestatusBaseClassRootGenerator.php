<?php

class LivestatusBaseClassRootGenerator extends class_generator {
	
	private $structure;
	
	public function __construct( $name, $descr ) {
		$this->classname = 'Base'.$descr['class'];
		$this->set_model();
	}
	
	public function generate() {
		parent::generate();
		$this->init_class();
		$this->variable( '_table', null, 'protected' );
		$this->variable( 'export', array(), 'protected' );
		$this->generate_export();
		$this->finish_class();
	}
	
	private function generate_export() {
		$this->init_function('export');
		$this->write( '$result=array();');
		$this->write( 'foreach( $this->export as $field) {' );
		$this->write(     '$value = $this->{"get_$field"}();');
		$this->write(     'if( $value instanceof ObjectRoot'.self::$model_suffix.' ) {');
		$this->write(          '$value = $value->export();');
		$this->write(     '}');
		$this->write(     '$result[$field] = $value;');
		$this->write( '}');
		$this->write( 'return $result;');
		$this->finish_function();
	}
}