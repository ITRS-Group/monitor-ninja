<?php

/**
 * Mock log interface for op5lib Log functionality. It simply accepts log
 * messages and optionally writes them to stdout, otherwise just drop them
 *
 * Useful not to depend on a writable log dir and log configuration
 */
class MockLog extends op5log {
	private $print = false;
	private $messages = array();

	/**
	 * Initialize the MockLog interface
	 *
	 * @param $print Boolean, if messages should be written to stdout
	 */
	public function __construct($print = false) {
		$this->print = $print;
	}

	/**
	 * Log a message
	 *
	 * @param $namespace Namespace to write
	 * @param $level Message level
	 * @param $message Message
	 */
	public function log($namespace, $level, $message) {
		$this->messages[] = array(
			'namespace' => $namespace,
			'level' => $level,
			'message' => $message,
		);
		if($this->print) {
			printf("Log: %s %s %s\n", $namespace, $level, $message);
		}
	}

	/**
	 * @return mixed
	 */
	public function dequeue_message() {
		return array_shift($this->messages);
	}
}
