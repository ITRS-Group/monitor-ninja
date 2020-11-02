<?php

/**
 * A hidden field.
 */
class Form_Field_Hidden_Model extends Form_Field_Model {

	/**
	 * @param $name string
	 */
	public function __construct($name) {
		parent::__construct($name, $name);
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'hidden';
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
		if (!is_string($raw_data[$name])) {
			throw new FormException("$name is not a text field", $this);
		}
		$result->set_value($name, $raw_data[$name]);
	}
}
