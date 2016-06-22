<?php

/**
 * Let the user says yes or no to a specific option.
 */
class Form_Field_Boolean_Model extends Form_Field_Model {

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
		return 'boolean';
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$result->set_value($this->get_name(), isset($raw_data[$this->get_name()]));
	}
}
