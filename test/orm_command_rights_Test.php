<?php
require_once('op5/objstore.php');

class Orm_Command_Rights_Test extends PHPUnit_Framework_TestCase {

	protected function setup() {
		op5objstore::instance()->mock_clear();
		$mayi_auth = new mayi_auth_hooks();
		$this->user = new User_AlwaysAuth_Model();

		$auth = new op5auth(array(
			'common' => array (
				'default_auth' => 'mydefault',
				'session_key' => 'testkey'
			),
			'mydefault' => array (
				'driver' => 'Default'
			)
		));

		op5objstore::instance()->mock_add('Op5Auth', $auth);
		$auth->force_user($this->user);
		$mayi = new op5MayI();

		$mayi->be('user', $auth);
		$mayi->act_upon(new user_mayi_authorization(), 10);
		op5objstore::instance()->mock_add('op5MayI', $mayi);
	}

	protected function tearDown() {
		op5objstore::instance()->mock_clear();
	}

	private function set_authorization ($authdata) {
		foreach ($authdata as $point => $bool) {
			$this->user->set_authorized_for($point, $bool);
		}
	}

	public function test_host_command_list_without_right_to_delete () {

		$this->set_authorization(array(
			'host_add_delete' => false
		));

		$commands = Host_Model::list_commands_static();
		$this->assertArrayNotHasKey('delete', $commands);

	}

	public function test_host_command_list_without_rights () {

		$this->user = new User_NoAuth_Model();
		Op5Auth::instance()->force_user($this->user);

		$commands = Host_Model::list_commands_static();
		$this->assertTrue((count($commands) === 0));

	}

	public function test_service_command_list_without_right_to_delete () {

		$this->set_authorization(array(
			'service_add_delete' => false
		));

		$commands = Service_Model::list_commands_static();
		$this->assertArrayNotHasKey('delete', $commands);

	}

	public function test_service_command_list_without_rights () {

		$this->user = new User_NoAuth_Model();
		Op5Auth::instance()->force_user($this->user);

		$commands = Service_Model::list_commands_static();
		$this->assertTrue((count($commands) === 0));

	}

	public function test_hostgroup_command_list_without_right_to_delete () {

		$this->set_authorization(array(
			'hostgroup_add_delete' => false
		));

		$commands = HostGroup_Model::list_commands_static();
		$this->assertArrayNotHasKey('delete', $commands);

	}

	public function test_hostgroup_command_list_without_rights () {

		$this->user = new User_NoAuth_Model();
		Op5Auth::instance()->force_user($this->user);

		$commands = HostGroup_Model::list_commands_static();
		$this->assertTrue((count($commands) === 0));

	}

	public function test_servicegroup_command_list_without_right_to_delete () {

		$this->set_authorization(array(
			'servicegroup_add_delete' => false
		));

		$commands = ServiceGroup_Model::list_commands_static();
		$this->assertArrayNotHasKey('delete', $commands);

	}

	public function test_servicegroup_command_list_without_rights () {

		$this->user = new User_NoAuth_Model();
		Op5Auth::instance()->force_user($this->user);

		$commands = ServiceGroup_Model::list_commands_static();
		$this->assertTrue((count($commands) === 0));

	}

	/**
	 * @group MON-9413
	 */
	public function test_user_allowed_to_cancel_downtime_if_allowed_to_edit_hosts () {
		$user = new User_Model(array(
			'username' => 'a_forced_user',
			'realname' => 'This is forced',
			'email' => 'i_do_have@n.email',
			'auth_data' => array (
				'host_edit_all' => true,
			),
		));

		Op5Auth::instance()->force_user($user);
		op5MayI::instance()->be('user', Op5Auth::instance());

		$acl_auth = new user_mayi_authorization();
		op5MayI::instance()->act_upon($acl_auth, 10);

		$commands = Downtime_Model::list_commands_static();

		$this->assertArrayHasKey('delete', $commands);
	}

	/**
	 * @group MON-9413
	 */
	public function test_user_allowed_to_delete_comment_if_allowed_to_edit_hosts () {
		$user = new User_Model(array(
			'username' => 'a_forced_user',
			'realname' => 'This is forced',
			'email' => 'i_do_have@n.email',
			'auth_data' => array (
				'host_edit_all' => true,
			),
		));

		Op5Auth::instance()->force_user($user);
		op5MayI::instance()->be('user', Op5Auth::instance());

		$acl_auth = new user_mayi_authorization();
		op5MayI::instance()->act_upon($acl_auth, 10);

		$commands = Comment_Model::list_commands_static();

		$this->assertArrayHasKey('delete', $commands);
	}

}
