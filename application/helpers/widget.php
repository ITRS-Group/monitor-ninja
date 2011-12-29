<?php defined('SYSPATH') OR die('No direct access allowed.');
require_once(Kohana::find_file(Kohana::config('widget.dirname'), 'widget_Base'));

function widget_error_handler($a, $b, $c, $d)
{
	throw new ErrorException($b, 0, $a, $c, $d);
}

/**
 * widget helper class.
 */
class widget_Core
{
	private static $with_chrome = true;

	public static function set_show_chrome($with_chrome) {
		self::$with_chrome = $with_chrome;
	}

	public static function add_widgets($page, $widgets, $master) {
		$order = Ninja_widget_Model::fetch_widget_order($page);
		if (is_array($order)) {
			foreach ($order as $placeholder => $widget_names) {
				$order[$placeholder] = array();
				foreach ($widget_names as $idx => $widget_name) {
					# upgrade from pre-instance-id widget_order string
					if (!isset($widgets[$widget_name]))
						$widget_name = $widget_name.'-1';
					if (!isset($widgets[$widget_name])) {
						unset($order[$placeholder][$idx]);
						continue;
					}
					$order[$placeholder][$widget_name] = self::add($widgets[$widget_name], $master);
					unset($widgets[$widget_name]);
				}
			}
		}
		if(!empty($widgets)) {
			foreach ($widgets as $idx => $widget) {
				$order['unknown'][$idx] = self::add($widget, $master);
			}
		}

		$master->xtra_js = array_unique($master->xtra_js);
		$master->xtra_css = array_unique($master->xtra_css);

		return $order;
	}

	/**
	 * Add a new widget
	 */
	public static function add($widget_obj, $master)
	{
		if (!isset($widget_obj->id) || !$widget_obj->id)
			return;
		set_error_handler('widget_error_handler', error_reporting());
		try {
			$obj = self::get($widget_obj);
			$out = $obj->render('index', self::$with_chrome);
		} catch (Exception $ex) {
			if (ob_get_level() > 2)
				ob_end_clean();
			require_once(Kohana::find_file(Kohana::config('widget.dirname').'error', 'error'));
			$obj = new Error_Widget($widget_obj, $ex);
			$out = $obj->render('index', self::$with_chrome);
		}
		restore_error_handler();
		self::set_resources($obj, $master);
		return $out;
	}

	public static function get($widget_obj)
	{
		# first try custom path
		$path = Kohana::find_file(Kohana::config('widget.custom_dirname').$widget_obj->name, $widget_obj->name, false);
		if ($path === false) {
			# try core path if not found in custom
			$path = Kohana::find_file(Kohana::config('widget.dirname').$widget_obj->name, $widget_obj->name, true);
		}
		if (!is_file($path))
			throw new Exception("Widget not found on disk");
		require_once($path);
		$classname = $widget_obj->name.'_Widget';

		return new $classname($widget_obj);
	}

	public static function set_resources($widget, $master) {
		if (!request::is_ajax()) {
			$master->xtra_js = array_merge(isset($master->xtra_js) && is_array($master->xtra_js) ? $master->xtra_js : array(), $widget->resources($widget->js, 'js'));
			$master->xtra_css = array_merge(isset($master->xtra_css) && is_array($master->xtra_css) ? $master->xtra_css : array(), $widget->resources($widget->css, 'css'));
			$master->inline_js .= $widget->inline_js;
			if ($widget->model) {
				$widget_id = 'widget-'.$widget->model->name.'-'.$widget->model->instance_id;
				$master->inline_js .= "$.fn.AddEasyWidget('#$widget_id', \$('#$widget_id').parent().id, window.easywidgets_obj);";
			}
		}
	}

	public function __construct() {
		throw new Exception("This widget needs to be ported to the new widget API. See ".APPPATH."widgets/PORTING_GUIDE");
	}
}
