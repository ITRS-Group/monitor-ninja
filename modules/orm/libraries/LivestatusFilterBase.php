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
}
