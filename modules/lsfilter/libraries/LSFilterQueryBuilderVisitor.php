<?php
class LSFilterQueryBuilderVisitor implements LivestatusFilterVisitor {
	public function visit_and( LivestatusFilterAnd $filt ) {
		$subfilters = array();
		foreach( $filt->get_sub_filters() as $sub_filt ) {
			$subfilters[] = '(' . $sub_filt->visit($this) . ')';
		}
		return implode(' and ', $subfilters);
	}
	
	public function visit_or( LivestatusFilterOr $filt ) {
		$subfilters = array();
		foreach( $filt->get_sub_filters() as $sub_filt ) {
			$subfilters[] = '(' . $sub_filt->visit($this) . ')';
		}
		return implode(' or ', $subfilters);
	}
	
	public function visit_match( LivestatusFilterMatch $filt ) {
		$value = $filt->get_value();
		if( !is_numeric($value) ) $value = '"'.addslashes($value).'"';
		return $filt->get_field().$filt->get_op().$value;
	}
	
	public function visit_not( LivestatusFilterNot $filt ) {
		return 'not ('.$filt->get_filter()->visit($this).')';
	}
}