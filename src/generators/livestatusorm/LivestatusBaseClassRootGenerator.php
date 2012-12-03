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
		$this->write( 'protected $_table = null;' );
		$this->finish_class();
	}
}