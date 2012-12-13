<?php

class LivestatusFilterOr extends LivestatusFilterBase {
	private $sub_filters = array();

	function get_sub_filters() {
		return $this->sub_filters;
	}
	
	function __clone() {
		$this->sub_filters = array_map(
				function($filt) {
					return clone $filt;
				},
				$this->sub_filters );
	}
	
	function prefix( $prefix ) {
		$res = new LivestatusFilterOr();
		foreach( $this->sub_filters as $subf ) {
			$res->add( $subf->prefix( $prefix ) );
		}
		return $res;
	}
	
	function generateFilter() {
		$result = "";
		foreach( $this->sub_filters as $subf ) {
			$result .= $subf->generateFilter();
		}

		$count = count($this->sub_filters);
		if( $count != 1 )
			$result .= "Or: $count\n";
		return $result;
	}
	
	function generateStats() {
		$result = "";
		foreach( $this->sub_filters as $subf ) {
			$result .= $subf->generateStats();
		}

		$count = count($this->sub_filters);
		if( $count != 1 )
			$result .= "StatsOr: $count\n";
		return $result;
	}
	
	function add( $filter ) {
		if( $filter instanceof self ) {
			foreach( $filter->sub_filters as $subf ) {
				$this->sub_filters[] = $subf;
			}
		} else {
			$this->sub_filters[] = $filter;
		}
	}
	
	function visit( LivestatusFilterVisitor $visitor, $data ) {
		return $visitor->visit_or($this, $data);
	}
}
