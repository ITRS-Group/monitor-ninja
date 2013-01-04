<?php defined('SYSPATH') OR die('No direct access allowed.');
require_once(Kohana::find_file(Kohana::config('widget.dirname'), 'widget_Base'));

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
class Tac_Controller extends Authenticated_Controller {

	public function index()
	{
		$this->template->content = $this->add_view('tac/index');
		$this->template->title = _('Monitoring » Tactical overview');
		$this->xtra_css[] = $this->add_path('/css/dashinq.css');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->template->disable_refresh = true;

		$this->template->content->links = array
		(
			_('logout') => 'default/logout'
		);

		# make sure we have this done before letting widgets near
		$model = Current_status_Model::instance();
		$model->analyze_status_data();

		# fetch data for all widgets
		$widget_objs = Ninja_widget_Model::fetch_all(Router::$controller.'/'.Router::$method);
		//$widgets = widget::add_widgets(Router::$controller.'/'.Router::$method, $widget_objs, $this);

		$dashinq_widgets = array();

		foreach ($widget_objs as $key => $widget) {
			$path = Kohana::find_file(Kohana::config('widget.custom_dirname').$widget->name, $widget->name, false);
			if ($path === false) {
				# try core path if not found in custom
				$path = Kohana::find_file(Kohana::config('widget.dirname').$widget->name, $widget->name, false);
			}

			if (is_file($path)) { # Widget resource exists

				$settings = Ninja_setting_Model::fetch_page_setting($key, 'tac/index');

				require_once($path);
				$classname = ucfirst($widget->name).'_Widget';
				$wb = new $classname($widget);

				$options = $wb->get_arguments();

				if ($settings) {

					$settings = array($key => $settings);
					$settings = json_decode($settings[$key]->setting, true);

					foreach($settings as $k => $v) {
						$widget->setting[$k] = $v;
					}

				}

				$dashinq_widgets[] = array(
					"friendly_name" => $widget->friendly_name, 
					"name" => $widget->name, 
					"settings" => json_encode($settings), 
					"id" => $key,
					"options" => json_encode($options)
				);
			}
		}

		$this->template->content->dashinq_widgets = $dashinq_widgets;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;

	}
}
