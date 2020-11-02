<?php

/**
 * Let the user input a number
 */
class Form_Field_Number_Model extends Form_Field_Model {

	/**
	 * @param $name string
	 * @param $pretty_name string
	 */
	public function __construct($name, $pretty_name) {
		parent::__construct($name, $pretty_name);
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'number';
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 * @throws FormException
	 * @throws MissingValueException
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]) || strval($raw_data[$name]) === "") {
			throw new MissingValueException("Missing a value for the field '$name'", $this);
		}
		if (!is_numeric($raw_data[$name])) {
			throw new FormException("The value of '$name' should be a number", $this);
		}
		$result->set_value($name, $raw_data[$name]);
	}
}
