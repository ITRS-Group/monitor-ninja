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
		$this->assertEquals('5k', $string);
	}

	public function test_million_prefixing () {
		$string = text::clipped_number(5361030);
		$this->assertEquals('5M', $string);
	}

	public function test_million_truncate_prefixing () {
		$string = text::clipped_number(5521030);
		$this->assertEquals('5M', $string);
	}

	public function test_decimals () {
		$string = text::clipped_number(5361030, 3);
		$this->assertEquals('5.361M', $string);
	}

	public function test_negative_number () {
		$string = text::clipped_number(-1321, 3);
		$this->assertEquals('-1.321m', $string);
	}

	public function test_low_negative_number () {
		$string = text::clipped_number(-123256322345, 3);
		$this->assertEquals('-123.256n', $string);
	}

	public function test_php_int_max () {
		$string = text::clipped_number(PHP_INT_MAX, 3);
		$this->assertEquals('9.223E', $string);
	}

	public function test_invalid_divisor () {
		$this->setExpectedException('InvalidArgumentException');
		$string = text::clipped_number(PHP_INT_MAX, 3, 0);
	}

}
