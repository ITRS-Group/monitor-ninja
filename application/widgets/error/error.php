<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Widget for rendering load errors
 */
class Error_Widget extends widget_Base {
	private $message = false;
	public function __construct($widget_obj, $exception) {
		$this->model = $widget_obj;
		if ($this->model == false) {
			$this->model = new stdclass();
			$this->model->name = 'unknown';
			$this->model->instance_id = 1;
			$this->model->friendly_name = 'Unknown widget';
		}
		if (empty($exception))
			$exception = new Exception("Unknown error");
		$this->exception = $exception;

		$this->auto_render = FALSE;
		$this->theme_path = zend::instance('Registry')->get('theme_path');
	}
	public function index() {
		require(dirname(__FILE__).'/view.php');
	}
}
