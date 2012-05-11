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
	public function index()
	{
		$this->template->content = $this->add_view('tac/index');
		$this->template->title = $this->translate->_('Monitoring » Tactical overview (NOC)');
		$this->xtra_js[] = $this->add_path('/js/widgets.js');
		$this->template->disable_refresh = true;

		#$this->xtra_js[] = $this->add_path('noc/js/noc');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		#$this->xtra_css[] = 'application/media/css/jmenu.css';
		$this->template->css_header->css = $this->xtra_css;

		# make sure data is analyzed
		$model = Current_status_Model::instance();
		$model->analyze_status_data();

		$widget_objs = Ninja_widget_Model::fetch_all(Router::$controller.'/'.Router::$method);
		$widgets = widget::add_widgets(Router::$controller.'/'.Router::$method, $widget_objs, $this);

		if (empty($widgets)) {
			foreach ($widget_objs as $obj) {
				$obj->save();
			}
			$widgets = widget::add_widgets(Router::$controller.'/'.Router::$method, $widget_objs, $this);
		}

		if (array_keys($widgets) == array('unknown')) {
			$nwidgets = count($widgets['unknown']);
			$widgets['widget-placeholder'] = array_splice($widgets['unknown'], 0, round($nwidgets/3));
			$widgets['widget-placeholder1'] = array_splice($widgets['unknown'], 0, round($nwidgets/3));
			$widgets['widget-placeholder2'] = $widgets['unknown'];
			unset($widgets['unknown']);
		} else if (isset($widgets['unknown'])) {
			$widgets['widget-placeholder'] = array_merge($widgets['widget-placeholder'], $widgets['unknown']);
			unset($widgets['unknown']);
		}

		$this->template->inline_js = $this->inline_js;

		$this->template->content->widgets = $widgets;
		$this->template->widgets = $widget_objs;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
	}
}
