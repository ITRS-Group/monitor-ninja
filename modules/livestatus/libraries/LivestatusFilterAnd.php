<?php

class LivestatusFilterAnd extends LivestatusFilterBase {
	private $sub_filters = array();
	
	function generateFilter() {
		$result = "";
		foreach( $this->sub_filters as $subf ) {
			$result .= $subf->generateFilter();
		}

		$count = count($this->sub_filters);
		if( $count != 1 )
			$result .= "And: $count\n";
	}
	
	function add( $filter ) {
		if( $filter instanceof LivestatusAnd ) {
			foreach( $filter->sub_filters as $subf ) {
				$this->sub_filters[] = $subf;
			}
		} else {
			$this->sub_filters[] = $filter;
		}
	}
}