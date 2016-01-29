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
	 * Handles the case of $notice_manager_model[] = $my_notice;
	 * Huge notice: the $offset does not mean diddly-squat, it will be
	 * overritten and I don't care about it. Don't use it. Why?
	 * Users don't ever want duplicate notices, so we don't store them.
	 *
	 * If we use $notice_manager[] = $info; $notice_manager[] = $warning
	 * with the same message, we keep the message but bump the type to a
	 * warning.
	 *
	 * @param $offset Ignored, don't rely on this
	 * @param $value Notice_Model
	 */
	public function offsetSet($offset, $value) {
		if(!$value instanceof Notice_Model) {
			$type = gettype($value);
			if($type == 'object') {
				$type = get_class($type);
			}
			throw new NoticeManager_Exception("Invalid argument to NoticeManager_Model, need Notice_Model, got '$type'");
		}
		$prioritized_notices = array(
			'error',
			'warning',
			'info',
			'success',
		);
		if(!isset($this->notices[$value->get_message()])) {
			$this->notices[$value->get_message()] = $value;
		}
		$previous_notice = $this->notices[$value->get_message()];
		if(array_search($value->get_typename(), $prioritized_notices)
			< array_search($previous_notice->get_typename(), $prioritized_notices)) {
			$this->notices[$value->get_message()] = $value;
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

