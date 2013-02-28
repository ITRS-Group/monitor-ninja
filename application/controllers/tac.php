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
	public function __call($method, $args)
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

		if (empty($widgets) && $method=='index') {
			# probably a new user, we should populate the widget list
			# yeah, this does Weird Things™ if a user should try to hide everything
			# but that is a silly thing to do, so just blame the user.
			#
			# But only do it on the main tac...
			foreach ($widget_objs as $obj) {
				$obj->save();
			}
			$widgets = widget::add_widgets(Router::$controller.'/'.Router::$method, $widget_objs, $this);
		}
		
		if(empty($widgets)) {
			# By some wierd reason, empty arrays doesn't exist in the result of
			# Kohana methods, beacuse they somehow thought of using the more
			# explicit way to say empty array, by using "false"
			# That works quite well with array_keys below, so let's to that
			# explicitly too...
			$widgets = array();
		}
		
		$n_placeholders = 7;

		if (array_keys($widgets) == array('unknown')) {
			/* If only unknown widgets, spread them equally-ish */
			$nwidgets = count($widgets['unknown']);

			for( $i=0; $i<$n_placeholders-1; $i++ ) {
				$widgets[$i] = array_splice($widgets['unknown'], 0, round($nwidgets/$n_placeholders));
			}
			# All the rest at the last one
			$widgets[$n_placeholders-1] = $widgets['unknown'];
			
			unset($widgets['unknown']);
		} else if (isset($widgets['unknown'])) {
			/* If unknown widgets exist, place them at the first box */
			if(!isset($widgets[0])) {
				$widgets[0] = array();
			}
			$widgets[0] = array_merge($widgets[0], $widgets['unknown']);
			unset($widgets['unknown']);
		}
		
		/* Support old-style syntax of placeholders */
		for( $i=0; $i<$n_placeholders; $i++ ) {
			$name = 'widget-placeholder' . ($i>0?$i:'');
			if( isset( $widgets[$name] ) ) {
				$widgets[$i] = $widgets[$name];
				unset( $widgets[$name] );
			}
		}

		$this->template->content->widgets = $widgets;
		$this->template->widgets = $widget_objs;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;
	}
}
