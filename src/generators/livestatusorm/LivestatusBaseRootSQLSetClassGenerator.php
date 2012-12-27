<?php

class LivestatusBaseRootSQLSetClassGenerator extends class_generator {
	private $name;
	private $structure;
	private $objectclass;
	
	public function __construct( $name, $descr ) {
		$this->name = $name;
		$this->structure = $descr;
		$this->classname = "BaseObjectSQLSet";
		$this->set_model();
	}
	
	public function generate() {
		parent::generate();
		$this->init_class( 'ObjectSet', array('abstract') );
		$this->generate_stats();
		$this->generate_count();
		$this->generate_it();
		$this->finish_class();
	}
	
	public function generate_stats() {
		$this->init_function('stats',array('intersections'));
		$this->write('return array();');
		$this->finish_function();
	}
	
	public function generate_count() {
		$this->init_function('count');
		$this->write('return 0;');
		$this->finish_function();
	}
	
	public function generate_it() {
		$this->init_function( 'it', array('columns','order','limit','offset'), array(), array('limit'=>false, 'offset'=>false) );
		$this->write('$sql = $this->filter->visit(new LivestatusFilterBuilderVisitor(), false);');

		$this->write('return new LivestatusSetIterator(array(), array(), $this->class);');
		$this->finish_function();
	}
}