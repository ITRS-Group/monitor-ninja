<?php

/**
 * Let the user input any text she wants.
 */
class Form_Field_Text_Model extends Form_Field_Model {

	private $placeholder;

	/**
	 * @param $name string
	 * @param $pretty_name string
	 * @param $placeholder string
	 */
	public function __construct($name, $pretty_name, $placeholder = '') {
		parent::__construct( $name, $pretty_name );
		$this->placeholder = $placeholder;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'text';
	}

	/**
	 * @return string
	 */
	public function get_placeholder() {
		return $this->placeholder;
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
