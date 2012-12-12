<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Nagvis widget
 *
 * @package NINJA
 * @author op5 Valerij Stukanov
 * @license GPL
 */
class Nagvis_Widget extends widget_Base {
	protected $duplicatable = true;
	public function options() {
		$maps = nagvisconfig::get_map_list();
		$default = false;
		if (count($maps)) {
			$default = $maps[0];
			foreach (array_keys($maps) as $key) {
				$name = $maps[$key];
				unset($maps[$key]);
				$maps[$name] = $name;
			}
			$tmp = array_values($maps);
		}

		# don't call parent, nagvis reloads itself
		$map = new option('nagvis', 'map', 'Map', 'dropdown', array('options' => $maps), $default);
		$height = new option('nagvis', 'height', 'Height (px)', 'input', array('size'=>3), 400);
		$height->should_render_js(false);
		return array($map, $height);
	}

	public function index() {
		# fetch widget view path
		$view_path = $this->view_path('view');

		# set required extra resources
		$this->js = array('/js/nagvis');

		$arguments = $this->get_arguments();

		# fetch widget content
		require($view_path);
	}
}

