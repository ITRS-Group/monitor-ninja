<?php
class Dashboard_Permission_Test extends PHPUnit_Framework_TestCase {
	private function mock_data($tables) {
		foreach ( $tables as $driver => $tables ) {
			op5objstore::instance ()->mock_add ( $driver, new ORMDriverNative ( $tables, null, $driver ) );
		}
	}

	public function setUp() {
		op5objstore::instance ()->mock_clear ();
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array( 'id' => 1, 'name' => 'For me', 'username' => 'me', 'read_perm' => ',1,' ),
					array( 'id' => 2, 'name' => 'My no read', 'username' => 'me', 'read_perm' => ',5,' ),
					array( 'id' => 3, 'name' => 'For you', 'username' => 'you', 'read_perm' => ',2,' ),
					array( 'id' => 4, 'name' => 'For us', 'username' => 'me', 'read_perm' => ',1,2,' ),
					array( 'id' => 5, 'name' => 'For noone', 'username' => 'noone', 'read_perm' => ',' ),
					array( 'id' => 6, 'name' => 'For someone', 'username' => 'someone', 'read_perm' => ',3,' ),
					array( 'id' => 7, 'name' => 'For admins', 'username' => 'mr_admin', 'read_perm' => ',4,' )
				),
				'dashboard_widgets' => array(),
				'permission_quarks' => array(
					array( 'id' => 1, 'type' => 'user', 'name' => 'me' ),
					array( 'id' => 2, 'type' => 'user', 'name' => 'you' ),
					array( 'id' => 3, 'type' => 'user', 'name' => 'someone' ),
					array( 'id' => 4, 'type' => 'group', 'name' => 'admins' ),
					array( 'id' => 5, 'type' => 'group', 'name' => 'noone' )
				)
			),
			'ORMDriverYAML default' => array(
				'users' => array(),
				'usergroups' => array(
					array( 'groupname' => 'admins' ),
					array( 'groupname' => 'noone' )
				),
				'authmodules' => array(
					array( 'name' => 'Default', 'properties' => array() )
				)
			)
		));
	}

	public function tearDown() {
		op5objstore::instance ()->mock_clear ();
	}

	/**
	 * assert that we have access to widgets for the given dashboard.
	 *
	 * This needs to create a widget to verify
	 *
	 * @param Dashboard_Model $db
	 */
	private function assertAccessToWidgets( $db ) {
		$old_count = count($db->get_dashboard_widgets_set());
		$widget = new Dashboard_Widget_Model();
		$widget->set_dashboard_id($db->get_id());
		$widget->set_name('netw_health');
		$widget->set_position('here');
		$widget->set_setting(array());
		$widget->save();

		/* Assert that we have access to any widgets. Just created one */
		$this->assertGreaterThan(
			$old_count,
			count($db->get_dashboard_widgets_set()),
			'Failing dashboard for widget test: '.$db->get_name()
		);
	}

	public function provider_read_users() {
		return array(
			array('me', array( 'admins' ), array( 'For me', 'My no read', 'For us', 'For admins' )),
			array('you', array( 'admins' ), array( 'For you', 'For us', 'For admins' )),
			array('someone', array(), array( 'For someone' )),
		);
	}

	/**
	 * @dataProvider provider_read_users
	 */
	public function test_read_permissions($username, $groups, $dashboards) {
		$user = new User_Model ();
		$user->set_username ( $username );
		$user->set_groups ( $groups );
		op5auth::instance ()->force_user ( $user );

		$dbs = array();
		foreach ( DashboardPool_Model::all ()->it ( array( 'name', 'username', 'can_write' ), array( 'id' ) ) as $obj ) {
			$dbs [] = $obj->get_name ();

			/* I whould only have write access if I'm the creator */
			$this->assertEquals($obj->get_username() == $username, $obj->get_can_write());

			/* Test we have access to read widgets */
			$this->assertAccessToWidgets($obj);
		}

		$this->assertEquals ( $dashboards, $dbs );
	}


	/**
	 * Users, for which dashboards the user owns. The groups are irrelevant.
	 */
	public function provider_write_users() {
		return array(
			array( 'me', array( 'admins' ), array( 'For me', 'My no read', 'For us' ) ),
			array( 'you', array( 'admins' ), array( 'For you' ) ),
			array( 'someone', array(), array( 'For someone' ) )
		);
	}

	/**
	 * @dataProvider provider_write_users
	 */
	public function test_write_permissions($username, $groups, $dashboards) {
		$user = new User_Model();
		$user->set_username( $username );
		$user->set_groups( $groups );
		op5auth::instance()->force_user( $user );

		$dbs = array();
		foreach ( DashboardPool_Model::all()->it( array( 'name', 'can_write' ), array( 'id' ) ) as $obj ) {
			if ($obj->get_can_write()) {
				$dbs [] = $obj->get_name();
			}

			/* Test we have access to read widgets */
			$this->assertAccessToWidgets($obj);
		}

		$this->assertEquals( $dashboards, $dbs );
	}
}
