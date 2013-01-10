<?php

require_once( '../../../modules/exp/libraries/ExpParser.php');

class LalrGrammarParser extends ExpParser_Core {
	public function trimLeft() {
		while( false !== strpos( " \t", substr( $this->expr, $this->ptr, 1 ) ) ) $this->ptr++;
	}

	public function trimLine() {
		$c = substr( $this->expr, $this->ptr, 1 );
		while( $c != "" && $c != "\n" ) {
			$this->ptr++;
			$c = substr( $this->expr, $this->ptr, 1 );
		}
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