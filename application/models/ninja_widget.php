<?php defined('SYSPATH') OR die('No direct access allowed.');

function sort_widgets_by_friendly_name($a, $b) {
	return strcmp($a->name, $b->name) || strcmp($a->friendly_name, $b->friendly_name);
}

/**
 * A widget consists of four (or, well, five) identifying pieces of information.
 *
 * It has a name. The name links it to the on-disk PHP files.
 *
 * It has a page. This means the same widget can/will look different depending
 * on your URL.
 *
 * It has a user. This means that different users see the same widget
 * differently.
 *
 * It has an instance id. This means that the same user can see the same widget
 * multiple times on the same page, with different options in each.
 *
 * (there's also an ID column, but it's pretty useless)
 *
 * When showing a widget, we first try to find a widget with the same page, user
 * and instance id. If that's not possible, we fall back to the same page and
 * user, but any id we can find, then to the same page but with blank user and
 * id, and then to the 'tac/index' page with a blank user and id. When returning
 * data from this model, nobody should have to bother about whether the widget
 * information was returned from a fallback or not.
 *
 * (If you have a clever, quick, cross-database solution to do this whole thing
 * in-database, please don't hesitate to do it)
 *
 * This means we must do copy-on-read, as we must "claim" a new instance id
 * in case we have multiple browser tabs showing the same thing - keeping the
 * javascript aware of when an instance id is assigned after an hour of showing
 * a widget and then editing it seems doomed to create bugs.
 *
 * Saving an edited widget must never save to any of the fallback options. In
 * particular, for legacy systems, that means we must never write to anything
 * with an empty instance id. FIXME: tested?
 *
 * There are two add/remove pairs of functions: install/uninstall, and
 * copy/delete. The first pair works globally, the second per-user.
 */

class Ninja_widget_Model extends Model
{
	public $name;
	public $page;
	public $instance_id;
	public $username;
	public $setting;
	/**
	 * You should not call this constructor directly!
	 *
	 * What you're looking for is probably get.
	 */
	function __construct($db_row) {
		parent::__construct();
		$db_row['setting'] = i18n::unserialize(trim($db_row['setting']));
		if (isset($db_row['setting']['widget_title']))
			$db_row['friendly_name'] = $db_row['setting']['widget_title'];
		$this->db_row = $db_row;
		foreach ($db_row as $keys => $vals) {
			$this->$keys = $vals;
		}
	}

	/**
	 * Fetches a list of widget names for a given page
	 * @returns array of Ninja_widget_Model objects
	 */
	public static function fetch_all($page)
	{
		if (empty($page))
			return array();

		$user = Auth::instance()->get_user()->username;
		$db = Database::instance();

		# warning: cleverness!
		# sort any rows with non-NULL instance_id first, so we can ignore the
		# generic widget rows for widgets that have "personalized" rows
		$res = $db->query('SELECT name, instance_id FROM ninja_widgets WHERE page='.$db->escape($page).' OR page=\'tac/index\' GROUP BY name, instance_id ORDER BY instance_id DESC');
		$widgets = array();
		$seen_widgets = array();
		foreach ($res as $row) {
			if ($row->instance_id === null && isset($seen_widgets[$row->name]))
				continue;
			$seen_widgets[$row->name] = 1;
			$widget = self::get($page, $row->name, $row->instance_id);
			assert('$widget !== false');
			$widgets['widget-'.$widget->name.'-'.$widget->instance_id] = $widget;
		}
		uasort($widgets, 'sort_widgets_by_friendly_name');
		return $widgets;
	}

