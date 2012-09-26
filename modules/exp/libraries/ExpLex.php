<?php

class ExpLex_Core {
	/**
	 * Normal state is all states outside a string. This is stateless, but can
	 * accumulate a buffer for tokens (which could be interpreted as sub-
	 * states
	 */
	const STATE_NORMAL = 0;
	/**
	 * This state is when within a string, but not directly after a \-char
	 */
	const STATE_STR    = 1;
	/**
	 * This state is when witin a string, and after a \-char. Turns directly
	 * to STATE_STR afterwards.
	 */
	const STATE_STRESC = 2;
	/**
	 *  Current state of the lexer
	 * @var string
	 */
	private $state  = self::STATE_NORMAL;
	/**
	 * Buffer that accumulates a token or string.
	 * @var string
	 */
	private $buffer = "";
	/**
	 * Current position for the lexer.
	 * @var int
	 */
	private $pos    = 0;
	/**
	 * Position of where the current token starts
	 * @var int
	 */
	private $oppos  = 0;
	
	/**
	 * Tokenize a string, or part of a string.
	 * 
	 * @param string $instr string to tokenize
	 * @param boolean $finish true if the string is the last (or only) in a buffer.
	 * @return array of tokens
	 */
	public function lex( $instr, $finish = true ) {
		$tokens = array();
		for( $i=0; $i<strlen($instr); $i++ ) {
			$tokens = array_merge( $tokens, $this->lexchr( $instr[$i] ) );
		}
		if( $finish ) {
			$tokens = array_merge( $tokens, $this->lexend() );
		}
		return $tokens;
	}
	
	public function lexchr( $inchr ) {
		$tokens = array();
		switch( $this->state ) {
			case self::STATE_NORMAL:
				if( ctype_alnum( $inchr ) ) {
					/* Handle symbols and integers equally */
					if( empty($this->buffer) ) {
						$this->oppos = $this->pos;
					}
					$this->buffer .= $inchr;
				} else {
					if( !empty( $this->buffer ) ) {
						if( ctype_digit( $this->buffer ) ) {
							$tokens[] = array( 'num', intval( $this->buffer ), $this->oppos );
						} else {
							$tokens[] = array( 'sym', strtolower( $this->buffer ), $this->oppos );
						}
						$this->buffer = '';
					}
					if( ctype_space( $inchr ) ) {
					} elseif( $inchr == '"' ) {
						$this->state = self::STATE_STR;
						$this->oppos = $this->pos;
					} else {
						$tokens[] = array( 'op', $inchr, $this->pos );
					}
				}
				break;
			case self::STATE_STR:
				switch( $inchr ) {
					case '"':
						$tokens[] = array( 'str', $this->buffer, $this->oppos );
						$this->buffer = '';
						$this->state = self::STATE_NORMAL;
						break;
					case '\\':
						$this->state = self::STATE_STRESC;
						break;
					default:
						$this->buffer .= $inchr;
				}
				break;
			case self::STATE_STRESC:
				$this->buffer .= $inchr;
				$this->state = self::STATE_STR;
				break;
		}
		$this->pos++;
		return $tokens;
	}
	
	public function lexend() {
		$tokens = array();
		if( $this->buffer ) {
			if( $this->state == self::STATE_STR ) {
				/* Missing "? */
				$tokens[] = array( 'str', $this->buffer, $this->oppos );
			} else {
				if( ctype_digit( $this->buffer ) ) {
					$tokens[] = array( 'num', intval( $this->buffer ), $this->oppos );
				} else {
					$tokens[] = array( 'sym', strtolower( $this->buffer ), $this->oppos );
				}
			}
		}
		$tokens[] = array( 'end', '', $this->pos );
		return $tokens;
	}
}