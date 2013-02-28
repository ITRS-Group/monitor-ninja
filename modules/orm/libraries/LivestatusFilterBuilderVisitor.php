<?php

/**
 * Convert a Livestatus Filter tree to a livestatus query
 */
class LivestatusFilterBuilderVisitor implements LivestatusFilterVisitor {
	protected $filter = "Filter: ";
	protected $and    = "And: ";
	protected $or     = "Or: ";
	protected $not    = "Negate:";

	/**
	 * Visit an and node
	 */
	public function visit_and( LivestatusFilterAnd $filt, $data ) {
		$subfilters = $filt->get_sub_filters();
		$result = "";
		foreach( $subfilters as $subf )
			$result .= $subf->visit($this,false);
		$count = count($subfilters);
		if( $count != 1 )
			$result .= $this->and . $count . "\n";
		return $result;
	}

	/**
	 * Visit an or node
	 */
	public function visit_or( LivestatusFilterOr $filt, $data ) {
		$subfilters = $filt->get_sub_filters();
		$result = "";
		foreach( $subfilters as $subf )
			$result .= $subf->visit($this,false);
		$count = count($subfilters);
		if( $count != 1 )
			$result .= $this->or . $count . "\n";
		return $result;
	}

	/**
	 * Visit an negation node
	 */
	public function visit_match( LivestatusFilterMatch $filt, $data ) {
		$fields = explode('.',$filt->get_field());
		if( count($fields) > 2 ) {
			$fields = array_slice($fields, count($fields)-2);
		}
		$field = implode('_',$fields);
		$op = $filt->get_op();
		$value = $filt->get_value();
		return $this->filter . $field . " " . $op . " " . $value . "\n";
	}
	
	public function visit_not( LivestatusFilterNot $filt, $data ) {
		$subfilter = $filt->get_filter();
		$result = $subfilter->visit($this,false);
		$result .= $this->not . "\n";
		return $result;
	}
}
