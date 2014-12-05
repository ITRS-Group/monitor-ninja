<?php
class json_Test extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider data_structures
	 */
	public function testJson($value) {
		$this->assertEquals($value, json_decode(json::pretty($value), true));
	}

	public function data_structures() {
		return array(
			array(1),
			array("foo"),
			array(false),
			array(array(1, 2, 3, 4)),
			array(array("foo" => "bar", "baz" => 2, "quux")),
			array(array("foo" => array(1,2,3), "bar" => array('1' => 1, 2 => '2', "foo"))),
		);
	}
}