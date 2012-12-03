<?php

class LalrParserJSGenerator extends js_class_generator {
	private $fsm;
	private $grammar;
	private $goto_map;
	
	public function __construct( $parser_name, $fsm, $grammar ) {
		$this->classname = $parser_name . "Parser";
		$this->grammar = $grammar;
		$this->fsm = $fsm;
		
		
		$this->goto_map = array();
		foreach( $this->fsm->get_statetable() as $state_id => $map ) {
			foreach( $map as $symbol => $action_arr ) {
				list( $action, $target ) = explode(':',$action_arr,2);
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
		
		$this->init_class(array('visitor'));
		$this->variable( 'visitor' );
		$this->variable( 'stack', array() );
		$this->variable( 'cont', false );
		$this->variable( 'done', false );
		$this->write( 'this.visitor = visitor;' );
		$this->generate_parse();
		
		$this->write( 'this.states = [' );
		foreach( $this->fsm->get_statetable() as $state_id => $map ) {
			$this->generate_state( $state_id, $map );
		}
		$this->write( '];' );
		foreach( $this->grammar->get_rules() as $name => $item ) {
			$this->generate_reduce( $item );
		}
		$this->finish_class();
	}
	
	private function generate_parse() {
		$this->init_function( 'parse', array( 'lexer' ) );
		$this->write( 'this.stack = new Array();' );
		$this->write( 'this.stack.push( %s );', array(0,"start"));
		$this->write( 'this.done = false;' );
		$this->write( 'var result = false;' );
		$this->write( 'do {' );
		$this->write(   'var token = lexer.fetch_token();' );
		$this->write(   'do {' );
		$this->write(     'this.cont = false;' );
		$this->write(     'var head = this.stack[this.stack.length-1];' );
		
		/* Fixme: How to better call the method and not losing "this"? */
		$this->write(     'this.tmp = this.states[head[0]];' );
		$this->write(     'result = this.tmp(token);');
		
		$this->write(   '} while( this.cont );' );
		$this->write( '} while( !this.done );' );
		$this->write( 'return result;' );
		$this->finish_function();
	}
	
	private function generate_state( $state_id, $map ) {
		$this->init_function( false, array('token'), 'private' );
//		$this->comment( "State: $state_id\n".trim(strval( $this->fsm->get_state($state_id) )) );
		$this->comment( "State: $state_id" );
		$this->write( 'switch( token[0] ) {' );
		
		/* Merge cases per action... many cases use same action... */
		$map_r = array();
		foreach( $map as $token => $action_arr ) {
			if(!isset($map_r[$action_arr])) $map_r[$action_arr] = array();
			$map_r[$action_arr][] = $token;
		}
		foreach( $map_r as $action_arr => $tokens ) {
			list( $action, $target ) = explode(':',$action_arr,2);
			if( $action == 'goto' ) continue;
			foreach( $tokens as $token ) {
				$this->write( 'case %s:', $token );
			}
			$this->comment( $action_arr );
			switch( $action ) {
				case 'shift':
					$this->write( 'this.stack.push( [%s,token] );', intval($target) );
					$this->write( 'return null;' );
					break;
				case 'reduce':
					$this->write( 'this.reduce_'.$target.'();');
					$this->write( 'return null;' );
					break;
				case 'accept':
					$this->write( 'var program = this.stack.pop();');
					$this->write( 'this.done = true;' );
					$this->write( 'return this.visitor.accept(program[1][1]);');
					break;
				case 'error': // To be implemented
					break;
			}
		}
		$this->write( '}' );
		$this->comment( 'error handler...' );
		$this->write( 'throw "Error at state '.$state_id.', got token " + token[0];' );
		$this->write( 'return null;' );
		$this->write( '},' ); // FIXME: Should be finish_function, but with , instead of ;
	}
	
	private function generate_reduce( $item ) {
		if( isset($this->goto_map[$item->generates()]) ) {
			$targets = $this->goto_map[$item->generates()];
		} else {
			return; /* This method isn't used appearently */
		}
		
		$this->init_function( 'reduce_'.$item->get_name(), array(), 'private' );
		$this->write( 'this.cont = true;' );

		$args = array();
		foreach( array_reverse($item->get_symbols(),true) as $i => $symbol ) {
			if( $item->symbol_enabled($i) ) {
				$this->write( 'var arg'.$i.' = this.stack.pop();');
				$args[] = 'arg'.$i.'[1][1]';
			} else {
				$this->write( 'this.stack.pop();');
			}
		}
		$item_name = $item->get_name();
		if( $item_name[0] == '_' ) {
			if( count( $args ) != 1 ) {
				throw new GeneratorException( "Rule $item_name can not be used as transparent more than one usable argument" );
			}
			$this->write( 'var new_token = [%s, '.$args[0].', 0, 0];', $item->generates());
		} else {
			$this->write( 'var new_token = [%s, this.visitor.visit_'.$item->get_name().'('.implode(',',array_reverse($args)).'), 0, 0];', $item->generates());
		}
		$this->write( 'switch( this.stack[this.stack.length-1][0] ) {' );
		
		/* Merge cases */
		$cases = array();
		foreach( $targets as $old_state => $new_state ) {
			if( !isset( $cases[$new_state] ) ) $cases[$new_state] = array();
			$cases[$new_state][] = $old_state;
		}
		
		foreach( $cases as $new_state => $old_states ) {
			foreach( $old_states as $old_state ) {
				$this->write( 'case %s:', $old_state );
			}
			$this->write( 'this.stack.push([%s,new_token]); break;', $new_state );
		}
		$this->write( '}' );
		$this->comment( 'error handler...' );
		$this->write( 'return null;' );
		$this->finish_function();
	}
}