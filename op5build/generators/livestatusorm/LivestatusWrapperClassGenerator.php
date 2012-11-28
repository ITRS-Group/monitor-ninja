<?php

class LivestatusWrapperClassGenerator extends class_generator {
	
	private $structure;
	private $modifiers = array();
	
	public function __construct( $name, $descr, $nameformat="%s" ) {
		$this->classname = sprintf( $nameformat, $descr['class'] );
		if (isset($descr['modifiers'])) {
			$this->modifiers = $descr['modifiers'];
		}
		$this->set_model();
	}
	
	public function generate() {
		parent::generate( true );
		$this->init_class( 'Base'.$this->classname, $this->modifiers );
		$this->finish_class();
	}
}