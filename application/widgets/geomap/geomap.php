<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Geomap widget
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 */
class Geomap_Widget extends widget_Base {
	public function __construct($model)
	{
		parent::__construct($model);

		$_SESSION['nagvis_user'] = user::session('username');
	}

	public function options()
	{
		$mapmodel = new Nagvis_Maps_Model;
		$map_array = $mapmodel->get_list();
		foreach ($map_array as $map) {
			$all_maps[$map] = $map;
		}
		$all_maps['geomap'] = 'geomap';
		$all_maps['automap'] = 'automap';

		$options = parent::options();
		$options[] = new option($this->model->name, 'geomap_map', 'Map', 'dropdown', array('options' => $all_maps), 'geomap');
		$options[] = new option($this->model->name, 'geomap_height', 'Height (px)', 'input', array('size'=>3, 'type'=>'text'), 400);
		return $options;
	}

	public function index()
	{
		$arguments = $this->get_arguments();
		$view_path = $this->view_path('view');
		require($view_path);
	}
}
