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
		if (empty($page))
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
	*
	*
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
				$add = ORM::factory('ninja_widget');
					$add->user = $user;
					$add->page = $page;
					$add->name = $row->name;
					$add->friendly_name = $row->friendly_name;
					$add->setting = $row->setting;
					$add->save();
					$add->clear();
			}
		}
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
		$setting = ORM::factory('ninja_widget');

		# check if the user already have customized widgets settings
		# (already removed/added a widget)
		self::customize_widgets($page);

		# all widgets for current page should exist under users name

		switch ($method) {
			case 'hide': case 'close':
				# remove current widget for user and page
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
					$fetch->delete($fetch->id);
				}
				break;
			case 'show': case 'add':
				# user added a widget to current page
				# copy settings from default and insert for user
				$fetch_clean = ORM::factory('ninja_widget')
					->where(
						array
						(
							'name' => $widget,
							'user' => '',
							'page' => $page
						)
					)->find();
				if ($fetch_clean->loaded) {
					$add = ORM::factory('ninja_widget');
					$add->user = $user;
					$add->page = $page;
					$add->name = $widget;
					$add->friendly_name = $fetch_clean->friendly_name;
					$add->setting = $fetch_clean->setting;
					$add->save();
				}

				break;
		}
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
			$new_state->setting = serialize($data);
			$new_state->save();
			return $new_state->saved;
		}
		return false;
	}
}