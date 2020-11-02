<?php
/**
 * Tests the extinfo (the view of a single object).
 */
class Extinfo_Test extends PHPUnit_Framework_TestCase {
	protected function tearDown() {
		op5objstore::instance()->mock_clear();
	}

	private function mock_data($tables) {
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add(
				$driver,
				new ORMDriverNative($tables, null, $driver)
			);
		}
	}

	public function test_extinfo_show_process_info_has_an_options_menu() {
		$this->mock_data(array(
			'ORMDriverLS default' => array(
				'status' => array(
					// we must have at least one row,
					// representing the status model
					array()
				)
			),
		));

		$controller = new Extinfo_Controller();
		$controller->show_process_info();
		$menus = $controller->template->toolbar->get_menu();
		$this->assertCount(1, $menus,
			"Let's assume that the Options menu is the only one, ".
			"change this value if that no longer is the truth");
		$view = current($menus);
		$this->assertInstanceOf('View', $view);

		$menu_items = $view->menu;
		$this->assertInstanceOf('Menu_Model', $menu_items);

		$start_obsess_cmd = $menu_items->get("options.".
			"host_operations.start_obsessing_over_hosts");
		$this->assertInstanceOf('Menu_Model', $start_obsess_cmd);
		$this->assertContains("/index.php/cmd?command=".
			"start_obsessing_over_hosts&table=status&object=0",
			$start_obsess_cmd->get_href()
		);
	}
}
