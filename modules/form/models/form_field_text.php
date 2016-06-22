<?php

/**
 * Let the user input any text she wants.
 */
class Form_Field_Text_Model extends Form_Field_Model {

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
		return 'text';
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 * @throws FormException
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]))
			throw new FormException("Unknown field $name");
		if (!is_string($raw_data[$name]))
			throw new FormException("$name is not a text field");
		$result->set_value($name, $raw_data[$name]);
	}
}
