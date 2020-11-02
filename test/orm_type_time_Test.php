<?php

class ORM_Type_Time_Test extends PHPUnit_Framework_TestCase {

	public function valid_values_provider () {
		return array(
			array(123, 123),
			array("123", 123),
		);
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_factory_from_array ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_array = TestClassA_Model::factory_from_array(array("time" => $value), array());
		$this->assertSame($expect, $from_array->get_time());
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_factory_from_setiterator ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_iterator = TestClassA_Model::factory_from_setiterator(array("time" => $value), false, array());
		$this->assertSame($expect, $from_iterator->get_time());
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_using_setter ($value, $expect) {
		$set_instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$set_instance->set_time($value);
		$this->assertSame($expect, $set_instance->get_time());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_from_array_set_not_existing () {
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$this->assertSame(0, $from_array->get_time());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_from_setiterator_set_not_existing () {
		$from_iterator = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$this->assertSame(0, $from_iterator->get_time());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_setter_set_not_existing () {
		$set_instance = TestClassA_Model::factory_from_array(array(), array());
		$set_instance->set_set(TestClassBPool_Model::none());
		$this->assertSame(0, $set_instance->get_time());
	}

	public function invalid_data_provider () {
		return array(
			array("foobar", "'string' is not valid for time 'time'"),
			array(array(), "'array' is not valid for time 'time'"),
			array((object)array(), "'object' is not valid for time 'time'"),
		);
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_factory_from_array_invalid_values ($value, $expected) {
		$this->setExpectedException('InvalidArgumentException', $expected);
		TestClassA_Model::factory_from_array(array("time" => $value), array());
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_factory_from_setiterator_invalid_values ($value, $expected) {
		$this->setExpectedException('InvalidArgumentException', $expected);
		TestClassA_Model::factory_from_setiterator(array("time" => $value), false, array());
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_setter_invalid_values ($value, $expected) {
		$this->setExpectedException('InvalidArgumentException', $expected);
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$from_array->set_time($value);
	}

}
