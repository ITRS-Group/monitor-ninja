<?php

class LivestatusFilterMatch extends LivestatusFilterBase {
	private $field;
	private $op;
	private $value;
	
	function __construct( $field, $value, $op = "=" ) {
		$this->field = str_replace('.','_',$field); //TODO: Do this in some fancy way...
		$this->op = $op;
		$this->value = $value;
	}
	
	function prefix( $prefix ) {
		return new LivestatusFilterMatch( $prefix.$this->field, $this->value, $this->op );
	}

	function generateFilter() {
		/* TODO: escape */
		return "Filter: ".$this->field." ".$this->op. " ".$this->value."\n";
	}
	function generateStats() {
		/* TODO: escape */
		return "Stats: ".$this->field." ".$this->op. " ".$this->value."\n";
	}
}