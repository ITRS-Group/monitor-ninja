<?php

class LivestatusWrapperClassGenerator extends class_generator {

	private $structure;
	private $modifiers = array();
	private $classpaths = array();

	public function __construct( $name, $descr, $nameformat="%s", $classpaths ) {
		$this->classname = sprintf( $nameformat, $descr['class'] );
		if (isset($descr['modifiers'])) {
			$this->modifiers = $descr['modifiers'];
		}
		$this->classpaths = $classpaths;
		$this->set_model();
	}

	public function generate() {
		parent::generate( true );
		$baseclassname = 'Base'.$this->get_classname();
		if( isset( $this->classpaths[$baseclassname] ) ) {
			$this->classfile($this->classpaths[$baseclassname],true);
		}
		$this->init_class( 'Base'.$this->classname, $this->modifiers );
		$this->finish_class();
	}
}