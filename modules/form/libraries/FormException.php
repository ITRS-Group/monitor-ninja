<?php

/**
 * When a form wants to indicate validation failure, this exception gets
 * thrown.
 */
class FormException extends Exception {
	private $field;

	/**
	 * @param $message string
	 * @param $field Form_Field_Model = null
	 * @param $previous Exception = null
	 */
	public function __construct($message, $field = null, Exception $previous = null) {
		parent::__construct($message, null, $previous);
		$this->field = $field;
	}

	/**
	 * @return Form_Field_Model
	 */
	public function get_field() {
		return $this->field;
	}
}
