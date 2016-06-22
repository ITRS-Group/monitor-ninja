<?php

/**
 * Ask the user for an integer.
 */
class Form_Field_Integer_Model extends Form_Field_Model {

	/**
	 * @return string
	 */
	public function get_type() {
		return 'integer';
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 * @throws FormException
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]))
			throw new FormException( "Unknown field $name");
		if (!preg_match("/^\d+$/", $raw_data[$name]))
			throw new FormException( "$name is not an integer");
		$result->set_value($name, (int)$raw_data[$name]);
	}
}
