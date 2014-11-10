<?php
/**
 * Exception for MockLivestatus.
 * Not expected to be fetched anywhere but in phpunit library
 */
class MockLivestatus_Exception extends Exception {}

/**
 * Interface for differnent types of stats aggregators.
 * Takes register for each object, and gets the return value when finished by
 * calling ->calculate()
 */
interface MockLivestatus_StatsAggregator {
	/**
	 * Register an object to the aggregator, represented by an associative array
	 *
	 * @param $obj object
	 */
	public function register($obj);
	/**
	 * Get the result of the aggregator
	 */
	public function calculate();
}

/**
 * A stats aggregator, which counts matches to a stats search
 */
class MockLivestatus_FilterCountStats implements MockLivestatus_StatsAggregator {
	private $count = 0;
	private $filterfunc = false;

	/**
	 * Initialize the filter count stats aggregator with a filter function.
	 *
	 * @param $filterfunc The
	 *        	filter function, should be callable and take an object as
	 *        	argument and return a boolean
	 */
	public function __construct($filterfunc) {
		$this->filterfunc = $filterfunc;
	}
	/**
	 * Register an object to the aggregator, represented by an associative array
	 *
	 * @param $obj object
	 */
	public function register($obj) {
		$filterfunc = $this->filterfunc;
		if ($filterfunc( $obj ))
			$this->count ++;
	}
	/**
	 * Get the result of the aggregator
	 */
	public function calculate() {
		return $count;
	}
}
/**
 * Calculated stats aggregator, to aggregate columns to calculate average, sum,
 * min max value and so on.
 */
class MockLivestatus_CalculatedStats implements MockLivestatus_StatsAggregator {
	private $min = false;
	private $max = false;
	private $sum = 0;
	// private $suminv = 0;
	private $count = 0;
	private $column = false;
	private $type = false;
	/**
	 * Initialize the caluculated stats aggregator
	 *
	 * @param $column Name
	 *        	of the column to calculate
	 * @param $type Type
	 *        	of stats as string (min, max, sum, avg)
	 */
	public function __construct($column, $type) {
		$this->column = $column;
		$this->type = $type;
	}
	/**
	 * Register an object to the aggregator, represented by an associative array
	 *
	 * @param $obj object
	 */
	public function register($obj) {
		$value = 0;
		if (isset( $obj[$this->column] )) {
			$value = $obj[$this->column];
		}

		if ($this->count == 0) {
			$this->min = $value;
			$this->max = $value;
		} else {
			if ($this->min > $value)
				$this->min = $value;
			if ($this->max < $value)
				$this->max = $value;
		}
		$this->sum += $value;
		// $this->suminv += 1.0 / $value;
		$this->count ++;
	}
	/**
	 * Get the result of the aggregator
	 */
	public function calculate() {
		switch ($this->type) {
			case 'min' :
				return $this->min;
			case 'max' :
				return $this->max;
			case 'sum' :
				return $this->sum;
			case 'avg' :
				return ( float ) $this->sum / $this->count;
			/* case 'std' TODO: to be implmemented when needed */
			/* case 'suminv': TODO: to be implmemented when needed */
			/* case 'avginv' : TODO: to be implmemented when needed */
		}
		return false;
	}
}

/**
 * Local helper for MockLivestatus-library.
 * Handles the stack for resolving livestatus filters on a given object.
 */
class MockLivestatus_StateMachine {
	/**
	 * Stack for the state of the state machine
	 */
	public $stack;

	/**
	 * Initialize the state machine to generate a filter function.
	 */
	public function __construct() {
		$this->stack = array ();
	}

