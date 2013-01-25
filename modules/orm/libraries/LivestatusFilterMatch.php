<?php

class LivestatusFilterMatch extends LivestatusFilterBase {
	private $field;
	private $op;
	private $value;

	function get_field() {
		return $this->field;
	}
	function get_op() {
		return $this->op;
	}
	function get_value() {
		return $this->value;
	}
	
	function __construct( $field, $value, $op = "=" ) {
		$this->field = $field; //TODO: Do this in some fancy way...
		$this->op = $op;
		$this->value = $value;
	}
	
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

	function visit( LivestatusFilterVisitor $visitor, $data ) {
		return $visitor->visit_match($this, $data);
	}
}