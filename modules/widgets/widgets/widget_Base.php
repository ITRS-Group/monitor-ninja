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
	public function __construct(Widget_Model $model) {
		try {
			$this->widget_full_path = $model->widget_path();
		} catch (Exception $e) {
			$this->widget_full_path = false;
		}

		$this->model = $model;
	}

	/**
	 * Retrieves this widgets setting value for $key, if not yet configured it
	 * will fetch the default from the widgets option definition
	 *
	 * @param $key string
	 * @return mixed
	 */
	protected function get_setting ($key) {
		$setting = $this->model->get_setting();
		if (isset($setting[$key])) {
			return $setting[$key];
		}

		op5log::instance('ninja')->log(
			'error', "Attempt to fetch invalid setting $key for widget '" .
			$this->model->get_name() . "'"
		);

		throw new WidgetSettingException(
			"Invalid setting $key for widget '" . $this->model->get_name() . "'"
		);
	}

	/**
	 * The backwards compatible way to get widget default title
	 *
	 * @return string
	 */
	protected function get_suggested_title () {
		return $this->model->get_friendly_name();
	}

	/**
	 * Returns the widgets title, takes into account user configured title,
	 * the widgets override title and lastly the "default" title of the widget.
	 *
	 * @return string
	 */
	final public function get_title () {
		$setting = $this->model->get_setting();
		if (isset($setting['title']))
			return $setting['title'];
		return $this->get_suggested_title();
	}

	/**
	 * Return the default friendly name for the widget type default to the
	 * model name, but should be overridden by widgets.
	 *
	 * @return array
	 */
	public function get_metadata() {
		return array(
			'friendly_name' => "Widget " . $this->model->get_name(),
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
			if ($option instanceof option) {
				$arguments[$option->name] = $option->value($this->model->get_setting());
			} elseif($option instanceof Form_Model) {
				foreach($option->get_fields() as $field) {
					$arguments[$field] = $option->get_value($field);
				}
			}
		}
		if (is_array($this->model->get_setting())) {
			foreach ($this->model->get_setting() as $opt => $val) {
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

		$widget = $this->model->get_name();
		# first try custom path

		$path = Kohana::find_file(Kohana::config('widget.custom_dirname').$this->model->get_name(), $view, false);
		if ($path === false) {
			# try core path if not found in custom
			$path = Kohana::find_file(Kohana::config('widget.dirname').$this->model->get_name(), $view, false);
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
	 * @return array of option
	 */
	public function options () {

		$options = array();

		$title_option = new option(
			$this->model->get_name(),
			'title', 'Custom title',
			'input', array(
				'type' => 'text',
				'placeholder' => 'No custom title'
			), ''
		);
		$options[] = $title_option;

		$refresh = new option(
			$this->model->get_name(),
			'refresh_interval', 'Refresh (sec)',
			'input', array(
				'size'=>3,
				'type'=>'text'
			),
			60
		);

		$refresh->should_render_js(false);
		$options['refresh'] = $refresh;

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

		$title = $this->model->get_friendly_name();
		ob_start();

		/* Invoke "error-prone" methods first,
		 * yield a dead widget on exception */
		try {
			if ($with_chrome)
				$options = $this->options();
			$this->$method();
			$title = $this->get_title();
		} catch (WidgetSettingException $e) {
			echo "<div class=\"alert\"><h1>Widget Setting Error</h1>";
			echo "<p>" . $e->getMessage() . "</p></div>";
		} catch (Exception $e) {
			require_once(Kohana::find_file('widgets/dead', 'dead'));
			$dead_widget = new Dead_Widget($this->model, $e);
			$dead_widget->index();
		}

		$content = ob_get_clean();
		if (!$with_chrome)
			return $content;

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

		$editable = $this->editable;
		$setting = $this->model->get_setting();

		$loaded = isset(self::$loaded_widgets[$this->model->get_name()]);
		$props = array(
				'classes' => $classes,
				'key' => $this->model->get_key(),
				'data_attributes' => $data_attributes,
				'title' => $title,
				'editable' => $editable,
				'content' => $content,
				'setting' => $setting
		);
		if(isset($options)) {
			$props['options'] = $options;
		}
		$template = new View('widget', $props);

		self::$loaded_widgets[$this->model->get_name()] = 1;

		return $template->render(false);
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
		 case 'js':
		 default:
			return $files;
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
