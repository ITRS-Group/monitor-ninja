<?php
require_once('op5/objstore.php');

class Orm_Command_Rights_Test extends PHPUnit_Framework_TestCase {

	function setup() {

		$mayi_auth = new mayi_auth_hooks();
		$acl_auth = new user_mayi_authorization();
		$this->user = new User_AlwaysAuth_Model();

		Op5Auth::instance()->force_user($this->user, true);
		op5MayI::instance()->be('user', Op5Auth::instance());
		op5MayI::instance()->act_upon($acl_auth, 10);

	}

	private function set_authorization ($authdata) {
		foreach ($authdata as $point => $bool) {
			$this->user->set_authorized_for($point, $bool);
		}
	}

	function test_host_command_list_without_right_to_delete () {

		$this->set_authorization(array(
			'host_add_delete' => false
		));

		$commands = Host_Model::list_commands_static();
		$this->assertArrayNotHasKey('delete', $commands);

	}

	function test_host_command_list_without_rights () {

		$this->user = new User_NoAuth_Model();
		Op5Auth::instance()->force_user($this->user);

		$commands = Host_Model::list_commands_static();
		$this->assertTrue((count($commands) === 0));

	}

	function test_service_command_list_without_right_to_delete () {

		$this->set_authorization(array(
			'service_add_delete' => false
		));

		$commands = Service_Model::list_commands_static();
		$this->assertArrayNotHasKey('delete', $commands);

	}

	function test_service_command_list_without_rights () {

		$this->user = new User_NoAuth_Model();
		Op5Auth::instance()->force_user($this->user);

		$commands = Service_Model::list_commands_static();
		$this->assertTrue((count($commands) === 0));

	}

	function test_hostgroup_command_list_without_right_to_delete () {

        $this->set_authorization(array(
            'hostgroup_add_delete' => false
        ));

        $commands = HostGroup_Model::list_commands_static();
        $this->assertArrayNotHasKey('delete', $commands);

    }

    function test_hostgroup_command_list_without_rights () {

        $this->user = new User_NoAuth_Model();
        Op5Auth::instance()->force_user($this->user);

        $commands = HostGroup_Model::list_commands_static();
        $this->assertTrue((count($commands) === 0));

    }

    function test_servicegroup_command_list_without_right_to_delete () {

        $this->set_authorization(array(
            'servicegroup_add_delete' => false
        ));

        $commands = ServiceGroup_Model::list_commands_static();
        $this->assertArrayNotHasKey('delete', $commands);

    }

    function test_servicegroup_command_list_without_rights () {

        $this->user = new User_NoAuth_Model();
        Op5Auth::instance()->force_user($this->user);

        $commands = ServiceGroup_Model::list_commands_static();
        $this->assertTrue((count($commands) === 0));

    }

}
