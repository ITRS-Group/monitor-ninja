<?php

/**
 * A token, as formatted as php method token_get_all.
 * Used to generalize the token between terminals and tokens.
 */
class php_miner_token {
	/**
	 * String representing the original value of the token
	 *
	 * @var string
	 */
	public $string = false;
	/**
	 * Token id, if a token, false if terminal
	 *
	 * @var string|boolean
	 */
	public $token = false;
	/**
	 * Line number if available, false otherwise
	 *
	 * @var integer|boolean
	 */
	public $lineno = false;

	/**
	 * Generate a token from output of token_get_all
	 *
	 * @param array|string $tokdesc
	 */
	public function __construct($tokdesc) {
		if (is_array( $tokdesc )) {
			$this->token = $tokdesc[0];
			$this->string = $tokdesc[1];
			$this->lineno = $tokdesc[2];
		} else {
			$this->token = false;
			$this->string = $tokdesc;
			$this->lineno = false;
		}
	}

	/**
	 * Return true if it is a token, equal to $token.
	 *
	 * @param integer|boolean $token
	 *        	T_-constant, or false if terminal
	 * @return boolean
	 */
	public function is_token($token) {
		return $this->token === $token;
	}
	/**
	 * Return true if is a terminal, equal to $terminal
	 *
	 * @param string $terminal
	 * @return boolean
	 */
	public function is_terminal($terminal) {
		return $this->token === false && $this->string == $terminal;
	}
	/**
	 * Output a nice formatting of the token, for debug purposes
	 *
	 * @return string
	 */
	public function __toString() {
		$tokstr = $this->token === false ? "TERM" : token_name( $this->token );
		return $tokstr . ": " . $this->string;
	}
	/**
	 * Get the name of the token
	 */
	public function get_name() {
		if ($this->token === false)
			return "TERMINAL";
		return token_name( $this->token );
	}
}
/**
 * Holds a list of statements, for which other statements can be extracted
 * This class can't be used by itself, but needs to be subclassed.
 * There are two major subclasses: file, which holds a phpfile, or a statement
 * (see below)
 */
abstract class php_miner_block {
	/**
	 * List of statements within the block
	 *
	 * @var php_miner_statement[]
	 */
	protected $block_statements = false;
	public function extract($classname, $recursive = false) {
		$result = array ();
		if ($this->block_statements !== false) {
			foreach ( $this->block_statements as $stmt ) {
				if (false !== ($new_stmt = $classname::factory( $stmt ))) {
					$result[] = $new_stmt;
				}
				if ($recursive) {
					$substmts = $stmt->extract( $classname, true );
					$result = array_merge( $result, $substmts );
				}
			}
		}
		return $result;
	}
}

/**
 * A generalized PHP statement.
 * A PHP statement in this miner is the
 * constallation of two or three parts:
 * 1. (optional) Docstring comment
 * 2. A number of tokens or terminals, except for { and ;
 * 3. An ending, either ; (not stored) or {}, if a block follows.
 * If block block follows the statement, it can be interfaced by php_miner_block
 * methods for further mining.
 */
class php_miner_statement extends php_miner_block {
	/**
	 * List of tokens
	 *
	 * @var php_miner_token[]
	 */
	protected $tokens;
	/**
	 * Doc comment associated with this token
	 *
	 * @var boolean|string
	 */
	public $docstring = false;
	/**
	 *
	 * @param php_miner_token[] $tokens
	 * @param string $block_statements
	 */
	public function __construct($tokens, $block_statements = false) {
		if (count( $tokens ) >= 1 && $tokens[0]->is_token( T_DOC_COMMENT )) {
			$doc_token = array_shift( $tokens );
			/* @var $doc_token php_miner_token */
			$this->docstring = $doc_token->string;
		}
		$this->tokens = $tokens;
		$this->block_statements = $block_statements;
	}
	/**
	 * Format a statement to a nice printed line
	 *
	 * @return string
	 */
	public function __toString() {
		return implode( " ", array_map( function ($tok) {
			return $tok->string;
		}, $this->tokens ) ) . ($this->tokens === false ? ";" : " {}");
	}
	/**
	 * Return a list of all docstring lines following an @
	 *
	 * @return string[]
	 */
	public function get_docstring_tags() {
		$raw_lines = explode( "\n", $this->docstring );

		$tags = array ();
		foreach ( $raw_lines as $line ) {
			$lineparts = explode( "@", $line, 2 );
			if (count( $lineparts ) >= 2) {
				$tags[] = trim( $lineparts[1] );
			}
		}
		return $tags;
	}
}

