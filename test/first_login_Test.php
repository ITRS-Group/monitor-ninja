<?php
/**
 * Example Test.
 *
 * $Id$
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class First_Login_Test extends \PHPUnit\Framework\TestCase {

	private $mock_data_path = false;
	protected function setUp () : void {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(),
				'dashboard_widgets' => array(),
				'settings' => array()
			)
		));
	}

	private function mock_data($tables) {
		$this->mock_data_path = __DIR__ . '/' . $this->toString() . '.json';
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add(
				$driver,
				new ORMDriverNative($tables, $this->mock_data_path, $driver)
			);
		}
	}

	public function test_first_login_sets_login_time () {

		$user = new User_Model();
		$user->set_username('Voltron');

		op5auth::instance()->force_user($user);

		/* Should in turn run ninja.first_login as this is the first login for
		 * this user */
		Event::run('ninja.logged_in');

		/* Login time should after this event be higher than 0 */
		$this->assertGreaterThan(0, $user->get_last_login_time());
		$this->assertTrue($user->has_logged_in());

	}

	public function test_first_login_creates_default_dashboard () {

		$user = new User_Model();
		$user->set_username('Voltron');

		op5auth::instance()->force_user($user);

		/* Should in turn run ninja.first_login as this is the first login for
		 * this user */
		Event::run('ninja.logged_in');

		/* The first login event should have created a new dashboard for
		 * Voltron */
		$dashboard = DashboardPool_Model::all()->reduce_by('username', 'Voltron', '=')->one();
		$this->assertInstanceOf('Dashboard_Model', $dashboard);

		/* Should have recieved the correct amount of proper widgets from the
		 * default dashboard configuration */
		$widgets = $dashboard->get_dashboard_widgets_set();
		$this->assertGreaterThan(0, $widgets->count());

	}

	public function test_another_login_does_not_create_dashboard () {

		$user = new User_Model();
		$user->set_username('Voltron');

		op5auth::instance()->force_user($user);

		$login_time = new Setting_Model();
		$login_time->set_username('Voltron');
		$login_time->set_type('login_time');
		$login_time->set_setting(time());
		$login_time->save();

		/* Should NOT run ninja.first_login since the user Voltron has a
			* previous login time */
		Event::run('ninja.logged_in');

		/* This login should not create a dashboard for Voltron */
		$dashboard = DashboardPool_Model::all()->reduce_by('username', 'Voltron', '=')->one();
		$this->assertThat(
			$dashboard,
			$this->logicalOr(
				$this->isNull(),
				$this->isFalse()
			)
		);

	}

}
