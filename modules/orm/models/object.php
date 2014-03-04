<?php

require_once( dirname(__FILE__).'/base/baseobject.php' );

/**
 * Describes a single object from livestatus
 */
abstract class Object_Model extends BaseObject_Model {
	/**
	 * Get the table of the current object
	 */
	public function get_table() {
		return $this->_table;
	}

	/**
	 * Get a list of custom variables related to the object, if possible
	 */
	public function get_custom_variables() {
		return array();
	}

	/**
	 * Get the current logged in username
	 */
	public function get_current_user() {
		return Auth::instance()->get_user()->username;
	}
}
