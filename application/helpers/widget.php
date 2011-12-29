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

	/**
	 * Set to true to render the widget with chrome, ie the settings and titlebar
	 * Set to false to only render the content.
	 *
	 * @param $with_chrome boolean Whether to show chrome or not
	 */
	public static function set_show_chrome($with_chrome) {
		self::$with_chrome = $with_chrome;
	}

	/**
	 * Render all the widgets in $widgets in the proper order for $page.
	 * Set the widgets' resources to $master.
	 *
	 * @param $page The name of the page to use ordering from
	 * @param $widgets A list of ninja widget model objects
	 * @param $master A parent container, often the caller
	 * @return An array of the placeholders, and the rendered widgets therein
	 */
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
	 * Given a widget model object and a parent object, add the widget's resources
	 * to the parent object and return a string rendering of the widget.
	 *
	 * In other words, this does the combination of get and set_resources, with
	 * some extra error handling.
	 *
	 * Also see add_widgets method, which is probably what you want.
	 *
	 * @param $widget_obj A widget model object to render
	 * @param $master The parent object to set resources on
	 * @returns string The rendered widget output
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

	/**
	 * Given a widget model object, return an instance of the widget class.
	 *
	 * Also see the add method, which is probably what you want.
	 *
	 * @param $widget_obj The widget model object
	 * @return An instance of the widget class
	 */
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

	/**
	 * Extract the external resources from the widget and provides them to the master.
	 *
	 * The master will have the resources assigned to it's xtra_js, xtra_css and inline_js properties.
	 *
	 * Also see the add method, which is probably what you want.
	 *
	 * @param $widget A widget object
	 * @param $master Generally the caller controller, which will then have to provide this to their template
	 */
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
