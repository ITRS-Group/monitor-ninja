<?php

class ORM_Type_Flags_Test extends \PHPUnit\Framework\TestCase {

	public function valid_values_provider () {
		return array(
			array("a,b,c", array("a", "b", "c")),
			array(array("a", "b", "c"), array("a", "b", "c")),
			array(array("a", "b"), array("a", "b")),
			array(array("a"), array("a")),
			array(array("b", "c"), array("b", "c")),
			array(array("c"), array("c")),
			array(array("a", "c"), array("a", "c")),
		);
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_factory_from_array ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_array = TestClassA_Model::factory_from_array(array("flags" => $value), array());
		$this->assertSame($expect, $from_array->get_flags());
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_factory_from_setiterator ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_iterator = TestClassA_Model::factory_from_setiterator(array("flags" => $value), false, array());
		$this->assertSame($expect, $from_iterator->get_flags());
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_using_setter ($value, $expect) {
		$set_instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$set_instance->set_flags($value);
		$this->assertSame($expect, $set_instance->get_flags());
	}

    #[Group('ORMType')]
	public function test_factory_from_array_set_not_existing () {
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$this->assertSame(array(), $from_array->get_flags());
	}

    #[Group('ORMType')]
	public function test_factory_from_setiterator_set_not_existing () {
		$from_iterator = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$this->assertSame(array(), $from_iterator->get_flags());
	}

    #[Group('ORMType')]
	public function test_factory_setter_set_not_existing () {
		$set_instance = TestClassA_Model::factory_from_array(array(), array());
		$set_instance->set_set(TestClassBPool_Model::none());
		$this->assertSame(array(), $set_instance->get_flags());
	}

	/**
	 * The factories for the ORMTypeSet accept queries (strings) that
	 * resolve as the fields set ORM Model OR the set model.
	 */
	public function invalid_data_provider () {
		return array(
			array("foobar", "'string' is not valid for flags 'flags'"),
			array(1, "'integer' is not valid for flags 'flags'"),
			array(1.1, "'double' is not valid for flags 'flags'"), # double because PHP gettype is stupid
			array(true, "'boolean' is not valid for flags 'flags'"),
			array((object)array(), "'object' is not valid for flags 'flags'"),
		);
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_factory_from_array_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_array(array("flags" => $value), array());
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_factory_from_setiterator_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_setiterator(array("flags" => $value), false, array());
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_setter_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$from_array->set_flags($value);
	}

}
