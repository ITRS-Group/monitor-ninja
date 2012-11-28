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
}