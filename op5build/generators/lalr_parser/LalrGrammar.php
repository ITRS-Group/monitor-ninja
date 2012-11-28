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
		return isset( $this->tokens[$symbol] );
	}
	
	public function terminals() {
		$symbols = array();
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
		/* FIXME: everything doesn't follow... */
		return $this->terminals();
	}
}