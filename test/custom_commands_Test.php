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
		$auth->force_user( new User_Model( array (
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
		$host = Host_Model::factory_from_setiterator( array (
			'name' => 'stuff',
			'custom_variables' => array ()
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}

	public function test_no_access() {
		$host = Host_Model::factory_from_setiterator( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => 'i_dont_exist'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}

	public function test_no_access_through_user() {
		$host = Host_Model::factory_from_setiterator( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => 'greger'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}

	public function test_access_single_contactgroup() {
		$host = Host_Model::factory_from_setiterator( array (
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
		$host = Host_Model::factory_from_setiterator( array (
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
		$host = Host_Model::factory_from_setiterator( array (
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
		$host = Host_Model::factory_from_setiterator( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => ''
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}

	public function test_no_access_empty_groups() {
		$host = Host_Model::factory_from_setiterator( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5X__ACTION__SOMECOMMAND' => 'do stuff',
				'OP5X__ACCESS__SOMECOMMAND' => ',,,'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}
	public function test_multi_commands() {
		$host = Host_Model::factory_from_setiterator( array (
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
		$host = Host_Model::factory_from_setiterator( array (
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
		$host = Host_Model::factory_from_setiterator( array (
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
		$host = Host_Model::factory_from_setiterator( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5__BOLL__ACTION__STUFF' => 'do stuff',
				'OP5__BOLL__ACCESS__STUFF' => 'greger_and_his_friends'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}

	public function test_no_access_attr() {
		$host = Host_Model::factory_from_setiterator( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5H__ACTION__SOMECOMMAND' => 'do stuff'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}

	public function test_no_action_attr() {
		$host = Host_Model::factory_from_setiterator( array (
			'name' => 'stuff',
			'custom_variables' => array (
				'OP5__ACCESS__SOMECOMMAND' => 'greger_and_his_friends'
			)
		), '', array () );

		$this->assertEquals( array (), $host->list_custom_commands() );
	}

	public function custom_variables_provider() {
		return array(
			// name of custom variable, publicly visible?
			array("OP5H_", false),
			array("OP5H_ello", false),

			array("OP5H", true),
			array("OP5Hello", true),
			array("OP5h_ello", true),
			array("Something entirely different", true),
		);
	}

	/**
	 * @dataProvider custom_variables_provider
	 */
	public function test_visibility_of_custom_variable($custom_variable, $is_public) {
		$this->assertSame($is_public, custom_variable::is_public($custom_variable));
	}

	public function objects_with_hidden_custom_varible_provider() {
		$actually_public_custom_variables = array(
			"IS_AUSTRALIA_DANGEROUS" => "yes"
		);

		// let us use the ACTION/ACCESS couple used for "dynamic buttons"
		$custom_variables = array_merge(
			$actually_public_custom_variables,
			array(
				"OP5H__ACTION__SOMECOMMAND" => "tickle_crocodile.sh",
				"OP5H__ACCESS__SOMECOMMAND" => "tickle_crocodile.sh",
			)
		);

		$host = Host_Model::factory_from_setiterator(array(
			"name" => "black_widow",
			"custom_variables" => $custom_variables
		), "", array());
		$service = Service_Model::factory_from_setiterator(array(
			"host" => array(
				"name" => "Steve",
			),
			"description" => "Irwin",
			"custom_variables" => $custom_variables
		), "", array());

		return array(
			array($host, $actually_public_custom_variables),
			array($service, $actually_public_custom_variables),
		);
	}

	/**
	 * @dataProvider objects_with_hidden_custom_varible_provider
	 */
	public function test_custom_variables_that_should_be_hidden_are_not_public(Object_Model $object, $expected) {
		$this->assertSame(
			$expected,
			$object->get_public_custom_variables(),
			"Some custom variables that should have been hidden, ".
			"are public. This exposes potentially harmful data and ".
			"should be considered a security issue."
		);
	}

}
