<?php

class lalrtest_visitor {
	public function visit_expr_not( $arg ) {
		print "Call: visit_expr_not($arg)\n";
		return "!$arg";
	}
	
	public function __call( $method, $args ) {
		print "Call: $method(".implode( ', ', $args ).")\n";
		return implode(', ',$args);
	}
}

class testvisit {
	public function __call( $name, $args ) {
		return str_replace('visit_','',$name).'('.implode(',',$args).')';
	}
}


class lalrtest_Controller extends Ninja_Controller {
	public function index() {
		$string = $GLOBALS['argv'][2];
		try {
			print "Parsing: $string\n";
			
			$parser = new LSFilter( new LSFilterPreprocessor_Core(), new testvisit() );
			print_r( $parser->parse( $string ) );
			
		} catch( Exception $e ) {
			print "Exception: ".$e->getMessage()."\n\n";
		}
		die();
	}
}