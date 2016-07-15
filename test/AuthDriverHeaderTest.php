<?php

require_once "op5/auth/AuthDriver_Header.php";
require_once "op5/objstore.php";

class AuthDriverHeaderTest extends PHPUnit_Framework_TestCase
{
	private $driver;

	static function setUpBeforeClass() {
		op5objstore::instance()->mock_add( 'op5log', new MockLog() );
	}

	static function tearDownAfterClass() {
		op5objstore::instance()->clear();
		op5objstore::instance()->mock_clear();
	}

	public function get_driver ($properties) {
		$module = new AuthModule_Model();
		$module->set_modulename('HeaderAuth');
		$module->set_properties($properties);
		$driver = new op5AuthDriver_Header($module);
		return $driver;
	}

	public function test_only_username() {

		$driver = $this->get_driver(array(
			'header_username' => 'Test-Username'
		));

		$driver->test_mock_headers(array(
			'Test-Username' => 'testuser'
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username validity");
		$this->assertEquals($user->get_realname(), 'testuser', "Realname validity");
		$this->assertEquals($user->get_email(), '', "Email validity");
		$this->assertEquals($user->get_groups(), array(), "Groups validity");

	}

	public function test_realname() {

		$driver = $this->get_driver(array(
			'header_username' => 'Test-Username',
			'header_realname' => 'Test-Realname'
		));

		$driver->test_mock_headers(array(
			'Test-Username' => 'testuser',
			'Test-Realname' => 'Test User',
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username validity");
		$this->assertEquals($user->get_realname(), 'Test User', "Realname validity");
		$this->assertEquals($user->get_email(), '', "Email validity");
		$this->assertEquals($user->get_groups(), array(), "Groups validity");

	}

	public function test_realname_not_specified() {

		$driver = $this->get_driver(array(
			'header_username' => 'Test-Username',
			'header_realname' => 'Test-Realname'
		));

		$driver->test_mock_headers(array(
			'Test-Username' => 'testuser'
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username validity");
		$this->assertEquals($user->get_realname(), 'testuser', "Realname validity");
		$this->assertEquals($user->get_email(), '', "Email validity");
		$this->assertEquals($user->get_groups(), array(), "Groups validity");
	}

	public function test_email() {

		$driver = $this->get_driver(array(
			'header_username' => 'Test-Username',
			'header_email' => 'Test-Email'
		));

		$driver->test_mock_headers(array(
			'Test-Username' => 'testuser',
			'Test-Email' => 'test@example.org',
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username validity");
		$this->assertEquals($user->get_realname(), 'testuser', "Realname validity");
		$this->assertEquals($user->get_email(), 'test@example.org', "Email validity");
		$this->assertEquals($user->get_groups(), array(), "Groups validity");
	}

	public function test_email_not_specified() {

		$driver = $this->get_driver(array(
			'header_username' => 'Test-Username',
			'header_email' => 'Test-Email'
		));

		$driver->test_mock_headers(array(
			'Test-Username' => 'testuser',
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username validity");
		$this->assertEquals($user->get_realname(), 'testuser', "Realname validity");
		$this->assertEquals($user->get_email(), '', "Email validity");
		$this->assertEquals($user->get_groups(), array(), "Groups validity");
	}

	public function test_groups_not_specified() {
		$driver = $this->get_driver(array(
			'header_username' => 'Test-Username',
			'header_groups' => 'Test-Groups'
		));

		$driver->test_mock_headers(array(
			'Test-Username' => 'testuser'
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username validity");
		$this->assertEquals($user->get_realname(), 'testuser', "Realname validity");
		$this->assertEquals($user->get_email(), '', "Email validity");
		$this->assertEquals($user->get_groups(), array(), "Groups validity");
	}

	public function test_groups_default_delimiter() {
		$driver = $this->get_driver(array(
			'header_username' => 'Test-Username',
			'header_groups' => 'Test-Groups',
		));

		$driver->test_mock_headers(array(
			'Test-Username' => 'testuser',
			'Test-Groups' => 'grpa,grpb'
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username validity");
		$this->assertEquals($user->get_realname(), 'testuser', "Realname validity");
		$this->assertEquals($user->get_email(), '', "Email validity");
		$this->assertEquals($user->get_groups(), array('grpa', 'grpb'), "Groups validity");
	}

	public function test_groups_explicit_delimiter() {
		$driver = $this->get_driver(array(
			'header_username' => 'Test-Username',
			'header_groups' => 'Test-Groups',
			'group_list_delimiter' => ','
		));

		$driver->test_mock_headers(array(
			'Test-Username' => 'testuser',
			'Test-Groups' => 'grpa,grpb'
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username validity");
		$this->assertEquals($user->get_realname(), 'testuser', "Realname validity");
		$this->assertEquals($user->get_email(), '', "Email validity");
		$this->assertEquals($user->get_groups(), array('grpa', 'grpb'), "Groups validity");
	}

	public function test_groups_explicit_delimiter_trim() {
		$driver = $this->get_driver(array(
			'header_username' => 'Test-Username',
			'header_groups' => 'Test-Groups',
			'group_list_delimiter' => ','
		));

		$driver->test_mock_headers(array(
			'Test-Username' => 'testuser',
			'Test-Groups' => 'grpa, grpb'
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username validity");
		$this->assertEquals($user->get_realname(), 'testuser', "Realname validity");
		$this->assertEquals($user->get_email(), '', "Email validity");
		$this->assertEquals($user->get_groups(), array('grpa', 'grpb'), "Groups validity");
	}

	public function test_groups_empty_list() {
		$driver = $this->get_driver(array(
			'header_username' => 'Test-Username',
			'header_groups' => 'Test-Groups',
			'group_list_delimiter' => ','
		));

		$driver->test_mock_headers(array(
			'Test-Username' => 'testuser',
			'Test-Groups' => ''
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username validity");
		$this->assertEquals($user->get_realname(), 'testuser', "Realname validity");
		$this->assertEquals($user->get_email(), '', "Email validity");
		$this->assertEquals($user->get_groups(), array(), "Groups validity");
	}

	public function test_case_sensititivty() {
		$driver = $this->get_driver(array(
			'header_username' => 'tEST-UserNAMe'
		));

		$driver->test_mock_headers(array(
			'tEsT-USerNamE' => 'testuser'
		));

		$user = $driver->auto_login();

		$this->assertInstanceOf('User_Model', $user);
		$this->assertEquals($user->get_username(), 'testuser', "Username vailidity");
		$this->assertEquals($user->get_realname(), 'testuser', "Realname vailidity");
		$this->assertEquals($user->get_email(), '', "Email validity");
		$this->assertEquals($user->get_groups(), array(), "Groups validity");
	}

	public function test_no_header_username_header() {

		$driver = $this->get_driver(array(
			'header_username' => 'ååå(€',
			'header_email' => 'äää°%',
			'header_realname' => 'ööö#$',
		));

		$user = $driver->auto_login();
		$this->assertEquals($user, false, "You can't login if you don't provide any headers");

		$driver->test_mock_headers(array());
		$user = $driver->auto_login();
		$this->assertEquals($user, false, "You can't login if you don't provide any headers");

		$driver->test_mock_headers(array('äää°%' => 'blaha@pippi.com', 'ööö#$' => 'Pippi Longstocking'));
		$user = $driver->auto_login();
		$this->assertEquals($user, false, "You can't login if you don't provide header_username");
	}
}
