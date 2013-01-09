<?php

require_once( '../buildlib.php' );

require_once( '../lalr_parser/LalrGenerator.php' );

require_once( 'op5/spyc.php' );

class Calculator_generator extends generator_module {
	protected function do_run() {
		$grammar = Spyc::YAMLLoad( $this->gen_dir.'grammar.yml' );
		
		$generator = new LalrGenerator( 'Calculator', $grammar );
		$generator->generate();
	}
}

$generator = new Calculator_generator('calculator');
$generator->run();