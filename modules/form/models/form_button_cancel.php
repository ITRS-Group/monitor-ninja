<?php

/**
 * Represents a form cancel button
 */
class Form_Button_Cancel_Model extends Form_Button_Model {

	public function __construct($name, $pretty_name) {
		parent::__construct($name, $pretty_name);
	}

	public function get_type() {
		return "button_cancel";
	}

}
