<?php

/**
 * An match-node in the livestatus filter node tree
 */
class LivestatusFilterMatch extends LivestatusFilterBase {
	private $field;
	private $op;
	private $value;

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
}