<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle page data - saving and fetching
 */
class Ninja_widget_Model extends ORM
{
	/**
	*	Fetch all available widgets for a page
	*
	*/
	public function fetch_widgets($page=false, $all=false)
	{
		if (empty($page) && $all !== true)
			return false;
		$widgets = ORM::factory('ninja_widget');
		$user = Auth::instance()->get_user()->username;
		if ($all===true) {
			$result = $widgets->where(array('page' => $page, 'user'=> ''))
				->orderby('friendly_name', 'ASC')
				->find_all();
		} else {
			$result = $widgets->where(array('page' => $page, 'user' => $user))
				->orderby('friendly_name', 'ASC')
				->find_all();
		}
		return !empty($result) ? $result : false;
	}

	/**
	*	Fetch info on a saved widget for a page
	*
	*/
	public function get_widget($page=false, $widget=false, $get_user=false)
	{
		if (empty($page) || empty($widget))
			return false;
		$handle = ORM::factory('ninja_widget');
		if ($get_user === true) {
			# fetch customized widget for user
			# i.e a user has saved settings
			$user = Auth::instance()->get_user()->username;
			$result = $handle->where(array('page' => $page, 'user'=> $user, 'name' => $widget))->find();
		} else {
			# fetch default widget settings
			$result = $handle->where(array('page' => $page, 'user'=> '', 'name' => $widget))->find();
		}
		return $result->loaded ? $result : false;
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
		$setting = ORM::factory('ninja_widget');
		$check = $setting->where(array('user'=> $user, 'page' => $page))->find_all();
		if (!count($check)) {
			# copy all under users' name
			$result = $setting->where(array('user'=> '', 'page' => $page))->find_all();
			foreach ($result as $row) {
				# copy widget setting to user
				self::copy_to_user($row);
			}
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
		$add = ORM::factory('ninja_widget');
		$add->user = $user;
		$add->page = $old_widget->page;
		$add->name = $old_widget->name;
		$add->friendly_name = $old_widget->friendly_name;
		$add->setting = $old_widget->setting;
		$add->save();
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

		# all widgets for current page should exist under users name

		switch ($method) {
			case 'hide': case 'close':
				# mark current widget as hidden for user and page
				$fetch = ORM::factory('ninja_widget')
					->where(
						array
						(
							'name' => $widget,
							'user' => $user,
							'page' => $page
						)
					)->find();
				if ($fetch->loaded) {
					$setting = ORM::factory('ninja_widget', $fetch->id);
					$setting->setting = self::merge_settings($fetch->setting, array('status' => 'hide'));
					$setting->save();
				}
				break;
			case 'show': case 'add':
				# user added a widget to current page
				# merge settings with previous settings
				$fetch_clean = ORM::factory('ninja_widget')
					->where(
						array
						(
							'name' => $widget,
							'user' => $user,
							'page' => $page
						)
					)->find();
				if ($fetch_clean->loaded) {
					$add = ORM::factory('ninja_widget', $fetch_clean->id);
					$new_setting = self::merge_settings($fetch_clean->setting, array('status' => 'show'));
					$add->setting = $new_setting;
					$add->save();
				}

				break;
		}
	}

	/**
	*	Merges old settings with new settings and reurns serialized settings
	* 	If settings index of new settings exists in old settings the old value
	* 	will be	replaced with the value of the new one.
	*/
	private function merge_settings($old_setting=false, $new_setting=false)
	{
		if (!empty($old_setting)) {
			$old_setting = unserialize($old_setting);
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
		$current_settings = false;
		if ($current_widget !== false) {
			$user = Auth::instance()->get_user()->username;
			$new_state = ORM::factory('ninja_widget', $current_widget->id);
			$new_state->setting = self::merge_settings($current_widget->setting, $data);
			$new_state->save();
			return $new_state->saved;
		}
		return false;
	}

	/**
	*	Fetch all info required to show widgets on a page
	*/
	public static function fetch_page_widgets($page=false, $model=false)
	{
		$all_widgets = self::fetch_widgets($page, true);
		$settings_widgets = false;
		$widget_list = false;
		if (!empty($all_widgets)) {
			foreach ($all_widgets as $row) {
				$settings[$row->name] = unserialize($row->setting);
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
				$user_settings = unserialize($w->setting);
				if (isset($settings[$w->name]) && is_array($settings[$row->name])) {
					# replace default settings with user settings if available
					if (!empty($user_settings) && is_array($user_settings)) {
						if (array_key_exists('status', $user_settings) && $user_settings['status'] == 'show') {
							$settings[$w->name] = $user_settings;
							array_unshift($settings[$w->name], $model);
						}
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
					$inline_js .= "\$('#".$id."').hide();\n";
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
	*
	*
	*/
	public static function update_all_widgets($page=false, $value=false, $type='refresh_interval')
	{
		if (empty($page) || empty($value) || empty($type))
			return false;

		# check if the user already have customized widgets settings
		# (already removed/added a widget)
		self::customize_widgets($page);

		# fetch all available widgets for a page
		$all_widgets = self::fetch_widgets($page);
		if ($all_widgets !== false) {
			$new_setting = array('refresh_interval' => $value);
			foreach ($all_widgets as $widget) {
				$edit = ORM::factory('ninja_widget', $widget->id);
				$edit->setting = self::merge_settings($widget->setting, $new_setting);
				$edit->save();
				if ($edit->saved == false) {
					return false;
				}
			}
			return true;
		}
		return false;
	}
}