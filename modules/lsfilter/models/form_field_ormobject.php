<?php
class Form_Field_ORMObject_Model extends Form_Field_Model {

	public function __construct($name, $pretty_name, array $tables) {
		parent::__construct($name, $pretty_name);
		$this->tables = $tables;
	}

	public function get_type() {
		return 'ormobject';
	}

	public function get_tables () {
		return $this->tables;
	}

	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]))
			throw new FormException("Unknown field $name");
		if (!is_array($raw_data[$name]))
			throw new FormException("$name is not a text field");

		$table = $raw_data[$name]['table'];
		$value = $raw_data[$name]['value'];

		$settings = autocomplete::get_settings($table);
		$query = sprintf($settings['query'], html::specialchars($value));

		$object = ObjectPool_Model::get_by_query($query)->one();
		if (!$object) {
			throw new FormException("Could not find any object in table '$table' named '$value'");
		}
		$result->set_value($name, $object);
	}

}
