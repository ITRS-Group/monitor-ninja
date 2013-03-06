<?php

class LalrJSGenerator extends js_class_generator {
	public function __construct( $parser_name ) {
		$this->classname = $parser_name;
	}
	
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		
		$this->init_class(array('preprocessor','visitor'));
		$this->write( 'this.preprocessor = preprocessor;' );
		$this->write( 'this.visitor = visitor;');
		$this->generate_parse();
		$this->finish_class();
	}
	
	private function generate_parse() {
		$this->init_function( 'parse', array( 'string' ) );
		$this->write( 'var lexer = new '.$this->classname.'Lexer( string, this.preprocessor );' );
		$this->write( 'var parser = new '.$this->classname.'Parser( this.visitor );' );
		$this->write( 'return parser.parse( lexer );' );
		$this->finish_function();
	}
}