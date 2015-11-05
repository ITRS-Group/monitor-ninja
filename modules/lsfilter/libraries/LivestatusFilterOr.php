<?php

/**
 * An or-node in the livestatus filter node tree
 */
class LivestatusFilterOr extends LivestatusFilterBoolOp {
	/**
	 * Visit the filter node with a visitor, to generate a filter query
	 */
	public function visit( LivestatusFilterVisitor $visitor, $data ) {
		return $visitor->visit_or($this, $data);
	}

	public function get_hash () {
		return $this->get_bool_op_hash("or");
	}
}
