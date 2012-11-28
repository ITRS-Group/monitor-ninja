<?php

class LalrStateMachine {
	private $grammar;
	
	public function __construct(Ê$grammar ) {
		$this->grammar = $grammar;
	}
	
	public function items_for_production( $symbol ) {
		$items = array();
		foreach( $this->grammar as $item ) {
			if( $item->produces( $symbol ) ) {
				$items[] = $item;
			}
		}
		return $items;
	}
	
	public function expand( $item ) {
		
	}
}