	/**
	 * Process a "Filter: args" line, and push the reuslt to the stack
	 *
	 * @param $args the
	 *        	"args" part of the filter line
	 * @throws MockLivestatus_Exception
	 */
	public function process_Filter($args) {
		if (preg_match( '/^(min|max|avg|sum|std|suminv|avginv)\s*(.*)$/', $args, $matches )) {
			$this->stack[] = array (
					$matches[2],
					$matches[1]
			);
			return;
		}

		if (preg_match( '/^([a-zA-Z_]+)\s+(!?)([<>=~]+)\s*(.*)$/', $args, $matches )) {
			$this->stack[] = function ($obj) use($matches) {
				$match_var = $matches[1];
				$match_negate = $matches[2];
				$match_op = $matches[3];
				$match_value = $matches[4];

				if (! isset( $obj[$match_var] )) {
					throw new MockLivestatus_Exception( 'Unknown field ' . $match_var );
				}

				$value = $obj[$match_var];

				$result = false;
				switch ($match_op) {
					case '=':/* equality */
					$result = ($match_value == $value);
						break;
					case '~': /* match regular expression (substring match) */
					$result = (false != preg_match( '/' . $match_value . '/', $value ));
						break;
					case '=~': /* equality ignoring case */
					$result = (strtolower( $match_value ) == strtolower( $value ));
						break;
					case '~~': /* regular expression ignoring case */
					$result = (false != preg_match( '/' . $match_value . '/i', $value ));
						break;
					case '<': /* less than */
					$result = ($match_value < $value);
						break;
					case '>': /* greater than */
					$result = ($match_value > $value);
						break;
					case '<=': /* less or equal */
					$result = ($match_value <= $value);
						break;
					case '>=': /* greater or equal */
					$result = ($match_value >= $value);
						break;
					default :
						throw new MockLivestatus_Exception( 'Unknown filter operator ' . $match_op );
				}

				if ($match_negate == '!') {
					$result = ! $result;
				}

				return $result;
			};
			return;
		}
		throw new MockLivestatus_Exception( "Malformed filter: " . $args );
	}
	/**
	 * Process a "And: N" livestatus filter line, manipulates the stack
	 * accordingly
	 *
	 * @param $args the
	 *        	"N" part of the filter line, expected to be numeric
	 * @throws MockLivestatus_Exception
	 */
	public function process_And($args) {
		$result = true;
		if (! is_numeric( $args )) {
			throw new MockLivestatus_Exception( "And statement isn't numeric: " . $args );
		}
		$subfilters = array ();
		for($i = 0; $i < intval( $args ); $i ++) {
			$subfilters[] = array_pop( $this->stack );
		}
		$this->stack[] = function ($obj) use($subfilters) {
			foreach ( $subfilters as $subf ) {
				if (! $subf( $obj )) {
					return false;
				}
			}
			return true;
		};
	}
	/**
	 * Process a "Or: N" livestatus filter line, manipulates the stack
	 * accordingly
	 *
	 * @param $args the
	 *        	"N" part of the filter line, expected to be numeric
	 * @throws MockLivestatus_Exception
	 */
	public function process_Or($args) {
		$result = true;
		if (! is_numeric( $args )) {
			throw new MockLivestatus_Exception( "Or statement isn't numeric: " . $args );
		}
		$subfilters = array ();
		for($i = 0; $i < intval( $args ); $i ++) {
			$subfilters[] = array_pop( $this->stack );
		}
		$this->stack[] = function ($obj) use($subfilters) {
			foreach ( $subfilters as $subf ) {
				if ($subf( $obj )) {
					return true;
				}
			}
			return false;
		};
	}
	/**
	 * Process a "Negate:" livestatus filter line, negates the top of the stack
	 *
	 * @param $args an
	 *        	empty string (tested to be empty)
	 * @throws MockLivestatus_Exception
	 */
	public function process_Negate($args) {
		if (! empty( $args )) {
			throw new MockLivestatus_Exception( '"Negate:" line with arguments isn\'t allowed' );
		}
		$subfilter = ! array_pop( $this->stack );
		$this->stack[] = function ($obj) use($subfilter) {
			return ! $subfilter( $obj );
		};
	}

	/**
	 * Finish up all posts in stack with an implicit And filter, so the stack
	 * is guaranteed to contain one element.
	 */
	public function finish_And() {
		$this->process_And( count( $this->stack ) );
	}

	/**
	 * Get the result from the stack, as anding all the lines that is left.
	 *
	 * @return boolean
	 */
	public function get_stack() {
		return $this->stack;
	}
}

/**
 * A mock replacement for op5Livestatus, which works on the data array passed to
 * the constructor.
 * Useful for unit testing
 */
class MockLivestatus {
	/**
	 * Storage for the mocked environment.
	 */
	public $data;

	/**
	 * Options for behaviour of mock environment
	 */
	protected $options = array (

			// Requesting undefined columns ends up with an empty string instead
			// of error
			'allow_undefined_columns' => false
	);

