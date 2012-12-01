<?php

require_once( 'LalrItem.php' );
require_once( 'LalrState.php' );
require_once( 'LalrGrammar.php' );

class LalrStateMachine {
	private $grammar;
	private $states;
	private $statetable;
	
	public function __construct( LalrGrammar $grammar ) {
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
						if( isset( $transistions[$sym] ) ) {
							throw new GeneratorException( "Disambigous grammar\n".var_export($transistions,true)."\nAdding: $sym\n".$state );
						}
						$transistions[$sym] = array('reduce', $item->get_name());
					}
				}
			}
			
			/* shift */
			foreach( $state->next_symbols() as $sym ) {
				$next_state = $state->take( $sym );
				$j = $this->get_state_id( $next_state );
				if( $j === false ) {
					throw new GeneratorException( "ERROR in parser generator, should never happend...");
				}
				if( $this->grammar->is_terminal($sym) ) {
					if( isset( $transistions[$sym] ) ) {
						throw new GeneratorException( "Disambigous grammar\n".var_export($transistions,true)."\nAdding: $sym\n".$state );
					}
					if( $sym == 'end' ) {
						$transistions[$sym] = array('accept', $j);
					} else {
						$transistions[$sym] = array('shift', $j);
					}
				}
			}
			
			/* goto */
			foreach( $this->grammar->non_terminals() as $sym ) {
				$next_state = $state->take( $sym );
				$j = $this->get_state_id( $next_state );
				if( $j !== false ) {
					if( isset( $transistions[$sym] ) ) {
						throw new GeneratorException( "Disambigous grammar\n".var_export($transistions,true)."\nAdding: $sym\n".$state);
					}
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
	
	public function get_statetable() {
		return $this->statetable;
	}
	
	public function get_state( $state_id ) {
		return $this->states[$state_id];
	}
	
	public function __toString() {
		$outp = "";
		foreach( $this->states as $i => $state ) {
			$outp .= "===== State $i =====\n";
			$outp .= $state;
			$outp .= "\n";

			foreach( $this->statetable[$i] as $sym => $action ) {
				$outp .= sprintf( "%20s: ", $sym );
				list( $a, $t ) = $action;
				$outp .= "$a $t";
				$outp .= "\n";
			}
			$outp .= "\n";
		}
		return $outp;
	}
}