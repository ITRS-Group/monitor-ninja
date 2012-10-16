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
		$nagvis_maps = new Nagvis_Maps_Model();
		$maps = $nagvis_maps->get_list(true);
		$default = false;
		if (count($maps)) {
			$tmp = array_values($maps);
			$default = $tmp[0];
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

