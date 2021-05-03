<?php

class ORM_Type_Int_Test extends \PHPUnit\Framework\TestCase {

	public function valid_values_provider () {
		return array(
			array(123, 123),
			array(1.53, 1), // will floor floats
			array("41", 41), // allows and casts strings matching pattern ^-?\d+$
		);
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_factory_from_array ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_array = TestClassA_Model::factory_from_array(array("int" => $value), array());
		$this->assertSame($expect, $from_array->get_int());
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_factory_from_setiterator ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_iterator = TestClassA_Model::factory_from_setiterator(array("int" => $value), false, array());
		$this->assertSame($expect, $from_iterator->get_int());
	}

	/**
	 * @dataProvider valid_values_provider
	 * @group ORMType
	 */
	public function test_using_setter ($value, $expect) {
		$set_instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$set_instance->set_int($value);
		$this->assertSame($expect, $set_instance->get_int());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_from_array_set_not_existing () {
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$this->assertSame(0, $from_array->get_int());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_from_setiterator_set_not_existing () {
		$from_iterator = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$this->assertSame(0, $from_iterator->get_int());
	}

	/**
	 * @group ORMType
	 */
	public function test_factory_setter_set_not_existing () {
		$set_instance = TestClassA_Model::factory_from_array(array(), array());
		$set_instance->set_set(TestClassBPool_Model::none());
		$this->assertSame(0, $set_instance->get_int());
	}

	/**
	 * The factories for the ORMTypeSet accept queries (strings) that
	 * resolve as the fields set ORM Model OR the set model.
	 */
	public function invalid_data_provider () {
		return array(
			array("foobar", "'string' is not valid for int 'int'"),
			array(array(), "'array' is not valid for int 'int'"),
			array((object)array(), "'object' is not valid for int 'int'"),
		);
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_factory_from_array_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_array(array("int" => $value), array());
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_factory_from_setiterator_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_setiterator(array("int" => $value), false, array());
	}

	/**
	 * @dataProvider invalid_data_provider
	 * @group ORMType
	 */
	public function test_setter_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$from_array->set_int($value);
	}

}