/**
 * php_miner_file parses and represents an entire PHP file,
 * It stores information as a php_miner_block for further mining
 */
class php_miner_file extends php_miner_block {
	/**
	 * List of tokens, as token_get_all returns it
	 *
	 * @var multitype
	 */
	protected $tokens;

	/**
	 * Current parsing position in the file
	 *
	 * @var integer
	 */
	protected $position;
	/**
	 * The current token
	 *
	 * @var php_miner_token
	 */
	protected $current_token;

	/**
	 * If the parse is valid
	 *
	 * @var boolean
	 */
	protected $valid = false;

	/**
	 * Parse a php file, given its filename
	 *
	 * @param string $filename
	 * @return php_miner_file
	 */
	public static function parse_file($filename) {
		$content = file_get_contents( $filename );
		$tokens = token_get_all( $content );
		$instance = new self( $tokens );
		if (! $instance->valid)
			return false;
		return $instance;
	}

	/**
	 * Parse a php file, given its content
	 *
	 * @param string $content
	 *        	the php file content
	 * @return php_miner_file
	 */
	public static function parse_content($content) {
		$tokens = token_get_all( $content );
		$instance = new self( $tokens );
		if (! $instance->valid)
			return false;
		return $instance;
	}

	/**
	 * Parse the file
	 *
	 * @param string $filename
	 */
	private function __construct($tokens) {
		$this->tokens = $tokens;
		$this->position = 0;
		$this->next_token();

		$lines = array ();
		while ( false !== ($line = $this->accept_statement()) ) {
			$lines[] = $line;
		}

		/* FIXME: expect end, otherwise don't set $this->valid */
		$this->valid = true;

		$this->block_statements = $lines;
	}
	private function accept_statement() {
		$savepos = $this->position;
		$tokens = array ();

		do {
			if ($this->current_token->is_terminal( "}" )) {
				/* Shouldn't happen within a line, not valid line */
				/*
				 * TODO: be greedy, we should allow this as a ; if statements
				 * before
				 */
				$this->position = $savepos;
				return false;
			} else if ($this->accept_terminal( ";" )) {
				/* End of line, just return the tokens */
				return new php_miner_statement( $tokens );
			} else if ($this->current_token->is_terminal( "{" )) {
				$block = $this->accept_block();
				if ($block === false) {
					$this->position = $savepos;
					return false;
				}
				/* Got a block, return a block statement */
				return new php_miner_statement( $tokens, $block );
			} else {
				/* Some other token, just add and continue */
				$tokens[] = $this->current_token;
			}
		} while ( $this->next_token() );

		/* We should already have exited if ok */
		$this->position = $savepos;
		return false;
	}
	private function accept_block() {
		$savepos = $this->position;

		if (false === $this->accept_terminal( "{" )) {
			$this->position = $savepos;
			return false;
		}

		$lines = array ();
		while ( false !== ($line = $this->accept_statement()) ) {
			$lines[] = $line;
		}

		if (false === $this->accept_terminal( "}" )) {
			$this->position = $savepos;
			return false;
		}

		return $lines;
	}
	private function accept_terminal($terminal) {
		if ($this->current_token->is_terminal( $terminal )) {
			$this->next_token();
			return true;
		}
		return false;
	}
	private function next_token() {
		for(;;) {
			if ($this->position >= count( $this->tokens ))
				return false;

			$this->current_token = new php_miner_token( $this->tokens[$this->position] );
			$this->position ++;

			if ($this->current_token->is_token( T_WHITESPACE ))
				continue;
			if ($this->current_token->is_token( T_INLINE_HTML ))
				continue;
			if ($this->current_token->is_token( T_OPEN_TAG ))
				continue;
			if ($this->current_token->is_token( T_CLOSE_TAG ))
				continue;
			if ($this->current_token->is_token( T_COMMENT ))
				continue;

			break;
		}
		return true;
	}
}

