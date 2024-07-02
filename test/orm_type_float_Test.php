<?php

class ORM_Type_Float_Test extends \PHPUnit\Framework\TestCase {

	public function valid_values_provider () {
		return array(
			array(1.1, 1.1),
			array("1.1", 1.1),
		);
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_factory_from_array ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_array = TestClassA_Model::factory_from_array(array("float" => $value), array());
		$this->assertSame($expect, $from_array->get_float());
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_factory_from_setiterator ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_iterator = TestClassA_Model::factory_from_setiterator(array("float" => $value), false, array());
		$this->assertSame($expect, $from_iterator->get_float());
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_using_setter ($value, $expect) {
		$set_instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$set_instance->set_float($value);
		$this->assertSame($expect, $set_instance->get_float());
	}

    #[Group('ORMType')]
	public function test_factory_from_array_set_not_existing () {
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$this->assertSame(0.0, $from_array->get_float());
	}

    #[Group('ORMType')]
	public function test_factory_from_setiterator_set_not_existing () {
		$from_iterator = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$this->assertSame(0.0, $from_iterator->get_float());
	}

    #[Group('ORMType')]
	public function test_factory_setter_set_not_existing () {
		$set_instance = TestClassA_Model::factory_from_array(array(), array());
		$set_instance->set_set(TestClassBPool_Model::none());
		$this->assertSame(0.0, $set_instance->get_float());
	}

	/**
	 * The factories for the ORMTypeSet accept queries (strings) that
	 * resolve as the fields set ORM Model OR the set model.
	 */
	public function invalid_data_provider () {
		return array(
			array("foobar", "'string' is not valid for float 'float'"),
			array(array(), "'array' is not valid for float 'float'"),
			array((object)array(), "'object' is not valid for float 'float'"),
		);
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_factory_from_array_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_array(array("float" => $value), array());
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_factory_from_setiterator_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_setiterator(array("float" => $value), false, array());
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_setter_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$from_array->set_float($value);
	}

}
