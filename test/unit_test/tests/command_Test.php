<?php
require_once('op5/auth/Auth.php');

class MockUser extends Op5User {
	public function authorized_for_object($type, $object_definition, $case_sensitivity = true) {
		return $this->fields['authorized_for'][$type][$object_definition];
	}
}

class Command_Test extends PHPUnit_Framework_TestCase {
	# some common and/or "interesting" commands of each relevant type
	# didn't grep for them, instead copied from
	# http://old.nagios.org/developerinfo/externalcommands/commandlist.php
	var $host_commands = array(
		'PROCESS_HOST_CHECK_RESULT',
		'SEND_CUSTOM_HOST_NOTIFICATION',
		'ACKNOWLEDGE_HOST_PROBLEM',
		'DEL_HOST_COMMENT',
		'SCHEDULE_HOST_CHECK',
		'SCHEDULE_HOST_SVC_DOWNTIME',
		'SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME',
	);

	var $service_commands = array(
		'DISABLE_SERVICE_FLAP_DETECTION',
		'DISABLE_SVC_FLAP_DETECTION', #wait, what? two ways to type this? jay :/
		'PROCESS_SERVICE_CHECK_RESULT',
		'ADD_SVC_COMMENT',
		'SCHEDULE_SVC_CHECK',
		'SCHEDULE_SVC_DOWNTIME',
		'SEND_CUSTOM_SVC_NOTIFICATION',
	);

	var $hostgroup_commands = array(
		'DISABLE_HOSTGROUP_SVC_CHECKS',
	);

	var $servicegroup_commands = array(
		'ENABLE_SERVICEGROUP_HOST_CHECKS',
	);

	var $system_commands = array(
		'RESTART_PROGRAM',
		'PROCESS_FILE',
		'DISABLE_FLAP_DETECTION',
	);

	var $authorized_for = array(
		'host' => array('host1', 'host2'),
		'service' => array('host3;service1', 'host4;service2'),
		'hostgroup' => array('hostgroup1', 'hostgroup2'),
		'servicegroup' => array('servicegroup1', 'servicegroup2'),
	);

	var $types = array('host', 'service', 'hostgroup', 'servicegroup', 'system');

	public function test_get_command_type() {
		foreach ($this->types as $type) {
			foreach ($this->{$type.'_commands'} as $cmd) {
				$this->assertEquals($type, nagioscmd::get_command_type($cmd), "$cmd should be a $type command");
			}
		}
	}

	public function test_authorization_all() {
		foreach ($this->types as $type_to_check) {
			if ($type_to_check == 'system')
				$right = 'system_commands';
			else
				$right = $type_to_check.'_edit_all';
			$user = new Op5User(array('username' => 'testuser', 'auth_data' => array($right => true)));
			Op5Auth::factory(array('session_key' => false))->force_user($user, true);
			foreach ($this->types as $type) {
				foreach ($this->{$type.'_commands'} as $cmd) {
					if ($type == $type_to_check)
						$this->assertEquals(true, nagioscmd::is_authorized_for(false, $cmd), "$type_to_check edit all should authorize you for $cmd");
					else
						$this->assertTrue(nagioscmd::is_authorized_for(false, $cmd) !== true, "$type_to_check edit all should not authorize you for $cmd");
				}
			}
		}
	}

	public function test_authorization_by_contact_for_others() {
		foreach ($this->types as $type_to_check) {
			if ($type_to_check == 'system')
				continue;

			$user = new MockUser(array('username' => 'testuser', 'auth_data' => array($type_to_check.'_edit_contact' => true), 'authorized_for' => $this->authorized_for));
			Op5Auth::factory(array('session_key' => false))->force_user($user, true);
			foreach ($this->types as $type) {
				if ($type == $type_to_check)
					continue;
				foreach ($this->{$type.'_commands'} as $cmd) {
					$this->assertTrue(nagioscmd::is_authorized_for(false, $cmd) !== true, "$type_to_check edit contact should not authorize you for $cmd");
				}
			}
		}
	}

