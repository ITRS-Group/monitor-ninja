<?php

/**
 * Represents a form confirm button
 */
class Form_Button_Confirm_Model extends Form_Button_Model {

	public function __construct($name, $pretty_name) {
		parent::__construct($name, $pretty_name);
	}

	public function get_type() {
		return "button_confirm";
	}

}
