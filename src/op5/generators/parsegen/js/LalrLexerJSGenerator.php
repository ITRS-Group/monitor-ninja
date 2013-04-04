<?php

class LalrLexerJSGenerator extends js_class_generator {
	private $grammar;

	public function __construct( $parser_name, $grammar ) {
		$this->classname = $parser_name . "Lexer";
		$this->grammar = $grammar->get_tokens();
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);

		$this->init_class( array( 'buffer', 'visitor' ));
		$this->write( 'this.buffer = buffer;' );
		$this->write( 'this.visitor = visitor;' );
		$this->write( 'this.expression = buffer;' );
		$this->variable( 'position', 0 );

		$this->generate_fetch_token();
		$this->finish_class();
	}


	private function generate_fetch_token() {
		$this->init_function( 'fetch_token', array() );

		$this->write('do {'); /* Until token is found */

		$this->write('var length = -1;');
		$this->write('var token = 0;');
		$this->write('var value = 0;');
		$this->write('var token_pos = this.position;');

		$this->write();
		$this->comment( "Match end token" );
		$this->write('if( this.buffer.length == 0 ) {');
		$this->write('length = 0;');
		$this->write('token = "end";');
		$this->write('}');

		foreach( $this->grammar as $name => $match ) {
			$this->write();
			$this->comment( "Match token: $name" );
			$this->write( 'if( length < 0 ) {' );
			$this->write( 'var matches = this.buffer.match('.$match.');');
			$this->write( 'if( matches != null ) {' );
			$this->write( 'length = matches[1].length;' );
			if( substr($name,0,1) != '_' ) {
				$this->write( 'token = '.json_encode( $name ).';' );
				$this->write( 'value = this.visitor.preprocess_'.$name.'(matches[1]);' );
			}
			$this->write( '}' );
			$this->write( '}' );
		}

		$this->write();
		$this->comment( 'Error if no match' );
		$this->write( 'if( length < 0 ) {' );
		$this->write( 'throw "Unknown token: " + this.buffer.substring(0,30);' );
		$this->write( '}' );
		$this->write();
		$this->comment( 'Remove token from buffer, and move length forward' );
		$this->write( 'this.buffer = this.buffer.substr( length );');
		$this->write( 'this.position += length; ');
		$this->write( '} while( token == 0 );');
		$this->write( 'return [token, value, token_pos, length ];');

		$this->finish_function();
	}
}