	/**
	 * An array of the previously requested columns
	 */
	public $last_columns = false;
	/**
	 * Load the mocked livestatus environment
	 *
	 * @param $data Data
	 *        	to be available in the mocked environemnt, indexed by table,
	 *        	structure as livestatus result (but with names as keys)
	 * @param $options Attributes to change behaviour of MockLivestatus. An
	 *              array containing flags, if not set, use defaults. set
	 *              'allow_undefined_columns' to true to make all undefined
	 *              columns get the value of an empty string.
	 */
	public function __construct($data, $options = array()) {
		$this->data = $data;
		foreach ( $options as $k => $v ) {
			$this->options[$k] = $v;
		}
	}

	/**
	 * Query the mocked livestatus environment.
	 *
	 * @param $table Table
	 *        	to search in
	 * @param $filter Filter,
	 *        	as an array, or multiline string
	 * @param $columns Columns
	 *        	to request
	 * @param $options Options
	 *        	(not used ATM)
	 * @throws MockLivestatus_Exception
	 * @return array, as op5Livestatus returns
	 */
	public function query($table, $filter, $columns, $options = array()) {

		/* Strip down $filter-var to make sure it's an array */
		if (is_string( $filter )) {
			$filter = explode( "\n", $filter );
		}
		if (empty( $filter )) {
			$filter = array ();
		}

		/* Parse the filter */
		$filter_sm = new MockLivestatus_StateMachine();
		$stats_sm = new MockLivestatus_StateMachine();
		foreach ( $filter as $filterline ) {
			if (! empty( $filterline )) {
				$filterop = explode( ':', $filterline, 2 );
				if (count( $filterop ) == 2) {
					list ( $op, $args ) = array_map( 'trim', $filterop );
					switch ($op) {
						case 'Filter' :
							$filter_sm->process_Filter( $args );
							break;
						case 'And' :
							$filter_sm->process_And( $args );
							break;
						case 'Or' :
							$filter_sm->process_Or( $args );
							break;
						case 'Negate' :
							$filter_sm->process_Negate( $args );
							break;
						case 'Stats' :
							$stats_sm->process_Filter( $args );
							break;
						case 'StatsAnd' :
							$stats_sm->process_And( $args );
							break;
						case 'StatsOr' :
							$stats_sm->process_Or( $args );
							break;
						case 'StatsNegate' :
							$stats_sm->process_Negate( $args );
							break;
					}
				}
			}
		}

		/*
		 * Filter should always at the end be one function in the stack (apply
		 * implcit AND in livestatus), and retreive the function
		 */
		$filter_sm->finish_And();
		list ( $filter_func ) = $filter_sm->get_stack();

		/*
		 * Identify stats columns, some is filters, some is aggregated columns
		 */
		$stats_accumelators = array ();
		foreach ( $stats_sm->get_stack() as $col ) {
			if (is_array( $col )) {
				$stats_accumelators[] = new MockLivestatus_CalculatedStats( $col[0], $col[1] );
			} else {
				$stats_accumelators[] = new MockLivestatus_FilterCountStats( $col );
			}
		}

		/* Fetch the data in storage */
		$table_data = $this->data[$table];

		$this->last_columns = $columns;
		if (is_array( $this->last_columns )) {
			sort( $this->last_columns );
		}

		if (empty( $columns )) {
			$columns = array_keys( $table_data[0] );
		}
		if (! is_array( $columns )) {
			throw new MockLivestatus_Exception( 'Unknown column definition: ' . var_dump( $columns, false ) );
		}

		if (count( $stats_accumelators ) == 0) {
			/* Do a non-stats search */
			$objects = array ();
			foreach ( $table_data as $obj ) {
				if ($filter_func( $obj )) {
					$this_obj = array ();
					foreach ( $columns as $col ) {
						if (array_key_exists( $col, $obj )) {
							$this_obj[] = $obj[$col];
						} else {
							if ($this->options['allow_undefined_columns']) {
								$this_obj[] = '';
							} else {
								throw new MockLivestatus_Exception( 'Unknown column ' . $col . ' for table ' . $table );
							}
						}
					}
					$objects[] = $this_obj;
				}
			}
		} else {
			/*
			 * Do a stats search, (Note: doesn't support bucketed stats for now)
			 */
			foreach ( $table_data as $obj ) {
				if ($filter_func( $obj )) {
					foreach ( $stats_accumelators as $statcol ) {
						$statcol->register( $obj );
					}
				}
			}
			$this_obj = array ();
			$columns = array ();
			foreach ( $stats_accumelators as $i => $statcol ) {
				$this_obj[] = $statcol->calculate();
				$columns[] = 'statscol_' . $i;
			}
			$objects[] = $this_obj;
		}
		return array (
				$columns,
				$objects,
				count( $objects )
		);
	}
}
