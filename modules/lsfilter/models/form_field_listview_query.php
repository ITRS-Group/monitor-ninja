<?php

/**
 * Lets the user write a syntax highlighted query in free form
 */
class Form_Field_Listview_Query_Model extends Form_Field_Model {

	/**
	 * @param $name string
	 * @param $pretty_name $string
	 */
	public function __construct($name, $pretty_name) {
		parent::__construct( $name, $pretty_name );
	}

	public function get_type() {
		return 'listview_query';
	}

	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]))
			throw new FormException("Unknown field $name");
		$result->set_value($name, $raw_data[$name]);
	}

}
