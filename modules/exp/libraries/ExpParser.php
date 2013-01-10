<?php

class ExpParserException extends Exception {}

abstract class ExpParser_Core {
	protected $expr; /* Protected, just so we can add custom acceptors... */
	protected $ptr;
	
	public function parse( $expr ) {
		$this->expr = $expr;
		$this->ptr  = 0;
		
		$result = $this->run();

		$curptr = $this->ptr; /* Keep $this->ptr for error message */
		while( ctype_space( substr( $this->expr, $curptr, 1 ) ) ) $this->ptr++;
		if( $curptr < strlen( $this->expr ) ) {
			$this->error( 'Expected end' );
		}
		
		return $result;
	}

	abstract protected function run();
	
	protected function trimLeft() {
		while( ctype_space( substr( $this->expr, $this->ptr, 1 ) ) ) $this->ptr++;
	}
	
	protected function acceptSym( $tokenlist ) {
		$this->trimLeft();
		
		/* Test tokens */
		foreach( $tokenlist as $token ) {
			if( substr( $this->expr, $this->ptr, strlen( $token ) ) == $token ) {
				$this->ptr += strlen( $token );
				return $token;
			}
		}
		return false;
	}
	
	protected function expectSym( $tokenlist ) {
		$sym = $this->acceptSym( $tokenlist );
		if( $sym === false )
			$this->error('Unexpected token, expected '.implode(',',$tokenlist));
		return $sym;
	}
	
	protected function acceptKeyword( $keywordlist = false, $case_insensitive = false, $numeric = false ) {
		$this->trimLeft();
		
		/* Peek at next keyword */
		$curptr = $this->ptr;
		$buffer = '';
		$c = substr( $this->expr, $curptr, 1 );
		while( ctype_alpha( $c ) || $c == '_' || ($buffer !== '' && ctype_digit($c)) ) {
			$curptr++;
			$buffer .= $c;
			$c = substr( $this->expr, $curptr, 1 );
		}
		if( $case_insensitive )
			$buffer = strtolower( $buffer );
		if( $keywordlist === false || in_array( $buffer, $keywordlist ) ) {
			$this->ptr = $curptr;
			return $buffer;
		}
		return false;
	}
	
	protected function expectKeyword( $keywordlist = false, $case_insensitive = false, $numeric = false ) {
		$sym = $this->acceptKeyword( $keywordlist, $case_insensitive, $numeric );
		if( $sym === false )
			$this->error('Unexpected token, expected '.(($keywordlist===false)?('keyword'):implode(',',$keywordlist)));
		return $sym;
	}
	
	protected function acceptString( ) {
		$this->trimLeft();
		
		/* Fetch first " */
		if( substr( $this->expr, $this->ptr, 1 ) != '"' ) {
			return false;
		}
		$this->ptr++;
		
		$buffer = '';
		while( ($c = substr( $this->expr, $this->ptr++, 1 )) != '"' ) {
			if( $c == '\\' ) {
				$c = substr( $this->expr, $this->ptr++, 1 );
			}
			$buffer .= $c;
		}
		return $buffer;
	}
	
	protected function expectString() {
		$sym = $this->acceptString();
		if( $sym === false )
			$this->error('Unexpected token, expected string');
		return $sym;
	}
	
	/* FIXME: number should handle more than positive integers */
	protected function acceptNum() {
		$this->trimLeft();
		
		/* Peek at next integer */
		$curptr = $this->ptr;
		$buffer = '';
		while( ctype_digit( $c = substr( $this->expr, $curptr, 1 ) ) ) {
			$curptr++;
			$buffer .= $c;
		}
		
		if( strlen( $buffer ) > 0 ) {
			$this->ptr = $curptr;
			return intval($buffer);
		}
		return false;
	}
	
	protected function expectNum() {
		$sym = $this->acceptNum();
		if( $sym === false )
			$this->error('Unexpected token, expected number');
		return $sym;
	}
	
	protected function error( $msg ) {
		throw new ExpParserException($msg . ' at ' . $this->ptr);
	}
}