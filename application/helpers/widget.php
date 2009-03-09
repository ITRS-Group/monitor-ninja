<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * widget helper class.
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */

class widget_Core {
	public $result = false; 	# widget content result
	public $js = false;			# required js resources?
	public $css = false;		# additional css?
	public $widget_base_path = false;# base_path to widget
	public $widget_full_path = false;
	public $master_obj = false;
	public $widgetname = false;

	public function __construct()
	{
		$this->widget_base_path = Kohana::config('widget.widget_path').Kohana::config('widget.widget_dirname');
		$this->auto_render = FALSE;

		# suppress output until widget is done
		ob_implicit_flush(0);
		ob_start();
	}

	/**
	*	@name	add
	*	@desc	Add a new widget
	* 	@param  string $name
	*
	*/
	public function add($name=false, $arguments=false, &$master=false)
	{
		# first try custom path
		$path = Kohana::find_file(Kohana::config('widget.widget_custom_dirname').$name, $name, false);
		if ($path === false) {
			# try core path if not found in custom
			$path = Kohana::find_file(Kohana::config('widget.widget_dirname').$name, $name, true);
		}

		require_once($path);
		$classname = $name.'_Widget';
		$obj = new $classname;
		# if we have a requested widget method - let's call it
		if (!empty($arguments) & is_array($arguments)) {
			$widget_method = $arguments[0];
			if (method_exists($obj, $widget_method)) {
				array_shift($arguments);
				return $obj->$widget_method($arguments, $master);
			}
		}

		# return false if no method defined
		return false;
	}

	/**
	*	@name	widget_name
	*	@desc	Find name of input class and set wiidget_full path for later use
	*
	*/
	public function set_widget_name($input=false, $dirname=false)
	{
		if (empty($input))
			return false;

		$this->widgetname = strtolower(str_replace('_Widget', '',$input));

		$path = Kohana::find_file(Kohana::config('widget.widget_custom_dirname').$this->widgetname, $this->widgetname, false);
		if ($path === false) {
			# try core path if not found in custom
			$path = Kohana::find_file(Kohana::config('widget.widget_dirname').$this->widgetname, $this->widgetname, false);
		}

		if (strstr($path, Kohana::config('widget.widget_custom_dirname'))) {
			# we have a custom_widget
			$this->widget_base_path = Kohana::config('widget.widget_path').Kohana::config('widget.widget_custom_dirname');
		}

		$this->widget_full_path = $this->widget_base_path.$this->widgetname;
	}

	/**
	*	@name 	view_path
	*	@desc	Find path of widget viewer
	* 	@return str path to viewer
	*
	*/
	public function view_path($view=false)
	{
		if (empty($view))
			return false;

		# first try custom path
		$path = Kohana::find_file(Kohana::config('widget.widget_custom_dirname').$this->widgetname, $view, false);
		if ($path === false) {
			# try core path if not found in custom
			$path = Kohana::find_file(Kohana::config('widget.widget_dirname').$this->widgetname, $view, true);
		}

		return $path;
	}

	/**
	*	@name	fetch
	*	@desc	Fetch content from output buffer for widget
	* 			and clean up output buffer.
	* 			Assign required external files (js, css) on to master controller variables.
	*
	*/
	public function fetch()
	{
		$content = ob_get_contents();
		ob_end_clean();
		$this->resources($this->js, 'js');
		$this->resources($this->css, 'css');
		$this->master_obj->widgets = array_merge($this->master_obj->widgets, array($content));
		#return array('content' => $content, 'js' => $this->js, 'css' => $this->css);
	}

	/**
	 * Merge current widgets resource files with other
	 * widgets to be printed to HTML head
	 *
	 * @param 	array $in_files
	 */
	public function resources($in_files=false, $type='js')
	{
		if (empty($in_files) || empty($this->master_obj) || empty($type))
			return false;
		$type = strtolower($type);
		$files = false;
		foreach ($in_files as $file) {
			$files[] = $this->widget_base_path.$this->widgetname.$file;
		}
		switch ($type) {
			case 'css':
				$this->master_obj->xtra_css = array_merge($this->master_obj->xtra_css, $files);
				break;
			case 'js': default:
				$this->master_obj->xtra_js = array_merge($this->master_obj->xtra_js, $files);
				break;
		}
	}

}
