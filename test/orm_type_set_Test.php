<?php

class ORM_Type_Set_Test extends \PHPUnit\Framework\TestCase {

	public function valid_values_provider () {
		return array(
			array(TestClassBPool_Model::all(), 'TestClassBSet_Model'),
			array(TestClassBPool_Model::none(), 'TestClassBSet_Model'),
			array('[test_class_b] all', 'TestClassBSet_Model'),
		);
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_factory_from_array_set_existing ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_array = TestClassA_Model::factory_from_array(array("set" => $value), array());
		$this->assertInstanceOf($expect, $from_array->get_set());
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_factory_from_setiterator_set_existing ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_iterator = TestClassA_Model::factory_from_setiterator(array("set" => $value), false, array());
		$this->assertInstanceOf($expect, $from_iterator->get_set());
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_using_setter_set_existing ($value, $expect) {
		$set_instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$set_instance->set_set($value);
		$this->assertInstanceOf($expect, $set_instance->get_set());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_from_array_set_not_existing () {
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$this->assertInstanceOf('TestClassBSet_Model', $from_array->get_set());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_from_setiterator_set_not_existing () {
		$from_iterator = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$this->assertInstanceOf('TestClassBSet_Model', $from_iterator->get_set());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_setter_set_not_existing () {
		$set_instance = TestClassA_Model::factory_from_array(array(), array());
		$set_instance->set_set(TestClassBPool_Model::none());
		$this->assertInstanceOf('TestClassBSet_Model', $set_instance->get_set());
	}

	/**
	 * The factories for the ORMTypeSet accept queries (strings) that
	 * resolve as the fields set ORM Model OR the set model.
	 */
	public function invalid_data_provider () {
		return array(
			array("foobar", "'string' is not valid for set 'set'"),
			array(1, "'integer' is not valid for set 'set'"),
			array(1.1, "'double' is not valid for set 'set'"), # double because PHP gettype is stupid
			array(true, "'boolean' is not valid for set 'set'"), # double because PHP gettype is stupid
			array(array(), "'array' is not valid for set 'set'"), # double because PHP gettype is stupid
			array((object)array(), "'object' is not valid for set 'set'"), # double because PHP gettype is stupid
		);
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_factory_from_array_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_array(array("set" => $value), array());
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_factory_from_setiterator_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_setiterator(array("set" => $value), false, array());
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_setter_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$from_array->set_set($value);
	}

}