/**
 * The special statement "class" matches and mines out all classes from a
 * statement block
 * Example:
 * // Parse a file
 * $file = php_miner_file::parse_file("my_file.php");
 * // Mine out all classes in the file, recursive
 * $classes = $file->extract("php_miner_statement_class", true);
 * foreach($classes as $class) {
 * echo "Found class: " . $class->name . "\n";
 * }
 */
class php_miner_statement_class extends php_miner_statement {
	public $name = false;
	public $is_abstract = false;

	/**
	 * Generate a php_miner_statement_class if$statement is a valid class.
	 * return false otherwise.
	 * Used as filter method in php_miner_block::extract()
	 *
	 * @param php_miner_statement $stmt
	 * @return php_miner_statement_class|boolean
	 */
	public function factory(php_miner_statement $stmt) {
		foreach ( $stmt->tokens as $token ) {
			if ($token->is_token( T_CLASS )) {
				return new self( $stmt );
			}
		}
		return false;
	}
	public function __construct(php_miner_statement $stmt) {
		$this->docstring = $stmt->docstring;
		$this->tokens = $stmt->tokens;
		$this->block_statements = $stmt->block_statements;

		$tmptokens = $this->tokens;
		/* @var $tmptokens php_miner_token[] */
		while ( ! $tmptokens[0]->is_token( T_CLASS ) ) {
			$prefix = array_shift( $tmptokens );
			/* @var $prefix php_miner_token */
			if ($prefix->is_token( T_ABSTRACT ))
				$this->is_abstract = true;
		}
		$this->name = $tmptokens[1]->string;
	}
}
class php_miner_statement_function extends php_miner_statement {
	public $name = false;
	public $is_abstract = false;
	public $scope = "public";

	/**
	 * Generate a php_miner_statement_fucntion if $statement is a valid
	 * function.
	 * return false otherwise.
	 * Used as filter method in php_miner_block::extract()
	 *
	 * @param php_miner_statement $stmt
	 * @return php_miner_statement_function|boolean
	 */
	public function factory(php_miner_statement $stmt) {
		$tmptokens = $stmt->tokens;
		$is_abstract = false;
		$scope = "public";
		$name = false;

		/* @var $tmptokens php_miner_token[] */
		while ( count( $tmptokens ) >= 1 && ! $tmptokens[0]->is_token( T_FUNCTION ) ) {
			$prefix = array_shift( $tmptokens );
			/* @var $prefix php_miner_token */
			if ($prefix->is_token( T_ABSTRACT ))
				$is_abstract = true;
			if ($prefix->is_token( T_PRIVATE )) {
				$scope = "private";
			}
			if ($prefix->is_token( T_PUBLIC )) {
				$scope = "public";
			}
			if ($prefix->is_token( T_PROTECTED )) {
				$scope = "protected";
			}
		}
		if (count( $tmptokens ) >= 2 && $tmptokens[1]->is_token( T_STRING )) {
			$name = $tmptokens[1]->string;
		}

		if ($name !== false)
			return new self( $stmt, $name, $scope, $is_abstract );

		return false;
	}
	public function __construct(php_miner_statement $stmt, $name, $scope = "public", $is_abstract = false) {
		$this->docstring = $stmt->docstring;
		$this->tokens = $stmt->tokens;
		$this->block_statements = $stmt->block_statements;

		$this->name = $name;
		$this->is_abstract = $is_abstract;
		$this->scope = $scope;
	}
}