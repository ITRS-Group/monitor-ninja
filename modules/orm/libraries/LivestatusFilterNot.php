<?php

/**
 * An not-node in the livestatus filter node tree
 */
class LivestatusFilterNot extends LivestatusFilterBase {
	private $filter;

	/**
	 * Get the sub filter
	 */
	public function get_filter() {
		return $this->filter;
	}

	/**
	 * Clone the filter
	 */
	public function __clone() {
		$this->filter = clone $this->filter;
	}

	/**
	 * Generate a negation filter
	 */
	public function __construct( $filter ) {
		$this->filter = $filter;
	}

	/**
	 * Returns a copy of the filter, but with a variables prefixed
	 */
	public function prefix( $prefix ) {
		return new LivestatusFilterNot( $this->filter->prefix( $prefix ) );
	}

	/**
	 * Visit the filter node with a visitor, to generate a filter query
	 */
	public function visit( LivestatusFilterVisitor $visitor, $data ) {
		return $visitor->visit_not($this, $data);
	}

	/**
	 * Simplify
	 */
	public function simplify() {
		if( $this->filter instanceof LivestatusFilterAnd ) {
			$out = new LivestatusFilterOr();
			foreach($this->filter->get_sub_filters() as $subf) {
				$out->add(new LivestatusFilterNot($subf));
			}
			return $out->simplify();
		}
		if( $this->filter instanceof LivestatusFilterOr ) {
			$out = new LivestatusFilterAnd();
			foreach($this->filter->get_sub_filters() as $subf) {
				$out->add(new LivestatusFilterNot($subf));
			}
			return $out->simplify();
		}
		if( $this->filter instanceof LivestatusFilterNot ) {
			return $this->filter->filter->simplify();
		}
		if( $this->filter instanceof LivestatusFilterMatch ) {
			return $this->filter->negate();
		}
		return new static( $this->filter->simplify() );
	}
}
