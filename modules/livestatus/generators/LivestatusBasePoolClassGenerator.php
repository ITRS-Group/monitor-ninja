<?php

require_once( 'LivestatusClassGenerator.php' );

class LivestatusBasePoolClassGenerator extends LivestatusClassGenerator {
	
	private $structure;
	
	public function __construct( $name, $descr ) {
		$this->classname = 'Base'.$descr['class'].'Pool';
	}
	
	public function generate( $fp ) {
		parent::generate( $fp );
		$this->init_class();
		$this->generate_setbuilder_all();
		$this->finish_class();
	}
	
	private function generate_setbuilder_all() {
		$this->init_function( 'all', array(), 'static' );
		$this->finish_function();
	}
}