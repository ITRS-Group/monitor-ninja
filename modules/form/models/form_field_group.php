<?php
class Form_Field_Group_Model extends Form_Field_Model {
	private $fields = array();
	public function __construct($pretty_name, array $fields = array()) {
		parent::__construct( false, $pretty_name );
		foreach ( $fields as $field ) {
			$this->add_field( $field );
		}
	}
	public function add_field(Form_Field_Model $field) {
		$this->fields [] = $field;
	}
	public function get_fields() {
		return $this->fields;
	}
	public function get_type() {
		return 'group';
	}
	public function process_data(array $raw_data, Form_Result_Model $result) {
		foreach ($this->get_fields() as $field) {
			/* @var $field Form_Field_Model */
			$field->process_data($raw_data, $result);
		}
	}
}
