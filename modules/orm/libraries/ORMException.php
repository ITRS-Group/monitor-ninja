<?php

class ORMException extends Exception {
	private $table = false;
	private $field = false;
	
	public function __construct( $msg, $table = false, $field = false ) {
		$message = $msg;
		$this->table = $table;
		$this->field = $field;
		if( $table )
			$msg .= "Table: ".$table;
		if( $field )
			$msg .= "Field: ".$field;
		parent::__construct($msg);
	}
	
	public function getTable() {
		return $this->table;
	}
	
	public function getField() {
		return $this->field;
	}
}