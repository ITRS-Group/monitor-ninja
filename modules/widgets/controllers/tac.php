<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Tactical overview controller
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
class Tac_Controller extends Ninja_Controller {
	/**
	 * Display a TAC screen
	 * @param $method tac screen name
	 * @param $args not used
	 */
	public function __call($method, $args)
	{
		$this->_verify_access('ninja.tac:read.tac');
		$this->template->content = $this->add_view('tac/index');
		$this->template->title = _('Monitoring » Tactical overview');
		$this->template->js[] = 'modules/widgets/views/js/tac.js';
		$this->template->disable_refresh = true;

		$page = 'tac/'.$method;

		/* Fetch a set representing all widgets for the page */
		$widget_set = Ninja_WidgetPool_Model::all()->reduce_by('page', $page, '=');

		/* Generate widgets, order per tag */
		$widgets_per_tag = array();
		foreach ($widget_set as $widget_model) {
			$widget = $widget_model->build();
			if ($widget === false) {
				/* Skip widgets if we have uninstalled them. But don't remove, since it might be temporarly during an upgrade */
				continue;
			}
			$tag = 'widget-' . $widget_model->get_name() . '-' .
				 $widget_model->get_instance_id();
			$widgets_per_tag[$tag] = $widget;
			widget::set_resources($widget, $this);
		}


		/* Build storage for placeholders */
		$tac_column_count_str = config::get('tac.column_count', $page);
		$tac_column_count = array_map('intval',explode(',',$tac_column_count_str));
		$n_placeholders = array_sum( $tac_column_count );

		/* Generate the output widget table */
		$widget_table = array();
		for($i = 0; $i<$n_placeholders; $i++) {
			$widget_table[$i] = array();
		}

		/* Fill the output table, according to saved order in best effort */
		$widget_order = array();
		$widget_order_setting = Ninja_setting_Model::fetch_page_setting('widget_order', $page);
		if ($widget_order_setting !== false && !empty($widget_order_setting->setting)) {
			$widget_order = self::parse_widget_order($widget_order_setting->setting);
		}

		/*
		 * We didn't have any widgets, so we should fetch them according to the
		 * default setup
		 */
		if(count($widgets_per_tag) == 0) {
			/* Tac is empty, fill with default */
			/* Do default as earlier, load everything */
			foreach(Ninja_WidgetPool_Model::get_available_widgets() as $wname => $friendly_name) {
				$widget_model = new Ninja_Widget_Model();
				$widget_model->set_name($wname);
				$widget_model->set_friendly_name($friendly_name);
				$widget_model->set_instance_id(mt_rand(0, 10000000)); // needs to be unique
				$widget_model->set_page($page);
				$widget_model->set_setting(array());
				$widget_model->set_username(op5auth::instance()->get_user()->get_username());
				$widget_model->save();

				$widget = $widget_model->build();
				if ($widget === false) {
					/* Skip widgets if we have uninstalled them. But don't remove, since it might be temporarly during an upgrade */
					continue;
				}

				$tag = 'widget-' . $widget_model->get_name() . '-' .
					 $widget_model->get_instance_id();
				$widgets_per_tag[$tag] = $widget;
				widget::set_resources($widget, $this);
			}
		}

		/* Place known widgets in the correct placeholders */
		foreach ($widget_order as $p_name => $p_content ) {
			if (preg_match ( '/^widget-placeholder([0-9]*)$/', $p_name, $matches )) {
				$p_id = intval ( $matches [1] );
			} else if (is_numeric ( $p_name )) {
				$p_id = intval ( $p_name );
			} else {
				continue;
			}
			foreach ( $p_content as $w_name ) {
				if ($w_name == "")
					continue;
				if (! isset ( $widgets_per_tag [$w_name] ))
					continue;
				$widget_table [$p_id] [] = $widgets_per_tag [$w_name];
				unset ( $widgets_per_tag [$w_name] );
			}
		}

		/* Place widgets that's left equally over the placeholders */
		$p_id = 0;
		foreach( $widgets_per_tag as $w_name => $widget ) {
			$widget_table[$p_id][] = $widget;
			$p_id = ($p_id+1)%$n_placeholders;
		}

		$this->template->content->widgets = $widget_table;
		$this->template->content->tac_column_count = $tac_column_count;

		$this->template->toolbar = $toolbar = new Toolbar_Controller("Tactical Overview");
		$menu = new Menu_Model();

		$menu->set("Add widget", null, null, 'icon-16 x16-sign-add');
		$add_widget_menu = $menu->get("Add widget");

		foreach(Ninja_WidgetPool_Model::get_available_widgets() as $name => $friendly_name) {
			$add_widget_menu->set($friendly_name, "#", null, null,
				array(
					'data-widget-name' => $name,
					'class' => "menuitem_widget_add"
				));
		}
		$toolbar->menu($menu);
	}

