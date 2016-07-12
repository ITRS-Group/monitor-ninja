<?php

/**
 * If you want to make form fields dependant on other fields, you should use
 * this class.
 *
 * Usage:
 *
 * $my_field = new Form_Field_Option_Model('species',
 *     array('crocodile', 'elephant', 'tripod'));
 * // only tripods have enemies, of course:
 * $number_of_enemies = new Form_Field_Conditional_Model(
 *     'species', 'tripod', new Form_Field_Number_Model(
 *         'enemies', 'Number of enemies'));
 *
 * By declaring the number of enemies as dependant of another field's value, we
 * get the validation, server side- and client side rendering for free. Yay!
 */
class Form_Field_Conditional_Model extends Form_Field_Model {
	private $rel = false;
	private $value = false;
	private $field = false;

	/**
	 * @param $rel string
	 * @param $value string
	 * @param $field Form_Field_Model
	 */
	public function __construct($rel, $value, Form_Field_Model $field) {
		parent::__construct( false, false );
		$this->rel = $rel;
		$this->value = $value;
		$this->field = $field;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'conditional';
	}

	/**
	 * @return string
	 */
	public function get_rel() {
		return $this->rel;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * @return Form_Field_Model
	 */
	public function get_field() {
		return $this->field;
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 * @throws FormException
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		if (!$result->has_value($this->rel)) {
			throw new FormException("The form should contain '$this->rel' so that this conditional model can refer to it", $this);
		}
		if ($result->get_value($this->rel) !== $this->value)
			return;
		$this->field->process_data($raw_data, $result);
	}
}
