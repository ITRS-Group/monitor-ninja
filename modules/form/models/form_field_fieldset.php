<?php
class Form_Field_Fieldset_Model extends Form_Field_Model {
	private $fields = array();
	public function __construct($pretty_name, array $fields = array()) {
		parent::__construct( false, $pretty_name );
		foreach ( $fields as $field ) {
			$this->addField( $field );
		}
	}
	public function addField(Form_Field_Model $field) {
		$this->fields [] = $field;
	}
	public function get_fields() {
		return $this->fields;
	}
	public function get_type() {
		return 'fieldset';
	}
	public function process_data(array $raw_data) {
		$result = array();
		foreach ( $this->get_fields() as $field ) {
			/* @var $field Form_Field_Model */
			$result = array_merge( $result, $field->process_data( $raw_data ) );
		}
		return $result;
	}
}