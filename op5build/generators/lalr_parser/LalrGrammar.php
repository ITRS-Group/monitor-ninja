<?php

require_once( 'LalrItem.php' );

class LalrGrammar {
	private $tokens;
	private $rules;
	
	public function __construct( $grammar ) {
		$this->tokens = $grammar['tokens'];
		$this->rules = array();
		foreach( $grammar['rules'] as $name => $rule ) {
			$this->rules[$name] = new LalrItem( $name, $rule['generate'], $rule['symbols'] );
		}
	}
	
	public function get_tokens() {
		return $this->tokens;
	}
	
	public function get( $name ) {
		if( !isset( $this->rules[$name] ) )
			return false;
		return $this->rules[$name];
	}

	public function productions( $symbol ) {
		$items = array();
		foreach( $this->rules as $item ) {
			if( $item->produces( $symbol ) ) {
				$items[] = $item;
			}
		}
		return $items;
	}
	
	public function is_terminal( $symbol ) {
		if( $symbol == 'end' ) return true;
		return isset( $this->tokens[$symbol] );
	}
	
	public function terminals() {
		$symbols = array('end');
		foreach( $this->tokens as $sym => $re ) {
			if( $sym[0] != '.' ) {
				$symbols[] = $sym;
			}
		}
		return $symbols;
	}
	
	public function non_terminals() {
		$symbols = array();
		foreach( $this->rules as $rule ) {
			if( !in_array( $rule->generates(), $symbols ) ) {
				$symbols[] = $rule->generates();
			}
		}
		return $symbols;
	}
	
	public function symbols() {
		return array_merge( $this->terminals(), $this->non_terminals() );
	}
	
	public function follow( $symbol ) {
		$next = array();
		$search_list = array($symbol);
		
		/* Exctract next */
		for( $i=0; $i < count( $search_list ); $i++ ) {
			foreach( $this->rules as $rule ) {
				foreach( $rule->follow( $symbol ) as $sym ) {
					if( $sym === false ) {
						$gen = $rule->generates();
						if( !in_array( $gen, $search_list ) ) {
							$search_list[] = $gen;
						}
					} else {
						$next[] = $sym;
					}
				}
			}
		}
		
		/* Reduce to next terminal */
		$next_term = array();
		for( $i=0; $i<count($next);$i++ ) {
			if( $this->is_terminal() ) {
				if( !in_array( $sym, $next_term ) ) {
					$next_term[] = $sym;
				}
			} else {
				foreach( $this->productions($sym) as $rule ) {
					$next_sym = $rule->next();
					if( !in_array( $next_sym, $next ) ) {
						$next[] = $next_sym;
					}
				}
			}
		}
		
		/* FIXME: non-terminals should be reduced to it's first terminal... */
		return $next_term;
	}
}