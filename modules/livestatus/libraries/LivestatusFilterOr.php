<?php

class LivestatusFilterOr extends LivestatusFilterBase {
	private $sub_filters = array();
	
	function generateFilter() {
		$result = "";
		foreach( $this->sub_filters as $subf ) {
			$result .= $subf->generateFilter();
		}

		$count = count($this->sub_filters);
		if( $count != 1 )
			$result .= "Or: $count\n";
	}
	
	function add( $filter ) {
		if( $filter instanceof LivestatusOr ) {
			foreach( $filter->sub_filters as $subf ) {
				$this->sub_filters[] = $subf;
			}
		} else {
			$this->sub_filters[] = $filter;
		}
	}
}