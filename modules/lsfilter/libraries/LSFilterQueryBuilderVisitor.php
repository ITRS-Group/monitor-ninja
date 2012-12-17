<?php
class LSFilterQueryBuilderVisitor implements LivestatusFilterVisitor {
	public function visit_and( LivestatusFilterAnd $filt, $prio ) {
		$subfilters = $filt->get_sub_filters();
		$subqueries = array();
		
		if( count( $subfilters ) == 1) {
			return $subfilters[0]->visit($this, $prio);
		} else if( count( $subfilters ) == 0 ) {
			return 'all';
		}
		
		foreach( $subfilters as $sub_filt ) {
			$subqueries[] = $sub_filt->visit($this, 2);
		}
		$query = implode(' and ', $subqueries);
		
		if( $prio > 2 ) $query = "($query)";
		return $query; 
	}
	
	public function visit_or( LivestatusFilterOr $filt, $prio ) {
	$subfilters = $filt->get_sub_filters();
		$subqueries = array();
		
		if( count( $subfilters ) == 1) {
			return $subfilters[0]->visit($this, $prio);
		} else if( count( $subfilters ) == 0 ) {
			return 'none';
		}
		
		foreach( $subfilters as $sub_filt ) {
			$subqueries[] = $sub_filt->visit($this, 2);
		}
		$query = implode(' or ', $subqueries);
		
		if( $prio > 1 ) $query = "($query)";
		return $query; 
	}
	
	public function visit_match( LivestatusFilterMatch $filt, $prio ) {
		$op    = $filt->get_op();
		$field = $filt->get_field();
		$value = $filt->get_value();
		
		if( !is_numeric($value) ) {
			$value = '"'.addslashes($value).'"';
			
			if( $op == '>=' ) {
				/* Special case for groups */
				$field_parts = explode('.', $field);
				if( end($field_parts) == 'groups' ) {
					$op = ' in ';
					array_pop($field_parts);
					$field = implode('.', $field_parts);
				}
			}
		}
		
		return $field.$op.$value;
	}

	
	public function visit_not( LivestatusFilterNot $filt, $prio ) {
		$query = 'not '.$filt->get_filter()->visit($this, 3);
		if( $prio >= 3 ) $query = "($query)";
		return $query; 
	}
}
