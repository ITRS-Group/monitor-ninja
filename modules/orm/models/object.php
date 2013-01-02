<?php

require_once( dirname(__FILE__).'/base/baseobject.php' );

abstract class Object_Model extends BaseObject_Model {
	static public $macros = array();
	
	public function expand_macros($str) {
		$matches = array_keys(static::$macros);
		$fields  = array_values(static::$macros);
		$values  = array();
		foreach($fields as $field) {
			$getter = "get_".$field;
			$values[] = $this->$getter();
		}
		return str_replace($matches, $values, $str);
	}
	
	public function get_current_user() {
		return Auth::instance()->get_user()->username;
	}
}
