<?php

require_once( 'LivestatusClassGenerator.php' );

class LivestatusWrapperClassGenerator extends LivestatusClassGenerator {
	
	private $structure;
	
	public function __construct( $name, $descr ) {
		$this->classname = $descr['class'];
	}
	
	public function generate( $fp ) {
		parent::generate( $fp );
		$this->classfile( "base/".'Base'.$this->classname.".php" );
		$this->init_class( 'Base'.$this->classname );
		$this->generate_construct();
		$this->finish_class();
	}
	private function generate_construct() {
		$this->init_function( "__construct", array( 'values', 'prefix' ) );
		$this->write("parent::__construct( \$values, \$prefix );");
		$this->finish_function();
	}
}