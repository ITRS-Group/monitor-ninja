<?php

/**
 * An exception in relation to a Notice
 */
class NoticeException extends Exception {}

/**
 * An immutable representation of a Notice
 */
abstract class Notice_Model {

	/**
	 * The message of the notice
	 */
	private $message = "";

	/**
	 * Constructs the notice
	 *
	 * @throws NoticeException
	 * @param $message string The message the notice contains
	 */
	function __construct ($message) {
		if (!is_string($message)) {
			$type = gettype($message);
			if($type == 'object') {
				$type = get_class($type);
			}
			throw new NoticeException(sprintf("A notice must be a string, '%s' given", $type));
		}
		$this->message = $message;
	}

	/**
	 * Gets the notice message as a string
	 *
	 * @return string
	 */
	function get_message() {
		return $this->message;
	}

	/**
	 * Get the rendereable typename as a string
	 * @return string
	 */
	abstract function get_typename();
}
