<?php

class LivestatusBaseSetClassGenerator extends class_generator {
	private $name;
	private $structure;
	private $objectclass;
	
	public function __construct( $name, $descr ) {
		$this->name = $name;
		$this->structure = $descr;
		$this->objectclass = $descr['class'].self::$model_suffix;
		$this->classname = 'Base'.$descr['class'].'Set';
		$this->set_model();
	}
	
	public function generate() {
		parent::generate();
		$this->init_class( 'ObjectSet', array('abstract') );
		$this->variable('table',$this->name,'protected');
		$this->variable('class',$this->structure['class'].self::$model_suffix,'protected');
		$this->finish_class();
	}
}