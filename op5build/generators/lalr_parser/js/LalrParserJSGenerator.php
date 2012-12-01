<?php

class LalrParserJSGenerator extends js_class_generator {
	private $fsm;
	private $grammar;
	
	public function __construct( $parser_name, $fsm, $grammar ) {
		$this->classname = $parser_name . "Parser";
		$this->grammar = $grammar;
		$this->fsm = $fsm;
		
		
		$this->goto_map = array();
		foreach( $this->fsm->get_statetable() as $state_id => $map ) {
			foreach( $map as $symbol => $action_arr ) {
				list( $action, $target ) = $action_arr;
				if( $action == 'goto' ) {
					if( !isset( $this->goto_map[$symbol] ) )
						$this->goto_map[$symbol] = array();
					$this->goto_map[$symbol][$state_id] = $target;
				}
			}
		}
	}
	
	public function generate() {
		parent::generate();
		
		$this->init_class();
		$this->variable( 'visitor' );
		$this->variable( 'state_stack', array(0) );
		$this->variable( 'token_stack', array('start') );
		$this->generate_constructor();
		$this->generate_process();
		
		foreach( $this->fsm->get_statetable() as $state_id => $map ) {
			$this->generate_state( $state_id, $map );
		}
		foreach( $this->goto_map as $symbol => $targets ) {
			$this->generate_goto( $symbol, $targets );
		}
		$this->finish_class();
	}
	
	private function generate_constructor() {
		$this->init_function( '__construct', array( 'visitor' ) );
		$this->write( '$this->visitor = $visitor;' );
		$this->finish_function();
	}
	
	private function generate_process() {
		$this->init_function( 'process', array('token') );
		$this->write('do {');
		$this->write('$state_handler = "state_".end($this->state_stack);');
		$this->write('} while( $this->$state_handler($token) );');
		$this->finish_function();
	}
	
	private function generate_state( $state_id, $map ) {
		$this->init_function( 'state_'.$state_id, array('token'), 'private' );
		$this->comment( strval( $this->fsm->get_state($state_id) ) );
		$this->write( 'list($name,$content,$start,$length) = $token;' );
		$this->write( 'switch( $name ) {' );
		foreach( $map as $token => $action_arr ) {
			list( $action, $target ) = $action_arr;
			switch( $action ) {
				case 'shift':
					$this->write( 'case %s:', $token );
					$this->comment( implode( ': ', $action_arr ) );
					$this->write( 'array_push( $this->state_stack, %s );', $target );
					$this->write( 'array_push( $this->token_stack, $token );' );
					$this->write( 'return false;' );
					break;
				case 'reduce':
					$this->write( 'case %s:', $token );
					$this->comment( implode( ': ', $action_arr ) );
					$item = $this->grammar->get($target);
					$args = array();
					foreach( array_reverse($item->get_symbols(),true) as $i => $symbol ) {
						if( $item->symbol_enabled($i) ) {
							$this->write( '$arg'.$i.' = array_pop($this->token_stack);');
							$args[] = '$arg'.$i.'[1]';
						} else {
							$this->write( 'array_pop($this->token_stack);');
						}
					}
					$this->write( '$this->state_stack = array_slice($this->state_stack, 0, %s);', -count($item->get_symbols()) );
					$this->write( '$new_token = array(%s, $this->visitor->visit_'.$target.'('.implode(',',array_reverse($args)).'), 0, 0);', $item->generates());
					
					$this->write( 'array_push( $this->state_stack, $this->goto_'.$item->generates().'(end($this->state_stack)) );' );//$map[$item->generates()] );
					$this->write( 'array_push( $this->token_stack, $new_token );' );
					$this->write( 'return true;' );
					break;
				case 'accept': // To be implemented
					$this->write( 'case %s:', $token );
					$this->comment( implode( ': ', $action_arr ) );
					$this->write( '$program = array_pop($this->token_stack);');
					$this->write( 'array_pop($this->token_stack);');
					$this->write( '$this->visitor->accept($program[1]);');
					$this->write( 'return false;' );
					break;
				case 'error': // To be implemented
					break;
			}
		}
		$this->write( '}' );
		$this->comment( 'error handler...' );
		$this->write( 'throw new Exception( "Error at state %s\n".%s."\nGot token ".var_export($token,true) );', $state_id, strval( $this->fsm->get_state($state_id) ) );
		$this->write( 'return false;' );
		$this->finish_function();
	}
	
	private function generate_goto( $symbol, $targets ) {
		$this->init_function( 'goto_'.$symbol, array('state'), 'private' );
		$this->write( 'switch( $state ) {' );
		foreach( $targets as $old_state => $new_state ) {
					$this->write( 'case %s: return %s;', $old_state, $new_state );
		}
		$this->write( '}' );
		$this->comment( 'error handler...' );
		$this->write( 'return 0;' );
		$this->finish_function();
	}
}