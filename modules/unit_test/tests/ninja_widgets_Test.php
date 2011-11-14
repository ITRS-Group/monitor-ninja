<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Ninja_widgets_Test extends TapUnit {
	public function test_table_ninja_widgets_table_exists()
	{
		$db = Database::instance();
		$table = 'ninja_widgets';
		$this->ok($db->table_exists($table), "Unable to find table $table");
	}

	/**
	 * check that we have default database settings for all widgets
	 *
	 */
	public function test_widgets_db_settings()
	{
		$db = Database::instance();
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
			if ($widget == 'README')
				continue;
			$sql = "SELECT * FROM ".$table." WHERE username='' AND name = ".$db->escape($widget);
			$result = $db->query($sql);
			if (!count($result) && $widget !== 'geomap')
				$missing[] = $widget;
		}

		$this->ok(empty($missing), 'Missing database settings for '.implode(',',$missing));
	}

}