	private static function parse_widget_order($setting)
	{
		$widget_order = array();
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
	*	Save location and order of widgets on a page
	*/
	public function on_change_positions()
	{
		$this->auto_render = false;
		// This is a basic functionality of the tac, so keep it to the same permission as tac
		$this->_verify_access('ninja.tac:read.tac');

		$page = $this->input->post('page', false);
		$positions = $this->input->post('positions', false);
		$positions = trim($positions);
		$page = trim($page);
		if (empty($positions) || empty($page))
			return false;

		Ninja_setting_Model::save_page_setting('widget_order', $page, $positions);

		return json::ok(array('result' => 'ok'));
	}

	/**
	 * Create a new widget of a given type
	 */
	public function on_refresh() {
		$this->auto_render = false;
		// This is a basic functionality of the tac, so keep it to the same permission as tac
		$this->_verify_access('ninja.tac:read.tac');

		$page = $this->input->post('page');
		$widget_name = $this->input->post('name');
		$widget_instance_id = $this->input->post('instance_id');
		$username = op5auth::instance()->get_user()->get_username();

		$widget_model = Ninja_WidgetPool_Model::all()->reduce_by('page', $page, '=')
			->reduce_by('name', $widget_name, '=')
			->reduce_by('instance_id', $widget_instance_id, '=')
			->reduce_by('username', $username, '=')
			->one();
		if (! ($widget_model instanceof Ninja_Widget_Model)) {
			echo json::fail(array(
				'result' => 'Unknown widget'
			));
		}

		$widget = $widget_model->build();
		if ($widget === false) {
			echo json::fail(
				array(
					'result' => 'Widget ' . $widget_model->get_name() .
						 ' not installed'
				));
		}

		$result = array(
			'widget' => $widget->render('index', false),
			'instance_id' => $widget_model->get_instance_id()
		);
		echo json::ok($result);
	}

	/**
	 * Create a new widget of a given type
	 */
	public function on_widget_add() {
		$this->auto_render = false;
		// This is a basic functionality of the tac, so keep it to the same permission as tac
		$this->_verify_access('ninja.tac:read.tac');

		$page = $this->input->post('page');
		$widget_name = $this->input->post('widget');

		$widget_model = new Ninja_Widget_Model();
		$widget_model->set_name($widget_name);
		$widget_model->set_page($page);
		$widget_model->set_instance_id(time()); // FIXME: increment id nicely

		/* We need to build the widget to get the default friendly name */
		$widget = $widget_model->build();
		if ($widget === false) {
			echo json::fail(
				array(
					'result' => 'Widget ' . $widget_model->get_name() .
						 ' not installed'
				));
		}

		$metadata = $widget->get_metadata();
		if(!$metadata['instanceable']) {
			echo json::fail(
				array(
					'result' => 'Widget ' . $widget_model->get_name() .
						 ' can not be created'
				));
		}
		$widget_model->set_friendly_name($metadata['friendly_name']);
		$widget_model->save();

		$result = array(
			'widget' => $widget->render('index', true),
			'instance_id' => $widget_model->get_instance_id()
		);
		echo json::ok($result);
	}

	/**
	 * Remove widget
	 */
	public function on_widget_remove() {
		$this->auto_render = false;
		// This is a basic functionality of the tac, so keep it to the same permission as tac
		$this->_verify_access('ninja.tac:read.tac');

		$page = $this->input->post('page');
		$widget_name = $this->input->post('name');
		$widget_instance_id = $this->input->post('instance_id');
		$username = op5auth::instance()->get_user()->get_username();

		$widget = Ninja_WidgetPool_Model::all()->reduce_by('page', $page, '=')
			->reduce_by('name', $widget_name, '=')
			->reduce_by('instance_id', $widget_instance_id, '=')
			->reduce_by('username', $username, '=')
			->one();

		if($widget instanceof Ninja_Widget_Model) {
			$widget->delete();
			echo json::ok(array('result' => 'ok'));
		}

		echo json::fail(array('result' => 'error'));
	}

	/**
	 * Create a new widget of a given type
	 */
	public function on_widget_rename() {
		$this->auto_render = false;
		// This is a basic functionality of the tac, so keep it to the same permission as tac
		$this->_verify_access('ninja.tac:read.tac');

		$page = $this->input->post('page');
		$widget_name = $this->input->post('name');
		$widget_instance_id = $this->input->post('instance_id');
		$new_name = $this->input->post('new_name');
		$username = op5auth::instance()->get_user()->get_username();

		$widget = Ninja_WidgetPool_Model::all()->reduce_by('page', $page, '=')
			->reduce_by('name', $widget_name, '=')
			->reduce_by('instance_id', $widget_instance_id, '=')
			->reduce_by('username', $username, '=')
			->one();

		if ($widget instanceof Ninja_Widget_Model) {
			$widget->set_friendly_name($new_name);
			$widget->save();
			echo json::ok(array('result' => 'ok'));
		}

		echo json::fail(array('result' => 'error'));
	}

	/**
	 * Create a new widget of a given type
	 */
	public function on_widget_save_settings() {
		$this->auto_render = false;
		// This is a basic functionality of the tac, so keep it to the same permission as tac
		$this->_verify_access('ninja.tac:read.tac');

		$page = $this->input->post('page');
		$widget_name = $this->input->post('name');
		$widget_instance_id = $this->input->post('instance_id');
		$setting = $this->input->post('setting');
		$username = op5auth::instance()->get_user()->get_username();

		$widget = Ninja_WidgetPool_Model::all()->reduce_by('page', $page, '=')
			->reduce_by('name', $widget_name, '=')
			->reduce_by('instance_id', $widget_instance_id, '=')
			->reduce_by('username', $username, '=')
			->one();

		if ($widget instanceof Ninja_Widget_Model) {
			$widget->set_setting($setting);
			$widget->save();
			echo json::ok(array('result' => 'ok'));
		}

		echo json::fail(array('result' => 'error'));
	}
}
