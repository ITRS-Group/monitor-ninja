<?php
class Form_Field_Number_Model extends Form_Field_Model {
	public function __construct($name, $pretty_name) {
		parent::__construct( $name, $pretty_name );
	}
	public function get_type() {
		return 'number';
	}
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]))
			throw new FormException( "Unknown field $name");
		if (!is_numeric($raw_data[$name]))
			throw new FormException( "$name is not a number field");
		$result->set_value($name, (float)$raw_data[$name]);
	}
}
