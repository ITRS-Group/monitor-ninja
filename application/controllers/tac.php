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
	public function __call($method, $args)
	{
		$this->_verify_access('ninja.tac:read');
		$this->template->content = $this->add_view('tac/index');
		$this->template->title = _('Monitoring » Tactical overview');
		$this->template->js[] = $this->add_path('/js/widgets.js');
		$this->template->disable_refresh = true;

		# make sure we have this done before letting widgets near
		$model = Current_status_Model::instance();
		$model->analyze_status_data();

		# fetch data for all widgets
		$widget_objs = Ninja_widget_Model::fetch_all(Router::$controller.'/'.Router::$method);
		$db_widgets = widget::add_widgets(Router::$controller.'/'.Router::$method, $widget_objs, $this);

		if (empty($db_widgets) && $method=='index') {
			/* No Empty defalut tac isn't allowed, populate with the set of widgets */
			foreach ($widget_objs as $obj) {
				$obj->save();
			}
			$db_widgets = widget::add_widgets(Router::$controller.'/'.Router::$method, $widget_objs, $this);
		}
		
		if(empty($db_widgets)) {
			/* Force the widgets variable to be an array...
			 * empty array kohana-style is defined as false, by some wierd
			 * reason
			 */
			$db_widgets = array();
		}
		
		$tac_column_count_str = config::get('tac.column_count', 'tac/'.$method);
		
		$tac_column_count = array_map('intval',explode(',',$tac_column_count_str));
		$n_placeholders = array_sum( $tac_column_count );
		
		$widgets = array();
		$unknown_widgets = array();
		
		foreach( $db_widgets as $placeholder => $widgetlist ) {
			$id = false;
			if( preg_match( '/^widget-placeholder([0-9]*)$/', $placeholder, $matches ) ) {
				$id = intval($matches[1]);
			} if( is_numeric( $placeholder ) ) {
				$id = intval($placeholder);
			}
			if( $id !== false && $id >= 0 && $id < $n_placeholders ) {
				if( !isset( $widgets[$id] ) )
					$widgets[$id] = array();
				$widgets[$id] = array_merge( $widgets[$id], $widgetlist );
			} else {
				$unknown_widgets = array_merge( $unknown_widgets, $widgetlist );
			}
		}
		
		$placeholder_id = 0;
		foreach( $unknown_widgets as $name => $widget ) {
			$widgets[$placeholder_id][$name] = $widget;
			$placeholder_id = ($placeholder_id+1)%$n_placeholders;
		}
		
		$this->template->content->widgets = $widgets;
		$this->template->content->tac_column_count = $tac_column_count;
		$this->template->widgets = $widget_objs;
		$this->template->inline_js = $this->inline_js;
	}
}
