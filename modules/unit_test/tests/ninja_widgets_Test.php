<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Ninja_widgets_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public function table_ninja_widgets_table_exists_test()
	{
		$db = new Database();
		$table = 'ninja_widgets';
		$this->assert_true_strict($db->table_exists($table), "Unable to find table $table");
	}

	/**
	 * check that we have default database settings for all widgets
	 *
	 */
	public function widgets_db_settings_test()
	{
		$db = new Database();
		$table = 'ninja_widgets';
		$widget_list = array();
		$missing = array();

		# finnd all widgets and put in widget_list array
		if ($handle = opendir(APPPATH.'widgets/')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					$widget_list[] = $file;

				}
			}
			closedir($handle);
		}

		# check custom_widgets and assign to widget_list if not already done
		if ($handle = opendir(APPPATH.'custom_widgets/')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && !in_array($file, $widget_list)) {
					$widget_list[] = $file;

				}
			}
			closedir($handle);
		}

		foreach ($widget_list as $widget) {
			$sql = "SELECT * FROM ".$table." WHERE user='' AND name = ".$db->escape($widget);
			$result = $db->query($sql);
			if (!count($result))
				$missing[] = $widget;
		}

		$this->assert_true(empty($missing), 'Missing database settings for '.implode(',',$missing));
	}

}