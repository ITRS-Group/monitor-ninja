<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle page data - saving and fetching
 */
class Ninja_widget_Model extends Model
{
	const USERFIELD = 'username';

	/**
	*	Fetch all available widgets for a page
	*
	*/
	public function fetch_widgets($page=false, $all=false)
	{
		if (empty($page) && $all !== true)
			return false;
		$user = Auth::instance()->get_user()->username;
		$db = new Database();
		$sql = "SELECT * FROM ninja_widgets ";
		if ($all===true) {
			$sql .= " WHERE page=".$db->escape($page)." AND (".self::USERFIELD."='' OR ".
				self::USERFIELD." IS NULL) ORDER BY friendly_name";
		} else {
			$sql .= " WHERE page=".$db->escape($page)." AND ".self::USERFIELD."=".$db->escape($user).
				"ORDER BY friendly_name";
		}
		$result = $db->query($sql);
        if (count($result) == 0) {
		return false;
        } else {
            $rc = array();
            foreach ( $result as $row ) {
                $rc[] = $row;
            }
            unset($result);
            return $rc;
        }
	}

	/**
	*	Fetch info on a saved widget for a page
	*
	*/
	public function get_widget($page=false, $widget=false, $get_user=false)
	{
		if (empty($page) || empty($widget))
			return false;
		$db = new Database();
		$sql = "SELECT * FROM ninja_widgets ";
		if ($get_user === true) {
			# fetch customized widget for user
			# i.e a user has saved settings
			$user = Auth::instance()->get_user()->username;
			$sql .= " WHERE page=".$db->escape($page)." AND ".self::USERFIELD."=".
				$db->escape($user)." AND name=".$db->escape($widget);
		} else {
			# fetch default widget settings
			$sql .= " WHERE page=".$db->escape($page)." AND (".self::USERFIELD."='' OR ".
				self::USERFIELD ." IS NULL) AND name=".$db->escape($widget);
		}
		$result = $db->query($sql);

		return count($result)!=0 ? $result->current() : false;
	}

	/**
	*	Copy all standard widgets for a page to user as customized
	*/
	private function customize_widgets($page=false)
	{
		if (empty($page))
			return false;
		$page = trim($page);
		$user = Auth::instance()->get_user()->username;
		$db = new Database();
		$sql_base = "SELECT * FROM ninja_widgets ";
		$sql = $sql_base." WHERE page=".$db->escape($page)." AND ".self::USERFIELD."=".$db->escape($user);
		$res = $db->query($sql);
		if (!count($res)) {
			unset($res);
			# copy all under users' name
			$sql = $sql_base." WHERE page=".$db->escape($page)." AND (".self::USERFIELD."='' OR ".self::USERFIELD." IS NULL)";
			$res = $db->query($sql);
			foreach ($res as $row) {
				# copy widget setting to user
				self::copy_to_user($row);
			}
			unset($res);
		}
	}

	/**
	*	Copy an existing widget and save as customized (ie for a user)
	*	Assuming that checks has already been made that the user doesn't
	* 	already have the widget.
	*/
	public static function copy_to_user($old_widget=false)
	{
		if (empty($old_widget)) {
			return false;
		}
		$user = Auth::instance()->get_user()->username;
		$db = new Database();
		$sql = "INSERT INTO ninja_widgets (".self::USERFIELD.", page, name, friendly_name, setting) ".
			"VALUES(".$db->escape($user).", ".$db->escape($old_widget->page).", ".$db->escape($old_widget->name).
			",".$db->escape($old_widget->friendly_name).", ".$db->escape($old_widget->setting).")";
		$db->query($sql);
	}

	/**
	*	User has decided to add or remove a widget from a page
	*	Store this customized setting for user
	*/
	public function save_widget_state($page=false, $method='hide', $widget=false)
	{
		if (empty($page) || empty($widget))
			return false;
		$page = trim($page);
		$method = trim(strtolower($method));
		$widget = trim($widget);
		$widget = self::clean_widget_name($widget);
		$user = Auth::instance()->get_user()->username;

		# check if the user already have customized widgets settings
		# (already removed/added a widget)
		self::customize_widgets($page);

		# Make sure that this particular widget exists for user.
		# Could've been added later
		$current_widget = self::get_widget($page, $widget, true);
		if ($current_widget === false) {
			self::copy_to_user(self::get_widget($page, $widget));
		}

		# all widgets for current page should exist under users name

		$db = new Database();
		$sql_base = "SELECT * FROM ninja_widgets ";
		$sql = $sql_base." WHERE name=".$db->escape($widget).
			" AND ".self::USERFIELD."=".$db->escape($user).
			" AND page=".$db->escape($page);

		$res = $db->query($sql);
		$setting = array();
		if (count($res)!=0) {
			$cur = $res->current();
			$setting = $cur->setting;
			$id = $cur->id;
			unset($res);
			unset($cur);
		}

		switch ($method) {
			case 'hide': case 'close':
				$setting = self::merge_settings($setting, array('status' => 'hide'));
				break;
			case 'show': case 'add':
				$setting = self::merge_settings($setting, array('status' => 'show'));
				break;
		}
		if (!empty($setting)) {
			$sql = "UPDATE ninja_widgets SET setting=".$db->escape($setting)." WHERE ".
				"id=".(int)$id;
			$db->query($sql);
			return true;
		}
		return false;
	}

