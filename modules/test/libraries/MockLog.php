<?php

/**
 * Mock log interface for op5lib Log functionality. It simply accepts log
 * messages and optionally writes them to stdout, otherwise just drop them
 *
 * Useful not to depend on a writable log dir and log configuration
 */
class MockLog {
	private $print = false;

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
		/* TODO: Do something about this stuff, so we can verify it */
		if($this->print) {
			printf("Log: %s %s %s\n", $namespace, $level, $message);
		}
	}
}
