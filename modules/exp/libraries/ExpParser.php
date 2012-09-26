<?php

abstract class ExpParser_Core {
	public function __construct( $tokens ) {
		$this->tokens = $tokens;
		$this->ptr    = 0;
	}
	
	public function parse() {
		$result = $this->expression();
		$this->expect( array( 'end' ) );
		return $result;
	}

	abstract protected function expression();
	
	protected function accept( $match ) {
		$sym = $this->tokens[$this->ptr];
		if( array_slice( $sym, 0, count( $match ) ) == $match ) {
			$this->ptr++;
			return $sym;
		}
		return false;
	}
	
	protected function expect( $match ) {
		$sym = $this->accept( $match );
		if( $sym === false )
			throw new Exception('Unexpected symbol, expected '.$match[0].' got '.$this->tokens[$this->ptr][0]);
		return $sym;
	}
}