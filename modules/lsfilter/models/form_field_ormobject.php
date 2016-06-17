<?php

/**
 * Let the user select a single object with this field model. Autocompletion of
 * object names should be supported by frontend code. By submitting multiple
 * tables to the constructor, you can let the user get autocomplete options for
 * several object types (as long as they are registered with
 * @see autocomplete::add_table()).
 */
class Form_Field_ORMObject_Model extends Form_Field_Model {

	/**
	 * @param $name string
	 * @param $pretty_name string
	 * @param $tables array Front end names of tables, such as saved_filters or hosts
	 */
	public function __construct($name, $pretty_name, array $tables) {
		parent::__construct($name, $pretty_name);
		$this->tables = $tables;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'ormobject';
	}

	/**
	 * @return array
	 */
	public function get_tables () {
		return $this->tables;
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 * @throws Form_Exception
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]))
			throw new FormException("Unknown field $name");
		if (!is_array($raw_data[$name]) ||
			!array_key_exists('table', $raw_data[$name]) ||
			!array_key_exists('value', $raw_data[$name])
		) {
			throw new FormException("$name does not point at a valid object (".var_export($raw_data[$name], true).")");
		}

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
