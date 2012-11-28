<?php

require_once( 'LalrLexerPHPGenerator.php' );
require_once( 'LalrStateMachine.php' );
require_once( 'LalrGrammar.php' );

class LalrGenerator {
	private $name;
	private $grammar;
	
	public function __construct( $name, $grammar ) {
		$this->name = $name;
		$this->grammar = new LalrGrammar( $grammar );
	}
	
	public function generate() {
/*		$generator = new LalrLexerPHPGenerator( $this->name, $this->grammar );
		$generator->generate();*/
		
		$generator = new LalrStateMachine( $this->name, $this->grammar );
		print $generator;
	}
}