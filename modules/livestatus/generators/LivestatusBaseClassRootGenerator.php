<?php

require_once( 'LivestatusClassGenerator.php' );

class LivestatusBaseClassRootGenerator extends LivestatusClassGenerator {
	
	private $structure;
	
	public function __construct( $name, $descr ) {
		$this->classname = 'Base'.$descr['class'];
	}
	
	public function generate( $fp ) {
		parent::generate( $fp );
		$this->init_class();
		$this->finish_class();
	}
}