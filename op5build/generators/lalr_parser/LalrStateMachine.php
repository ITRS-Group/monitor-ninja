<?php

require_once( 'LalrItem.php' );
require_once( 'LalrState.php' );
require_once( 'LalrGrammar.php' );

class LalrStateMachine {
	private $grammar;
	private $parser_name;
	private $states;
	private $statetable;
	
	public function __construct( $parser_name, LalrGrammar $grammar ) {
		$this->parser_name = $parser_name;
		$this->grammar = $grammar;
		
		$this->build_states();
		$this->build_table();
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
				
				if( !$this->has_state( $sub_state ) ) {
					$state_queue[] = $sub_state;
				}
			}
		}
	}
	
	private function build_table() {
		$this->statetable = array();
		foreach( $this->states as $i => $state ) {
			$transistions = array();
			
			/* reduce */
			foreach( $state->closure() as $item ) {
				if( $item->complete() ) {
					foreach( $this->grammar->follow( $item->generates() ) as $sym ) {
						$transistions[$sym] = array('reduce', $item->get_name());
					}
				}
			}
			
			/* shift */
			foreach( $state->next_symbols() as $sym ) {
				$next_state = $state->take( $sym );
				$j = $this->get_state_id( $next_state );
				if( $j === false ) {
					print "ERROR\n";
				}
				if( $this->grammar->is_terminal($sym) ) {
					$transistions[$sym] = array('shift', $j);
				}
			}
			
			/* goto */
			foreach( $this->grammar->non_terminals() as $sym ) {
				$next_state = $state->take( $sym );
				$j = $this->get_state_id( $next_state );
				if( $j !== false ) {
					$transistions[$sym] = array( 'goto', $j );
				}
			}
			
			$this->statetable[$i] = $transistions;
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
	
	public function __toString() {
		$outp = "";
		foreach( $this->states as $i => $state ) {
			$outp .= "===== State $i =====\n";
			$outp .= $state;
			$outp .= "\n";

			foreach( $this->grammar->symbols() as $sym ) {
				$outp .= sprintf( "%20s: ", $sym );
				if( isset( $this->statetable[$i][$sym] ) ) {
					list( $a, $t ) = $this->statetable[$i][$sym];
					$outp .= "$a $t";
				}
				$outp .= "\n";
			}
			$outp .= "\n";
		}
		return $outp;
	}
}