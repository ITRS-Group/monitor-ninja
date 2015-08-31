<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * widget helper class.
 */
class widget_Base
{
	protected $editable = TRUE; /**< An editable widget has settings that can be changed */
	protected $movable = TRUE; /**< A movable widget can be dragged around */
	protected $collapsable = TRUE; /**< A collapsable widget can be collapsed, so only the title bar is visible */
	protected $removable = TRUE; /**< A removable widget can be deleted */
	protected $closeconfirm = TRUE; /**< Whether to ask the user to confirm widget deletion */
	protected $duplicatable = FALSE; /**< Whether the widget can be copied. Setting this to true requires testing, so default to the more backwards compatible mode */

	public $result = false; /**< widget content result */
	public $js = false; /**< required js resources? */
	public $css = false; /**< additional css? */
	public $inline_js = false; /**< additional inline javascript, as a string */
	public $widget_full_path = false; /**< path to this widget's directory */
	public $model = false; /**< The widget model instance this widget represents */
	public $extra_data_attributes = array(); /**<  array Key-value to attach to widget-container (for example ["hello"] => "bye" which renders as <div data-hello="bye" />, good for javascript-hooks */
	private static $loaded_widgets = array();

	public $arguments = array(); /**< The arguments for this instance, constructed from the option objects */

	/**
	 * Create new widget instance from a given widget model.
	 */
	public function __construct($widget_model)
	{
		/* @var $widget_model Ninja_Widget_Model */
		$this->widget_full_path = $widget_model->widget_path();

		$this->model = $widget_model;
	}

	/**
	 * DEPRECATED: Do not use
	 *
	 * For legacy reasons, this provides a shortcut to a populated Current_status_Model instance.
	 * There once were significant performance advantages to use this wrapper, but there isn't anymore.
	 * Just call Current_status_Model::instance() instead.
	 */
	public static function get_current_status() {
		$current_status = Current_status_Model::instance();
		$current_status->analyze_status_data();
		return $current_status;
	}

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array(
			'friendly_name' => "Widget " . $this->model->name,
			'instanceable' => true
			);
	}

	/**
	 * Returns the populated argument array
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
	 * @param $view Template object
	 * @return str path to viewer
	 */
	public function view_path($view=false)
	{

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
	 * Return the list of options to use in this widget. This should be an array of
	 * option instances, or - if you want to do more manual work - strings.
	 *
	 * Actual widgets typically want to extend this method.
	 */
	public function options()
	{
		$refresh = new option($this->model->name, 'refresh_interval', 'Refresh (sec)', 'input', array('size'=>3, 'type'=>'text'), 60);
		$refresh->should_render_js(false);
		return array(
			$refresh,
			'<div class="refresh_slider"></div>'
		);
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
	public function render($method='index', $with_chrome=true)
	{
		$content = '';
		$widget_id = $this->model->name.'-'.$this->model->instance_id;
		if ($with_chrome) {
			$options = $this->options();
			$widget_legal_classes = array('editable', 'movable', 'collapsable', 'removable', 'closeconfirm', 'duplicatable');
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

			$content .= '<div class="widget '.implode(' ', $widget_classes).'" id="widget-'.$widget_id.'" data-name="'.$this->model->name.'" '.$data_attributes.' data-instance_id="'.$this->model->instance_id.'">';
			$content .= '<div class="widget-header"><span class="'.$widget_id.'_editable" id="'.$widget_id.'_title">'.$this->model->friendly_name.'</span></div>';
			if (!empty($options) && $this->editable) {
				$content .= '<div class="clear"></div><div class="widget-editbox">';
				$content .= form::open('widget/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;'));
				$content .= '<fieldset>';
				if (!isset(self::$loaded_widgets[$this->model->name]))
					$this->inline_js .= "widget.register_widget_load('".$this->model->name."', function() {";
				foreach ($options as $option) {
					if (is_string($option)) {
						$content .= $option;
					}
					else {
						$content .= '<div class="widget-editbox-label"><div>'.$option->render_label($this->model->instance_id).'</div></div>';
						$content .= '<div class="widget-editbox-field"><div>'.$option->render_widget($this->model->instance_id, $this->model->setting).'</div></div>';
						$js = $option->render_js();
						if (!empty($js) && !isset(self::$loaded_widgets[$this->model->name]))
							$this->inline_js .= "($js)(this);\n";
					}
					$content .= '<br/>';
				}
				if (!isset(self::$loaded_widgets[$this->model->name]))
					$this->inline_js .= "});\n";
				$content .= '</fieldset>';
				$content .= form::close();
				$content .= '</div>';
			}

			$content .= '<div class="%%WIDGET_CLASS%%" style="overflow: auto;">'; // Clear and end widget header and start widget content
		}
		ob_start();
		$this->$method();

		if ($this->widget_full_path === false) {
			$content .= '<h2>Widget Error</h2><br />';
			$content = str_replace('%%WIDGET_CLASS%%', 'widget-content-error', $content);
		} else {
			$content = str_replace('%%WIDGET_CLASS%%', 'widget-content', $content);
		}

		$content .= ob_get_contents();
		ob_end_clean();
		if ($with_chrome) {
			$content .= '</div>';
			$content .= '</div>';
		}
		self::$loaded_widgets[$this->model->name] = 1;
		return $content;
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
