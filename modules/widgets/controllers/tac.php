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
	 * Get the add widget menu
	 *
	 * exported as a seperate function due to testability
	 */
	public function _get_add_widget_menu() {
		$menu = new Menu_Model();

		$menu->set("Add widget", null, null);
		$add_widget_menu = $menu->get("Add widget");

		/* Fill with metadata, and build menu */
		$order = 0;
		foreach(Dashboard_WidgetPool_Model::get_available_widgets() as $name => $metadata) {
			$add_widget_menu->set($metadata['friendly_name'], "#", $order, null,
				array(
					'data-widget-name' => $name,
					'class' => "menuitem_widget_add"
				));
			if(isset($metadata['css'])) {
				foreach($metadata['css'] as $stylesheet) {
					$this->template->css[] = $metadata['path'] . $stylesheet;
				}
			}
			$order++; /* We want the rows in the order they appear. They are already sorted */
		}
		return $menu;
	}

	/**
	 * Get the select layout menu
	 */
	private function get_select_layout_menu() {
		$menu = new Menu_Model();

		$menu->set("Select layout", null, null);
		$select_layout_menu = $menu->get("Select layout");

		$layout = $this->_current_dashboard()->get_layout();

		$img_url = url::base() . '/application/views/icons/layout-132.png';
		$name = "1,3,2";
		$select_layout_menu->set($name, "#", null, $img_url, array(
			'data-layout-name' => $name,
			'class' => "menuitem_change_layout",
			'selected' => $layout == $name ? 'yes' : 'no'
		));

		$img_url = url::base() . '/application/views/icons/layout-321.png';
		$name = "3,2,1";
		$select_layout_menu->set($name, "#", null, $img_url, array(
			'data-layout-name' => $name,
			'class' => "menuitem_change_layout",
			'selected' => $layout == $name ? 'yes' : 'no'
		));

		return $menu;
	}

	/**
	 * Get the current dashboard
	 *
	 * public, but not exposed (prefix with _) due to testability
	 */
	public function _current_dashboard() {
		/* Just pick the first dashboard... (we only have access to our own) */
		$dashboard = DashboardPool_Model::all()->one();
		if(!$dashboard) {
			/* We don't have a dashboard, create one */
			$dashboard = new Dashboard_Model();
			$username = op5auth::instance()->get_user()->get_username();
			$dashboard->set_username($username);

			$dashboard->import_array(Kohana::config('tac.default'));

			$dashboard->set_name('Dashboard for '.$username);
			$dashboard->save();
		}

		return $dashboard;
	}

	/**
	 * Display a TAC screen
	 */
	public function index()	{
		$this->_verify_access('ninja.tac:read.tac');

		$dashboard = $this->_current_dashboard();

		$layout = $this->input->post('layout');
		if ($layout !== null) {
			widget::convert_layout($dashboard, $layout);
		}

		$this->template->content = $this->add_view('tac/index');
		$this->template->title = _('Monitoring » Tactical overview');
		$this->template->js[] = 'modules/widgets/views/js/tac.js';
		$this->template->content_class = 'dashboard';
		$this->template->disable_refresh = true;

		/* Build storage for placeholders */
		$tac_column_count_str = $dashboard->get_layout();
		$tac_column_count = array_map('intval',explode(',', $tac_column_count_str));
		$n_dashboard_cells = array_sum($tac_column_count);

		/* Generate the output widget table */
		$widget_table = array();
		for($i = 0; $i < $n_dashboard_cells; $i++) {
			$widget_table[$i] = array();
		}

		$widget_models = $dashboard->get_dashboard_widgets_set();

		/* Place widgets that's left equally over the placeholders */
		foreach ($widget_models as $model) {
			$pos = $model->get_position();
			/* No cell number is same as incorrect posistion */
			if (is_array($pos) && isset($pos['c']) && $pos['c'] >= 0 && $pos['c'] < $n_dashboard_cells) {
				if(isset($pos['p']) && !isset($widget_table[$pos['c']][$pos['p']])) {
					/* If set, and not a conflict, add correctly... */
					$widget_table[$pos['c']][$pos['p']] = $model->build();
				} else {
					/* ...otherwise place at end of cell */
					$widget_table[$pos['c']][] = $model->build();
				}
			}
			else {
				// If we can't parse position, place widget in last cell.
				$widget_table[$n_dashboard_cells - 1][] = $model->build();
			}
		}

		// We need to make sure all indexes comes in order (they may actually not).
		foreach ($widget_table as &$cell) {
			ksort($cell);
		}

		$this->template->content->widgets = $widget_table;
		$this->template->content->tac_column_count = $tac_column_count;

		$this->template->toolbar = $toolbar = new Toolbar_Controller("Tactical Overview");

		$toolbar->menu($this->_get_add_widget_menu());
		$toolbar->image_menu($this->get_select_layout_menu());
	}

	/**
	 * Save new positions for widgets.
	 * $_POST['positions'] is used through $this->input->post(). It should
	 * contain the widgets and their positions. A weird home-made format is
	 * used at the moment (but is converted into JSON below).
	 */
	public function on_change_positions() {
		// This is a basic functionality of the tac,
		// so keep it to the same permission as tac
		$this->_verify_access('ninja.tac:read.tac');

		$positions = $this->input->post('positions', false);

		// Parse position data from frontend
		$placeholders = explode('|', $positions);
		$pos_data = array_map(
			function ($ph) {
				$values = explode('=', $ph);
				if ($values[1] == '') return array();
				return explode(',', $values[1]);
			},
			$placeholders
		);

		// Loop through position data and save to widgets
		$c_count = count($pos_data);
		for ($i = 0; $i < $c_count; $i++) {
			$p_count = count($pos_data[$i]);
			for ($j = 0; $j < $p_count; $j++) {
				$widget_model_id = substr($pos_data[$i][$j], 7);
				$widget_model = $this->_current_dashboard()
					->get_dashboard_widgets_set()
					->intersect(Dashboard_WidgetPool_Model::set_by_key($widget_model_id))
					->one();

				if ($widget_model) {
					$widget_model->set_position(array('c' => $i, 'p' => $j));
					$widget_model->save();
				}
			}
		}
		$this->template = new View('json');
		$this->template->success = true;
		$this->template->value = array('result' => $pos_data);
	}

	/**
	 * Refresh the content of a widget.
	 * $_POST['key'] is used through $this->input->post(). It should contain
	 * the ID for the widget that should be refreshed.
	 */
	public function on_refresh() {
		// This is a basic functionality of the tac,
		// so keep it to the same permission as tac
		$this->_verify_access('ninja.tac:read.tac');

		$dashboard = $this->_current_dashboard();
		$widget_model = $dashboard->get_dashboard_widgets_set()->intersect(
			Dashboard_WidgetPool_Model::set_by_key($this->input->post('key'))
		)->one();

		if (!$widget_model instanceof Widget_Model) {
			$this->template = new View('json');
			$this->template->success = false;
			$this->template->value = array('result' => 'Unknown widget');
			return;
		}

		// If an error occurs when building widget this will result in a
		// "dead widget" containing an error message.
		$widget = $widget_model->build();

		$custom_title = '';
		$setting = $widget_model->get_setting();
		if (isset($setting['title'])) {
			$custom_title = $setting['title'];
		}

		// We need to provide both the calculated title for rendering,
		// and the value for the settings form.
		$this->template = new View('json');
		$this->template->success = true;
		$this->template->value = array(
			'widget' => $widget->render('index', false),
			'title' => $widget->get_title(),
			'custom_title' => $custom_title
		);
	}

	/**
	 * Create a new widget of a given type.
	 * $_POST['cell'] is used through $this->input->post(). It is expected to
	 * end with a number. The number should correspond to the cell where the
	 * widget is added.
	 * $_POST['widget'] is also used and is expected to be a widget name that
	 * corresponds to a predefined widget.
	 */
	public function on_widget_add() {
		// This is a basic functionality of the tac,
		// so keep it to the same permission as tac
		$this->_verify_access('ninja.tac:read.tac');

		// $cell_num should be equal to the number in the end of $cell_name.
		$cell_name = $this->input->post('cell');
		$numbers = array();
		$cell_num = 0;
		if (preg_match_all('/\d+/', $cell_name, $numbers))
			$cell_num = intval(end($numbers[0]));

		// We need to update the position of all widgets in the cell to which
		// the widget is added.
		$dashboard = $this->_current_dashboard();
		$widget_models = $dashboard->get_dashboard_widgets_set();

		foreach ($widget_models as $wm) {
			$pos = $wm->get_position();
			if ($pos['c'] === $cell_num) {
				// Move widget one step "down" if it's in the cell
				// where we add the new widget.
				$pos['p'] += 1;
				$wm->set_position($pos);
				$wm->save();
			}
		}

		$widget_name = $this->input->post('widget');

		// Create new widget at position 0.
		$widget_model = new Dashboard_Widget_Model();
		$widget_model->set_dashboard_id($dashboard->get_id());
		$widget_model->set_name($widget_name);
		$widget_model->set_position(array(
			'c' => $cell_num,
			'p' => 0
		));
		$widget_model->set_setting(array());
		$widget_model->save();

		// We need to build the widget to get the default friendly name.
		// If an error occurs when building widget this will result in a
		// "dead widget" containing an error message.
		$widget = $widget_model->build();

		$metadata = $widget->get_metadata();
		if (!$metadata['instanceable']) {
			$res = 'Widget ' . $widget_model->get_name() . ' can not be created';
			$this->template->success = false;
			$this->template->value = array('result' => $res);
			return;
		}

		$this->template = new View('json');
		$this->template->success = true;
		$this->template->value = array(
			'widget' => $widget->render('index', true),
			'key'    => $widget_model->get_key()
		);
	}

	/**
	 * Remove widget.
	 * $_POST['key'] is used through $this->input->post() and should contain
	 * the ID of the widget to delete.
	 */
	public function on_widget_remove() {
		// This is a basic functionality of the tac,
		// so keep it to the same permission as tac
		$this->_verify_access('ninja.tac:read.tac');

		$widget_key = $this->input->post('key');

		$dashboard_set = $this->_current_dashboard()->get_dashboard_widgets_set();
		$widget_model = $dashboard_set->intersect(
			Dashboard_WidgetPool_Model::set_by_key($this->input->post('key'))
		)->one();

		$this->template = new View('json');
		if ($widget_model instanceof Dashboard_Widget_Model) {
			$widget_model->delete();
			$this->template->success = true;
			$this->template->value = array('result' => 'ok');
			return;
		}

		$this->template->success = false;
		$this->template->value = array('result' => 'error');
	}

	/**
	 * Save settings for a widget.
	 * $_POST['key'] is used through $this->input->post() and should contain
	 * the ID of the widget to update.
	 * $_POST['setting'] is also used should contain the settings to save.
	 */
	public function on_widget_save_settings() {
		$this->_verify_access('ninja.tac:read.tac');

		$key = $this->input->post('key');
		$this->template = new View('json');
		if (!$key) {
			$this->template->success = false;
			$this->template->value = array(
				'result' => 'No widget ID submitted, cannot update widget.'
			);
			return;
		}

		$setting = $this->input->post('setting');
		if (!$setting) {
			$this->template->success = true;
			$this->template->value = array(
				'result' => 'Did not update anything because there were ' .
					'no new settings submitted.'
			);
			return;
		}

		$current_dashboard = $this->_current_dashboard();
		$widget_model = $current_dashboard->get_dashboard_widgets_set()
			->intersect(Dashboard_WidgetPool_Model::set_by_key($key))->one();
		if (!$widget_model instanceof Widget_Model) {
			$this->template->success = false;
			$this->template->value = array (
					'result' => 'Could not find a widget with that ID'
			);
			return;
		}

		$widget_model->set_setting($setting);
		$widget_model->save();

		$this->template->success = true;
		$this->template->value = array(
			'result' => 'ok',
		);
	}
}
