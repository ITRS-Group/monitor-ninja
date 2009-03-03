<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Widget controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Widget_Controller extends Authenticated_Controller {

	public $result = false; 	# widget content result
	public $js = false;			# required js resources?
	public $css = false;		# additional css?
	public $widget_path = false;# path to widget

	public function __construct()
	{
		parent::__construct();
		$this->widget_path = 'application/widgets/';
		$this->auto_render = FALSE;

		# suppress output until widget is done
		ob_implicit_flush(0);
		ob_start();
	}

	/**
	*	@name	widget_name
	*	@desc	Find name of input class
	*
	*/
	public function widget_name($input=false)
	{
		if (empty($input))
			return false;
		return strtolower(str_replace('_Controller', '',$input));
	}

	/**
	*	@name 	view_path
	*	@desc	Find path of widget viewer
	* 	@return str path to viewer
	*
	*/
	public function view_path($widget_class=false, $view=false)
	{
		if (empty($widget_class) || empty($view))
			return false;
		$widget = $this->widget_name($widget_class);

		return Kohana::find_file('widgets/'.$widget, $view, true);
	}

	/**
	*	@name	fetch
	*	@desc	Fetch content from output buffer for widget
	* 			and clean up output buffer.
	* 			Finally pass required external files (js, css) on to master template.
	*
	*/
	public function fetch()
	{
		$content = ob_get_contents();
		ob_end_clean();
		return array('content' => $content, 'js' => $this->js, 'css' => $this->css);
	}

}