	/**
	* Fetch info on a saved widget for a page
	*
	* @param $page The page the widget is to be shown at
	* @param $widget The widget name to retrieve
	* @param $instance_id The instance_id to retrieve. Sending in NULL will create a new widget
	* @returns A widget object, or false if none could be found matching the input
	*
	*/
	public static function get($page, $widget, $instance_id=null)
	{
		$user = Auth::instance()->get_user();
		if (!empty($user))
			$user = $user->username;
		else
			$user = false;

		$db = Database::instance();

		if (is_numeric($instance_id))
			$subquery = 'page='.$db->escape($page).' AND username = '.$db->escape($user).($instance_id===false?'':' AND instance_id = '.$db->escape($instance_id));
		else
			$subquery = '(page='.$db->escape($page).' OR page=\'tac/index\') AND (username IS NULL OR username='.$db->escape($user).' AND instance_id IS NULL)';

		$result = $db->query('SELECT * FROM ninja_widgets WHERE name='.$db->escape($widget).' AND '.$subquery.' LIMIT 1');

		if (!count($result))
			return false;

		$obj = new Ninja_widget_Model($result->result(false)->current());
		if ($instance_id !== null && $obj->instance_id === null) {
			// we were asked for a specific widget, but it could not be found
			return false;
		}
		else if ($user && $obj->instance_id === null) {
			// we were asked for a widget with "any" instance_id, so create one
			// and re-fetch it to get correct ID
			// if we don't have a user, that should mean we're being called from CLI
			// scripts, so don't do anything.
			$instance_id = $obj->instance_id = 1;
			// just in case...
			$obj->username = $user;
			$obj->page = $page;
			unset($obj->id);
			$obj->save();
			return $obj;
		}
		$obj->page = $page;
		$obj->widget = $widget;
		$obj->instance_id = $instance_id;
		$obj->username = $user;

		return $obj;
	}

	public function save()
	{
		$new = false;
		$user = Auth::instance()->get_user()->username;
		if (!isset($this->id))
			$new = true;
		else if ($this->db_row['name'] !== $this->name)
			$new = true;
		else if ($this->db_row['page'] !== $this->page)
			$new = true;
		else if ($this->db_row['username'] !== $user)
			$new = true;
		else if ($this->db_row['instance_id'] !== $this->instance_id)
			$new = true;

		if ($new) {
			$sql = 'INSERT INTO ninja_widgets (username, page, name, friendly_name, setting, instance_id) VALUES (%s, %s, %s, %s, %s, %s)';
		}
		else {
			$sql = 'UPDATE ninja_widgets SET username=%s, page=%s, name=%s, friendly_name=%s, setting=%s, instance_id=%s WHERE id='.$this->db->escape($this->id);
		}

		$this->db->query(sprintf($sql,
			$this->db->escape($this->username),
			$this->db->escape($this->page),
			$this->db->escape(self::clean_widget_name($this->name)),
			$this->db->escape($this->friendly_name),
			$this->db->escape(serialize($this->setting)),
			$this->db->escape($this->instance_id)));

		if (!isset($this->id)) {
			$res = $this->db->query(sprintf('SELECT id FROM ninja_widgets WHERE username%s AND page%s AND name=%s AND instance_id=%s',
				$this->username ? '='.$this->db->escape($this->username) : ' IS NULL',
				$this->page ? '='.$this->db->escape($this->page) : ' IS NULL',
				$this->db->escape(self::clean_widget_name($this->name)),
				$this->db->escape($this->instance_id)));
			$this->id = $res->current()->id;
		}

		foreach ($this->db_row as $key => $_) {
			$this->db_row[$key] = $this->$key;
		}
		return true;
	}

	/**
	* Merges old settings with new settings and reurns serialized settings
	* If settings index of new settings exists in old settings the old value
	* will be	replaced with the value of the new one.
	*/
	public function merge_settings($new_setting)
	{
		if (!is_array($this->setting))
			$this->setting = array();
		$this->setting = array_merge($this->setting, $new_setting);
	}

	/**
	* Add a new widget to ninja_widgets table
	*/
	public static function install($page, $name, $friendly_name)
	{
		if (empty($name) || empty($friendly_name)) {
			return false;
		}

		if (Ninja_widget_Model::get($page, $name) !== false) {
			# widget already exists
			return false;
		}
		$db = Database::instance();
		$sql = "INSERT INTO ninja_widgets(name, page, friendly_name) ".
			'VALUES('.$db->escape($name).', '.$db->escape($page).', '.$db->escape($friendly_name).')';
		$db->query($sql);
		# add the new widget to the widget_order string
		return true;
	}

	public static function uninstall($name)
	{
		$db = Database::instance();
		$sql = 'DELETE FROM ninja_widgets WHERE name='.$db->escape($name);
		$db->query($sql);
		$sql = "SELECT id, setting FROM ninja_settings WHERE type='widget_order'";
		$result = $db->query($sql);
		foreach ($result as $row) {
			if (strpos($row->setting, $name) === false)
				continue;
			$parsed_setting = self::parse_widget_order($row->setting);
			$widgets = array();
			foreach ($parsed_setting as $container => $widgets) {
				foreach ($widgets as $idx => $id) {
					if (strpos($id, 'widget-'.$name) === 0) {
						unset($parsed_setting[$container][$idx]);
					}
				}
				$widget_string[] = $container . '=' . implode(',', $widgets);
			}
			$db->query('UPDATE ninja_settings SET setting='.$db->escape(implode('|', $widget_string)).' WHERE id='.$row->id);
		}
		return true;
	}

