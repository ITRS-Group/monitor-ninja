<?php

class LivestatusBaseRootPoolClassGenerator extends class_generator {
	
	private $structure;
	private $objectclass;
	
	public function __construct( $name, $full_structure ) {
		$this->name = $name;
		$this->classname = 'BaseObjectPool';
		$this->full_structure = $full_structure;
		$this->set_model();
	}
	
	public function generate() {
		parent::generate();
		$this->init_class( false, array('abstract') );
		$this->generate_pool();
		$this->finish_class();
	}
	
	private function generate_pool() {
		$this->init_function( 'pool', array('name'), 'static' );
		$this->write( 'switch( $name ) {' );
		foreach( $this->full_structure as $name => $struct ) {
			$this->write( 'case %s:', $name );
			$this->write( 'return new '.$struct['class'].'Pool'.$this->class_suffix.'();' );
		}
		$this->write( '}' );
		$this->write( 'return null;' );
		$this->finish_function();
	}
}