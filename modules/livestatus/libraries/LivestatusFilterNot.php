<?php

class LivestatusFilterNot extends LivestatusFilterBase {
	private $filter;
	
	function __construct( $filter ) {
		$this->filter;
	}
	
	function generateFilter() {
		return $this->filter->generateFilter()."Negate: \n";
	}
}