	public function copy()
	{
		$db = Database::instance();
		$res = $db->query('SELECT MAX(instance_id) AS max_instance_id FROM ninja_widgets WHERE name='.$db->escape($this->name).' AND page='.$db->escape($this->page).' AND username='.$db->escape($this->username));
		$res = $res->current();
		$the_copy = self::get($this->page, $this->name, $this->instance_id);
		unset($the_copy->id);
		if (isset($res->max_instance_id))
			$the_copy->instance_id = $res->max_instance_id + 1;
		else
			$the_copy->instance_id = 0;
		$the_copy->save();
		$order = self::fetch_widget_order($this->page);
		foreach ($order as $container => $widgets) {
			if (in_array('widget-'.$this->name.'-'.$this->instance_id, $widgets))
				$order[$container][] = 'widget-'.$this->name.'-'.$the_copy->instance_id;
		}
		self::set_widget_order($this->page, $order);
		return $the_copy;
	}

	/**
	 * Deletes a copy of a widget.
	 *
	 * @returns true if widget was deleted, false if this was the last copy which thus wasn't deleted
	 */
	public function delete()
	{
		$user = Auth::instance()->get_user()->username;
		$sql = 'SELECT COUNT(instance_id) AS count FROM ninja_widgets WHERE instance_id IS NOT NULL AND username='.$this->db->escape($user).' AND page='.$this->db->escape($this->page).' AND name='.$this->db->escape($this->name);
		$res = $this->db->query($sql);
		if ($res->current()->count <= 1)
			return false;

		$sql = 'DELETE FROM ninja_widgets WHERE id='.$this->db->escape($this->id);
		$this->db->query($sql);
		return true;
	}

	/**
	* Clean the widget name received from easywidgets
	*/
	private function clean_widget_name($widget)
	{
		return str_replace('#widget-', '', trim($widget));
	}

	/**
	* Update setting for all widgets on a page
	*/
	public static function update_all_widgets($page=false, $value=false, $type='refresh_interval')
	{
		if (empty($page) || ($value!=0 && empty($value)) || empty($type))
			return false;

		# check if the user already have customized widgets settings
		# (already removed/added a widget)
		self::customize_widgets($page);

		# fetch all available widgets for a page
		$all_widgets = self::fetch_widgets($page);
		if ($all_widgets !== false) {
			$new_setting = array($type => $value);
			foreach ($all_widgets as $widget) {
				$widget->merge_settings($new_setting);
				$widget->save();
			}
			return true;
		}
		return false;
	}

	private static function parse_widget_order($setting)
	{
		$widget_order = false;
		if (!empty($setting)) {
			$widget_parts = explode('|', $setting);
			if (!empty($widget_parts)) {
				foreach ($widget_parts as $part) {
					$parts = explode('=', $part);
					if (is_array($parts) && !empty($parts)) {
						$widget_sublist = explode(',', $parts[1]);
						if (is_array($widget_sublist) && !empty($widget_sublist)) {
							$widget_order[$parts[0]] = $widget_sublist;
						}
					}
				}
			}
		}
		return $widget_order;
	}

	/**
	* Parse the widget order for use on a page
	*/
	public static function fetch_widget_order($page=false)
	{
		$data = Ninja_setting_Model::fetch_page_setting('widget_order', $page);
		if ($data === false || empty($data->setting)) {
			return false;
		}
		return self::parse_widget_order($data->setting);
	}

	public static function set_widget_order($page, $widget_order) {
		$res = array();
		foreach ($widget_order as $key => $ary) {
			$res[] = "$key=".implode(',', $ary);
		}
		Ninja_setting_Model::save_page_setting('widget_order', $page, implode('|', $res));
	}

	/**
	 * DANGER WILL ROBINSON!
	 * This makes a ton of assumptions, and should only be called after much
	 * consideration.
	 */
	public static function rename_widget($old_name, $new_name)
	{
		$db = Database::instance();
		$sql = 'UPDATE ninja_widgets SET name='.$db->escape($new_name).' WHERE name='.$db->escape($old_name);
		$db->query($sql);
	}
}
