<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controller for displaying single widget on an external web
 */
class External_widget_Controller extends Ninja_Controller {
	public function __construct() {
		parent::__construct();
		$this->template = $this->add_view('external_widget');
		$this->template->js = array();
		$this->template->css = array();
		$this->template->current_skin = 'default/';
	}
	/**
	 * Show page with single widget
	 * @param $name str widget name to show, defaults to netw_health
	 * @param $instance_id
	 */
	public function __call($name = null, $instance_id = 1)
	{
		$this->_verify_access('ninja.external_widget:read');

		$conf = Kohana::config('external_widget');

		$page = Router::$controller.'/'.$name;

		if(!isset($conf['widgets'][$name])) {
			header("HTTP/1.1 404 Not Found");
			echo 'Widget not found';
			exit(1);
		}
		$widget_conf = $conf['widgets'][$name];

		$this->template->title = _('External widget');
		$this->template->widget = false;

		$model = new Ninja_Widget_Model();
		$model->set_username(op5auth::instance()->get_user()->get_username());
		$model->set_name($widget_conf['name']);
		$model->set_page($page);
		$model->set_friendly_name($widget_conf['friendly_name']);
		if(isset($widget_conf['setting'])) {
			$model->set_setting($widget_conf['setting']);
		}
		$model->set_instance_id($instance_id);

		/* We need to build the widget to get the default friendly name */
		$widget = $model->build();
		if ($widget !== false) {
			$metadata = $widget->get_metadata();
			if($metadata['instanceable']) {
				$model->set_friendly_name($metadata['friendly_name']);
				$this->template->widget = $widget;
			}
		}
	}
}
