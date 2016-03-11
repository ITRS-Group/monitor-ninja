<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once('op5/spyc.php');

/**
 * General widget exception
 */
class WidgetException extends Exception {}
/**
 * Widget setting exception
 */
class WidgetSettingException extends Exception {}

/**
 * Widget base class.
 */
class widget_Base {

	/**
	 * An editable widget has settings that can be changed
	 */
	protected $editable = true;

	/**
	 * A movable widget can be dragged around
	 */
	protected $movable = true;

	/**
	 * A collapsable widget can be collapsed, so only
	 * the title bar is visible
	 */
	protected $collapsable = true;

	/**
	 * A removable widget can be deleted
	 */
	protected $removable = TRUE;

	/**
	 * Whether to ask the user to confirm widget
	 * deletion
	 */
	protected $closeconfirm = TRUE;

	/**
	 * Whether the widget can be copied. Setting this
	 * to true requires testing, so default to the more backwards
	 * compatible mode
	 */
	protected $duplicatable = FALSE;

	/**
	 * Path to this widget's directory
	 */
	protected  $widget_full_path = false;

	/**
	 * List of all loaded widgets
	 */
	private static $loaded_widgets = array();

	/**
	 * The widget model instance this widget represents
	 */
	public $model = false;

	/**
	 * Array Key-value to attach to widget-container
	 * (for example ["hello"] => "bye" which renders as <div data-hello="bye"
	 * />, good for javascript-hooks
	 */
	public $extra_data_attributes = array();

	/**
	 * Additional JavaScript files to load
	 */
	public $js = false;

	/**
	 * Additional CSS files to load
	 */
	public $css = false;

	/**
	 * A JavaScript string to inline into the widget
	 */
	public $inline_js = false;

	/**
	 * The arguments for this instance, constructed from the
	 * option objects
	 */
	public $arguments = array();

	/**
	 * Create new widget instance from a given widget model.
	 *
	 * @param $model Ninja_Widget_Model ORM Model for a ninja widget
	 */
	public function __construct(Ninja_Widget_Model $model) {

		try {
			$this->widget_full_path = $model->widget_path();
		} catch (Exception $e) {
			$this->widget_full_path = false;
		}

		$this->model = $model;
		$option_manifest_path = $this->widget_full_path . '/options.php';
		if (file_exists($option_manifest_path)) {
			$this->options_definition = include(
				$option_manifest_path
			);
		}

	}

