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
		'pagination.group_items_per_page' => 'int',
		'keycommands.activated' => 'bool',
		'keycommands.search' => 'string',
		'keycommands.pause' => 'string',
		'keycommands.forward' => 'string',
		'keycommands.back' => 'string',
		'checks.show_passive_as_active' => 'bool',
		'config.current_skin' => 'select',
		'config.use_popups' => 'bool',
		'config.popup_delay' => 'int'
	);

	/**
	*	Default method
	*	Enable user to edit some GUI settings
	*/
	public function index()
	{
		$updated = $this->input->get('updated', false);
		$title = $this->translate->_('User Settings');
		$this->template->title = $title;

		$this->template->disable_refresh = true;
		$this->template->content = $this->add_view('user/settings');

		$template = $this->template->content;

		$this->template->js_header = $this->add_view('js_header');

		$this->template->content->widgets = $this->widgets;

		$t = $this->translate;
		$available_setting_sections = array(
			$t->_('Pagination') => 'pagination',
			$t->_('Checks') => 'checks',
			$t->_('Config') => 'config',
			$t->_('Keyboard Commands') => 'keycommands',
			$t->_('Pop ups') => 'popups'
		);

		$settings['pagination'] = array(
			$t->_('Pagination Limit') => array('pagination.default.items_per_page', self::$var_types['pagination.default.items_per_page']),
			$t->_('Pagination Step') => array('pagination.paging_step', self::$var_types['pagination.paging_step']),
			$t->_('Group Pagination Limit') => array('pagination.group_items_per_page', self::$var_types['pagination.group_items_per_page'])
		);
		$settings['keycommands'] = array(
			$t->_('Keycommands') => array('keycommands.activated', self::$var_types['keycommands.activated']),
			$t->_('Search') => array('keycommands.search', self::$var_types['keycommands.search']),
			$t->_('Pause') => array('keycommands.pause', self::$var_types['keycommands.pause']),
			$t->_('Paging Forward') => array('keycommands.forward', self::$var_types['keycommands.forward']),
			$t->_('Paging Back') => array('keycommands.back', self::$var_types['keycommands.back'])
		);
		$settings['checks'] = array(
			$t->_('Show Passive as Active') => array('checks.show_passive_as_active', self::$var_types['checks.show_passive_as_active'])
		);

		$settings['popups'] = array(
			$t->_('Use popups') => array('config.use_popups', self::$var_types['config.use_popups']),
			$t->_('Popup delay') => array('config.popup_delay', self::$var_types['config.popup_delay'])
		);

		$skins = glob(APPPATH.'views/'.$this->theme_path.'css/*', GLOB_ONLYDIR);

		$settings['config'] = false;
		$available_skins = false;
		$required_css = array('common.css', 'status.css', 'reports.css');
		if (count($skins) > 1) {
			foreach ($skins as $skin) {

				# make sure we have all requred css
				$missing_css = false;
				foreach ($required_css as $css) {
					if (glob($skin.'/'.$css) == false) {
						$missing_css = true;
						continue;
					}
				}
				if ($missing_css !== false) {
					continue;
				}

				# all required css files seems to be exist
				$skinparts = explode('/', $skin);
				if (is_array($skinparts) && !empty($skinparts)) {
					$available_skins[$skinparts[sizeof($skinparts)-1].'/'] = $skinparts[sizeof($skinparts)-1];
				}
			}
			if (count($available_skins) > 1) {
				$settings['config'] = array(
					$t->_('Current Skin') => array('config.current_skin', self::$var_types['config.current_skin'], $available_skins)
				);
			} else {
				unset($available_setting_sections[$t->_('Config')]);
			}
		} else {
			unset($available_setting_sections[$t->_('Config')]);
		}

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

		$template->title = $t->_('User settings');
		$template->current_values = $current_values;
		$template->available_setting_sections = $available_setting_sections;
		$template->settings = $settings;
		$updated_str = false;
		if ($updated !== false) {
			$updated_str = $t->_('Your settings were successfully saved');
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

		# make sure we have field type info befor continuing
		if (empty($type_info)) {
			die($this->translate->_('Unable to process user settings since field type info is missing'));
		}

		# loop through actual settings, validate and save if OK
		$errors = false;
		$base_err_str = $this->translate->_('Wrong datatype vaule for field %s. Should be %s - found %s');
		$empty_str = $this->translate->_('Ignoring %s since no value was found for it.');
		foreach ($data as $key => $val) {
			if ($val == '') {
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
					$errors[$key] = sprintf($this->translate->_('Found no type information for %s so skipping it'), $key);
			}
		}

		if (!empty($errors)) {
			$title = $this->translate->_('User Settings');
			$this->template->title = $title;

			$this->template->disable_refresh = true;
			$this->template->content = $this->add_view('user/error');

			$template = $this->template->content;

			$this->template->js_header = $this->add_view('js_header');

			$this->template->content->widgets = $this->widgets;
			$template->errors = $errors;
		} else {
			url::redirect('user/index?updated=true');
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
		$t = zend::instance('Registry')->get('Zend_Translate');

		$keyboard_help = '<br />'.$t->_("Possible Modifier keys are Alt, Shift, Ctrl + any key.
			Modifier keys should be entered in alphabetical order. Add a combination of keys
			with a + sign between like 'Alt+Shift-f' without any spaces. All keys are case insensitive.");

		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array(
			'pagination.default.items_per_page' => $t->_('Set number of items shown on each page. Defaults to 100.'),
			'pagination.paging_step' => $t->_('This value is used to generate drop-down for nr of items per page to show. Defaults to 100.'),
			'pagination.group_items_per_page' => $t->_('This value is used for the initial items to show on host- and service group pages. Defaults to 10.'),
			'checks.show_passive_as_active' => $t->_('This setting affects if to show passive checks as active in the GUI'),
			'config.current_skin' => $t->_('Select the skin to use in the GUI. Affects colors and images.'),
			'keycommands.activated' => $t->_('Switch keyboard commands ON or OFF. Default is OFF'),
			'keycommands.search' => $t->_('Keyboard command to set focus to search field. Defaults to Alt+Shift+f.').' '.$keyboard_help,
			'keycommands.pause' => $t->_('Keyboard command to pause/unpause page refresh. Defaults to Alt+Shift+p.').' '.$keyboard_help,
			'keycommands.forward' => $t->_('Keyboard command to move forward in a paginated result (except search results). Defaults to Alt+Shift+right.').' '.$keyboard_help,
			'keycommands.back' => $t->_('Keyboard command to move back in a paginated result (except search results). Defaults to Alt+Shift+left.').' '.$keyboard_help,
			'config.use_popups' => $t->_('Enable or disable the use of pop-ups for PNP graphs and comments.'),
			'config.popup_delay' => $t->_('Set the delay in milliseconds before the pop-ups (PNP graphs and comments) will be shown. Defaults to 1500ms (1.5s).')
		);
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		}
		else
			echo sprintf($translate->_("This helptext ('%s') is yet not translated"), $id);
	}
}