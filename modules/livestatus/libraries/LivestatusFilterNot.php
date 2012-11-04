<?php

class LivestatusFilterNot extends LivestatusFilterBase {
	private $filter;
	
	function __clone() {
		$this->filter = clone $this->filter;
	}
	
	function __construct( $filter ) {
		$this->filter;
	}
	
	function generateFilter() {
		return $this->filter->generateFilter()."Negate: \n";
	}
}