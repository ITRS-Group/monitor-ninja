
<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User controller
 * Requires authentication
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class User_Controller extends Authenticated_Controller {
	# form field types
	private static $var_types = array(
		'pagination.default.items_per_page' => 'int',
		'pagination.paging_step' => 'int',
		'keycommands.activated' => 'bool',
		'keycommands.search' => 'string',
		'keycommands.pause' => 'string',
		'keycommands.forward' => 'string',
		'keycommands.back' => 'string',
		'checks.show_passive_as_active' => 'bool',
		'config.current_skin' => 'select',
		'config.use_popups' => 'bool',
		'config.popup_delay' => 'int',
		'config.show_display_name' => 'bool',
		'config.show_notes' => 'bool',
		'config.show_notes_chars' => 'int',
		'nagdefault.sticky' => 'bool',
		'nagdefault.persistent' => 'bool',
		'nagdefault.comment' => 'string',
		'nagdefault.services-too' => 'bool',
		'nagdefault.force' => 'bool',
		'nagdefault.duration' => 'int',
		'nagdefault.fixed' => 'bool',
		'nagdefault.notes_url_target' => 'select',
		'nagdefault.action_url_target' => 'select'
	);

	/**
	*	Default method
	*	Enable user to edit some GUI settings
	*/
	public function index()
	{
		$updated = $this->input->get('updated', false);
		$title = _('User Settings');
		$this->template->title = $title;

		$this->template->disable_refresh = true;
		$this->template->content = $this->add_view('user/settings');

		$template = $this->template->content;

		$this->template->js_header = $this->add_view('js_header');

		# check if user is an admin
		$is_admin = Auth::instance()->authorized_for('host_view_all');
		$template->is_admin = $is_admin;

		$this->template->content->widgets = $this->widgets;

		$available_setting_sections = array(
			_('Pagination') => 'pagination',
			_('Checks') => 'checks',
			_('Config') => 'config',
			_('Columns in list view') => 'listview',
			_('Keyboard Commands') => 'keycommands',
			_('Pop up graphs') => 'popups',
			_('Status Pages') => 'status',
			_('URL Targets') => 'url_target',
			_('Nagios Defaults') => 'nagdefault'
		);

		$settings['pagination'] = array(
			_('Pagination Limit') => array('pagination.default.items_per_page', self::$var_types['pagination.default.items_per_page']),
			_('Pagination Step') => array('pagination.paging_step', self::$var_types['pagination.paging_step']),
		);

		$settings['keycommands'] = array(
			_('Keycommands') => array('keycommands.activated', self::$var_types['keycommands.activated']),
			_('Search') => array('keycommands.search', self::$var_types['keycommands.search']),
			_('Pause') => array('keycommands.pause', self::$var_types['keycommands.pause']),
			_('Paging Forward') => array('keycommands.forward', self::$var_types['keycommands.forward']),
			_('Paging Back') => array('keycommands.back', self::$var_types['keycommands.back'])
		);
		$settings['checks'] = array(
			_('Show Passive as Active') => array('checks.show_passive_as_active', self::$var_types['checks.show_passive_as_active'])
		);

		$settings['status'] = array(
			_('Show display_name') => array('config.show_display_name', self::$var_types['config.show_display_name']),
			_('Show notes') => array('config.show_notes', self::$var_types['config.show_notes']),
			_('Note length') => array('config.show_notes_chars', self::$var_types['config.show_notes_chars'])
		);

		$settings['url_target'] = array(
			_('Notes URL Target') => array('nagdefault.notes_url_target', self::$var_types['nagdefault.notes_url_target'], Kohana::config('nagdefault.available_targets')),
			_('Action URL Target') => array('nagdefault.action_url_target', self::$var_types['nagdefault.action_url_target'], Kohana::config('nagdefault.available_targets')),
		);

		$settings['popups'] = array(
			_('Show pop-up graphs') => array('config.use_popups', self::$var_types['config.use_popups']),
			_('Popup delay') => array('config.popup_delay', self::$var_types['config.popup_delay'])
		);

		$settings['nagdefault'] = array(
			_('Sticky') => array('nagdefault.sticky', self::$var_types['nagdefault.sticky']),
			_('Persistent') => array('nagdefault.persistent', self::$var_types['nagdefault.persistent']),
			_('Force action') => array('nagdefault.force', self::$var_types['nagdefault.force']),
			_('Perform action for services too') => array('nagdefault.services-too', self::$var_types['nagdefault.services-too']),
			_('Fixed') => array('nagdefault.fixed', self::$var_types['nagdefault.fixed']),
			_('Duration (hours)') => array('nagdefault.duration', self::$var_types['nagdefault.duration']),
			_('Comment') => array('nagdefault.comment', self::$var_types['nagdefault.comment']));


		$listview_settings = array();
		foreach( Kohana::config('listview.columns') as $table => $value ) {
			$listview_settings[_('Table '.ucwords($table))] = array('listview.columns.'.$table, 'textarea');
		}
		$settings['listview'] = $listview_settings;
		
		$settings['config'] = false;
		$available_skins = ninja::get_skins();
		$settings['config'] = array(
			_('Current Skin') => array('config.current_skin', self::$var_types['config.current_skin'], $available_skins)
		);

		$current_values = false;
		if (!empty($available_setting_sections)) {
			foreach ($available_setting_sections as $str => $key) {
				if (!isset($settings[$key])) {
					continue;
				}
				foreach ($settings[$key] as $discard => $cfgkey) {
					if (is_array($cfgkey[0])) {
						continue;
					}
					$current_val = Ninja_setting_Model::fetch_page_setting($cfgkey[0], '*');
					if (is_object($current_val) && count($current_val)) {
						$current_values[$cfgkey[0]] = $current_val->setting;
					} else {
						$current_values[$cfgkey[0]] = Kohana::config($cfgkey[0]);
					}
				}
			}
		}

		$template->title = _('User settings');
		$template->current_values = $current_values;
		$template->available_setting_sections = $available_setting_sections;
		$template->settings = $settings;
		$updated_str = false;
		if ($updated !== false) {
			$updated_str = _('Your settings were successfully saved');
		}
		$template->updated_str = $updated_str;
		$this->template->js_header->js = $this->xtra_js;
	}

	/**
	*	Save data from form after some validation
	*/
	public function save()
	{
		unset($_POST['save_config']);

		# restore '.' in config keys
		$restore_string = '_99_';
		$data = false;
		foreach ($this->input->post() as $key => $val) {
			$key = str_replace($restore_string, '.', $key);
			$data[$key] = $val;
		}

		# fetch all param type info
		$type_info = self::$var_types;

		# Add string to all column types for listview
		$listview_settings = array();
		foreach( Kohana::config('listview.columns') as $table => $value ) {
			$type_info['listview.columns.'.$table] = 'string';
		}
		
		# make sure we have field type info befor continuing
		if (empty($type_info)) {
			die(_('Unable to process user settings since field type info is missing'));
		}

		# loop through actual settings, validate and save if OK
		$errors = false;
		$base_err_str = _('Wrong datatype vaule for field %s. Should be %s - found %s');
		$empty_str = _('Ignoring %s since no value was found for it.');
		foreach ($data as $key => $val) {
			if ($val == '' && $type_info[$key] != 'string') {
				$errors[$key] = sprintf($empty_str, $key);
			}
			switch ($type_info[$key]) {
				case 'int':
					if (!is_numeric($val)) {
						$errors[$key] = sprintf($base_err_str, $key, $type_info[$key], $val);
					} else {
						$this->_save_value($key, $val);
					}
					break;
				case 'bool':
					if (!is_numeric($val) || ($val != '0' && $val != '1')) {
						$errors[$key] = sprintf($base_err_str, $key, $type_info[$key], $val);
					} else {
						$this->_save_value($key, $val);
					}
					break;
				case 'select': case 'string':
					if (strstr($key, 'keycommand')) {
						$val = str_replace(' ', '', $val);
					}
					# no validation for these types yet
					$this->_save_value($key, $val);
					break;
				default:
					$errors[$key] = sprintf(_('Found no type information for %s so skipping it'), $key);
			}
		}

		if (!empty($errors)) {
			$title = _('User Settings');
			$this->template->title = $title;

			$this->template->disable_refresh = true;
			$this->template->content = $this->add_view('user/error');

			$template = $this->template->content;

			$this->template->js_header = $this->add_view('js_header');

			$this->template->content->widgets = $this->widgets;
			$template->errors = $errors;
		} else {
			return url::redirect('user/index?updated=true');
		}

	}

	/**
	*	Save a config key => value pair to db
	*	and session for current user.
	*/
	public function _save_value($key=false, $val=false, $page='*')
	{
		# save to db
		Ninja_setting_Model::save_page_setting($key, $page, $val);

		# save to session
		$page_val = '';
		if ($page != '' && !empty($page)) {
			$page_val = '.'.$page;
		}
		Session::instance()->set($key.$page_val, $val);
	}


	/**
	* Translated helptexts for this controller
	*/
	public static function _helptexts($id)
	{
		$keyboard_help = '<br />'._("Possible Modifier keys are Alt, Shift, Ctrl + any key.
			Modifier keys should be entered in alphabetical order. Add a combination of keys
			with a + sign between like 'Alt+Shift-f' without any spaces. All keys are case insensitive.");

		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array(
			'pagination.default.items_per_page' => _('Set number of items shown on each page. Defaults to 100.'),
			'pagination.paging_step' => _('This value is used to generate drop-down for nr of items per page to show. Defaults to 100.'),
			'checks.show_passive_as_active' => _('This setting affects if to show passive checks as active in the GUI'),
			'config.current_skin' => _('Select the skin to use in the GUI. Affects colors and images.'),
			'keycommands.activated' => _('Switch keyboard commands ON or OFF. Default is OFF'),
			'keycommands.search' => _('Keyboard command to set focus to search field. Defaults to Alt+Shift+f.').' '.$keyboard_help,
			'keycommands.pause' => _('Keyboard command to pause/unpause page refresh. Defaults to Alt+Shift+p.').' '.$keyboard_help,
			'keycommands.forward' => _('Keyboard command to move forward in a paginated result (except search results). Defaults to Alt+Shift+right.').' '.$keyboard_help,
			'keycommands.back' => _('Keyboard command to move back in a paginated result (except search results). Defaults to Alt+Shift+left.').' '.$keyboard_help,
			'config.use_popups' => _('Enable or disable the use of pop-ups for performance graphs and comments.'),
			'config.popup_delay' => _('Set the delay in milliseconds before the pop-ups (performance graphs and comments) will be shown. Defaults to 1500ms (1.5s).'),
			'config.show_display_name' => _('Use this setting to control whether to show display_name for your hosts and services on status/service and search result pages or not.'),
			'config.show_notes' => _('Use this setting to control whether to show notes for your services on status/service and search result pages or not.'),
			'config.show_notes_chars' => _('Control how many characters of the note to be displayed in the GUI. The entire note will be displayed on mouseover or click. <br />Use 0 to display everything. Default: 80.'),
			'edit_menu' => _('Edit menu item visibility for limited users.'),
			'nagdefault.notes_url_target' => _('This option determines the name of the frame target that notes URLs should be displayed in.'),
			'nagdefault.action_url_target' => _('This option determines the name of the frame target that action URLs should be displayed in.'),
			'nagdefault.sticky' => _('Configure the default value for the nagios "sticky" command option'),
			'nagdefault.persistent' => _('Configure the default value for the nagios "persistent" command option'),
			'nagdefault.force' => _('Configure the default value for the nagios "force" command option'),
			'nagdefault.services-too' => _('Configure the default value for the nagios "services-too" command option'),
			'nagdefault.fixed' => _('Configure the default value for the nagios "fixed" command option'),
			'nagdefault.duration' => _('Configure the default value for the nagios "duration" command option'),
			'nagdefault.comment' => _('Configure the default value for the nagios "comment" command option'),
		);
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		}
		else
			echo sprintf(_("This helptext ('%s') is not translated yet"), $id);
	}

	/**
	*	Remove menu item by index
	* 	Both section string ['about', 'monitoring', etc]
	* 	and item string ['portal', 'manual', 'support', etc] are required.
	* 	As a consequence, all menu items has to be explicitly removed before removing the section
	*/
	public function menu_remove(&$menu_links=false, &$menu_items=false, $section_str=false, $group=false, $item_str=false, $save=true)
	{
		if (empty($menu_links) || empty($menu_items) || empty($section_str)) {
			return false;
		}

		if (is_array($section_str)) {
			if ($save === true) {
				$config = Op5Config::instance();
				$ninja_menu = $config->getConfig('ninja_menu');
				$ninja_menu[$group] = $section_str;
				$config->setConfig('ninja_menu', $ninja_menu);
			}

			# we have to make recursive calls
			foreach ($section_str as $section => $items) {
				foreach ($items as $item) {
					$this->menu_remove($menu_links, $menu_items, $section, $item, $group);
				}
			}
		} else {
			if (empty($item_str) && isset($menu_links[$menu_items['section_'.$section_str]])
				&& empty($menu_links[$menu_items['section_'.$section_str]])) {
				# remove the section
				unset($menu_links[$menu_items['section_'.$section_str]]);
			} elseif (isset($menu_items[$item_str]) && !empty($item_str) && isset($menu_links[$menu_items['section_'.$section_str]][$menu_items[$item_str]])) {
				unset($menu_links[$menu_items['section_'.$section_str]][$menu_items[$item_str]]);
			}
		}
	}


	/**
	*	Edit menu items
	* 	Show form for editing menu items
	*/
	public function menu_edit()
	{
		if(!Auth::instance()->authorized_for('access_rights')) {
			// @todo add "you're not authed" flash message
			//_("You don't have access to this page. Only visible to administrators.");
			return url::redirect(Router::$controller.'/index');
		}
		$groups = Auth::get_groups_without_rights(array('access_rights'));
		$selected_group = $this->input->get('usergroup', false);
		if($selected_group && !isset($groups[$selected_group])) {
			return url::redirect(Router::$controller.'/menu_edit');
		}
		$this->template->disable_refresh = true;

		$this->template->content = $this->add_view('user/edit_menu');
		$this->xtra_js[] = $this->add_path('user/js/user.js');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;
		$content = $this->template->content;

		$content->select_user_message = _("Select the user below to edit the menu for.");
		$content->description = _("Check the menu items that the should not be visible to the selected user.");

		$content->groups = $groups;

		$remove_items = false;
		$all_items = false;
		if ($selected_group) {
			include(APPPATH.'views/menu/menu.php');
			$config = Op5Config::instance()->getConfig('ninja_menu');
			if(isset($config[$selected_group])) {
				$remove_items = $config[$selected_group];
			}

			// disallowing manually giving someone the right to access nacoma,
			// it's really controlled by system_information (an access right)
			unset($menu_base['Configuration']['Configure'], $menu_items['configure']);
			$all_items = $menu_base;

			$content->menu_base = $menu_base;
			$content->menu_items = $menu_items;
			$content->sections = $sections;
			$content->menu = $menu;
		}

		$content->selected_group = $selected_group;

		$content->remove_items = $remove_items;
		$content->all_items = $all_items;

		# protected menu items
		$untouchable_items = array('my_account');
		$content->untouchable_items = $untouchable_items;
	}

	/**
	*	Update menu - save removed items to db
	* 	and redirect to menu setup
	*/
	public function menu_update()
	{
		if(!Auth::instance()->authorized_for('access_rights')) {
			// @todo add "you're not authed" flash message
			//_("You don't have access to this page. Only visible to administrators.");
			return url::redirect(Router::$controller.'/index');
		}
		$group = $this->input->post('group', false);
		$remove_items = $this->input->post('remove_items', false);
		if($_SERVER['REQUEST_METHOD'] != 'POST' || !$group) {
			return url::redirect(Router::$controller.'/menu_edit');
		}

		include(APPPATH.'views/menu/menu.php');

		$all_items = $menu_base;
		if ($remove_items) {
			$this->menu_remove($menu_base, $menu_items, $remove_items, $group);
		} else {
			$config = Op5Config::instance();
			$ninja_menu = $config->getConfig('ninja_menu');
			if(isset($ninja_menu[$group])) {
				unset($ninja_menu[$group]);
			}
			$config->setConfig('ninja_menu', $ninja_menu);
		}

		return url::redirect(Router::$controller."/menu_edit?usergroup=$group");
	}
}
