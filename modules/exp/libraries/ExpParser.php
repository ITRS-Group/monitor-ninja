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
		if( $curptr != strlen( $this->expr ) ) {
			$this->error( 'Expected end' );
		}
		
		return $result;
	}

	abstract protected function run();
	
	protected function acceptSym( $tokenlist ) {
		/* Trim left */
		while( ctype_space( substr( $this->expr, $this->ptr, 1 ) ) ) $this->ptr++;
		
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
	
	protected function acceptKeyword( $keywordlist = false, $case_insensitive = false ) {
		/* Trim left */
		while( ctype_space( substr( $this->expr, $this->ptr, 1 ) ) ) $this->ptr++;
		
		/* Peek at next keyword */
		$curptr = $this->ptr;
		$buffer = '';
		while( ctype_alpha( $c = substr( $this->expr, $curptr, 1 ) ) || substr( $this->expr, $curptr, 1 ) == '_' ) {
			$curptr++;
			$buffer .= $c;
		}
		if( $case_insensitive )
			$buffer = strtolower( $buffer );
		if( $keywordlist === false || in_array( $buffer, $keywordlist ) ) {
			$this->ptr = $curptr;
			return $buffer;
		}
		return false;
	}
	
	protected function expectKeyword( $keywordlist = false, $case_insensitive = false ) {
		$sym = $this->acceptKeyword( $keywordlist, $case_insensitive );
		if( $sym === false )
			$this->error('Unexpected token, expected '.(($keywordlist===false)?('keyword'):implode(',',$keywordlist)));
		return $sym;
	}
	
	protected function acceptString( ) {
		/* Trim left */
		while( ctype_space( substr( $this->expr, $this->ptr, 1 ) ) ) $this->ptr++;
		
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
		/* Trim left */
		while( ctype_space( substr( $this->expr, $this->ptr, 1 ) ) ) $this->ptr++;
		
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