<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Unit_Test controller.
 */
class Unit_test_Controller extends Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function index()
	{
		// Run tests and show results!
		echo new Unit_Test;
	}
}
