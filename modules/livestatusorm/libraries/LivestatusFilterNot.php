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
		$this->filter = $filter;
	}
	
	function prefix( $prefix ) {
		return new LivestatusFilterNot( $this->filter->prefix( $prefix ) );
	}

	function visit( LivestatusFilterVisitor $visitor, $data ) {
		return $visitor->visit_not($this, $data);
	}
}
