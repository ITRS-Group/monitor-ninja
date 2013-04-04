<?php

class LalrGrammarParser {
	protected $expr; /* Protected, just so we can add custom acceptors... */
	protected $ptr;

	/* ********************************************
	 * Parser access method
	 *********************************************/

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

	/* ********************************************
	 * Lexer-internal helpers
	 *********************************************/

	protected function trimLeft() {
		while( false !== strpos( " \t", substr( $this->expr, $this->ptr, 1 ) ) ) $this->ptr++;
	}

	protected function trimLine() {
		$c = substr( $this->expr, $this->ptr, 1 );
		while( $c != "" && $c != "\n" ) {
			$this->ptr++;
			$c = substr( $this->expr, $this->ptr, 1 );
		}
	}

	/* ********************************************
	 * Lexer methods
	 *********************************************/

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


	public function acceptLinebreak() {
		$this->trimLeft();
		$c = substr( $this->expr, $this->ptr, 1 );
		if( $c == "" || $c == "\n" ) {
			$this->ptr++;
			return true;
		}
		return false;
	}

	protected function expectLinebreak() {
		$sym = $this->acceptLinebreak();
		if( $sym === false )
			$this->error('Unexpected token, expected linebreak');
		return $sym;
	}

	public function acceptRegexp() {
		$this->trimLeft();

		$c = substr( $this->expr, $this->ptr, 1 );
		if( $c != '/' )
			return false;

		$buffer = '';
		while( $c != "" && $c != "\n" ) {
			$buffer .= $c;
			$this->ptr++;
			$c = substr( $this->expr, $this->ptr, 1 );
		}

		return $buffer;
	}


	/* ********************************************
	 * Parser entry point
	 *********************************************/

	public function run() {
		$result = array();
		while( false !== ($line = $this->accept_line()) ) {
			/* Todo: conflicting lines */
			$result = array_merge_recursive($result, $line);
		}
		return $result;
	}

	public function accept_line() {
		$result = array();

		/* Match end-of-file */
		if( $this->ptr >= strlen( $this->expr ) ) {
			return false;
		}

		/* Trim empty lines */
		if( $this->acceptLinebreak() ) {
			return $result;
		}
		/* Trim comments */
		if( $this->acceptSym(array('--')) ) {
			$this->trimLine();
			return $result;
		}

		$name = $this->expectKeyword(false, false, true);

		/* Token */
		if( false !== ($re = $this->acceptRegexp()) ){
			$result['tokens'] = array($name => $re);
			$this->expectLinebreak();
			return $result;
		}

		/* Grammar rules */
		if( $this->acceptSym(array(':')) ) {
			$items = array();
			$items[] = $this->expectKeyword(false, false, true);
			$this->expectSym(array('='));
			while( $item = $this->acceptKeyword(false, false, true) ) {
				$items[] = $item;
			}
			$result['rules'] = array($name => $items);
			$this->expectLinebreak();
			return $result;
		}

		return false;
	}
}