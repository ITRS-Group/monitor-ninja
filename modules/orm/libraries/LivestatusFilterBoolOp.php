<?php

/**
 * An or-node in the livestatus filter node tree
 */
abstract class LivestatusFilterBoolOp extends LivestatusFilterBase {
	protected $sub_filters = array();

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
		$res = new static();
		foreach( $this->sub_filters as $subf ) {
			$res->add( $subf->prefix( $prefix ) );
		}
		return $res;
	}

	/**
	 * Add a filter to the current clause
	 */
	public function add( $filter ) {
		if( $filter instanceof static ) {
			foreach( $filter->sub_filters as $subf ) {
				$this->do_add($subf);
			}
		} else {
			$this->do_add($filter);
		}
	}
	private function do_add( $filter ) {
		$add = true;
		foreach( $this->sub_filters as $tsf ) {
			if( $tsf->equals( $filter ) ) {
				$add = false;
			}
		}
		if( $add ) {
			$this->sub_filters[] = $filter;
		}
	}

	/**
	 * Simplify the filter
	 */
	public function simplify() {
		$out = new static();
		foreach($this->sub_filters as $subf) {
			$out->add($subf->simplify());
		}
		return $out;
	}
}
