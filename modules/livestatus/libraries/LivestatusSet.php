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
		
	}
	public function intersect( $set ) {
		
	}
	
	/*
	 * Access
	 */
	public function count() {
		
	}
	public function getIterator() {
		
	}
}