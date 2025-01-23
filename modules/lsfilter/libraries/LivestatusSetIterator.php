<?php

/**
 * A helper to iterate over objects in the result set of an ORM object
 */
class LivestatusSetIterator implements Iterator {
	private $data;
	private $ptr;
	private $columns;
	private $export_columns;
	private $class;

	/**
	 * Generate a set of object in the ORM. Don't call directly. Used from the set:s in ORM generated code.
	 */
	public function __construct( $data, $columns, $export_columns, $class ) {
		if( is_array($data) ) {
			$data = new ArrayIterator($data);
		}
		$this->data = $data;
		$this->columns = $columns;
		$this->ptr = 0;
		$this->class = $class;
		$this->export_columns = $export_columns;
	}

	/**
	 * Get the current object from the dataset
	 */
	public function current(): mixed
	{
		if(!$this->valid()) {
			return false;
		}
		$cur_arr = $this->data->current();
		if( empty($cur_arr) ) {
			return false;
		}
		$varmap = array_combine(
				$this->columns,
				$cur_arr
				);
		$classname = $this->class;
		return $classname::factory_from_setiterator( $varmap, '', $this->export_columns );
	}

	/**
	 * Get the key of the element
	 */
	public function key(): mixed
	{
		return $this->data->key();
	}

	/**
	 * Move the cursor to the next object
	 */
	public function next(): int
	{
		$this->data->next();
	}

	/**
	 * Rewind the set
	 */
	public function rewind(): void
	{
		$this->ptr = $this->data->rewind();
	}

	/**
	 * Return if the data is valid
	 */
	public function valid(): bool
	{
		return $this->data->valid();
	}
}

?>