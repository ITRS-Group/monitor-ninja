<?php

require_once( '../buildlib.php' );

require_once( 'op5/generators/parsegen/LalrGenerator.php' );
require_once( 'op5/generators/parsegen/LalrGrammarParser.php' );

class Calculator_generator extends generator_module {
	protected function do_run() {
		$grammar_file = file_get_contents( $this->gen_dir.'grammar.txt' );
		$grammar_parser = new LalrGrammarParser();
		$grammar = $grammar_parser->parse($grammar_file);
		
		$generator = new LalrGenerator( 'Calculator', $grammar );
		$generator->generate();
	}
}

$generator = new Calculator_generator('calculator');
$generator->run();