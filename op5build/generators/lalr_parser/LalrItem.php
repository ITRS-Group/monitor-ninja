<?php

class LalrItem {
	private $name;
	private $generate;
	private $symbols;
	private $ptr = 0;
	
	public function __construct( $name, $generate, $symbols ) {
		$this->name = $name;
		$this->generate = $generate;
		$this->symbols = $symbols;
		$this->ptr = 0;
	}
	
	public function next() {
		if( !isset( $this->symbols[$this->ptr] ) )
			return false;
		return $this->symbols[$this->ptr];
	}
	
	public function take( $symbol ) {
		if( $this->ptr == count( $this->symbols ) )
			return false;
		
		if( $this->symbols[$this->ptr] != $symbol )
			return false;
		
		$rule = new self( $this->name, $this->generate, $this->symbols );
		$rule->ptr = $this->ptr + 1;
		return $rule;
	}
	
	public function done() {
		return $this->ptr == count( $this->symbols );
	}
	
	public function count() {
		return count( $this->symbols );
	}
	
	public function produces( $symbol ) {
		return $this->generate == $symbol;
	}
	
	public function equals( $item ) {
		return ($item->name == $this->name) && ($item->ptr == $this->ptr);
	}
	
	public function __toString() {
		$outp = sprintf( "%s: %s :=", $this->name, $this->generate );
		foreach( $this->symbols as $i=>$sym ) {
			if( $i == $this->ptr )
				$outp .= ' *';
			$outp .= " $sym";
		}
		if( count($this->symbols) == $this->ptr )
			$outp .= ' *';
		return $outp;
	}
}