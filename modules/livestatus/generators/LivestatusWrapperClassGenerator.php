<?php

require_once( 'LivestatusClassGenerator.php' );

class LivestatusWrapperClassGenerator extends LivestatusClassGenerator {
	
	private $structure;
	private $modifiers = array();
	
	public function __construct( $name, $descr, $nameformat="%s" ) {
		$this->classname = sprintf( $nameformat, $descr['class'] );
		if (isset($descr['modifiers'])) {
			$this->modifiers = $descr['modifiers'];
		}
	}
	
	public function generate( $fp ) {
		parent::generate( $fp );
		$this->classfile( "base/".'Base'.$this->classname.".php" );
		$this->init_class( 'Base'.$this->classname, $this->modifiers );
		$this->finish_class();
	}
}