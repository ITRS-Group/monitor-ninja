<?php
class Form_Field_Boolean_Model extends Form_Field_Model {
	public function __construct($name, $pretty_name) {
		parent::__construct($name, $pretty_name);
	}
	public function get_type() {
		return 'boolean';
	}
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]))
			$raw_data[$name] = false;
		else $raw_data[$name] = true;
		if (!is_bool($raw_data[$name]))
			throw new FormException( "$name is not a boolean field" );
		$result->set_value($name, $raw_data[$name]);
	}
}
