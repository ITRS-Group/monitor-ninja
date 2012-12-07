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
	
	public function get_table() {
		return $this->table;
	}
	
	public function get_class() {
		return $this->class;
	}
	
	/*
	 * Set operations
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
	
	public function complement() {
		$filter = new LivestatusFileNot($this->filter);
		
		$result = new LivestatusSet( $this->table, $this->class );
		$result->filter = $filter;
		return $result;
	}
	
	public function reduceBy( $column, $value, $op='=' ) {
		$newfilter = new LivestatusFilterAnd();
		$newfilter->add( $this->filter );
		$newfilter->add( new LivestatusFilterMatch( $column, $value, $op ) );
		
		$result = new LivestatusSet( $this->table, $this->class );
		$result->filter = $newfilter;
		return $result;
	}
	
	/*
	 * Access
	 */
	
	
	/* For Countable */
	public function count() {
		$ls = LivestatusAccess::instance();
		
		$columns = false;
		
		$filter  = "Limit: 0\n";
		$filter .= $this->filter->generateFilter();
		
		list($columns, $objects, $count) = $ls->query(
				$this->table,
				$filter,
				$columns
				);
		
		return $count;
	}
	
	/* For IteratorAggregate */
	public function getIterator()
	{
		return $this->it(false,array());
	}
	
	public function it($columns,$order)
	{
		$ls = LivestatusAccess::instance();

		$filter  = "";
		foreach( $order as $col ) {
			$filter .= "Sort: $col\n";
		}
		$filter .= $this->filter->generateFilter();
		
		list($columns, $objects, $count) = $ls->query(
				$this->table,
				$filter,
				$columns
				);
		
		return new LivestatusSetIterator($objects, $columns, $this->class);
	}
	
	/* For testing */
	public function test_getFormattedFilter() {
		return "GET ".$this->table."\n".$this->filter->generateFilter();
	}
}