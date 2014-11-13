<?php

require_once "op5/auth/AuthDriver_Header.php";
require_once "op5/objstore.php";

class AuthDriverHeaderTest extends PHPUnit_Framework_TestCase
{
	static function setUpBeforeClass() {
		op5objstore::instance()->mock_add( 'op5log', new MockLog() );
	}

	static function tearDownAfterClass() {
		op5objstore::instance()->clear();
		op5objstore::instance()->mock_clear();
	}

	public function test_only_username() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'Test-Username'
				));
		$drv->test_mock_headers(array(
				'Test-Username' => 'testuser'
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username validity");
		$this->assertEquals($user->realname, 'testuser', "Realname validity");
		$this->assertEquals($user->email, '', "Email validity");
		$this->assertEquals($user->groups, array(), "Groups validity");
	}

	public function test_realname() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'Test-Username',
				'header_realname' => 'Test-Realname'
				));
		$drv->test_mock_headers(array(
				'Test-Username' => 'testuser',
				'Test-Realname' => 'Test User',
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username validity");
		$this->assertEquals($user->realname, 'Test User', "Realname validity");
		$this->assertEquals($user->email, '', "Email validity");
		$this->assertEquals($user->groups, array(), "Groups validity");
	}

	public function test_realname_not_specified() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'Test-Username',
				'header_realname' => 'Test-Realname'
				));
		$drv->test_mock_headers(array(
				'Test-Username' => 'testuser',
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username validity");
		$this->assertEquals($user->realname, 'testuser', "Realname validity"); /* User username */
		$this->assertEquals($user->email, '', "Email validity");
		$this->assertEquals($user->groups, array(), "Groups validity");
	}

	public function test_email() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'Test-Username',
				'header_email' => 'Test-Email'
				));
		$drv->test_mock_headers(array(
				'Test-Username' => 'testuser',
				'Test-Email' => 'test@example.org',
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username validity");
		$this->assertEquals($user->realname, 'testuser', "Realname validity");
		$this->assertEquals($user->email, 'test@example.org', "Email validity");
		$this->assertEquals($user->groups, array(), "Groups validity");
	}

	public function test_email_not_specified() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'Test-Username',
				'header_email' => 'Test-Email'
				));
		$drv->test_mock_headers(array(
				'Test-Username' => 'testuser',
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username validity");
		$this->assertEquals($user->realname, 'testuser', "Realname validity"); /* User username */
		$this->assertEquals($user->email, '', "Email validity");
		$this->assertEquals($user->groups, array(), "Groups validity");
	}

	public function test_groups_not_specified() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'Test-Username',
				'header_groups' => 'Test-Groups'
				));
		$drv->test_mock_headers(array(
				'Test-Username' => 'testuser'
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username validity");
		$this->assertEquals($user->realname, 'testuser', "Realname validity"); /* User username */
		$this->assertEquals($user->email, '', "Email validity");
		$this->assertEquals($user->groups, array(), "Groups validity");
	}

	public function test_groups_default_delimiter() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'Test-Username',
				'header_groups' => 'Test-Groups',
				));
		$drv->test_mock_headers(array(
				'Test-Username' => 'testuser',
				'Test-Groups' => 'grpa,grpb'
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username validity");
		$this->assertEquals($user->realname, 'testuser', "Realname validity"); /* User username */
		$this->assertEquals($user->email, '', "Email validity");
		$this->assertEquals($user->groups, array('grpa', 'grpb'), "Groups validity");
	}

	public function test_groups_explicit_delimiter() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'Test-Username',
				'header_groups' => 'Test-Groups',
				'group_list_delimiter' => ','
				));
		$drv->test_mock_headers(array(
				'Test-Username' => 'testuser',
				'Test-Groups' => 'grpa,grpb'
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username validity");
		$this->assertEquals($user->realname, 'testuser', "Realname validity"); /* User username */
		$this->assertEquals($user->email, '', "Email validity");
		$this->assertEquals($user->groups, array('grpa', 'grpb'), "Groups validity");
	}

	public function test_groups_explicit_delimiter_trim() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'Test-Username',
				'header_groups' => 'Test-Groups',
				'group_list_delimiter' => ','
				));
		$drv->test_mock_headers(array(
				'Test-Username' => 'testuser',
				'Test-Groups' => 'grpa, grpb'
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username validity");
		$this->assertEquals($user->realname, 'testuser', "Realname validity"); /* User username */
		$this->assertEquals($user->email, '', "Email validity");
		$this->assertEquals($user->groups, array('grpa', 'grpb'), "Groups validity");
	}

	public function test_groups_empty_list() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'Test-Username',
				'header_groups' => 'Test-Groups',
				'group_list_delimiter' => ','
				));
		$drv->test_mock_headers(array(
				'Test-Username' => 'testuser',
				'Test-Groups' => ''
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username validity");
		$this->assertEquals($user->realname, 'testuser', "Realname validity"); /* User username */
		$this->assertEquals($user->email, '', "Email validity");
		$this->assertEquals($user->groups, array(), "Groups validity");
	}

	public function test_case_sensititivty() {
		$drv = new op5AuthDriver_Header(array(
				'header_username' => 'tEST-UserNAMe'
				));
		$drv->test_mock_headers(array(
				'tEsT-USerNamE' => 'testuser'
				));
		$user = $drv->auto_login();

		$this->assertEquals($user->username, 'testuser', "Username vailidity");
		$this->assertEquals($user->realname, 'testuser', "Realname vailidity");
		$this->assertEquals($user->email, '', "Email validity");
		$this->assertEquals($user->groups, array(), "Groups validity");
	}

	public function test_no_header_username_header() {
		$drv = new op5AuthDriver_Header(array(
			'header_username' => 'ååå(€',
			'header_email' => 'äää°%',
			'header_realname' => 'ööö#$',
		));

		$user = $drv->auto_login();
		$this->assertEquals($user, false, "You can't login if you don't provide any headers");

		$drv->test_mock_headers(array());
		$user = $drv->auto_login();
		$this->assertEquals($user, false, "You can't login if you don't provide any headers");

		$drv->test_mock_headers(array('äää°%' => 'blaha@pippi.com', 'ööö#$' => 'Pippi Longstocking'));
		$user = $drv->auto_login();
		$this->assertEquals($user, false, "You can't login if you don't provide header_username");
	}
}
