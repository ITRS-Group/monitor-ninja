<?php

require_once( 'LivestatusClassGenerator.php' );

class LivestatusAutoloaderGenerator extends LivestatusClassGenerator {
	private $classes = array();
	
	public function __construct( $classes ) {
		$this->classname = "LivestatusAutoloader";
		$this->classes = $classes;
	}
	
	public function generate( $fp ) {
		parent::generate( $fp );
		
		$this->init_class();
		$this->write( 'private $classes = '.var_export($this->classes, true).";" );
		$this->write( 'private $base_path;' );

		$this->init_function( '__construct' );
		$this->write( '$this->base_path = dirname(dirname(__FILE__));' );
		$this->write( 'spl_autoload_register(array($this,"autoload"));' );
		$this->finish_function();
		
		$this->init_function( 'autoload', array('name') );
		$this->write( 'require_once($this->base_path."/".$this->classes[$name]);' );
		$this->finish_function();
		
		$this->finish_class();
	}
}