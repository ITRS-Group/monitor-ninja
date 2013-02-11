<?php

class ORMWrapperGenerator extends class_generator {

	private $structure;
	private $modifiers = false;
	private $baseclasspath = false;

	public function __construct( $classname, $modifiers=false, $baseclasspath=false ) {
		$this->classname = $classname;
		$this->modifiers = $modifiers;
		$this->baseclasspath = $baseclasspath;
		$this->set_model();
		
		if( $this->modifiers === false ) {
			$this->modifiers = array();
		}
	}

	public function generate($skip_generated_note = true) {
		parent::generate($skip_generated_note);
		$baseclassname = 'Base'.$this->classname;
		if( $this->baseclasspath ) {
			$this->classfile($this->baseclasspath,true);
		}
		$this->init_class( $baseclassname, $this->modifiers );
		$this->finish_class();
	}
}
