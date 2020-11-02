<?php

/**
 * Custom variables is a key-value like list of properties placed on Naemon objects.
 */
class custom_variable {

	/**
	 * Should we render this custom variable in the GUI?
	 * @param $custom_variable string
	 * @return boolean
	 */
	public static function is_public($custom_variable) {
		return substr($custom_variable, 0, 5) !== 'OP5H_';
	}

}