<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Geomap widget
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 */
class Geomap_Widget extends widget_Core {

	public function __construct()
	{
		parent::__construct();

		# needed to figure out path to widget
		$this->set_widget_name(__CLASS__, basename(__FILE__));
	}

	public function index($arguments=false, $master=false)
	{
		$this->master_obj = $master;

		# fetch widget view path
		$view_path = $this->view_path('view');

		# assign variables to widget
		$widget_id = $this->widgetname;
		$refresh_rate = 0;
		$title = $this->translate->_('Geomap');
		if (isset($arguments['widget_title'])) {
			$title = $arguments['widget_title'];
		}

		# let view template know if wrapping div should be hidden or not
		$ajax_call = request::is_ajax() ? true : false;

		# set required extra resources
		$this->js = array('/js/geomap');
		$this->css = array('/css/geomap');

		# fetch widget content
		require_once($view_path);

		if(request::is_ajax()) {
			# output widget content
			echo json::encode($this->output());
		} else {
			# call parent helper to assign all
			# variables to master controller
			return $this->fetch();
		}
	}
}