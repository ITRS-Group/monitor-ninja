<?php

require('install_scripts/migrate_tac_hostperf_to_listview.php');

/**
 * Migrate Tac Hostperf Test
 *
 * @package    Unit_Test
 * @author     op5
 */
class Migrate_Tac_Hostperf_Test extends \PHPUnit\Framework\TestCase {

	public function setUp () : void {

		op5auth::instance()->force_user(new User_AlwaysAuth_Model());
		$this->mock_data(array(
            'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'name' => 'dummy',
						'id' => 1
					)
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'name' => 'tac_hostperf',
						'dashboard_id' => 1,
						'setting' => '{}'
					),
					array(
						'id' => 2,
						'name' => 'tac_hostperf',
						'dashboard_id' => 1,
						'setting' => '{"host_name":"Obaeron"}'
					),
					array(
						'id' => 3,
						'name' => 'tac_hostperf',
						'dashboard_id' => 1,
						'setting' => '{"host_name":"Fellion","hidden":"Burp;Zek"}'
					)
				)
			),
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Intini'
					),
					array(
						'name' => 'Obaeron'
					),
					array(
						'name' => 'Fellion'
					)

				),
				'services' => array(
					array(
						'description' => 'Servicon',
						'host' => 'Intini'
					),
					array(
						'description' => 'Betazoid',
						'host' => 'Obaeron'
					),
					array(
						'description' => 'Deltar',
						'host' => 'Fellion'
					),
					array(
						'description' => 'Burp',
						'host' => 'Fellion'
					),
					array(
						'description' => 'Zek',
						'host' => 'Fellion'
					)
				)
            )
		));

	}

    private function mock_data($tables) {
        foreach($tables as $driver => $tables) {
            op5objstore::instance()->mock_add(
                $driver,
                new ORMDriverNative($tables, null, $driver)
            );
        }
    }

	public function test_migration_tac_hostperf_are_migrated () {

		$all = Dashboard_WidgetPool_Model::all();

		$listviews = $all->reduce_by('name', 'listview', '=');
		$hostperfs = $all->reduce_by('name', 'tac_hostperf', '=');

		$this->assertEquals(3, $hostperfs->count());
		$this->assertEquals(0, $listviews->count());

		migrate_tac_hostperf_widgets();

		$listviews = $all->reduce_by('name', 'listview', '=');
		$hostperfs = $all->reduce_by('name', 'tac_hostperf', '=');

		$this->assertEquals(0, $hostperfs->count());
		$this->assertEquals(3, $listviews->count());

	}

	public function test_migration_tac_hostperf_becomes_listview_widget () {

		migrate_tac_hostperf_widgets();

		$widget = Dashboard_WidgetPool_Model::all()->reduce_by('name', 'listview', '=')->one();
		$this->assertInstanceOf('Dashboard_Widget_Model', $widget);

	}

	public function test_migration_tac_hostperf_gets_first_host_if_no_setting () {

		migrate_tac_hostperf_widgets();

		$widget = Dashboard_WidgetPool_Model::all()->reduce_by('name', 'listview', '=')->one();
		$setting = $widget->get_setting();
		$service = ObjectPool_Model::get_by_query($setting['query'])->one();
		$this->assertEquals("Intini", $service->get_host()->get_name());

	}

	public function test_migration_tac_hostperf_with_host_setting () {

		migrate_tac_hostperf_widgets();

		$widget = Dashboard_WidgetPool_Model::all()->reduce_by('name', 'listview', '=')->reduce_by('id', 2, '=')->one();
		$setting = $widget->get_setting();
		$service = ObjectPool_Model::get_by_query($setting['query'])->one();
		$this->assertEquals("Obaeron", $service->get_host()->get_name());

	}

	public function test_migration_tac_hostperf_with_hidden_setting_converted_to_filter_only_contains_one_service () {

		migrate_tac_hostperf_widgets();

		$widget = Dashboard_WidgetPool_Model::all()->reduce_by('name', 'listview', '=')->reduce_by('id', 3, '=')->one();
		$setting = $widget->get_setting();

		$services = ObjectPool_Model::get_by_query($setting['query']);
		$this->assertEquals(1, $services->count());
		$this->assertEquals("Deltar", $services->one()->get_description());

	}

	public function test_migration_tac_hostperf_with_no_hosts_in_system_and_no_setting_host_becomes_nonematching_service_filter () {

		$this->mock_data(array(
            'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'name' => 'dummy',
						'id' => 1
					)
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'name' => 'tac_hostperf',
						'dashboard_id' => 1,
						'setting' => '{}'
					)
				)
			),
			'ORMDriverLS default' => array(
				'hosts' => array(
				),
				'services' => array(
				)
            )
		));

		migrate_tac_hostperf_widgets();

		$widget = Dashboard_WidgetPool_Model::all()->reduce_by('name', 'listview', '=')->one();
		$setting = $widget->get_setting();
		$this->assertEquals('[services] description=""', $setting['query']);
		$services = ObjectPool_Model::get_by_query($setting['query']);
		$this->assertEquals(0, $services->count());

	}

}
