<?php

/**
 * An match-node in the livestatus filter node tree
 */
class LivestatusFilterMatch extends LivestatusFilterBase {
	private $field;
	private $op;
	private $value;

	private $negations = array(
			'='   => '!=',
			'!='  => '=',

			'~'   => '!~',
			'!~'  => '~',

			'=~'  => '!=~',
			'!=~' => '=~',

			'~~'  => '!~~',
			'!~~' => '~~',

			'<'   => '>=',
			'>='  => '<',

			'>'   => '<=',
			'<='  => '>',

			'<='  => '>',
			'>'   => '<=',

			'>='  => '<',
			'<'   => '>='
			);


	function get_hash () {
		$line = "match " . $this->field . " " . $this->op . " " . $this->value;
		return md5($line);
	}

	/**
	 * Get the name of the field to match
	 */
	function get_field() {
		return $this->field;
	}

	/**
	 * Get the operator to filter on
	 */
	function get_op() {
		return $this->op;
	}

	/**
	 * Get the value to match
	 */
	function get_value() {
		return $this->value;
	}

	/**
	 * Generate a match-filter
	 */
	function __construct( $field, $value, $op = "=" ) {
		$this->field = $field; //TODO: Do this in some fancy way...
		$this->op = $op;
		$this->value = $value;
	}

	/**
	 * Returns a copy of the filter, but with a variables prefixed
	 */
	function prefix( $prefix ) {
		$new_field = $prefix.$this->field;

		/* FIXME: Livestatus should be able to handle service.host.name for comments.. Until then... */
		$fields = explode('.',$new_field);
		if( count($fields) > 2 ) {
			$fields = array_slice($fields, count($fields)-2);
		}
		$new_field = implode('.',$fields);


		return new LivestatusFilterMatch( $new_field, $this->value, $this->op );
	}

	/**
	 * Visit the filter node with a visitor, to generate a filter query
	 */
	function visit( LivestatusFilterVisitor $visitor, $data ) {
		return $visitor->visit_match($this, $data);
	}

	/**
	 * Negate the current filter
	 *
	 * FIXME: This NEEDS to be type aware.
	 *
	 * simplify "not (list >= "kaka")" would be simplified to list < "kaka".
	 * >= in this case is a "contains"-operator
	 */
	public function negate() {
		return new LivestatusFilterMatch(
				$this->field,
				$this->value,
				$this->negations[$this->op]
				);
	}

	/**
	 * Test if two filters are equal.
	 *
	 * This is used for simplifications, return true if sure about equality.
	 * Return false if not equal, or unsure.
	 *
	 * Should only give false negatives.
	 */
	function equals( $filter ) {
		if( !( $filter instanceof self ) ) {
			return false;
		}
		if( $filter->field != $this->field ) {
			return false;
		}
		if( $filter->value != $this->value ) {
			return false;
		}
		if( $filter->op != $this->op ) {
			return false;
		}
		return true;
	}
}
