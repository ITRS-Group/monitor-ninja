<?php

/**
 * Models a and operation in a filer for livestatus
 */
class LivestatusFilterAnd extends LivestatusFilterBase {
	private $sub_filters = array();
	
	/**
	 * Get a list of sub filters
	 */
	public function get_sub_filters() {
		return $this->sub_filters;
	}

	/**
	 * Clone the filter
	 */
	public function __clone() {
		$this->sub_filters = array_map(
				function($filt) {
					return clone $filt;
				},
				$this->sub_filters );
	}

	/**
	 * Returns a copy of the filter, but with a variables prefixed
	 */
	public function prefix( $prefix ) {
		$res = new LivestatusFilterAnd();
		foreach( $this->sub_filters as $subf ) {
			$res->add( $subf->prefix( $prefix ) );
		}
		return $res;
	}

	/**
	 * Add a filter to the current and clause
	 */
	public function add( $filter ) {
		if( $filter instanceof self ) {
			foreach( $filter->sub_filters as $subf ) {
				$this->sub_filters[] = $subf;
			}
		} else {
			$this->sub_filters[] = $filter;
		}
	}

	/**
	 * Visit the filter node with a visitor, to generate a filter query
	 */
	public function visit( LivestatusFilterVisitor $visitor, $data ) {
		return $visitor->visit_and($this, $data);
	}
}
