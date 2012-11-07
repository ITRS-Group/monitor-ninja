<?php

class LivestatusSetIterator implements Iterator {
	private $data;
	private $ptr;
	private $columns;
	private $class;
	
	public function __construct( $data, $columns, $class ) {
		$this->data = $data;
		$this->columns = $columns;
		$this->ptr = 0;
		$this->class = $class;
	}
	
	public function current()
	{
		$varmap = array_combine(
				$this->columns,
				$this->data[$this->ptr]
				);
		return new $this->class( $varmap, '' );
	}
	
	public function key()
	{
		return $this->ptr;
	}
	
	public function next()
	{
		$this->ptr++;
	}
	
	public function rewind()
	{
		$this->ptr = 0;
	}
	
	public function valid()
	{
		return isset($this->data[$this->ptr]);
	}
}

?>