<?php

class LalrParserPHPGenerator extends class_generator {
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
		
		$this->set_library();
	}
	
	public function generate() {
		parent::generate();
		
		$this->init_class();
		$this->variable( 'visitor' );
		$this->variable( 'stack', null );
		$this->variable( 'continue', false );
		$this->variable( 'done', false );
		$this->generate_constructor();
		$this->generate_parse();
		
		foreach( $this->fsm->get_statetable() as $state_id => $map ) {
			$this->generate_state( $state_id, $map );
		}
		foreach( $this->grammar->get_rules() as $name => $item ) {
			$this->generate_reduce( $item );
		}
		$this->finish_class();
	}
	
	private function generate_constructor() {
		$this->init_function( '__construct', array( 'visitor' ) );
		$this->write( '$this->visitor = $visitor;' );
		$this->finish_function();
	}
	
	private function generate_parse() {
		$this->init_function( 'parse', array( 'lexer' ) );
		$this->write( '$this->stack = array(array(0,"start"));');
		$this->write( '$this->done = false;' );
		$this->write( 'do {' );
		$this->write(   '$token = $lexer->fetch_token();' );
		$this->write(   'do {' );
		$this->write(     '$this->continue = false;' );
		$this->write(     '$head = end($this->stack);' );
		$this->write(     '$state_handler = "state_".$head[0];' );
		$this->write(     '$result = $this->$state_handler($token);' );
		$this->write(   '} while( $this->continue );' );
		$this->write( '} while( !$this->done );' );
		$this->write( 'return $result;' );
		$this->finish_function();
	}
	
	private function generate_state( $state_id, $map ) {
		$this->init_function( 'state_'.$state_id, array('token'), 'private' );
		$this->comment( strval( $this->fsm->get_state($state_id) ) );
		$this->write( 'switch( $token[0] ) {' );
		
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
					$this->write( 'array_push( $this->stack, array(%s,$token) );', intval($target) );
					$this->write( 'return null;' );
					break;
				case 'reduce':
					$this->write( '$this->reduce_'.$target.'();');
					$this->write( 'return null;' );
					break;
				case 'accept':
					$this->write( '$program = array_pop($this->stack);');
					$this->write( '$this->done = true;' );
					$this->write( 'return $this->visitor->accept($program[1][1]);');
					break;
				case 'error': // To be implemented
					break;
			}
		}
		$this->write( '}' );
		$this->comment( 'error handler...' );
		$this->write( 'throw new Exception( "Error at state '.$state_id.', got token ".$token[0] );' );
		$this->write( 'return null;' );
		$this->finish_function();
	}
	
	private function generate_reduce( $item ) {
		if( isset($this->goto_map[$item->generates()]) ) {
			$targets = $this->goto_map[$item->generates()];
		} else {
			return; /* This method isn't used appearently */
		}
		
		$this->init_function( 'reduce_'.$item->get_name(), array(), 'private' );
		$this->write( '$this->continue = true;' );

		$args = array();
		foreach( array_reverse($item->get_symbols(),true) as $i => $symbol ) {
			if( $item->symbol_enabled($i) ) {
				$this->write( '$arg'.$i.' = array_pop($this->stack);');
				$args[] = '$arg'.$i.'[1][1]';
			} else {
				$this->write( 'array_pop($this->stack);');
			}
		}
		$item_name = $item->get_name();
		if( $item_name[0] == '_' ) {
			if( count( $args ) != 1 ) {
				throw new GeneratorException( "Rule $item_name can not be used as transparent more than one usable argument" );
			}
			$this->write( '$new_token = array(%s, '.$args[0].', 0, 0);', $item->generates());
		} else {
			$this->write( '$new_token = array(%s, $this->visitor->visit_'.$item->get_name().'('.implode(',',array_reverse($args)).'), 0, 0);', $item->generates());
		}
		$this->write( '$head = end($this->stack);' );
		$this->write( 'switch( $head[0] ) {' );
		
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
			$this->write( 'array_push( $this->stack, array(%s,$new_token) ); break;', $new_state );
		}
		$this->write( '}' );
		$this->comment( 'error handler...' );
		$this->write( 'return 0;' );
		$this->finish_function();
	}
}