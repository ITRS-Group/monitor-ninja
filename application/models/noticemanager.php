<?php

/**
 * Specialized exception
 */
class NoticeManager_Exception extends Exception {}

/**
 * Models an array of Notice objects
 */
class NoticeManager_Model implements ArrayAccess, IteratorAggregate, Countable {

	/**
	 * The stored notices
	 */
	private $notices = array();

	/**
	 * Enables count($model);
	 */
	public function count() {
		return count($this->notices);
	}

	/**
	 * Implements ArrayAccess
	 */
	public function offsetExists($offset) {
		return isset($this->notices[$offset]);
	}

	/**
	 * Implements ArrayAccess
	 */
	public function offsetGet($offset) {
		if(!$this->offsetExists($offset)) {
			return null;
		}
		return $this->notices[$offset];
	}

	/**
	 * Implements ArrayAccess
	 *
	 * @param $offset Notice_Model
	 * @param $value string
	 */
	public function offsetSet($offset, $value) {
		if(!$value instanceof Notice_Model) {
			$type = gettype($value);
			if($type == 'object') {
				$type = get_class($type);
			}
			throw new NoticeManager_Exception("Invalid argument to NoticeManager_Model, need Notice_Model, got '$type'");
		}
		if($offset == null) {
			$this->notices[] = $value;
		} else {
			$this->notices[$offset] = $value;
		}
	}

	/**
	 * Implements ArrayAccess
	 */
	public function offsetUnset($offset) {
		unset($this->notices[$offset]);
	}

	/**
	 * Implements IteratorAggregate
	 */
	public function getIterator() {
		return new ArrayIterator($this->notices);
	}
}

