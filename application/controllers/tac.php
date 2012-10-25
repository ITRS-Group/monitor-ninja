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
class Tac_Controller extends Authenticated_Controller {
	public function index()
	{
		$this->template->content = $this->add_view('tac/index');
		$this->template->title = _('Monitoring » Tactical overview');
		$this->xtra_js[] = $this->add_path('/js/widgets.js');
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->template->content->links = array
		(
			_('logout')     => 'default/logout'
		);

		# make sure we have this done before letting widgets near
		$model = Current_status_Model::instance();
		$model->analyze_status_data();

		# fetch data for all widgets
		$widget_objs = Ninja_widget_Model::fetch_all(Router::$controller.'/'.Router::$method);
		$widgets = widget::add_widgets(Router::$controller.'/'.Router::$method, $widget_objs, $this);

		if (empty($widgets)) {
			# probably a new user, we should populate the widget list
			# yeah, this does Weird Things™ if a user should try to hide everything
			# but that is a silly thing to do, so just blame the user.
			foreach ($widget_objs as $obj) {
				$obj->save();
			}
			$widgets = widget::add_widgets(Router::$controller.'/'.Router::$method, $widget_objs, $this);
		}

		if (array_keys($widgets) == array('unknown')) {
			$nwidgets = count($widgets['unknown']);

			# left column
			$widgets['widget-placeholder'] = array_splice($widgets['unknown'], 0, round($nwidgets/4));

			# middle column
			$widgets['widget-placeholder1'] = array_splice($widgets['unknown'], 0, round($nwidgets/4));

			# right column
			$widgets['widget-placeholder2'] = array_splice($widgets['unknown'], 0, round($nwidgets/4));

			# right column
			$widgets['widget-placeholder3'] = array_splice($widgets['unknown'], 0, round($nwidgets/4));

			# right column
			$widgets['widget-placeholder4'] = array_splice($widgets['unknown'], 0, round($nwidgets/4));

			# full width (placed at bottom)
			$widgets['widget-placeholder5'] = $widgets['unknown'];
			unset($widgets['unknown']);
		} else if (isset($widgets['unknown'])) {
			if(!isset($widgets['widget-placeholder'])) {
				$widgets['widget-placeholder'] = array();
			}
			$widgets['widget-placeholder'] = array_merge($widgets['widget-placeholder'], $widgets['unknown']);
			unset($widgets['unknown']);
		}

		$widgets = array_merge(array(
			'widget-placeholder' => array(),
			'widget-placeholder1' => array(),
			'widget-placeholder2' => array(),
			'widget-placeholder3' => array(),
			'widget-placeholder4' => array(),
			'widget-placeholder5' => array()
		), $widgets);

		$this->template->content->widgets = $widgets;
		$this->template->widgets = $widget_objs;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;
	}
}
