<?php

require_once( dirname(__FILE__).'/base/baseobject.php' );

/**
 * Describes a single object from livestatus
 */
abstract class Object_Model extends BaseObject_Model {
	/**
	 * An array of macros possible to expand related to the object
	 */
	static public $macros = array();

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
	 * Expands macros for the current object, with url encoding
	 */
	public function expand_macros_url($str) {
		return $this->expand_macros($str, function($str) { return urlencode($str); } );
	}

	/**
	 * Expands macros for the current object
	 */
	public function expand_macros($str, $value_postprocessor=false) {

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
			if ($value_postprocessor !== false) {
				$value = $value_postprocessor($value);
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

	/**
	 * Get the url to configure this object
	 */
	public function get_config_url() {
		$unexpanded_url = Kohana::config('config.config_url.'.$this->_table);
		if(!$unexpanded_url)
			return false;
		if(Auth::instance()->authorized_for('configuration_information')==false)
			return false;
		return $this->expand_macros_url($unexpanded_url);
	}

	/**
	 * Get the current logged in username
	 */
	public function get_current_user() {
		return Auth::instance()->get_user()->username;
	}
}
