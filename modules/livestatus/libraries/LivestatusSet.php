<?php

class LivestatusSet implements IteratorAggregate, Countable {
	private $table;
	private $class;
	private $filter;
	
	public function __construct( $table, $class ) {
		$this->table = $table;
		$this->class = $class;
		$this->filter = new LivestatusFilterAnd();
	}
	
	
	/*
	 * Set combinings
	 */
	public function union( $set ) {
		if( $this->table != $set->table )
			return false;
		
		$filter = new LivestatusFilterOr();
		$filter->add( $this->filter );
		$filter->add( $set->filter );
		
		$result = new LivestatusSet( $this->table, $this->class );
		$result->filter = $filter;
		return $result;
	}
	
	public function intersect( $set ) {
		if( $this->table != $set->table )
			return false;
		
		$filter = new LivestatusFilterAnd();
		$filter->add( $this->filter );
		$filter->add( $set->filter );
		
		$result = new LivestatusSet( $this->table, $this->class );
		$result->filter = $filter;
		return $result;
	}
	
	public function reduceBy( $filter ) {
		$newfilter = new LivestatusFilterAnd();
		$newfilter->add( $this->filter );
		$newfilter->add( $filter );
		$this->filter = $newfilter;
	}
	
	/*
	 * Access
	 */
	
	
	/* For Countable */
	public function count() {
		
	}
	
	/* For IteratorAggregate */
	public function getIterator() {
		$ls = LivestatusAccess::instance();
		
		$columns = false;
		
		list($columns, $objects, $count) = $ls->query(
				$this->table,
				$this->filter->generateFilter(),
				$columns
				);
		
		return new LivestatusSetIterator($objects, $columns, $this->class);
	}
	
	/* For testing */
	public function test_getFormattedFilter() {
		return "GET ".$this->table."\n".$this->filter->generateFilter();
	}
}