<?php defined('SYSPATH') OR die('No direct access allowed.');
Auth::instance()->force_login('monitor');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Ninja_widgets_Test extends TapUnit {
	public function setUp() {
		$this->orig_widgets = Ninja_widget_Model::fetch_all('tac/index');
		$this->ok(is_array($this->orig_widgets), "Fetch widgets returns an array");
		$this->ok(!empty($this->orig_widgets), "Fetch widgets returns widgets");
		foreach ($this->orig_widgets as $w)
			$this->ok($w !== false, 'No returned widgets should be false');
		$this->ok_id(Ninja_widget_Model::get('tac/index', 'foobar', 3), false, "There shouldn't be a foobar widget with instance_id 3");
		$this->ok_id(Ninja_widget_Model::get('tac/index', 'foobar'), false, "There shouldn't be a foobar widget at all");
		Ninja_widget_Model::install('tac/index', 'foobar', 'Foo Bar');
	}
	public function tearDown() {
		$db = Database::instance();
		foreach ($db->query('SELECT username, page, name, instance_id, count(1) as count FROM ninja_widgets GROUP BY username, page, name, instance_id') as $ary)
			$this->ok_eq($ary->count, 1, "There is 1 page=$ary->page,name=$ary->name,instance_id=$ary->instance_id,username=$ary->username");
		$this->ok(Ninja_widget_Model::uninstall('foobar'), "Widget was uninstalled");
		$new_widgets = Ninja_widget_Model::fetch_all('tac/index');
		foreach ($new_widgets as $w)
			$this->ok($w !== false, 'No returned widgets should be false');
		$this->ok(is_array($new_widgets), "Fetch widgets returns an array");
		$this->ok(!empty($new_widgets), "Fetch widgets returns widgets");
		$this->ok(count($new_widgets) === count($this->orig_widgets), 'The new widget is gone');
		foreach ($db->query('SELECT username, page, name, instance_id, count(1) as count FROM ninja_widgets GROUP BY username, page, name, instance_id') as $ary)
			$this->ok_eq($ary->count, 1, "There is 1 page=$ary->page,name=$ary->name,instance_id=$ary->instance_id,username=$ary->username");
	}

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
		$excluded = array(
			'error', // Internal implementation detail, should never be visible in widget listings
			'geomap' // In ninja, not in monitor - it's relationship status is It's Complicated
		);
		$db = Database::instance();
		$table = 'ninja_widgets';
		$widget_list = array();
		$missing = array();

		# finnd all widgets and put in widget_list array
		if ($handle = opendir(APPPATH.'widgets/')) {
			while (false !== ($file = readdir($handle))) {
				if (is_dir(APPPATH.'widgets/'.$file) && $file != "." && $file != "..") {
					$widget_list[] = $file;

				}
			}
			closedir($handle);
		}

		# check custom_widgets and assign to widget_list if not already done
		if ($handle = opendir(APPPATH.'custom_widgets/')) {
			while (false !== ($file = readdir($handle))) {
				if (is_dir(APPPATH.'widgets/'.$file) && $file != "." && $file != ".." && !in_array($file, $widget_list)) {
					$widget_list[] = $file;

				}
			}
			closedir($handle);
		}

		foreach ($widget_list as $widget) {
			$sql = "SELECT * FROM ".$table." WHERE username IS NULL AND name = ".$db->escape($widget);
			$result = $db->query($sql);
			if (!count($result) && !in_array($widget, $excluded))
				$missing[] = $widget;
		}

		$this->ok(empty($missing), 'Missing database settings for '.implode(',',$missing));
	}

	function test_get_widget() {
		$widget = Ninja_widget_Model::get('tac/index', 'foobar');
		$this->ok($widget !== false, 'Create new widget is successful');
		$this->ok_id(Ninja_widget_Model::get('tac/index', 'foobar', 3), false, "There still isn't a foobar widget with instance_id 3");
		$this->ok_id($widget->page, 'tac/index', 'New page has the given page name');
		$new_widgets = Ninja_widget_Model::fetch_all('tac/index');
		foreach ($new_widgets as $w)
				$this->ok($w !== false, 'No returned widgets should be false');
		$this->ok(is_array($new_widgets), "Fetch widgets returns an array");
		$this->ok(!empty($new_widgets), "Fetch widgets isn't empty");
		$this->ok(count($new_widgets) === count($this->orig_widgets) + 1, 'There should be 1 new widget, there is ' + count($new_widgets) - count($this->orig_widgets));
	}

	function test_copy_widget() {
		$widget = Ninja_widget_Model::get('tac/index', 'foobar');
		$this->ok($widget !== false, 'Create new widget is successful');
		$dup_widget = $widget->copy();
		$this->ok($dup_widget !== false, 'Copy widget is successful');
		$this->ok($dup_widget->instance_id == $widget->instance_id + 1, 'New widget has bumped instance id');
		$new_widgets = Ninja_widget_Model::fetch_all('tac/index');
		$this->ok(count($new_widgets) === count($this->orig_widgets) + 2, 'Both copies are fetched on fetch');
		$dup2 = $dup_widget->copy();
		$this->ok($dup2 !== false, 'Copy copied widget is successful');
		$this->ok($dup2->instance_id == $dup_widget->instance_id + 1, 'New widget has bumped instance id');
		$dup3 = $widget->copy();
		$this->ok($dup3 !== false, 'Copy original widget without highest instance_id is successful');
		$this->ok($dup3->instance_id == $dup2->instance_id + 1, 'New widget has bumped instance id');
		$dup3->delete();
		$dup2->delete();
		$dup_widget->delete();
		$new_widgets = Ninja_widget_Model::fetch_all('tac/index');
		$this->ok(count($new_widgets) === count($this->orig_widgets) + 1, 'After deleting copied widgets, only the original remains');
		$widget->delete();
		$new_widgets = Ninja_widget_Model::fetch_all('tac/index');
		$this->ok(count($new_widgets) === count($this->orig_widgets) + 1, "Last widget copy can't be deleted");
	}

	function test_edit_legacy() {
		# there were no instance_ids in the past
		$db = Database::instance();
		$db->query("INSERT INTO ninja_widgets (name, username, page, instance_id) VALUES ('foobar2', 'monitor', 'tac/index', null)");
		$widgets = Ninja_widget_Model::fetch_all('tac/index');
		$foobar = false;
		foreach ($widgets as $widget) {
			if ($widget->name === 'foobar2') {
				$foobar = $widget;
				break;
			}
		}
		$this->ok($foobar !== false, 'Foobar2 widget found');
		$this->ok_id($foobar->instance_id, 1, 'Instance ID is set to a number upon retrieval');
		$foobar->friendly_name='w00t';
		$foobar->save();
		$res = $db->query("SELECT count(1) AS count FROM ninja_widgets WHERE name='foobar2'");
		$this->ok_eq($res->current()->count, 2, 'There should be 2 foobar2 widgets in DB after write, there are '.$res->current()->count);
		$this->ok(Ninja_widget_Model::uninstall('foobar2'), "Widget can be uninstalled");
	}

	function test_settings() {
		$widget = Ninja_widget_Model::get('tac/index', 'foobar');
		$widget->merge_settings(array('foo' => 'bar', 'baz' => 3));
		$dupe = $widget->copy();
		$widget->save();
		$saved_widget = Ninja_widget_Model::get('tac/index', 'foobar', $widget->instance_id);
		$this->ok(!empty($saved_widget->setting), 'There are settings saved');
		$this->ok_id($widget->setting, $saved_widget->setting, 'The settings are identical');
		$saved_dupe = Ninja_widget_Model::get('tac/index', 'foobar', $dupe->instance_id);
		$this->ok_empty($saved_dupe->setting, 'There are no settings propagated to dupes');
	}

	function test_widget_helper() {
		$ws = Ninja_widget_Model::fetch_all('tac/index');
		foreach ($ws as $name => $widget_obj) {
			$this->ok_id($name, 'widget-'.$widget_obj->name.'-'.$widget_obj->instance_id, 'Not all widgets have correct array indexes');
		}
		$order = Ninja_widget_Model::fetch_widget_order('tac/index');
		$order['test_placeholder'] = array('widget-foobar-1');
		Ninja_widget_Model::set_widget_order('tac/index', $order);
		$widgets = widget::add_widgets('tac/index', $ws, $this);
		$this->ok(isset($widgets['test_placeholder']), 'The new placeholder exists');
		$this->ok(isset($widgets['test_placeholder']['widget-foobar-1']), 'Our widget is saved in our placeholder');
		$foobar = Ninja_widget_Model::get('tac/index', 'foobar', 1);
		$foobar2 = $foobar->copy();
		$ws = Ninja_widget_Model::fetch_all('tac/index');
		$widgets = widget::add_widgets('tac/index', $ws, $this);
		$this->ok(isset($widgets['test_placeholder']), 'The new placeholder exists');
		$this->ok(isset($widgets['test_placeholder']['widget-foobar-1']), 'Our old widget is saved in our placeholder');
		$this->ok(isset($widgets['test_placeholder']['widget-foobar-2']), 'Our new widget is saved in the same placeholder as it\'s original');
	}
}
