<?php

class LalrLexerPHPGenerator extends class_generator {
	private $grammar;
	
	public function __construct( $parser_name, $grammar ) {
		$this->classname = $parser_name . "Lexer";
		$this->grammar = $grammar;
		$this->set_library();
	}
	
	public function generate() {
		parent::generate();
		
		$this->init_class();
		$this->variable( 'buffer' );
		$this->variable( 'position', 0 );
		$this->generate_constructor();
		$this->generate_fetch_token();
		$this->finish_class();
	}
	
	private function generate_constructor() {
		$this->init_function( '__construct', array( 'buffer' ) );
		$this->write( '$this->buffer = $buffer;' );
		$this->finish_function();
	}
	
	private function generate_fetch_token() {
		$this->init_function( 'fetch_token', array() );
		
		$this->write('do {'); /* Until token is found */
		
		$this->write('$length = false;');
		$this->write('$token = false;');
		$this->write('$value = false;');
		$this->write('$token_pos = $this->position;');

		$this->write();
		$this->comment( "Match end token" );
		$this->write('if( strlen( $this->buffer ) == 0 ) {');
		$this->write('$length = 0;');
		$this->write('$token = \'end\';');
		$this->write('}');
		
		foreach( $this->grammar['tokens'] as $name => $match ) {
			$this->write();
			$this->comment( "Match token: $name" );
			$this->write( 'if( $length === false && preg_match( '.var_export( $match, true ).', $this->buffer, $matches ) ) {' );
			$this->write(     '$length = strlen( $matches[1] );' );
			if( substr($name,0,1) != '.' ) {
				$this->write(     '$token = '.var_export( $name, true ).';' );
				$this->write(     '$value = $matches[1];' );
			}
			$this->write( '}' );
		}
		
		$this->write();
		$this->comment( 'Exit if no match' );
		$this->write( 'if( $length === false ) return false;' );
		$this->write();
		$this->comment( 'Remove token from buffer, and move length forward' );
		$this->write( '$this->buffer = substr( $this->buffer, $length );');
		$this->write( '$this->position += $length; ');
		$this->write( '} while( $token === false );');
		$this->write( 'return array( $token, $value, $token_pos, $length );');
		
		$this->finish_function();
	}
}