<?php
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

class ORM_Type_Relation_Test extends \PHPUnit\Framework\TestCase {

	public function setUp () : void {
		op5objstore::instance()->mock_add(
			"ORMDriverMySQL default",
			new ORMDriverNative(array(
				"test_class_b" => array(
					array("string" => "a")
				)
			), null, "ORMDriverMySQL default")
		);
	}

	public function valid_values_provider () {
		return array(
			array('a', 'TestClassB_Model'),
			array(TestClassB_Model::factory_from_array(array(), array()), 'TestClassB_Model'),
		);
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_factory_from_array_relation_existing ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_array = TestClassA_Model::factory_from_array(array("relation" => $value), array());
		$this->assertInstanceOf($expect, $from_array->get_relation());
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_factory_from_setiterator_relation_existing ($value, $expect) {
		$from_iterator = TestClassA_Model::factory_from_setiterator(array("relation" => $value), false, array());
		$this->assertInstanceOf($expect, $from_iterator->get_relation());
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_using_setter_relation_existing ($value, $expect) {
		$set_instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$set_instance->set_relation($value);
		$this->assertInstanceOf($expect, $set_instance->get_relation());
	}

	#[Group('ORMType')]
	public function test_factory_from_array_relation_not_existing () {
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$this->assertNull($from_array->get_relation());
	}

	#[Group('ORMType')]
	public function test_factory_from_setiterator_relation_not_existing () {
		$from_iterator = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$this->assertNull($from_iterator->get_relation());
	}

	#[Group('ORMType')]
	public function test_factory_setter_relation_not_existing () {
		$set_instance = TestClassA_Model::factory_from_array(array(), array());
		$set_instance->set_set(TestClassBPool_Model::none());
		$this->assertNull($set_instance->get_relation());
	}

	/**
	 * The factories for the ORMTypeSet accept queries (strings) that
	 * resolve as the fields set ORM Model OR the set model.
	 */
	public static function invalid_data_provider () {
		return array(
			array(1, "'integer' is not valid for relation 'relation'"),
			array(1.1, "'double' is not valid for relation 'relation'"), # double because PHP gettype is stupid
			array(true, "'boolean' is not valid for relation 'relation'"),
			array(array(), "'array' is not valid for relation 'relation'"),
			array((object)array(), "'object' is not valid for relation 'relation'"),
		);
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_factory_from_array_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_array(array("relation" => $value), array());
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_factory_from_setiterator_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_setiterator(array("relation" => $value), false, array());
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_setter_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$from_array->set_relation($value);
	}

}
