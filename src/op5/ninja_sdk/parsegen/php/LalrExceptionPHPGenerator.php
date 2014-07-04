<?php

class LalrExceptionPHPGenerator extends class_generator {
	public function __construct( $parser_name ) {
		$this->classname = $parser_name.'Exception';
		$this->set_library();
		$this->class_suffix = ''; // Override class suffix... Exception should not be Exception_Core
	}
	
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		
		$this->init_class('Exception');
		$this->variable('query');
		$this->variable('position');
		$this->generate_construct();
		$this->generate_getter('query');
		$this->generate_getter('position');
		$this->finish_class();
	}
	
	private function generate_construct() {
		$this->init_function( '__construct', array( 'message', 'query', 'position' ) );
		$this->write( 'parent::__construct($message);');
		$this->write( '$this->query = $query;' );
		$this->write( '$this->position = $position;');
		$this->finish_function();
	}
}