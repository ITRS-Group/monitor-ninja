<?php
/**
 * Exception for MockLivestatus.
 * Not expected to be fetched anywhere but in phpunit library
 */
class MockLivestatus_Exception extends Exception {}

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
	 * The object to use as source for tests
	 */
	public $object;

	/**
	 * Initialize the state machine, with an empty stack and a object to work
	 * with
	 *
	 * @param $object an
	 *        	array with retrieved parameters
	 */
	public function __construct($object) {
		$this->stack = array ();
		$this->object = $object;
	}

	/**
	 * Process a "Filter: args" line, and push the reuslt to the stack
	 *
	 * @param $args the
	 *        	"args" part of the filter line
	 * @throws MockLivestatus_Exception
	 */
	public function process_Filter($args) {
		if (preg_match('/^\s*([a-zA-Z_]+)\s+(!?)([<>=~]+)\s+(.*)$/', $args,
			$matches)) {
			$match_var = $matches[1];
			$match_negate = $matches[2];
			$match_op = $matches[3];
			$match_value = $matches[4];

			if (!isset($this->object[$match_var])) {
				throw new MockLivestatus_Exception('Unknown field ' . $match_var);
			}

			$value = $this->object[$match_var];

			$result = false;
			switch ($match_op) {
			case '=':/* equality */
				$result = ($match_value == $value);
				break;
			case '~': /* match regular expression (substring match) */
				$result = (false != preg_match('/' . $match_value . '/', $value));
				break;
			case '=~': /* equality ignoring case */
				$result = (strtolower($match_value) == strtolower($value));
				break;
			case '~~': /* regular expression ignoring case */
				$result = (false != preg_match('/' . $match_value . '/i',
					$value));
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
			default:
				throw new MockLivestatus_Exception(
					'Unknown filter operator ' . $match_op);
			}

			if ($match_negate == '!') {
				$result = !$result;
			}

			$this->stack[] = $result;
		} else {
			throw new MockLivestatus_Exception("Malformed filter: " . $args);
		}
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
		if (!is_numeric($args)) {
			throw new MockLivestatus_Exception(
				"And statement isn't numeric: " . $args);
		}
		for ($i = 0; $i < intval($args); $i++) {
			$val = array_pop($this->stack);
			if (!$val) {
				$result = false;
			}
		}
		$this->stack[] = $result;
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
		$result = false;
		if (!is_numeric($args)) {
			throw new MockLivestatus_Exception(
				"Or statement isn't numeric: " . $args);
		}
		for ($i = 0; $i < intval($args); $i++) {
			$val = array_pop($this->stack);
			if ($val) {
				$result = true;
			}
		}
		$this->stack[] = $result;
	}
	/**
	 * Process a "Negate:" livestatus filter line, negates the top of the stack
	 *
	 * @param $args an
	 *        	empty string (tested to be empty)
	 * @throws MockLivestatus_Exception
	 */
	public function process_Negate($args) {
		if (!empty($args)) {
			throw new MockLivestatus_Exception(
				'"Negate:" line with arguments isn\'t allowed');
		}
		$result = !array_pop($this->stack);
		$this->stack[] = $result;
	}

	/**
	 * Get the result from the stack, as anding all the lines that is left.
	 *
	 * @return boolean
	 */
	public function get_result() {
		foreach ($this->stack as $val) {
			if (!$val) {
				return false;
			}
		}
		return true;
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
	protected $data;

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
	 */
	public function __construct($data) {
		$this->data = $data;
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
		if (is_string($filter))
			$filter = explode("\n", $filter);
		if (empty($filter))
			$filter = array ();

		$processed_filter = array ();
		foreach ($filter as $filterline) {
			if (!empty($filterline)) {
				$filterop = explode(':', $filterline, 2);
				if (count($filterop) == 2) {
					$processed_filter[] = array_map('trim', $filterop);
				}
			}
		}
		$table_data = $this->data[$table];

		$this->last_columns = $columns;

		if (empty($columns)) {
			$columns = array_keys($table_data[0]);
		}
		if (!is_array($columns)) {
			throw new MockLivestatus_Exception(
				'Unknown column definition: ' . var_dump($columns, false));
		}

		$objects = array ();
		foreach ($table_data as $obj) {
			$filter_sm = new MockLivestatus_StateMachine($obj);
			foreach ($processed_filter as $fop) {
				$filter_sm->{'process_' . $fop[0]}($fop[1]);
			}
			if ($filter_sm->get_result()) {
				$this_obj = array ();
				foreach ($columns as $col) {
					if (array_key_exists($col, $obj)) {
						$this_obj[] = $obj[$col];
					} else {
						throw new MockLivestatus_Exception(
							'Unknown column ' . $col . ' for table ' . $table);
					}
				}
				$objects[] = $this_obj;
			}
		}

		return array ($columns,$objects,count($objects));
	}
}