	/**
	 * Retrieves this widgets setting value for $key, if not yet configured it
	 * will fetch the default from the widgets option definition
	 *
	 * NOTE: cannot be used if the widget does not yet use an options.php
	 * definition file.
	 *
	 * @param $key string
	 * @return mixed
	 */
	protected function get_setting ($key) {

		if (isset($this->model->setting[$key])) {
			return $this->model->setting[$key];
		} elseif (isset($this->options_definition[$key], $this->options_definition[$key]['default'])) {
			return $this->options_definition[$key]["default"];
		}

		op5log::instance('ninja')->log('error', "Attempt to fetch invalid
			setting $key for widget '" . $this->model->get_name() . "'");
		throw new WidgetSettingException("Invalid setting $key for widget '" .
			$this->model->get_display_name() . "'");

	}

	/**
	 * Return the default friendly name for the widget type default to the
	 * model name, but should be overridden by widgets.
	 *
	 * @return array
	 */
	public function get_metadata() {
		return array(
			'friendly_name' => "Widget " . $this->model->name,
			'instanceable' => true
		);
	}

	/**
	 * Returns the populated argument array
	 *
	 * @return array
	 */
	public function get_arguments() {
		$arguments = array();
		foreach ($this->options() as $option) {
			if (!is_string($option))
				$arguments[$option->name] = $option->value($this->model->setting);
		}
		if (is_array($this->model->setting)) {
			foreach ($this->model->setting as $opt => $val) {
				if (!isset($arguments[$opt]))
					$arguments[$opt] = $val;
			}
		}
		return $arguments;
	}

	/**
	 * Find path of widget viewer
	 *
	 * @param $view Template object
	 * @return str path to viewer
	 */
	public function view_path($view = false) {

		if (empty($view))
			return false;

		$widget = $this->model->name;
		# first try custom path

		$path = Kohana::find_file(Kohana::config('widget.custom_dirname').$this->model->name, $view, false);
		if ($path === false) {
			# try core path if not found in custom
			$path = Kohana::find_file(Kohana::config('widget.dirname').$this->model->name, $view, false);
		}

		return $path;
	}

	/**
	 *
	 * Return the list of options to use in this widget. This should be an
	 * array of option instances, or - if you want to do more manual work -
	 * strings.
	 *
	 * Actual widgets typically want to extend this method.
	 *
	 * DEPRECATED USAGE: do not extend this within your widget, create an
	 * "options.yml" file in your widget directory instead and then use
	 * $this->get_setting($key) to fetch default and/or user settings, keeping
	 * you from messing with settings in your widget "controller", see big_number widget
	 *
	 * @return array
	 */
	public function options () {

		$refresh = new option($this->model->name, 'refresh_interval', 'Refresh (sec)', 'input', array('size'=>3, 'type'=>'text'), 60);
		$refresh->should_render_js(false);
		$options = array(
			$refresh,
			'<div class="refresh_slider"></div>'
		);

		if (!isset($this->options_definition))
			return $options;

		foreach ($this->options_definition as $key => $def) {

			$attr = array();
			$label = $key;
			$default = '';

			if (is_string($def)) $type = $def;
			elseif (is_array($def)) {


				$type = $def['type'];
				$label = isset($def['label']) ? $def['label'] : $key;
				$default = isset($def['default']) ? $def['default'] : '';

				if ($type === 'dropdown') {
					$attr['options'] = isset($def['options']) ? $def['options'] : array();
				}
			}

			$options[] = new option(
				$this->model->name,
				$key, $label, $type, $attr, $default
			);

		}

		return $options;


	}

	/**
	 * Hook to force additional CSS classes to the rendering
	 */
	public function add_css_class ($class)
	{
		if (!isset($this->added_classes))
			$this->added_classes = array();
		$this->added_classes[] = $class;
	}

	/**
	 * Method to render a widget
	 *
	 * @param $method Name of method
	 * @param $with_chrome True to generate widget with the menus and everything, false otherwise
	 * @return The rendered widget as a string
	 */
	public function render($method = 'index', $with_chrome = true) {

		ob_start();

		// Invoke "error-prone" methods first, yield a dead widget on exception
		try {
			$this->$method();
			if ($with_chrome) {
				$options = $this->options();
			}
		} catch (Exception $e) {
			require_once(Kohana::find_file('widgets/dead', 'dead'));
			$dead_widget = new Dead_Widget($this->model, $e);
			$dead_widget->index();
		}

		$content = ob_get_clean();
		if (!$with_chrome) {
			return $content;
		}
		ob_start();

		$widget_id = $this->model->get_widget_id();
		$widget_legal_classes = array(
			'editable',
			'movable',
			'collapsable',
			'removable',
			'closeconfirm',
			'duplicatable'
		);

		$widget_classes = array();
		foreach ($widget_legal_classes as $class) {
			if ($this->$class) {
				$widget_classes[] = $class;
			}
		}

		if (isset($this->added_classes)) {
			for($i = 0; $i < count($this->added_classes); $i++) {
				$widget_classes[] = $this->added_classes[$i];
			}
		}

		$data_attributes = "";
		foreach($this->extra_data_attributes as $key => $value) {
			$data_attributes .= " data-$key='$value'";
		}

		$classes = implode(" ", $widget_classes);
		$instance_id = $this->model->get_instance_id();

		$editable = $this->editable;
		$name = $this->model->name;
		$display_name = $this->model->get_friendly_name();
		$setting = $this->model->get_setting();

		$loaded = isset(self::$loaded_widgets[$this->model->name]);
		$template = MODPATH . 'widgets/views/widget.php';

		if (!is_readable($template)) {
			op5log::instance('ninja')->log('error', "Could not render widget
				due to missing template, expected template at '$template'");
			return _("Could not render widget due to missing template");
		}

		require($template);
		self::$loaded_widgets[$this->model->name] = 1;

		return ob_get_clean();
	}

	/**
	 * Print the widget contents here
	 *
	 * Concrete widgets typically want to override this.
	 */
	public function index()
	{
		echo "<p>(empty widget)</p>";
	}

	/**
	 * Weird little method that returns all the resources referenced in this instance
	 */
	public function resources($in_files=false, $type='js')
	{
		if (empty($in_files) || empty($type))
			return array();
		$type = strtolower($type);
		$files = array();
		foreach ($in_files as $file) {
			if (file_exists($this->widget_full_path.'/'.$file.'.'.$type))
				$files[] = $this->widget_full_path.'/'.$file.'.'.$type;
			else
				$files[] = $file.'.'.$type;
		}
		switch ($type) {
		 case 'css':
			return $files;
			break;
		 case 'js': default:
			return $files;
			break;
		}
	}

	/**
	 * Set correct paths
	 * @param $rel_path string: Relative path
	 * @return false on errors, "full relative" path on success.
	 */
	public function add_path($rel_path)
	{
		$rel_path = trim($rel_path);
		if (empty($rel_path)) {
			return false;
		}

		$path = false;
		# assume rel_path is relative to views directory
		$path = 'application/views/'.$rel_path;
		# make sure we didn't mix up start/end slashes
		$path = str_replace('//', '/', $path);
		return $path;
	}
}
