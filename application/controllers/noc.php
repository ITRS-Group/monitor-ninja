<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * NOC controller
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
class Noc_Controller extends Authenticated_Controller {

	public $model = false;

	public function __construct()
	{
		parent::__construct();
		$this->model = new Current_status_Model();
		#$this->template = $this->add_view('noc');
		$this->check_widgets();
	}

	public function index()
	{
		$this->template->content = $this->add_view('noc/index');
		$this->template->title = $this->translate->_('Monitoring » Tactical overview (NOC)');
		$this->xtra_js[] = $this->add_path('/js/widgets.js');
		$this->template->disable_refresh = true;

		#$this->xtra_js[] = $this->add_path('noc/js/noc');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		#$this->xtra_css[] = 'application/media/css/jmenu.css';
		$this->template->css_header->css = $this->xtra_css;

		# fetch data for all widgets
		$this->model->analyze_status_data();

		# first try to find widgets for this controller
		$widget_info = Ninja_widget_Model::fetch_page_widgets('noc/'.Router::$method, $this->model);
		$widget_order = Ninja_widget_Model::fetch_widget_order('noc/'.Router::$method);

		if (!empty($widget_info)) {
			$settings_widgets = $widget_info['settings_widgets'];
			$settings = $widget_info['settings'];
			$widget_list = $widget_info['widget_list'];
			$this->inline_js .= $widget_info['inline_js'];
			$user_widgets = $widget_info['user_widgets'];

			# add the widgets to the page using user settings or default if not available
			foreach ($widget_list as $widget_name) {
				widget::add($widget_name, $settings[$widget_name], $this);
			}

			$this->template->settings_widgets = $settings_widgets;
			$this->template->user_widgets = $user_widgets;
			$this->template->content->widget_settings = $settings;
		}

		# Validate that we have all the widgets in our order string.
		# If this fails users will get a jGrowl error each time the page
		# reloaded.
		$tmp_arr = array();
		foreach ($widget_order as $place => $widgets) {
			$tmp_arr = array_merge($tmp_arr, (array)$widgets);
		}

		# only continue checks if sizes differs
		if (sizeof($widget_info['widget_list']) != sizeof($tmp_arr)) {
			foreach ($widget_info['widget_list'] as $tmp) {
				if (!in_array('widget-'.$tmp, $tmp_arr)) {
					$widget_order['widget-placeholder'][] = 'widget-'.$tmp;
				}
			}
		}

		# add the inline javascript to master template header
		$this->template->inline_js = $this->inline_js;

		$this->template->content->widget_order = $widget_order;
		$this->template->content->widgets = $this->widgets;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
	}

	/**
	*	check that user has the default widgets connected to this controller
	*/
	public function check_widgets()
	{
		$widgets = Ninja_widget_Model::fetch_widgets('noc/index');

		if (empty($widgets)) {
			# nothing found so copy from tac

			Ninja_setting_Model::copy_widget_order('tac/index', 'noc/index');
			if (!Ninja_widget_Model::fetch_widgets('noc/index', true)) {
				$default_widgets = Ninja_widget_Model::fetch_widgets('tac/index', true);
				if (!empty($default_widgets)) {
					# copy these widgets as default for all users
					foreach ($default_widgets as $d) {
						Ninja_widget_Model::add_widget('noc/index', $d->name, $d->friendly_name);
						echo "Added ".$d->name."<br />";
					}
				}
			}

			$widgets = Ninja_widget_Model::fetch_widgets('tac/index');
			$widget_list = false;
			if (!empty($widgets)) { # just to be on the safe side
				foreach ($widgets as $w) {
					Ninja_widget_Model::copy_to_user($w, 'noc/index');
					$widget_list[] = $w->name;
				}
				if (!empty($widget_list)) {
					# make last check that we have all widgets in the widget_order string
					Ninja_widget_Model::add_to_widget_order('noc/index', $widget_list);
				}
			}
		}
	}
}
