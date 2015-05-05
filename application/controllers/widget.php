<?php defined('SYSPATH') OR die('No direct access allowed.');

class Widget_Controller extends Authenticated_Controller {
	public function __construct()
	{
		$this->session = Session::instance();
		if (!Auth::instance()->logged_in()) {
			$external_widget_user = Kohana::config('external_widget.username');
			$external_widget_groups = Kohana::config('external_widget.groups');
			if ($external_widget_user) {
				$auth = op5auth::instance();
				$auth->write_close();
				$auth->force_user(new op5User(array('username' => $external_widget_user, 'groups' => $external_widget_groups)), true);
			}
			// this is so ugly - we'll just redirect to the login page if we don't catch this here,
			// however we do want that for any URL that isn't the front page of the external widget
			// feature
			else if (get_called_class() == 'External_widget_Controller') {
				die(_('You are trying to access an '.
					'external widget but the system isn\'t configured properly for this!'.
					'<br />Please configure the config/external_widget.php config file first.'));
			}
		}
		parent::__construct();
	}

	/**
	 *	wrapper for widget ajax calls
	 */
	public function widget($widget)
	{
		$this->auto_render = false;
		$instance_id = $this->input->get('instance_id', false);
		$page = $this->input->get('page', false);

		$data = Ninja_widget_Model::get($page, $widget, $instance_id);
		widget::set_show_chrome(false);
		echo json_encode(widget::add($data, $this));

		# return false if no method defined
		return false;
	}

	/**
	*	Save location and order of widgets on a page
	*	@param  str $widget_str Serialized data for widget locations
	* 	@param 	str $page The page to save the data for
	*/
	public function save_widgets_order($widget_str=false, $page=false)
	{
		$widget_str = $this->input->post('widget_str', $widget_str);
		$page = $this->input->post('page', $page);
		$widget_str = trim($widget_str);
		$page = trim($page);
		if (empty($widget_str) || empty($page))
			return false;

		# save data to database
		Ninja_setting_Model::save_page_setting('widget_order', $page, $widget_str);
	}

	/**
	*	Fetch current widget orde from database
	*/
	public function fetch_widgets_order($page=false)
	{
		$this->auto_render = false;
		$page = $this->input->get('page', $page);
		$default = $this->input->get('default', false);
		$default = (int)$default == 1 ? true : $default;
		if (empty($page))
			return false;
		$data = Ninja_setting_Model::fetch_page_setting('widget_order', $page, $default);
		if (empty($data)) {
			echo json_encode(array('widget_order' => false));
			return false;
		}
		$settings = $data->setting;
		echo json_encode(array('widget_order' => $settings));
	}

	/**
	*	Save current state of single widget
	*
	*/
	public function save_widget_state()
	{
		$page = $this->input->post('page', false);
		$method = $this->input->post('method', false);
		$instance_id = $this->input->post('instance_id', false);
		$name = $this->input->post('name', false);
		if (empty($page))
			return false;
		# save data to database
		$widget = Ninja_widget_Model::get($page, $name, $instance_id);
		switch ($method) {
		 case 'hide': case 'close':
			$widget->delete();
			break;
		 case 'show': case 'add':
			$widget->add();
			break;
		}
	}

	/**
	*	Accepts call from a widget to save settings for a user
	*/
	public function save_widget_setting()
	{
		$page = $this->input->post('page', false);
		$widget = $this->input->post('widget', false);
		$instance_id = $this->input->post('instance_id', false);
		$data = false;
		foreach ($_POST as $key => $val) {
			if ($key == 'page' || $key == 'widget')
				continue;
			$data[$key] = $val;
		}
		if (empty($widget) || empty($data) || empty($page))
			return false;
		$widget = Ninja_widget_Model::get($page, $widget, $instance_id);
		$widget->merge_settings($data);
		$widget->save();
	}

	/**
	*	Accepts call from a widget to save custom settings for a user
	* 	The POST data should contain fieldname and fieldvalue
	*/
	public function save_dynamic_widget_setting()
	{
		$page = $this->input->post('page', false);
		$widget = $this->input->post('widget', false);
		$instance_id = $this->input->post('instance_id', false);
		$fieldname = $this->input->post('fieldname', false);
		$fieldvalue = $this->input->post('fieldvalue', false);
		$data = false;
		$data[$fieldname] = $fieldvalue;
		if (empty($widget) || empty($instance_id) || empty($data) || empty($page))
			return false;
		$widget = Ninja_widget_Model::get($page, $widget, $instance_id);
		$widget->merge_settings($data);
		$widget->save();
	}

	/**
	* Fetch widget setting through ajax call
	*/
	public function get_widget_setting()
	{
		$this->auto_render = false;
		$page = $this->input->post('page', false);
		$widget = $this->input->post('widget', false);
		$page = trim($page);
		$widget = trim($widget);
		$data = Ninja_widget_Model::get_widget($page, $widget, true);
		$setting = $data!==false ? $data->setting : serialize(array(false));
		echo json_encode(i18n::unserialize($setting));
	}

		/**
	*	Set a refresh rate for all widgets on a page.
	*/
	public function set_widget_refresh()
	{
		$this->auto_render = false;
		$page = $this->input->post('page', false);
		$value = $this->input->post('value', false);
		$type = $this->input->post('type', false);
		$success = Ninja_widget_Model::update_all_widgets($page, $value, $type);
		echo json_encode(array('success' => $success));
	}

	/**
	 * A "factory reset" is defined as "undefined, fairly evenly distributed
	 * widgets with default settings"
	 */
	 public function factory_reset_widgets()
	 {
		$this->auto_render = false;
		$username = user::session('username');
		$db = Database::instance();
		$db->query('DELETE FROM ninja_widgets WHERE username = ' . $db->escape($username));
		$res = $db->query('SELECT setting FROM ninja_settings WHERE type=\'widget_order\' AND username = \'\'');
		if (empty($res)) {
			$setting = '';
		} else {
			$row = $res->current();
			$setting = $row->setting;
		}
		$db->query('UPDATE ninja_settings SET setting='.$db->escape($setting).' WHERE type = \'widget_order\' AND username = '. $db->escape($username));
		echo json_encode(array('success' => true));
	 }

	public function copy_widget_instance() {
		$this->auto_render = false;
		$page = $this->input->post('page');
		$widget = $this->input->post('widget');
		$instance_id = $this->input->post('instance_id');
		$widget = Ninja_widget_Model::get($page, $widget, $instance_id);
		$dup_widget = $widget->copy();
		echo widget::add($dup_widget, $this);
		echo '<script type="text/javascript">'.$this->inline_js.'</script>';
	}
}
