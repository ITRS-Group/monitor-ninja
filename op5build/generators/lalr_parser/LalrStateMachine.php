<?php

require_once( 'LalrItem.php' );
require_once( 'LalrState.php' );
require_once( 'LalrGrammar.php' );

class LalrStateMachine {
	private $grammar;
	private $parser_name;
	private $states;
	
	public function __construct( $parser_name, LalrGrammar $grammar ) {
		$this->parser_name = $parser_name;
		$this->grammar = $grammar;
		
		$this->build_states();
	}
	
	private function build_states() {
		$state_queue = array(
				new LalrState( $this->grammar->get('entry'), $this->grammar )
				);
		
		$this->states = array();
		
		while( count( $state_queue ) ) {
			$state = array_pop( $state_queue );
			$this->states[] = $state;
			
			$next_symbols = $state->next_symbols();
			
			foreach( $next_symbols as $sym ) {
				$sub_state = $state->take( $sym );
				
				print "Take: $sym\n";
				print $sub_state;
				
				if( !$this->has_state( $sub_state ) ) {
					$state_queue[] = $sub_state;
				}
			}
		}
		
		
		print "\n";
		foreach( $this->states as $i => $state ) {
			print "\n=== $i\n";
			print $state;
		}
	}
	
	private function has_state( $state ) {
		return $this->get_state_id( $state ) !== false;
	}
	
	private function get_state_id( $state ) {
		foreach( $this->states as $i=>$cur_state ) {
			if( $cur_state->equals($state) ) {
				return $i;
			}
		}
		return false;
	}
}