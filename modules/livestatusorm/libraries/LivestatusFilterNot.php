<?php

class LivestatusFilterNot extends LivestatusFilterBase {
	private $filter;
	
	function get_filter() {
		return $this->filter;
	}
	
	function __clone() {
		$this->filter = clone $this->filter;
	}
	
	function __construct( $filter ) {
		$this->filter;
	}
	
	function prefix( $prefix ) {
		return new LivestatusFilterNot( $this->filter->prefix( $prefix ) );
	}

	function generateFilter() {
		return $this->filter->generateFilter()."Negate: \n";
	}
	
	function generateStats() {
		return $this->filter->generateStats()."StatsNegate: \n";
	}

	function visit( LivestatusFilterVisitor $visitor ) {
		return $visitor->visit_not($this);
	}
}