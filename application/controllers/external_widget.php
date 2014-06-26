<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controller for displaying single widget on an external web
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class External_widget_Controller extends Widget_Controller {
	public $widgets = array();
	public function __construct()
	{
		parent::__construct();
		$this->template = $this->add_view('external_widget');
		$this->template->current_skin = 'default/';
	}

	/**
	*	Show page with single widget
	*	@param $name str widget name to show, defaults to netw_health
	*/
	public function show_widget($name = null, $instance_id = null)
	{
		if (is_null($name)) {
			$name = Kohana::config('external_widget.widget_name');
			if (empty($name)) {
				die(_("Don't know what widget to show since no ".
					"default widget is specified in config file (config/external_widget.php)."));
			}
		}


		$this->template->content = $this->add_view('single_widget');
		$this->template->title = _('External widget');
		$this->template->js[] = $this->add_path('/js/widgets.js');

		$model = Ninja_widget_Model::get(Router::$controller.'/'.Router::$method, $name, $instance_id);
		if (!$model) {
			$this->template->content->widget = false;
			return;
		}
		if (!isset($model->id) || !$model->id)
			$model->save();
		$user = Auth::instance()->get_user();
		$_SESSION['external_widget_username'] = $user->username;
		$_SESSION['external_widget_groups'] = $user->groups;
		$widget = widget::add($model, $this);

		$this->template->inline_js = $this->inline_js;

		$this->template->content->widget = $widget;
		$this->template->disable_refresh = true;
	}
}
