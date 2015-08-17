<?php
require_once ('op5/objstore.php');
class custom_commands_Test extends PHPUnit_Framework_TestCase {

	/**
	 * The virtual environment this test is using
	 *
	 * @var array
	 */
	protected $objects = array (
		'contactgroups' => array (
			array (
				'name' => 'greger_and_his_friends',
				'members' => array (
					'greger'
				)
			)
		),
		'contacts' => array (
			array (
				'name' => 'greger',
				'groups' => array (
					'greger_and_his_friends'
				)
			)
		)
	);
	public function setup() {
		op5objstore::instance()->mock_clear();

		$auth = op5auth::instance( array (
			'session_key' => false
		) );
		$auth->force_user( new op5User( array (
			'username' => 'greger',
			'groups' => array (
				'greger_and_his_group'
			)
		) ), false );

		$this->ls = new MockLivestatus( $this->objects, array (
			'allow_undefined_columns' => true
		) );
		op5objstore::instance()->mock_add( 'op5Livestatus', $this->ls );
	}
	public function teardown() {
		op5objstore::instance()->mock_clear();
	}
	public function test_no_commands() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array ()
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}
	public function test_no_access() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => 'i_dont_exist'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}
	public function test_no_access_through_user() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => 'greger'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}
	public function test_access_single_contactgroup() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => 'greger_and_his_friends'
			)
		), '', array () );

		$this->assertEquals( array (
			'somecommand' => 'do stuff'
		), $host->list_custom_commands() );
	}
	public function test_access_multi_contactgroup_first() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => 'greger_and_his_friends,nogroup'
			)
		), '', array () );

		$this->assertEquals( array (
			'somecommand' => 'do stuff'
		), $host->list_custom_commands() );
	}
	public function test_access_multi_contactgroup_last() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => 'nogroup,greger_and_his_friends'
			)
		), '', array () );

		$this->assertEquals( array (
			'somecommand' => 'do stuff'
		), $host->list_custom_commands() );
	}
	public function test_no_access_empty_access() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => ''
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}
	public function test_no_access_empty_groups() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => ',,,'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}
	public function test_multi_commands() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__CMD1' => 'do stuff 1',
				'OP5X__ACCESS__CMD1' => 'noaccess',
				'OP5X__ACTION__CMD2' => 'do stuff 2',
				'OP5X__ACCESS__CMD2' => 'greger_and_his_friends',
				'OP5X__ACTION__CMD3' => 'do stuff 3',
				'OP5X__ACCESS__CMD3' => 'somegroup,greger_and_his_friends',
				'OP5X__ACTION__CMD4' => 'do stuff 4',
				'OP5X__ACCESS__CMD4' => 'greger_and_his_friends,anothergroup'
			)
		), '', array () );

		$this->assertEquals( array (
			'cmd2' => 'do stuff 2',
			'cmd3' => 'do stuff 3',
			'cmd4' => 'do stuff 4'
		), $host->list_custom_commands() );
	}
	public function test_noprefix() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5__ACCESS__SOMECOMMAND' => 'greger_and_his_friends'
			)
		), '', array () );

		$this->assertEquals( array (
			'somecommand' => 'do stuff'
		), $host->list_custom_commands() );
	}
	public function test_hidden_noprefix() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5H__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5H__ACCESS__SOMECOMMAND' => 'greger_and_his_friends'
			)
		), '', array () );

		$this->assertEquals( array (
			'somecommand' => 'do stuff'
		), $host->list_custom_commands() );
	}
	public function test_incorrect_order() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5__BOLL__ACTION__STUFF' => 'do stuff',
				'OP5__BOLL__ACCESS__STUFF' => 'greger_and_his_friends'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}
	public function test_no_access_attr() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5H__ACTION__SOMECOMMAND' => 'do stuff'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}
	public function test_no_action_attr() {
		$host = new Host_Model( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5__ACCESS__SOMECOMMAND' => 'greger_and_his_friends'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}
}
