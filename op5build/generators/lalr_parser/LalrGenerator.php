<?php

require_once( 'LalrLexerPHPGenerator.php' );

class LalrGenerator {
	private $name;
	private $grammar;
	
	public function __construct( $name, $grammar ) {
		$this->name = $name;
		$this->grammar = $grammar;
	}
	
	public function generate() {
		$generator = new LalrLexerPHPGenerator( $this->name, $this->grammar );
		$generator->generate();
	}
}