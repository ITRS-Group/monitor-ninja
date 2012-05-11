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
class Translation_Test extends TapUnit {
	public function test_translations_exists()
	{
		$default = new Default_Controller();
		$this->ok(is_object($default->translate), "Default translations exists");
		unset($default);
	}
}
