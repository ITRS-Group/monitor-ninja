<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Failing controller. Used to provoke an ORMDriverException.
 */
class Failing_Controller extends Ninja_Controller {

	/**
	 * Placeholder for the pre-controller hook
	 */
	public function hook () {
		/* noop, just a placeholder for the pre_controller hook */
	}

	/**
	* Run report tests
	*/
	public function orm_exception() {
		throw new ORMDriverException(
			'This exception is to test the handling of ORMDriverExceptions'
		);
	}
}
