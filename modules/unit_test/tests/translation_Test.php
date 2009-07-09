<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Example Test.
 *
 * $Id$
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Translation_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = true;

	public function translations_exists_test()
	{
		$default = new Default_Controller();
		$this->assert_true_strict(is_object($default->translate));
		unset($default);
	}
}