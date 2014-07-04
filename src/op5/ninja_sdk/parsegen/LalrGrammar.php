<?php

require_once( 'LalrItem.php' );
require_once( 'LalrError.php' );

class LalrGrammar {
	private $tokens;
	private $rules;
	private $errors;

	public function __construct( $grammar ) {
		$this->tokens = array_map( 'trim', $grammar['tokens'] );
		$this->rules = array();

		foreach( $grammar['rules'] as $name => $rule ) {
			$rule = array_map( 'trim', $rule );
			$generates = array_shift( $rule );
			$this->rules[$name] = new LalrItem( $name, $generates, $rule );
		}

		$this->errors = array();
		if( isset($grammar['errors']) ) {
			foreach( $grammar['errors'] as $name => $error ) {
				$error = array_map( 'trim', $error );
				$generates = array_shift( $error );
				if(isset($this->errors[$generates])) {
					/* FIXME: conflicting error handlers */
				}
				if(empty($error)) {
					foreach($this->rules as $rule ) {
						foreach( $this->follow($generates) as $next ) {
							if( !in_array( $next, $error ) ) {
								$error[] = $next;
							}
						}
					}
				}
				$this->errors[$generates] = new LalrError( $name, $generates, $error );
			}
		}

		/* If no default error handler exists, inject it */
		$program_element = $this->rules['entry']->next();
		if(!isset($this->errors[$program_element])) {
			/* As the default error handler is to kill the parser without recover, we only end at the end token, and not in the middle of the parse */
			$error = array('end');
			$this->errors[$program_element] = new LalrError( 'default_error_handler', $program_element, array('end'));
		}
	}

	public function get_tokens() {
		return $this->tokens;
	}

	public function get_rules() {
		return $this->rules;
	}

	public function get_errors() {
		return $this->errors;
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
		if( $symbol == 'end' ) return true;
		return isset( $this->tokens[$symbol] );
	}

	public function terminals() {
		$symbols = array('end');
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
		$next = array();
		$search_list = array($symbol);

		/* Exctract next */
		for( $i=0; $i < count( $search_list ); $i++ ) {
			$cur_symbol = $search_list[$i];
			foreach( $this->rules as $rule ) {
				foreach( $rule->follow( $cur_symbol ) as $sym ) {
					if( $sym === false ) {
						$gen = $rule->generates();
						if( !in_array( $gen, $search_list ) ) {
							$search_list[] = $gen;
						}
					} else {
						$next[] = $sym;
					}
				}
			}
		}

		/* Reduce to next terminal */
		$next_term = array();
		for( $i=0; $i<count($next);$i++ ) {
			if( $this->is_terminal($next[$i]) ) {
				if( !in_array( $next[$i], $next_term ) ) {
					$next_term[] = $next[$i];
				}
			} else {
				foreach( $this->productions($next[$i]) as $rule ) {
					$next_sym = $rule->next();
					if( $next_sym === false ) {
						print_r( $rule );
					} else {
						if( !in_array( $next_sym, $next ) ) {
							$next[] = $next_sym;
						}
					}
				}
			}
		}

		return $next_term;
	}

	public function errors($sym) {
		if(!isset($this->errors[$sym]))
			return false;
		return $this->errors[$sym];
	}

	public function __toString() {
		$outp = "";
		foreach( $this->tokens as $name => $el ) {
			$outp .= $name . ' = ' . strval($el)."\n";
		}
		foreach( $this->rules as $el ) {
			$outp .= strval($el)."\n";
		}
		foreach( $this->errors as $el ) {
			$outp .= strval($el)."\n";
		}
		return $outp;
	}
}