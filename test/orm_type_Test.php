<?php

class ORM_Type_Test extends PHPUnit_Framework_TestCase {

	/**
	 * See .../modules/test/src/orm/structure.php for test class structure
	 */
	public function simple_types_provider () {
		return array(
			/* Valid input */
			array("string", "abc", "abc"),
			array("integer", 1, 1),
			array("float", 1.0, 1.0),
			array("boolean", true, true),
			array("list", array(), array()),
			array("dict", array(), array()),

			/* Coerced input */
			array("string", 1, "1"),
			array("string", 1.123, "1.123"),
			array("string", true, "1"),
			array("string", false, ""),
			array("integer", "1", 1),
			array("integer", "01", 1),
			array("integer", "-1", -1),
			array("integer", true, 1),
			array("integer", false, 0),
			array("float", "1.0", 1.0),
			array("float", "1", 1.0),
		);
	}

	/**
	 * @dataProvider simple_types_provider
	 * @group ORMType
	 */
	public function test_simple_types ($field, $value, $expect) {

		$from_array = TestClassA_Model::factory_from_array(array($field => $value), array());
		$from_iterator = TestClassA_Model::factory_from_setiterator(array($field => $value), false, array());

		$getter = "get_$field";
		$this->assertSame($expect, $from_array->$getter());
		$this->assertSame($expect, $from_iterator->$getter());

	}

	/**
	 * Tests setting values that are coerced between types that the ORM currently depends on.
	 *
	 * @dataProvider simple_types_provider
	 * @group ORMType
	 */
	public function test_simple_types_setter_getter ($field, $value, $expect) {
		$setter = "set_$field";
		$getter = "get_$field";
		$instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$instance->$setter($value);
		$this->assertSame($expect, $instance->$getter());
	}

	/**
	 * See .../modules/test/src/orm/structure.php for test class structure
	 */
	public function simple_types_invalid_provider () {
		return array(
			/* Valid input */
			array("string", array(), "'array' is not valid for string 'string'"),
			array("string", (object)array(), "'object' is not valid for string 'string'"),
			array("integer", "abcdef", "'string' is not valid for integer 'integer'"),
			array("integer", array(), "'array' is not valid for integer 'integer'"),
			array("integer", (object)array(), "'object' is not valid for integer 'integer'"),
			array("boolean", array(), "'array' is not valid for bool 'boolean'"),
			array("boolean", (object)array(), "'object' is not valid for bool 'boolean'"),
			array("boolean", "true", "'string' is not valid for bool 'boolean'"),
			array("boolean", "", "'string' is not valid for bool 'boolean'"),
		);
	}

	/**
	 * Tests setting values that cannot be /reasonably/ coerced between types.
	 * Any attempt to set these should throw InvalidArgumentException's
	 *
	 * @dataProvider simple_types_invalid_provider
	 * @group ORMType
	 */
	public function test_simple_types_invalid_array ($field, $value, $expect) {
		$this->setExpectedException('InvalidArgumentException', $expect);
		$from_array = TestClassA_Model::factory_from_array(array($field => $value), array());
	}

	/**
	 * Tests setting values that cannot be /reasonably/ coerced between types.
	 * Any attempt to set these should throw InvalidArgumentException's
	 *
	 * @dataProvider simple_types_invalid_provider
	 * @group ORMType
	 */
	public function test_simple_types_invalid_setiterator ($field, $value, $expect) {
		$this->setExpectedException('InvalidArgumentException', $expect);
		$from_iterator = TestClassA_Model::factory_from_setiterator(array($field => $value), false, array());
	}

	/**
	 * Tests setting values that cannot be /reasonably/ coerced between types.
	 * Any attempt to set these should throw InvalidArgumentException's
	 *
	 * @dataProvider simple_types_invalid_provider
	 * @group ORMType
	 */
	public function test_simple_types_invalid_setter ($field, $value, $expect) {
		$setter = "set_$field";
		$this->setExpectedException('InvalidArgumentException', $expect);
		$instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$instance->$setter($value);
	}

}
