<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Failing controller. Used to provoke an ORMDriverException.
 */
class Failing_Controller extends Ninja_Controller {
	/**
	* Run report tests
	*/
	public function orm_exception()
	{
		count(FailingPool_Model::all());
	}
}
