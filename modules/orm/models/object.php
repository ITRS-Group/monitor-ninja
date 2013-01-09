<?php

require_once( dirname(__FILE__).'/base/baseobject.php' );

abstract class Object_Model extends BaseObject_Model {
	static public $macros = array();
	
	/* Make it avalible on all tables */
	public function get_custom_variables() {
		return array();
	}
	
	public function expand_macros($str) {
		$matches = array();
		$values  = array();
		foreach(static::$macros as $match => $field) {
			$value = $this;
			foreach( explode('.',$field) as $subfield ) {
				if( $value ) {
					$getter = "get_".$subfield;
					$value = $value->$getter();
				}
			}
			$matches[] = $match;
			$values[] = $value;
		}
		foreach( $this->get_custom_variables() as $var => $value ) {
			$matches[] = '$_'.strtoupper($var).'$';
			$values[] = $value;
		}
		return str_replace($matches, $values, $str);
	}
	
	public function get_current_user() {
		return Auth::instance()->get_user()->username;
	}
}
