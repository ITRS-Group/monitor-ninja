<?php

require_once( '../buildlib.php' );

require_once( '../lalr_parser/LalrGenerator.php' );

require_once( 'op5/spyc.php' );

class LSFilter_generator extends generator_module {
	protected function do_run() {
		$grammar = Spyc::YAMLLoad( $this->gen_dir.'grammar.yml' );
		
		$generator = new LalrGenerator( 'LSFilter', $grammar );
		$generator->generate();
	}
}

$generator = new LSFilter_generator('lsfilter');
$generator->run();