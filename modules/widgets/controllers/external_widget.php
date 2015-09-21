<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controller for displaying single widget on an external web
 */
class External_widget_Controller extends Ninja_Controller {
	public function __construct() {
		parent::__construct();
		$this->template = $this->add_view('external_widget');
		$this->template->js = array();
		$this->template->current_skin = 'default/';
	}

	/**
	 * Show page with single widget
	 * @param $name str widget name to show, defaults to netw_health
	 * @param $instance_id
	 */
	public function show_widget($name = null, $instance_id = 1)
	{
		$page = Router::$controller.'/'.Router::$method;

		if (is_null($name)) {
			$name = Kohana::config('external_widget.widget_name');
			if (empty($name)) {
				die(_("Don't know what widget to show since no ".
					"default widget is specified in config file (config/external_widget.php)."));
			}
		}

		$this->template->title = _('External widget');
		$this->template->widget = false;

		$model = Ninja_WidgetPool_Model::all()
			->reduce_by('username', op5auth::instance()->get_user()->username, '=')
			->reduce_by('page', $page, '=')
			->reduce_by('name', $name, '=')
			->reduce_by('instance_id', $instance_id, '=')
			->one();

		if ($model) {
			$this->template->widget = $model->build();
		} else {
			$model = new Ninja_Widget_Model();
			$model->set_name($name);
			$model->set_page($page);
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

		if (!isset($model->id) || !$model->id)
			$model->save();

		$user = Auth::instance()->get_user();
		$_SESSION['external_widget_username'] = $user->username;
		$_SESSION['external_widget_groups'] = $user->groups;
		$widget = widget::add($model, $this);

	}
}
