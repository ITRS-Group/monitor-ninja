<?php

class LalrPHPGenerator extends class_generator {
	public function __construct( $parser_name ) {
		$this->classname = $parser_name;
		$this->set_library();
	}
	
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		
		$this->init_class();
		$this->variable('preprocessor', null);
		$this->variable('visitor', null);
		$this->generate_construct();
		$this->generate_parse();
		$this->finish_class();
	}
	
	private function generate_construct() {
		$this->init_function( '__construct', array( 'preprocessor', 'visitor' ) );
		$this->write( '$this->preprocessor = $preprocessor;' );
		$this->write( '$this->visitor = $visitor;');
		$this->finish_function();
	}
	
	private function generate_parse() {
		$this->init_function( 'parse', array( 'string' ) );
		$this->write( '$lexer = new '.$this->classname.'Lexer( $string, $this->preprocessor );' );
		$this->write( '$parser = new '.$this->classname.'Parser( $this->visitor );' );
		$this->write( 'return $parser->parse( $lexer, $string );' );
		$this->finish_function();
	}
}