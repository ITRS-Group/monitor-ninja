<?php
class Form_Field_Conditional_Model extends Form_Field_Model {
	private $rel = false;
	private $value = false;
	private $field = false;
	public function __construct($rel, $value, $field) {
		parent::__construct( false, false );
		$this->rel = $rel;
		$this->value = $value;
		$this->field = $field;
	}
	public function get_type() {
		return 'conditional';
	}
	public function get_rel() {
		return $this->rel;
	}
	public function get_value() {
		return $this->value;
	}
	public function get_field() {
		return $this->field;
	}
	public function process_data(array $raw_data, Form_Result_Model $result) {
		if (!$result->has_value($this->rel))
			throw new FormException( "Unknown field {$this->rel} to relate to" );
		if ($result->get_value($this->rel) != $this->value)
			return;
		$this->field->process_data($raw_data, $result);
	}
}
