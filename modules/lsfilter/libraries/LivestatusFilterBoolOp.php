<?php

/**
 * An or-node in the livestatus filter node tree
 */
abstract class LivestatusFilterBoolOp extends LivestatusFilterBase {
	protected $sub_filters = array(); /**< The sub filters */

	/**
	 * Get a list of sub filters
	 */
	public function get_sub_filters() {
		return array_values($this->sub_filters);
	}

	/**
	 * Clone the filter
	 */
	public function __clone() {
		$new_filters = array();
		foreach ($this->sub_filters as $hash => $subfilter) {
			$new_filters[$hash] = clone $subfilter;
		}
		$this->sub_filters = $new_filters;
	}

	/**
	 * Retrieve the hash of a bool op
	 */
	protected function get_bool_op_hash ($op) {
		$hashes = array_keys($this->sub_filters);
		sort($hashes);
		return md5($op . " " . implode(" ", $hashes));
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
			$this->sub_filters = array_merge($this->sub_filters, $filter->sub_filters);
		} else {
			$this->sub_filters[$filter->get_hash()] = $filter;
		}
	}

	/**
	 * Simplify the filter
	 */
	public function simplify() {
		if(count($this->sub_filters) == 1) {
			list($sub_f) = array_values($this->sub_filters);
			return $sub_f->simplify();
		}
		$out = new static();
		foreach($this->sub_filters as $subf) {
			$out->add($subf->simplify());
		}
		return $out;
	}
}
