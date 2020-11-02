<?php

class User_Permission_Test extends PHPUnit_Framework_TestCase {
	private function mock_data($tables) {
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add($driver, new ORMDriverNative($tables, null, $driver));
		}
	}

	public function setUp() {
		op5objstore::instance()->mock_clear();
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'permission_quarks' => array()
			)
		));
	}

	public function tearDown() {
		op5objstore::instance()->mock_clear();
	}
	public function test_permission_quarks() {
		$quark_a = PermissionQuarkPool_Model::build('user', 'boll');
		$quark_b = PermissionQuarkPool_Model::build('user', 'kaka');
		$quark_c = PermissionQuarkPool_Model::build('group', 'boll');

		$this->assertNotEquals($quark_a, $quark_b);
		$this->assertNotEquals($quark_b, $quark_c);
		$this->assertNotEquals($quark_a, $quark_c);

		$this->assertEquals($quark_a, PermissionQuarkPool_Model::build('user', 'boll'));
		$this->assertEquals($quark_b, PermissionQuarkPool_Model::build('user', 'kaka'));
		$this->assertEquals($quark_c, PermissionQuarkPool_Model::build('group', 'boll'));
	}

	public function test_permission_quark_resolve() {
		$quark_a = PermissionQuarkPool_Model::build('users', 'boll');
		$quark_b = PermissionQuarkPool_Model::build('users', 'kaka');

		$obj_a = PermissionQuarkPool_Model::fetch_by_key($quark_a);
		$obj_b = PermissionQuarkPool_Model::fetch_by_key($quark_b);

		$this->assertInstanceOf('PermissionQuark_Model', $obj_a);
		$this->assertEquals('users', $obj_a->get_foreign_table());
		$this->assertEquals('boll', $obj_a->get_foreign_key());

		$this->assertInstanceOf('PermissionQuark_Model', $obj_b);
		$this->assertEquals('users', $obj_b->get_foreign_table());
		$this->assertEquals('kaka', $obj_b->get_foreign_key());
	}

	public function test_user_quark_regexp() {
		/* By defining quarks in mock data, and keep them in order, we can assume exact regexp string */
		$this->mock_data(array(
			'ORMDriverMySQL default' => array (
				'permission_quarks' => array (
					array(
						'id' => 77,
						'foreign_table' => 'users',
						'foreign_key' => 'myuser'
					),
					array(
						'id' => 131,
						'foreign_table' => 'users',
						'foreign_key' => 'anotheruser'
					),
					array(
						'id' => 37,
						'foreign_table' => 'usergroups',
						'foreign_key' => 'grp_a'
					),
					array(
						'id' => 19,
						'foreign_table' => 'usergroups',
						'foreign_key' => 'grp_dont_use'
					),
					array(
						'id' => 53,
						'foreign_table' => 'usergroups',
						'foreign_key' => 'grp_b'
					)
				)
			),
			'ORMDriverYAML default' => array (
				'users' => array (),
				'usergroups' => array (
					array (
						'groupname' => 'grp_a'
					),
					array (
						'groupname' => 'grp_b'
					)
				)
			)
		));
		$user = new User_Model();
		$user->set_username('myuser');
		$user->set_groups(array('grp_a', 'grp_b'));

		/* Verify user quark */
		$quark_user = $user->get_permission_quark();

		$quark_obj = PermissionQuarkPool_Model::fetch_by_key($quark_user);
		$this->assertInstanceOf('PermissionQuark_Model', $quark_obj);
		$this->assertEquals('users', $quark_obj->get_foreign_table());
		$this->assertEquals('myuser', $quark_obj->get_foreign_key());

		$this->assertEquals(',(77|37|53),', $user->get_permission_regexp());
	}
}
