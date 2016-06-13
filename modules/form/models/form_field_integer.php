<?php
class Form_Field_Integer_Model extends Form_Field_Model {
	public function __construct ($name, $pretty_name) {
		parent::__construct ($name, $pretty_name);
	}
	public function get_type() {
		return 'integer';
	}
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]))
			throw new FormException( "Unknown field $name");
		if (!preg_match("/^\d+$/", $raw_data[$name]))
			throw new FormException( "$name is not an integer");
		$result->set_value($name, (int)$raw_data[$name]);
	}
}
