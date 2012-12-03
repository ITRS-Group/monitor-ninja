<?php

class LivestatusBasePoolClassGenerator extends class_generator {
	
	private $structure;
	private $objectclass;
	
	public function __construct( $name, $descr ) {
		$this->name = $name;
		$this->set_model();
		$this->objectclass = $descr['class'].$this->class_suffix;
		$this->classname = 'Base'.$descr['class'].'Pool';
	}
	
	public function generate() {
		parent::generate();
		$this->init_class( 'ObjectPool', array('abstract') );
		$this->generate_pool();
		$this->generate_setbuilder_all();
		$this->finish_class();
	}
	
	private function generate_pool() {
		$this->init_function( 'pool', array('name'), 'static' );
		$this->write( 'if( $name === false ) return new self();');
		$this->write( 'return parent::pool($name);' );
		$this->finish_function();
	}
	
	private function generate_setbuilder_all() {
		$this->init_function( 'all', array() );
		$this->write('return new LivestatusSet('
				.var_export($this->name,true)
				.','
				.var_export($this->objectclass,true)
				.');');
		$this->finish_function();
	}
}