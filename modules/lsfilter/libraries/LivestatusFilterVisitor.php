<?php

/**
 * A visitor to convert a Livestatus Filter tree to a search query
 */
interface LivestatusFilterVisitor {
	/**
	 * Visit an and node
	 */
	public function visit_and( LivestatusFilterAnd $filt, $data );
	/**
	 * Visit an or node
	 */
	public function visit_or( LivestatusFilterOr $filt, $data );
	/**
	 * Visit an value match node
	 */
	public function visit_match( LivestatusFilterMatch $filt, $data );
	/**
	 * Visit an negation node
	 */
	public function visit_not( LivestatusFilterNot $filt, $data );
}
