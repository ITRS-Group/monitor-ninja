<?php

/**
 * Choose any of the icons from a hard coded folder on disk.
 */
class Form_Field_Icon_Model extends Form_Field_Model {

	/**
	 * @return array of icon names
	 */
	public function get_icons() {
		// Do not glob multiple times. PHP requests are not lasting
		// long enough for there to be a risk that a new icon is added
		// during that time.
		static $icons;
		if($icons) {
			return $icons;
		}
		// resolves to ninja/application/views/icons/x16
		$icons = glob(__DIR__."/../../../application/views/icons/x16/*.png");
		$icons = array_map(function($i) {
			return pathinfo($i, PATHINFO_FILENAME);
		}, $icons);
		return $icons;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return "icon";
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 * @throws FormException
	 * @throws MissingValueException
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]) || !is_string($raw_data[$name]) || !strlen($raw_data[$name])) {
			throw new MissingValueException("Missing a value for the field '$name'", $this);
		}
		if (!in_array($raw_data[$name], $this->get_icons())) {
			throw new FormException("$name is not a valid icon", $this);
		}
		$result->set_value($name, $raw_data[$name]);
	}
}
