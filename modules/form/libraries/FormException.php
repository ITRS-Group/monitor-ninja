<?php

class FormException extends Exception {
	protected $field;

	public function __construct($message, $field = null, Exception $previous = null) {
		parent::__construct($message, null, $previous);
		$this->field = $field;
	}

	public function get_field() {
		return $this->field;
	}
}