	public function test_authorization_regularcontacts() {
		foreach (array('host', 'hostgroup', 'servicegroup') as $type_to_check) {
			$user = new MockUser(array('username' => 'testuser', 'auth_data' => array($type_to_check.'_edit_contact' => true), 'authorized_for' => $this->authorized_for));
			Op5Auth::factory(array('session_key' => false))->force_user($user, true);
			foreach ($this->{$type_to_check.'_commands'} as $cmd) {
				$this->assertEquals(true, nagioscmd::is_authorized_for(false, $cmd), "$type_to_check edit contact should authorize you when not mentioning which object");
				$this->assertEquals(true, nagioscmd::is_authorized_for(array($type_to_check.'_name' => $type_to_check.'1'), $cmd), "{$type_to_check} edit contact should authorize you for one {$type_to_check} by contact");
				$this->assertEquals(true, nagioscmd::is_authorized_for(array($type_to_check.'_name' => array($type_to_check.'1', $type_to_check.'2')), $cmd), "{$type_to_check} edit contact should authorize you for array of {$type_to_check}s by contact");
				$this->assertEquals(true, nagioscmd::is_authorized_for(array($type_to_check.'_name' => array($type_to_check.'1'), 'service' => 'flubb'), $cmd), "Including service-lulz in {$type_to_check} command should still auth you");

				$this->assertTrue(nagioscmd::is_authorized_for(array($type_to_check.'_name' => $type_to_check.'3'), $cmd) !== true, "{$type_to_check} edit contact shouldn't authorize you for a {$type_to_check} you're not authorized for");
				$this->assertTrue(nagioscmd::is_authorized_for(array($type_to_check.'_name' => array($type_to_check.'1', $type_to_check.'3')), $cmd) !== true, "{$type_to_check} edit contact shouldn't authorize you for array of {$type_to_check}s where you're only contact for some");
			}
			$this->assertEquals(true, nagioscmd::is_authorized_for(array($type_to_check.'_name' => array($type_to_check.'1'))), "Sending a {$type_to_check} name and no command should auth you");
			$this->assertTrue(nagioscmd::is_authorized_for(array($type_to_check.'_name' => array($type_to_check.'3'))) !== true, "Sending a {$type_to_check} name you're not authed for and no command shouldn't auth you");
			$this->assertTrue(nagioscmd::is_authorized_for(array($type_to_check.'_name' => array($type_to_check.'3'), 'service' => 'service1')) !== true, "Including service-lulz when you're not mentioning which {$type_to_check} command you care about shouldn't auth you");
		}
	}

	public function test_authorization_servicecontacts() {
		$user = new MockUser(array('username' => 'testuser', 'auth_data' => array('service_edit_contact' => true), 'authorized_for' => $this->authorized_for));
		Op5Auth::factory(array('session_key' => false))->force_user($user, true);

		foreach ($this->service_commands as $cmd) {
			$this->assertEquals(true, nagioscmd::is_authorized_for(false, $cmd), "Service edit contact should authorize you when not mentioning which service");
			$this->assertEquals(true, nagioscmd::is_authorized_for(array('service' => 'host3;service1'), $cmd), "Service edit contact should authorize you for one service by contact");
			$this->assertEquals(true, nagioscmd::is_authorized_for(array('host_name' => array('host3;service1', 'host4;service2')), $cmd), "Service edit contact should authorize you for array of services by contact");
			$this->assertEquals(true, nagioscmd::is_authorized_for(array('host_name' => array('host1'), 'service' => 'host3;service1'), $cmd), "Including host-lulz in service command should still auth you");

			$this->assertTrue(nagioscmd::is_authorized_for(array('service' => 'host5;service'), $cmd) !== true, "Service edit contact shouldn't authorize you for a service you're not authorized for");
			$this->assertTrue(nagioscmd::is_authorized_for(array('service' => array('host3;service1', 'host3;service5')), $cmd) !== true, "Service edit contact shouldn't authorize you for array of services where you're only contact for some");
		}
		$this->assertEquals(true, nagioscmd::is_authorized_for(array('service' => array('host3;service1'))), "Sending a service name and no command should auth you");
		$this->assertTrue(nagioscmd::is_authorized_for(array('service' => array('host3;service5'))) !== true, "Sending a service name you're not authed for and no command shouldn't auth you");
	}
}
