<?php

/**
 * This container model enables a group of Form_Field_Models to act a single
 * Form_Field_Model. That means that you can, for example, group together a
 * bunch of form fields inside a conditional form field
 * (@see Form_Field_Conditional_Model), to toggle all of them at once.
 */
class Form_Field_Group_Model extends Form_Field_Model {
	private $fields = array();

	/**
	 * @param $pretty_name string
	 * @param $fields array
	 */
	public function __construct($pretty_name, array $fields = array()) {
		parent::__construct( false, $pretty_name );
		foreach ( $fields as $field ) {
			$this->add_field( $field );
		}
	}

	/**
	 * @param $field Form_Field_Model
	 */
	public function add_field(Form_Field_Model $field) {
		$this->fields [] = $field;
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'group';
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 * @throws FormException
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		foreach ($this->get_fields() as $field) {
			/* @var $field Form_Field_Model */
			$field->process_data($raw_data, $result);
		}
	}
}
