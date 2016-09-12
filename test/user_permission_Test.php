<?php

class User_Permission_Test extends PHPUnit_Framework_TestCase {
	private function mock_data ($tables, $file) {
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add($driver, new ORMDriverNative($tables, null, $driver));
		}
	}

	public function setUp() {
		op5objstore::instance()->mock_clear();
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
		$quark_a = PermissionQuarkPool_Model::build('user', 'boll');
		$quark_b = PermissionQuarkPool_Model::build('user', 'kaka');

		$obj_a = PermissionQuarkPool_Model::fetch_by_key($quark_a);
		$obj_b = PermissionQuarkPool_Model::fetch_by_key($quark_b);

		$this->assertInstanceOf('PermissionQuark_Model', $obj_a);
		$this->assertEquals($obj_a->get_type(), 'user');
		$this->assertEquals($obj_a->get_name(), 'boll');

		$this->assertInstanceOf('PermissionQuark_Model', $obj_b);
		$this->assertEquals($obj_b->get_type(), 'user');
		$this->assertEquals($obj_b->get_name(), 'kaka');
	}

	public function test_user_quark_regexp() {
		/* By defining quarks in mock data, and keep them in order, we can assume exact regexp string */
		$this->mock_data ( array (
			'ORMDriverMySQL default' => array (
				'permission_quarks' => array (
					array(
						'id' => 77,
						'type' => 'user',
						'name' => 'myuser'
					),
					array(
						'id' => 131,
						'type' => 'user',
						'name' => 'anotheruser'
					),
					array(
						'id' => 37,
						'type' => 'group',
						'name' => 'grp_a'
					),
					array(
						'id' => 19,
						'type' => 'group',
						'name' => 'grp_dont_use'
					),
					array(
						'id' => 53,
						'type' => 'group',
						'name' => 'grp_b'
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
		), __FUNCTION__ );
		$user = new User_Model();
		$user->set_username('myuser');
		$user->set_groups(array('grp_a', 'grp_b'));

		/* Verify user quark */
		$quark_user = $user->get_permission_quark();

		$quark_obj = PermissionQuarkPool_Model::fetch_by_key($quark_user);
		$this->assertInstanceOf('PermissionQuark_Model', $quark_obj);
		$this->assertEquals($quark_obj->get_type(), 'user');
		$this->assertEquals($quark_obj->get_name(), 'myuser');

		$this->assertEquals(',(77|37|53),', $user->get_permission_regexp());
	}
}
