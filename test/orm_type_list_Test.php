<?php

use PHPUnit\Framework\Attributes\DataProvider;

class ORM_Type_List_Test extends \PHPUnit\Framework\TestCase {

	public static function valid_values_provider () {
		return array(
			array(array(), array()),
			// must be able to handle PHP serialized data du to donwtime settings
			array('a:1:{i:0;s:1:"1";}', array("1")),
		);
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_factory_from_array ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_array = TestClassA_Model::factory_from_array(array("list" => $value), array());
		$this->assertSame($expect, $from_array->get_list());
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_factory_from_setiterator ($value, $expect) {
		$set = TestClassBPool_Model::all();
		$from_iterator = TestClassA_Model::factory_from_setiterator(array("list" => $value), false, array());
		$this->assertSame($expect, $from_iterator->get_list());
	}

	#[DataProvider('valid_values_provider')]
	#[Group('ORMType')]
	public function test_using_setter ($value, $expect) {
		$set_instance = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$set_instance->set_list($value);
		$this->assertSame($expect, $set_instance->get_list());
	}

    #[Group('ORMType')]
	public function test_factory_from_array_set_not_existing () {
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$this->assertSame(array(), $from_array->get_list());
	}

    #[Group('ORMType')]
	public function test_factory_from_setiterator_set_not_existing () {
		$from_iterator = TestClassA_Model::factory_from_setiterator(array(), false, array());
		$this->assertSame(array(), $from_iterator->get_list());
	}

    #[Group('ORMType')]
	public function test_factory_setter_set_not_existing () {
		$set_instance = TestClassA_Model::factory_from_array(array(), array());
		$set_instance->set_set(TestClassBPool_Model::none());
		$this->assertSame(array(), $set_instance->get_list());
	}

	/**
	 * The factories for the ORMTypeSet accept queries (strings) that
	 * resolve as the fields set ORM Model OR the set model.
	 */
	public static function invalid_data_provider () {
		return array(
			array("foobar", "'string' is not valid for list 'list'"),
			array(true, "'boolean' is not valid for list 'list'"),
			array(1, "'integer' is not valid for list 'list'"),
			array(1.1, "'double' is not valid for list 'list'"), # double because PHP gettype is stupid
			array((object)array(), "'object' is not valid for list 'list'"),
		);
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_factory_from_array_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_array(array("list" => $value), array());
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_factory_from_setiterator_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		TestClassA_Model::factory_from_setiterator(array("list" => $value), false, array());
	}

	#[DataProvider('invalid_data_provider')]
	#[Group('ORMType')]
	public function test_setter_invalid_values ($value, $expected) {
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage($expected);
		$from_array = TestClassA_Model::factory_from_array(array(), array());
		$from_array->set_list($value);
	}

	public function test_list_values_are_the_same_when_read_as_saved () {

		op5objstore::instance()->mock_clear();
		op5objstore::instance()->mock_add(
			"ORMDriverMySQL default",
			new ORMDriverNative(
				array("test_class_a" => array()),
				null,
				"ORMDriverMySQL default"
			)
		);

		$instance = new TestClassA_Model();
		$instance->set_string("test-object");
		$instance->set_list(array("a", "b", "c"));
		$instance->save();

		$this->assertEquals(1, TestClassAPool_Model::all()->count());
		$sought = TestClassAPool_Model::all()->reduce_by("string", "test-object", "=")->one();
		$this->assertEquals(array("a", "b", "c"), $sought->get_list());

	}

}
