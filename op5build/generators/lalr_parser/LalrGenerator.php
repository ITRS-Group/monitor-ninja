<?php

require_once( 'php/LalrLexerPHPGenerator.php' );
require_once( 'php/LalrPreprocessorPHPGenerator.php' );
require_once( 'php/LalrParserPHPGenerator.php' );
require_once( 'php/LalrVisitorPHPGenerator.php' );
require_once( 'php/LalrPHPGenerator.php' );
require_once( 'js/LalrLexerJSGenerator.php' );
require_once( 'js/LalrParserJSGenerator.php' );
require_once( 'html/LalrHTMLVisualizationGenerator.php' );
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
		print "- Building parser state table\n";
		$fsm = new LalrStateMachine( $this->grammar );

		print "- Building PHP Lexer\n";
		$generator = new LalrLexerPHPGenerator( $this->name, $this->grammar );
		$generator->generate();
		$generator = new LalrPreprocessorPHPGenerator( $this->name, $this->grammar );
		$generator->generate();
		print "- Building PHP Parser\n";
		$generator = new LalrParserPHPGenerator( $this->name, $fsm, $this->grammar );
		$generator->generate();
		$generator = new LalrVisitorPHPGenerator( $this->name, $this->grammar );
		$generator->generate();
		print "- Building PHP Wrapper\n";
		$generator = new LalrPHPGenerator( $this->name );
		$generator->generate();

		print "- Building Javascript Lexer\n";
		$generator = new LalrLexerJSGenerator( $this->name, $this->grammar );
		$generator->generate();
		print "- Building Javascript parser\n";
		$generator = new LalrParserJSGenerator( $this->name, $fsm, $this->grammar );
		$generator->generate();

		print "- Building HTML visualization of parser state table, and lexer\n";
		$generator = new LalrHTMLVisualizationGenerator( $this->name, $fsm, $this->grammar );
		$generator->generate();
	}
}