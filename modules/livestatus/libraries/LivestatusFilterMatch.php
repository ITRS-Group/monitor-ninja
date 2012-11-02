<?php

class LivestatusFilterMatch extends LivestatusFilterBase {
	private $field;
	private $op;
	private $value;
	
	function __construct( $field, $value, $op = "=" ) {
		$this->field = $field;
		$this->op = $op;
		$this->value = $value;
	}
	
	function generateFilter() {
		/* TODO: escape */
		return "Filter: ".$this->field." ".$this->op. " ".$this->value."\n";
	}
}