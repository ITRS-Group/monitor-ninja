<?php

/**
 * Exception for ORM
 */
class ORMException extends Exception {
	private $table = false;
	private $field = false;

	/**
	 * initialization of the ORMExcetpion
	 *
	 * @param $msg message to print
	 * @param $table related table, or false
	 * @param $field related field, or false
	 * @param $previous Optional, previous exception if using exception chaining
	 */
	public function __construct( $msg, $table = false, $field = false, Exception $previous = NULL ) {
		$message = $msg;
		$this->table = $table;
		$this->field = $field;
		if( $table )
			$msg .= "Table: ".$table;
		if( $field )
			$msg .= "Field: ".$field;
		parent::__construct($msg, 0, $previous);
	}

	/**
	 * Get the table name related to the exception, or false
	 * @return table name, or false
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Get the field name related to the exception, or false
	 * @return field name, or false
	 */
	public function getField() {
		return $this->field;
	}
}