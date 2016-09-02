<?php
/**
 * HTML Helper Test.
 *
 * @package    Unit_Test
 * @author     op5
 */
class HTMLHelper_Test extends PHPUnit_Framework_TestCase {

	public function test_get_delimited_string_no_item () {
		$this->assertEquals("", html::get_delimited_string(array()));
	}

	public function test_get_delimited_string_one_item () {
		$this->assertEquals("&#039;hej&#039;", html::get_delimited_string(array('hej')));
	}

	public function test_get_delimited_string_two_items () {
		$this->assertEquals("&#039;hej&#039; and &#039;foo&#039;", html::get_delimited_string(array('hej', 'foo')));
	}

	public function test_get_delimited_string_three_items () {
		$this->assertEquals("&#039;hej&#039;, &#039;zoo&#039; and &#039;foo&#039;", html::get_delimited_string(array('hej', 'zoo', 'foo')));
	}

	public function test_get_delimited_string_more_items () {
		$this->assertEquals("&#039;hej&#039;, &#039;zoo&#039;, &#039;bar&#039;, &#039;yo&#039; and &#039;foo&#039;", html::get_delimited_string(array('hej', 'zoo', 'bar', 'yo', 'foo')));
	}

}
