<?php

/**
 * Base class of livestatus filters
 */
abstract class LivestatusFilterBase {
	/**
	 * Returns a copy of the filter, but with a variables prefixed
	 */
	abstract function prefix( $prefix );

	/**
	 * Visit the filter node with a visitor, to generate a filter query
	 */
	abstract function visit( LivestatusFilterVisitor $visitor, $data );

	/**
	 * Simplify the filter
	 */
	function simplify() {
		return clone $this;
	}

	/**
	 * Test if two filters are equal.
	 *
	 * This is used for simplifications, return true if sure about equality.
	 * Return false if not equal, or unsure.
	 *
	 * Should only give false negatives.
	 */
	function equals( $filter ) {
		return false;
	}
}