	/**
	*	Merges old settings with new settings and reurns serialized settings
	* 	If settings index of new settings exists in old settings the old value
	* 	will be	replaced with the value of the new one.
	*/
	private function merge_settings($old_setting=false, $new_setting=false)
	{
		if (!empty($old_setting)) {
			$old_setting = trim($old_setting);
			$old_setting = !empty($old_setting) ? unserialize(trim($old_setting)) : array();
			$new_setting = serialize(array_merge($old_setting, $new_setting));
		} else {
			$new_setting = serialize($new_setting);
		}
		return $new_setting;
	}

	/**
	*	Clean the widget name received from easywidgets
	*/
	public function clean_widget_name($widget)
	{
		return str_replace('#widget-', '', $widget);
	}

	/**
	*	Accept call from a widget that has some settings to store
	* 	for a user.
	*
	*/
	public function save_widget_setting($page=false, $widget=false, $data=false)
	{
		if (empty($widget) || empty($data) || empty($page) || !is_array($data))
			return false;
		self::customize_widgets($page);
		# fetch current setting for widget and merge settings with new
		# merge/replace new settings with the old
		$current_widget = self::get_widget($page, $widget, true);
		if ($current_widget !== false) {
			$db = new Database();
			$setting = self::merge_settings($current_widget->setting, $data);
			$sql = "UPDATE ninja_widgets SET setting=".$db->escape($setting)." WHERE ".
				"id=".(int)$current_widget->id;
			$db->query($sql);
		} else {
			self::copy_to_user(self::get_widget($page, $widget));
			self::save_widget_setting($page, $widget, $data);
		}
		return true;
	}

	/**
	*	Fetch all info required to show widgets on a page
	*/
	public static function fetch_page_widgets($page=false, $model=false)
	{
		$all_widgets = self::fetch_widgets($page, true);
		$settings_widgets = false;
		$widget_list = false;
		$settings = false;
		if (!empty($all_widgets)) {
			foreach ($all_widgets as $row) {
				$settings[$row->name] = unserialize(trim($row->setting));
				if (!empty($settings[$row->name]) && is_array($settings[$row->name])) {
					# if we have settings we should add this
					# model to the start of the arguments array
					# since the widgets expect the first parameter
					# in arguments list to be the model
					array_unshift($settings[$row->name], $model);
				} else {
					$settings[$row->name][] = $model;
				}

				$widget_list[] = $row->name; # keep track of all available widgets
				$settings_widgets['widget-'.$row->name] = $row->friendly_name;
			}
		}

		# check if there is customized widgets (with user settings)
		$widgets = self::fetch_widgets($page);

		$user_widgets = false;
		if (!empty($widgets)) {
			foreach ($widgets as $w) {
				$user_settings = unserialize(trim($w->setting));
				if (isset($settings[$w->name]) && is_array($settings[$row->name])) {
					# replace default settings with user settings if available
					if (!empty($user_settings) && is_array($user_settings)) {
						$settings[$w->name] = $user_settings;
						array_unshift($settings[$w->name], $model);
					}
				}
				if (is_array($user_settings) && !empty($user_settings) && array_key_exists('status', $user_settings)) {
					if ($user_settings['status'] == 'hide') {
						# don't show widgets set to 'hide'
						continue;
					} else {
						$user_widgets['widget-'.$w->name] = $w->friendly_name;
					}
				} else {
					$user_widgets['widget-'.$w->name] = $w->friendly_name;
				}
			}
		}

		$inline_js = false;
		if (!empty($user_widgets)) {
			# customized settings detected
			# some widgets should possibly be hidden
			foreach ($settings_widgets as $id => $w) {
				if (!array_key_exists($id, $user_widgets)) {
					$inline_js .= "\$('#".$id."').removeClass('movable').hide();\n";
				}
			}
		}

		$widget_info = array(
			'settings_widgets' => $settings_widgets,
			'settings' => $settings,
			'widget_list' => $widget_list,
			'inline_js' => $inline_js,
			'user_widgets' => $user_widgets
		);

		return $widget_info;
	}

	/**
	*	Update setting fo all widgets on a page
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
				$db = new Database();
				$setting = self::merge_settings($widget->setting, $new_setting);
				$sql = "UPDATE ninja_widgets SET setting=".$db->escape($setting)." WHERE ".
					"id=".(int)$widget->id;
				$db->query($sql);
			}
			return true;
		}
		return false;
	}

	/**
	*	Parse the widget order for use on a page
	*/
	public function fetch_widget_order($page=false)
	{
		$data = Ninja_setting_Model::fetch_page_setting('widget_order', $page);
		$widget_parts = $data->setting;
		$widget_order = false;
		if (!empty($widget_parts)) {
			$widget_parts = explode('|', $widget_parts);
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
	*	Add a new widget to ninja_widgets table
	*/
	public function add_widget($page=false, $name=false, $friendly_name=false)
	{
		if (empty($name) || empty($friendly_name)) {
			return false;
		}

		if (Ninja_widget_Model::get_widget($page, $name) !== false) {
			# widget already exists
			return false;
		}
		$db = new Database();
		$sql = "INSERT INTO ninja_widgets(name, page, friendly_name) ".
			"VALUES(".$db->escape($name).", ".$db->escape($page).", ".$db->escape($friendly_name).")";
		$return = $db->query($sql);
		return $return;
	}
}
