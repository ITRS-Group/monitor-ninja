<?php

/**
 * Models a and operation in a filer for livestatus
 */
class LivestatusFilterAnd extends LivestatusFilterBoolOp {
	/**
	 * Visit the filter node with a visitor, to generate a filter query
	 */
	public function visit( LivestatusFilterVisitor $visitor, $data ) {
		return $visitor->visit_and($this, $data);
	}

	public function get_hash () {
		return $this->get_bool_op_hash("and");
	}
}
