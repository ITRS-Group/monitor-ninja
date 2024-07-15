<?php
use PHPUnit\Framework\Attributes\Group;

class ORM_Type_Bool_Test extends \PHPUnit\Framework\TestCase {

	public static function valid_values_provider () {
		return array(
			array(true, true),
			array(false, false),
		);
	}


	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_factory_from_array ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_array = TestClassA_Model::factory_from_array(array("bool" => $value), array());
		$this->assertSame($expect, $from_array->get_bool());
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_factory_from_setiterator ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_iterator = TestClassA_Model::factory_from_setiterator(array("bool" => $value), false, array());
		$this->assertSame($expect, $from_iterator->get_bool());
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_using_setter ($value, $expect) {
		$set_instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$set_instance->set_bool($value);
		$this->assertSame($expect, $set_instance->get_bool());
	}

	#[Group('ORMType')]
	public function test_factory_from_array_set_not_existing () {
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$this->assertSame(false, $from_array->get_bool());
	}

	#[Group('ORMType')]
	public function test_factory_from_setiterator_set_not_existing () {
		$from_iterator = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$this->assertSame(false, $from_iterator->get_bool());
	}

	#[Group('ORMType')]
	public function test_factory_setter_set_not_existing () {
		$set_instance = TestClassA_Model::factory_from_array(array(), array());
		$set_instance->set_set(TestClassBPool_Model::none());
		$this->assertSame(false, $set_instance->get_bool());
	}

	/**
	 * The factories for the ORMTypeSet accept queries (strings) that
	 * resolve as the fields set ORM Model OR the set model.
	 */
	public static function invalid_data_provider () {
		return array(
			array("foobar", "'string' is not valid for bool 'bool'"),
			array(1.1, "'double' is not valid for bool 'bool'"), # double because PHP gettype is stupid
			array(array(), "'array' is not valid for bool 'bool'"),
			array((object)array(), "'object' is not valid for bool 'bool'"),
		);
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_factory_from_array_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_array(array("bool" => $value), array());
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_factory_from_setiterator_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_setiterator(array("bool" => $value), false, array());
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_setter_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$from_array->set_bool($value);
	}

}
