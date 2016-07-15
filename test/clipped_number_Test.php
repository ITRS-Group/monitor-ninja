<?php
/**
 * Test text helper clipped_number method.
 *
 * $Id$
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Clipped_Number_Test extends PHPUnit_Framework_TestCase {

	public function test_hundreds_no_prefixing () {
		$string = text::clipped_number(500);
		$this->assertEquals('500', $string);
	}

	public function test_thousands_prefixing () {
		$string = text::clipped_number(5341);
		$this->assertEquals('5.3k', $string);
	}

	public function test_thousands_round_prefixing () {
		$string = text::clipped_number(5361);
		$this->assertEquals('5.4k', $string);
	}

	public function test_hundred_thousands_prefixing () {
		$string = text::clipped_number(536103);
		$this->assertEquals('536k', $string);
	}

	public function test_million_prefixing () {
		$string = text::clipped_number(5361030);
		$this->assertEquals('5.4M', $string);
	}

}
