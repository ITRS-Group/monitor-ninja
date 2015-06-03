<?php

require_once( dirname(__FILE__).'/base/baseobject.php' );

/**
 * Describes a single object from livestatus
 */
abstract class Object_Model extends BaseObject_Model {
	/**
	 * Mine out rewrite columns from doctags
	 *
	 * TODO: Don't do string magic in runtime... That's slow
	 * However... in this case, it's not that often...
	 */
	static public function rewrite_columns() {
		$orm_doctags = Module_Manifest_Model::get('orm_doctags');
		$classname = strtolower(get_called_class());
		if(!isset($orm_doctags[$classname]))
			return array();

		$rewrite_columns = array();
		foreach($orm_doctags[$classname] as $field => $info) {
			if(!isset($info['depend']))
				continue;
			if(substr($field,0,4) != 'get_')
				continue;
			$field = substr($field,4);
			$rewrite_columns[$field] = $info['depend'];
		}
		return $rewrite_columns;
	}

	/**
	 * Get the table of the current object
	 *
	 * @return string
	 */
	public function get_table() {
		return $this->_table;
	}

	/**
	 * Get a list of custom variables related to the object, if possible
	 *
	 * @return array
	 */
	public function get_custom_variables() {
		return array();
	}

	/**
	 * Get a list of commands related to the object
	 *
	 * @return array
	 */
	public function list_commands() {
		return array();
	}

	/**
	 * Get the current logged in username
	 *
	 * @return string
	 */
	public function get_current_user() {
		return Auth::instance()->get_user()->username;
	}
}
