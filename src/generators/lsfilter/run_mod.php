<?php

require_once( '../buildlib.php' );

require_once( '../lalr_parser/LalrGenerator.php' );
require_once( '../lalr_parser/LalrGrammarParser.php' );

class LSFilter_generator extends generator_module {
	protected function do_run() {
		$grammar_file = file_get_contents( $this->gen_dir.'grammar.txt' );
		$grammar_parser = new LalrGrammarParser();
		$grammar = $grammar_parser->parse($grammar_file);
		
		$generator = new LalrGenerator( 'LSFilter', $grammar );
		$generator->generate();
	}
}

$generator = new LSFilter_generator('lsfilter');
$generator->run();