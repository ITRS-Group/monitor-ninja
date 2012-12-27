<?php

class LivestatusSetIterator implements Iterator {
	private $data;
	private $ptr;
	private $columns;
	private $class;
	
	public function __construct( $data, $columns, $class ) {
		if( is_array($data) ) {
			$data = new ArrayIterator($data);
		}
		$this->data = $data;
		$this->columns = $columns;
		$this->ptr = 0;
		$this->class = $class;
	}
	
	public function current()
	{
		$cur_arr = $this->data->current();
		$varmap = array_combine(
				$this->columns,
				$cur_arr
				);
		return new $this->class( $varmap, '' );
	}
	
	public function key()
	{
		return $this->data->key();
	}
	
	public function next()
	{
		$this->data->next();
	}
	
	public function rewind()
	{
		$this->ptr = $this->data->rewind();
	}
	
	public function valid()
	{
		return $this->data->valid();
	}
}

?>