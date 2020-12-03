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
	 * Iterate through all widgets one time, to add the widgets' assets.
	 * If you call this method with the menu parameter, all of the widgets
	 * will also be attached to that menu.
	 *
	 * @param $menu Menu_Model = null
	 */
	public function _attach_assets_for_all_widgets(Menu_Model $menu = null) {
		/* Fill with metadata, and build menu */
		$order = 0;
		foreach(Dashboard_WidgetPool_Model::get_available_widgets() as $name => $metadata) {
			if($menu) {
				$menu->set($metadata['friendly_name'], "#", $order, null,
					array(
						'data-widget-name' => $name,
						'class' => "menuitem_widget_add"
					));
			}
			if(isset($metadata['css'])) {
				foreach($metadata['css'] as $stylesheet) {
					$this->template->css[] = $metadata['path'] . $stylesheet;
				}
			}
			if(isset($metadata['js'])) {
				foreach($metadata['js'] as $js) {
					$this->template->js[] = $metadata['path'] . $js;
				}
			}
			$order++; /* We want the rows in the order they appear. They are already sorted */
		}
	}
	/**
	 * Get the select layout menu
	 */
	private function get_select_layout_menu(Dashboard_Model $dashboard) {
		$menu = new Menu_Model("Select layout");
		$menu->set_style('image');

		$layout = $dashboard->get_layout();

		$img_url = url::base() . '/application/views/icons/layout-132.png';
		$name = "1,3,2";
		$menu->set($name, "#", null, $img_url, array(
			'data-layout-name' => $name,
			'class' => "menuitem_change_layout",
			'data-selected' => $layout == $name ? 'yes' : 'no'
		));

		$img_url = url::base() . '/application/views/icons/layout-321.png';
		$name = "3,2,1";
		$menu->set($name, "#", null, $img_url, array(
			'data-layout-name' => $name,
			'class' => "menuitem_change_layout",
			'data-selected' => $layout == $name ? 'yes' : 'no'
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
		} else {
			$dashboard_id = $this->input->post('dashboard_id');
			$dashboard = DashboardPool_Model::fetch_by_key($dashboard_id);
		}

		if (!$dashboard) {
			throw new Exception("No dashboard could be found!");
		}

		return $dashboard;
	}

	/**
	 * When layout is changed it should be posted to this method.
	 */
	public function change_layout() {
		$dashboard = $this->_current_dashboard();

		$layout = $this->input->post('layout');
		if ($layout !== null) {
			$dashboard->set_layout($layout);
			$dashboard->save();
		}

		$this->template = new View( 'simple/redirect', array( 'target' => 'controller',
			'url' => 'tac/index/' . $dashboard->get_id() ) );
	}


	/**
	 * Display a TAC screen
	 */
	public function index($dashboard_id = 0) {
		$this->_verify_access('ninja.tac:read.tac');

		/*
		 * Don't use "_current_dashboard" in index, since we want to be able
		 * to handle that specially. _current_dashboard uses POST field to
		 * select dashboard, and is useful for ajax requesets, without side
		 * effects
		 */
		$dashboard = DashboardPool_Model::fetch_by_key($dashboard_id);
		if (!$dashboard) {

			$dashboard = dashboard::get_default_dashboard();

			/* If dashboard found, show dashboard */
			if($dashboard) {
				$this->template = new View( 'simple/redirect', array( 'target' => 'controller',
					'url' => 'tac/index/' . $dashboard->get_id() ) );
				return;
			}

			/* If there are no dashboards, show a no-dashboards-available page */
			$this->template->content = new View( 'tac/nodashboards' );
			return;
		}

		$this->template->content = $this->add_view('tac/index');
		$this->template->title = 'Monitoring » ' . $dashboard->get_name();
		$this->template->content_class = 'dashboard';
		$this->template->content->dashboard = $dashboard;

		$this->template->js_strings = "var _dashboard_id = ".intval($dashboard->get_id()).";\n";
		$this->template->js_strings .= "var _dashboard_can_write = ".json_encode($dashboard->get_can_write()).";\n";

		/* Build storage for placeholders */
		$tac_column_count_str = $dashboard->get_layout();
		$tac_column_count = array_map('intval',explode(',', $tac_column_count_str));
		$n_dashboard_cells = array_sum($tac_column_count);

		$this->template->content->tac_column_count = $tac_column_count;

		/* Generate the output widget table */
		$widget_table = array();
		for($i = 0; $i < $n_dashboard_cells; $i++) {
			$widget_table[$i] = array();
		}

		$widget_models = $dashboard->get_dashboard_widgets_set();

		if (count($widget_models) == 0) {
			$this->template->content = new View('tac/nowidgets');
		} else {
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
		}

		// We need to make sure all indexes comes in order (they may actually not).
		foreach ($widget_table as &$cell) {
			ksort($cell);
		}

		$this->template->content->widgets = $widget_table;
		$this->template->toolbar = $toolbar = new Toolbar_Controller($dashboard->get_name());

		$menu = new Menu_Model();
		$toolbar->menu($menu);


		if (!dashboard::is_login_dashboard($dashboard)) {
			$menu->set("Dashboard options.Set as login dashboard",
				LinkProvider::factory()->get_url('tac', 'login_dashboard', array('dashboard_id'=> $dashboard->get_id())),
				30, null, array(
					'class' => "menuitem_dashboard_option"
				)
			);
		}

		$add_widget_menu = null;
		// Show certain options only for the logged in user's dashboards
		if($dashboard->get_can_write()) {
			$add_widget_menu = new Menu_Model("Add widget");
			$add_widget_menu->set_order(10);
			$menu->attach("Dashboard options", $add_widget_menu);
			$menu->attach("Dashboard options", $this->get_select_layout_menu($dashboard)->set_order(20));

			if(op5mayi::instance()->run("monitor.system.dashboards.shared:create")) {
				$menu->set("Dashboard options.Share this dashboard",
					LinkProvider::factory()->get_url(
						'tac',
						'share_dashboard',
						array(
							'dashboard_id'=> $dashboard->get_id()
						)
					),
					35,
					null,
					array(
						'class' => "menuitem_dashboard_option"
					)
				);
			}

			$menu->set("Dashboard options.Rename this dashboard",
				LinkProvider::factory()->get_url('tac', 'rename_dashboard', array('dashboard_id'=> $dashboard->get_id())),
				40, null, array(
					'class' => "menuitem_dashboard_option"
				));

			$menu->set("Dashboard options.Delete this dashboard",
				LinkProvider::factory()->get_url('tac', 'delete_dashboard', array('dashboard_id' => $dashboard->get_id())),
				60, null, array(
					'class' => "menuitem_dashboard_option"
				));
		}
		$this->_attach_assets_for_all_widgets($add_widget_menu);
	}

	/**
	 * Render the new dashboard dialog, as an entire page
	 *
	 * So we don't need to render it on every page, fancybox can load the
	 * dialog from an URL.
	 *
	 * Also receives the data from the form in the popup
	 */
	public function new_dashboard() {
		if($_POST) {
			/* If still no dashboard found, Create a default dashboard */
			$user = op5auth::instance()->get_user();
			/* @var $user User_Model */
			$dashboard = new Dashboard_Model();
			$dashboard->set_username( $user->get_username() );
			$dashboard->set_name( html::specialchars($this->input->post( 'name' ) ));
			$dashboard->set_layout( $this->input->post( 'layout', '3,2,1' ) );
			$dashboard->save();
			$this->template = new View( 'simple/redirect', array( 'target' => 'controller',
				'url' => 'tac/index/' . $dashboard->get_id() ) );
			return;
		}

		$lp = LinkProvider::factory();

		$form = new Form_Model(
			$lp->get_url('tac', 'new_dashboard'),
			'POST',
			array(
				new Form_Field_Group_Model('dashboard', array(
					new Form_Field_Text_Model('name', 'Name'),
					new Form_Field_Option_Model('layout', 'Layout', array(
						'3,2,1' => '321',
						'1,3,2' => '132'
					))
				))
			)
		);

		$username = op5auth::instance()->get_user()->get_username();
		$form->set_values(array(
			'name' => $username . ' dashboard',
			'layout' => '3,2,1'
		));

		$form->add_button(new Form_Button_Confirm_Model('save', 'Save'));
		$form->add_button(new Form_Button_Cancel_Model('cancel', 'Cancel'));
		$this->template = $form->get_view();
	}

	/**
	 * Render the share dashboard dialog, as an entire page
	 *
	 * So we don't need to render it on every page, fancybox can load the dialog from an URL
	 */
	public function share_dashboard() {
		$this->_verify_access("monitor.system.dashboards.shared:create");
		if($_POST) {
			$dashboard_id = $this->input->post('dashboard_id');
			$dashboard = DashboardPool_Model::fetch_by_key($dashboard_id);
			if(!$dashboard) {
				$this->template = json::fail_view("No ".
					"dashboard found with the id ".
					"'$dashboard_id'.");
				return;
			}

			if(!$dashboard->get_can_write()) {
				$this->template = json::fail_view("The ".
					"dashboard with the id ".
					"'$dashboard_id' doesn't seem to be ".
					"yours. You cannot share it, only ".
					"the dashboard owner can do that.");
				return;
			}

			$table = $this->input->post('table');
			if(!in_array($table, array('usergroups', 'users'), true)) {
				$this->template = json::fail_view("Bad value for 'table'.");
				return;
			}

			$object_key = $this->input->post($table);
			if(!$object_key || !$object_key['value']) {
				$this->template = json::fail_view("Missing ".
					"value for who to share to, check ".
					"your input");
				return;
			}

			if($table == "users" && $object_key["value"] == op5auth::instance()->get_user()->get_key()) {
				$this->template = json::fail_view("You cannot ".
					"share a dashboard with yourself.");
				return;
			}

			$dashboard->add_read_perm($table, $object_key['value']);
			$dashboard->save();
			$this->template = json::ok_view($dashboard->get_read_perm());
			return;
		}

		$dashboard_id = $this->input->get('dashboard_id');
		$dashboard = DashboardPool_Model::fetch_by_key($dashboard_id);

		if(!$dashboard) {
			$this->template = json::fail_view("No dashboard found with the id '$dashboard_id'");
			return;
		}

		$share_form = new Form_Model(
			LinkProvider::factory()->get_url('tac', 'share_dashboard'),
			'POST',
			array(
				new Form_Field_Hidden_Model('dashboard_id'),
				new Form_Field_HtmlDecorator_Model('<h2>Share "'.html::specialchars($dashboard->get_name()).'" with (read only):</h2>'),
				new Form_Field_Option_Model(
					'table',
					'Group or user',
					array(
						'usergroups' => 'Group',
						'users' => 'User'
					),
					'select'
				),
				new Form_Field_Conditional_Model(
					'table',
					'usergroups',
					new Form_Field_ORMObject_Model(
						'usergroups',
						'Group',
						array(
							'usergroups'
						)
					)
				),
				new Form_Field_Conditional_Model(
					'table',
					'users',
					new Form_Field_ORMObject_Model(
						'users',
						'User',
						array(
							'users'
						)
					)
				),
			)
		);

		$share_form->set_values(array(
			'dashboard_id' => $dashboard->get_id(),
		));

		$share_form->add_button(new Form_Button_Confirm_Model('share', 'Share'));

		$listing = new View('share_dashboard_listing');
		$listing->shared_with = $dashboard->get_read_perm();
		$listing->dashboard_id = $dashboard->get_id();

		$outer_form = new Form_Model('#', 'POST', array(
			new Form_Button_Cancel_Model('close', 'Close')
		));
		$this->template = new View('concat');
		$this->template->views = array(
			$share_form->get_view(),
			$listing,
			$outer_form->get_view(),
		);
	}

	/**
	 * Remove read rights for a user that was given read rights by the
	 * logged in user.
	 */
	public function unshare_dashboard() {
		$this->_verify_access("monitor.system.dashboards.shared:delete");
		if(!$_POST) {
			$this->template = json::fail_view("You should not visit this URL in your browser.");
			return;
		}

		$dashboard_id = $this->input->post('dashboard_id');
		$dashboard = DashboardPool_Model::fetch_by_key($dashboard_id);
		if(!$dashboard) {
			$this->template = json::fail_view("No dashboard found with the id '$dashboard_id'.");
			return;
		}

		$table = $this->input->post('table');
		if(!in_array($table, array('users', 'usergroups'), true)) {
			$this->template = json::fail_view("Bad value for 'table': $table.");
			return;
		}

		$object_key = $this->input->post('key');
		if(!$object_key) {
			$this->template = json::fail_view("Missing ".
				"value for who to share to, check ".
				"your input");
			return;
		}

		$dashboard->remove_read_perm($table, $object_key);
		$dashboard->save();
		$this->template = json::ok_view(sprintf("Unshared the dashboard '%s' from the %s %s.",
			$dashboard->get_name(),
			$table,
			$object_key['value']
		));
	}

	/**
	 * Render the new dashboard dialog, as an entire page
	 *
	 * So we don't need to render it on every page, fancybox can load the dialog from an URL
	 */
	public function rename_dashboard() {
		if($_POST) {
			$dashboard = $this->_current_dashboard();
			if ($dashboard->get_can_write()) {
				$dashboard->set_name( html::specialchars($this->input->post( 'name' ) ));
				$dashboard->save();
			}
			$this->template = new View( 'simple/redirect', array( 'target' => 'controller',
				'url' => 'tac/index/' . $dashboard->get_id() ) );
			return;
		}
		$dashboard_id = $this->input->get('dashboard_id');
		$dashboard = DashboardPool_Model::fetch_by_key($dashboard_id);

		if(!$dashboard) {
			$this->template = json::fail_view("No dashboard found with the id '$dashboard_id'.");
			return;
		}

		$form = new Form_Model(
			LinkProvider::factory()->get_url('tac', 'rename_dashboard'),
			'POST',
			array(
				new Form_Field_Hidden_Model('dashboard_id'),
				new Form_Field_Text_Model('name', 'Name')
			)
		);

		$form->set_values(array(
			'dashboard_id' => $dashboard->get_id(),
			'name' => $dashboard->get_name()
		));

		$form->add_button(new Form_Button_Confirm_Model('save', 'Save'));
		$form->add_button(new Form_Button_Cancel_Model('cancel', 'Cancel'));
		$this->template = $form->get_view();
	}

	/**
	 * Set Current dashboard as Login Dashboard
	 */
	public function login_dashboard() {
		if($_POST) {
			$user = op5auth::instance()->get_user();
			$dashboard = $this->_current_dashboard();

			/* Login dashboard setting already available update it else create setting */
			$login_dashboard = SettingPool_Model::all()
				->reduce_by('username', $user->get_username(), '=')
				->reduce_by('type', 'login_dashboard', '=')
				->one();

			if($login_dashboard) {
				$login_dashboard->set_setting($dashboard->get_id());
				$login_dashboard->save();
			}else {
				$login_dashboard = new Setting_Model();
				$login_dashboard->set_username($user->get_username());
				$login_dashboard->set_type('login_dashboard');
				$login_dashboard->set_setting($dashboard->get_id());
				$login_dashboard->save();
			}

			$this->template = new View( 'simple/redirect', array( 'target' => 'controller',
				'url' => 'tac/index/' . $dashboard->get_id() ) );
			return;
		}

		$dashboard_id = $this->input->get('dashboard_id');
		$dashboard = DashboardPool_Model::fetch_by_key($dashboard_id);

		$form = new Form_Model(
			LinkProvider::factory()->get_url('tac', 'login_dashboard'),
			'POST',
			array(
				new Form_Field_Hidden_Model('dashboard_id'),
				new Form_Field_HtmlDecorator_Model('Set dashboard "' . html::specialchars($dashboard->get_name()) .  '" as login dashboard?')
			)
		);

		$form->set_values(array(
			'dashboard_id' => $dashboard->get_id()
		));

		$form->add_button(new Form_Button_Confirm_Model('save', 'Save'));
		$form->add_button(new Form_Button_Cancel_Model('cancel', 'Cancel'));

		$this->template = $form->get_view();
	}

	/**
	 * Delete the current dashboard
	 */
	public function delete_dashboard() {
		if($_POST) {
			$dashboard = $this->_current_dashboard();
			/* @var $dashboard Dashboard_Model */
			if ($dashboard->get_can_write()) {
				$dashboard->get_dashboard_widgets_set()->delete();
				$dashboard->delete();
			}
			$this->template = new View( 'simple/redirect', array( 'target' => 'controller', 'url' => 'tac/index' ) );
			return;
		}
		$dashboard_id = $this->input->get('dashboard_id');
		$dashboard = DashboardPool_Model::fetch_by_key($dashboard_id);

		$form = new Form_Model(
			LinkProvider::factory()->get_url('tac', 'delete_dashboard'),
			'POST',
			array(
				new Form_Field_Hidden_Model('dashboard_id'),
				new Form_Field_HtmlDecorator_Model('<p>Are you sure you want to delete this dashboard?</p>'),
				new Form_Field_HtmlDecorator_Model('<p>Deleting a dashboard cannot be undone!</p>')
			)
		);

		$form->set_values(array(
			'dashboard_id' => $dashboard->get_id()
		));

		$form->add_button(new Form_Button_Confirm_Model('yes', 'Delete'));
		$form->add_button(new Form_Button_Cancel_Model('cancel', 'Cancel'));
		$this->template = $form->get_view();
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

		$dashboard = $this->_current_dashboard();
		if (! $dashboard->get_can_write()) {
			$this->template = new View( 'json' );
			$this->template->success = false;
			return;
		}

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
		$data = array(
			'widget' => $widget->render('index', false),
			'title' => $widget->get_title(),
			'custom_title' => ''
		);

		$setting = $widget_model->get_setting();
		if (isset($setting['title'])) {
			$data['custom_title'] = $setting['title'];
		}

		if (isset($setting['refresh_interval'])) {
			$data['refresh_interval'] = $setting['refresh_interval'];
		}

		// We need to provide both the calculated title for rendering,
		// and the value for the settings form.
		$this->template = new View('json');
		$this->template->success = true;
		$this->template->value = $data;
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

		$dashboard = $this->_current_dashboard();
		if (! $dashboard->get_can_write()) {
			$this->template = new View( 'json' );
			$this->template->success = false;
			return;
		}

		// $cell_num should be equal to the number in the end of $cell_name.
		$cell_name = $this->input->post('cell');
		$numbers = array();
		$cell_num = 0;
		if (preg_match_all('/\d+/', $cell_name, $numbers))
			$cell_num = intval(end($numbers[0]));

		// We need to update the position of all widgets in the cell to which
		// the widget is added.
		$widget_models = $dashboard->get_dashboard_widgets_set();

		$tac_column_count_str = $dashboard->get_layout();
		$tac_column_count = array_map('intval',explode(',', $tac_column_count_str));
		$n_dashboard_cells = array_sum($tac_column_count);

		foreach ($widget_models as $wm) {
			$pos = $wm->get_position();
			if(!$pos) {
				// A saved widget had incorrect (none at all)
				// values for its positioning. Let's "auto
				// heal" the database, even if the looks might
				// get wonky. We reuse the logic of index() to
				// place older, misconfigured (missing value
				// for merlin.dashboard_widgets.position)
				// widgets in the last cell.
				$wm->set_position(array(
					'c' => $n_dashboard_cells - 1,
					'p' => 0
				));
				$wm->save();
				$pos = $wm->get_position();
			}
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
		$widget_model->set_name(htmlspecialchars($widget_name, ENT_NOQUOTES));
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

		$dashboard = $this->_current_dashboard();
		if (! $dashboard->get_can_write()) {
			$this->template = new View( 'json' );
			$this->template->success = false;
			return;
		}

		$widget_key = $this->input->post('key');

		$dashboard_set = $this->_current_dashboard()->get_dashboard_widgets_set();
		$widget_model = $dashboard_set->intersect(
			Dashboard_WidgetPool_Model::set_by_key($widget_key)
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

		$dashboard = $this->_current_dashboard();
		if (! $dashboard->get_can_write()) {
			$this->template = new View( 'json' );
			$this->template->success = false;
			return;
		}

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

		// see if the widget is backed by a Form_Model, in that case,
		// perform the Form_Model's validation and react accordingly
		$widget = $widget_model->build();
		$widget_options = $widget->options();
		if($widget_options instanceof Form_Model) {
			try {
				$setting = $widget->options()->process_data($setting);
			} catch(FormException $e) {
				$this->template->success = false;
				$this->template->value = array (
					'result' => $e->getMessage()
				);
				return;
			}
		}

		foreach ($setting as $key => $value) {
			if ($value instanceof Object_Model) {
				$setting[$key] = array(
					"table" => $value->get_table(),
					"value" => $value->get_key()
				);
			}
		}

		$widget_model->set_setting($setting);
		$widget_model->save();

		$this->template->success = true;
		$this->template->value = array(
			'result' => 'ok',
		);
	}

	/**
	 * Echo a helptext based on input id
	 *
	 * @param $id string
	 */
	public static function _helptexts($id) {
		$helptexts = array(
			'bignumber_show_filter' => _('The full set to operate on, i.e. the total.'),
			'bignumber_with_selection' => _('The subset to operate on, i.e. part of the total.'),
			'bignumber_threshold_as' => _('For Lower than:<br>Filter selection percentage is lower than threshold.<br><br>For Higher than:<br>Filter selection percentage is higher than threshold'),
		);
		if(array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
			return;
		}
		echo sprintf(_("This helptext ('%s') is not translated yet"), $id);
	}
}
