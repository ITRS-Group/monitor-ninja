<?php

class ORM_Type_String_Test extends PHPUnit_Framework_TestCase {

	public function valid_values_provider () {
		return array(
			array("", ""),
			array(1, "1"),
			array(1.1, "1.1"),
			array(true, "1"),
			array(false, ""),
		);
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_factory_from_array ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_array = TestClassA_Model::factory_from_array(array("string" => $value), array());
		$this->assertSame($expect, $from_array->get_string());
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_factory_from_setiterator ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_iterator = TestClassA_Model::factory_from_setiterator(array("string" => $value), false, array());
		$this->assertSame($expect, $from_iterator->get_string());
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_using_setter ($value, $expect) {
		$set_instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$set_instance->set_string($value);
		$this->assertSame($expect, $set_instance->get_string());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_from_array_set_not_existing () {
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$this->assertSame("", $from_array->get_string());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_from_setiterator_set_not_existing () {
		$from_iterator = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$this->assertSame("", $from_iterator->get_string());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_setter_set_not_existing () {
		$set_instance = TestClassA_Model::factory_from_array(array(), array());
		$set_instance->set_set(TestClassBPool_Model::none());
		$this->assertSame("", $set_instance->get_string());
	}

	/**
	 * The factories for the ORMTypeSet accept queries (strings) that
	 * resolve as the fields set ORM Model OR the set model.
	 */
	public function invalid_data_provider () {
		return array(
			array(array(), "'array' is not valid for string 'string'"),
			array((object)array(), "'object' is not valid for string 'string'"),
		);
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_factory_from_array_invalid_values ($value, $expected) {
		$this->setExpectedException('InvalidArgumentException', $expected);
		TestClassA_Model::factory_from_array(array("string" => $value), array());
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_factory_from_setiterator_invalid_values ($value, $expected) {
		$this->setExpectedException('InvalidArgumentException', $expected);
		TestClassA_Model::factory_from_setiterator(array("string" => $value), false, array());
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_setter_invalid_values ($value, $expected) {
		$this->setExpectedException('InvalidArgumentException', $expected);
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$from_array->set_string($value);
	}

}
