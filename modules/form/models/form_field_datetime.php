<?php

/**
 * Let the user input a Datetime she wants.
 */
class Form_Field_Datetime_Model extends Form_Field_Model {

	/**
	 * @param $name string
	 * @param $pretty_name string
	 */
	public function __construct($name, $pretty_name) {
		parent::__construct( $name, $pretty_name );
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'datetime';
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 * @throws FormException
	 * @throws MissingValueException
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name])) {
			throw new MissingValueException("Missing a value for the field '$name'", $this);
		}

		$value = date("Y-m-d H:i:s", $raw_data[$name]);
		$result->set_value($name, $value);
	}
}
