<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Custom error for the Fieldset_Model
 */
class Fieldset_Exception extends Exception {}

/**
 * An ordered list of options
 */
class Fieldset_Model implements ArrayAccess, IteratorAggregate, Countable {
	private $legend;
	private $options = array();
	private $attributes = array();

	/**
	 * @param $legend string
	 * @param $attributes array
	 */
	public function __construct($legend, array $attributes = array()) {
		$this->legend = $legend;
		$this->attributes = $attributes;
	}

	/**
	 * Enables count($model);
	 */
	public function count() {
		return count($this->options);
	}

	/**
	 * @return array suitable to pass into form::open_fieldset()
	 */
	public function get_attributes() {
		return $this->attributes;
	}

	/**
	 * @return string
	 */
	public function get_legend() {
		return $this->legend;
	}

	/**
	 * Implements ArrayAccess
	 */
	public function offsetExists($offset) {
		return isset($this->options[$offset]);
	}

	/**
	 * Implements ArrayAccess
	 */
	public function offsetGet($offset) {
		if(!$this->offsetExists($offset)) {
			return null;
		}
		return $this->options[$offset];
	}

	/**
	 * @param $offset mixed
	 * @param $value option
	 */
	public function offsetSet($offset, $value) {
		if(!$value instanceof option) {
			$type = gettype($value);
			if($type == 'object') {
				$type = get_class($type);
			}
			throw new Fieldset_Exception("Invalid argument to ".__CLASS__.", need option, got '$type'");
		}
		if($offset === null) {
			$this->options[] = $value;
		} else {
			$this->options[$offset] = $value;
		}
	}

	/**
	 * Implements ArrayAccess
	 */
	public function offsetUnset($offset) {
		unset($this->options[$offset]);
	}

	/**
	 * Adds support for foreach
	 */
	public function getIterator() {
		return new ArrayIterator($this->options);
	}
}
