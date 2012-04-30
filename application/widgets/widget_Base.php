<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * widget helper class.
 */
class widget_Base
{
	protected $editable = TRUE;
	protected $movable = TRUE;
	protected $collapsable = TRUE;
	protected $removable = TRUE;
	protected $closeconfirm = TRUE;
	protected $duplicatable = FALSE; // setting this to true requires testing, so default to the more backwards compatible mode

	public $result = false; 	# widget content result
	public $js = false;			# required js resources?
	public $css = false;		# additional css?
	public $inline_js = false;
	public $widget_base_path = false;# base_path to widget
	public $widget_full_path = false;
	public $translate = false;
	public $model = false;
	private static $loaded_widgets = array();

	public $arguments = array();

	public function __construct($widget_model)
	{
		$this->widget_base_path = Kohana::config('widget.path').Kohana::config('widget.dirname');
		$this->auto_render = FALSE;

		$this->theme_path = zend::instance('Registry')->get('theme_path');

		# fetch our translation instance
		$this->translate = zend::instance('Registry')->get('Zend_Translate');

		$path = Kohana::find_file(Kohana::config('widget.custom_dirname').$widget_model->name, $widget_model->name, false);
		if ($path === false) {
			$path = Kohana::find_file(Kohana::config('widget.dirname').$widget_model->name, $widget_model->name, false);
		}
		if (strstr($path, Kohana::config('widget.custom_dirname')) !== false) {
			$this->widget_base_path = Kohana::config('widget.path').Kohana::config('widget.custom_dirname');
		}
		$this->widget_full_path = $this->widget_base_path.$widget_model->name;

		$this->model = $widget_model;
	}

	public static function get_current_status() {
		$current_status = Current_status_Model::instance();
		if (!$current_status->data_present())
			$current_status->analyze_status_data();
		return $current_status;
	}

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
			$path = Kohana::find_file(Kohana::config('widget.dirname').$this->model->name, $view, true);
		}

		return $path;
	}

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
	 * Method to render a widget
	 *
	 * @param $meth Name of method
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
				if ($this->$class)
					$widget_classes[] = $class;
			}
			$content .= '<div class="widget '.implode(' ', $widget_classes).'" id="widget-'.$widget_id.'" data-name="'.$this->model->name.'" data-instance_id="'.$this->model->instance_id.'">';
			$content .= '<div class="widget-header"><span class="'.$widget_id.'_editable" id="'.$widget_id.'_title">'.$this->model->friendly_name.'</span></div>';
			$content .= '<div class="widget-editbox">';
			$content .= form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;'));
			$content .= '<fieldset>';
			if (!isset(self::$loaded_widgets[$this->model->name]))
				$this->inline_js .= "widget.register_widget_load('".$this->model->name."', function() {";
			foreach ($options as $option) {
				if (is_string($option)) {
					$content .= $option;
				}
				else {
					$content .= $option->render_label($this->model->instance_id);
					$content .= $option->render_widget($this->model->instance_id, $this->model->setting);
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
			$content .= '<div class="widget-content">';
		}
		ob_start();
		$this->$method();
		$content .= ob_get_contents();
		ob_end_clean();
		if ($with_chrome) {
			$content .= '</div>';
			$content .= '</div>';
		}
		self::$loaded_widgets[$this->model->name] = 1;
		return $content;
	}

	public function resources($in_files=false, $type='js')
	{
		if (empty($in_files) || empty($type))
			return array();
		$type = strtolower($type);
		$files = array();
		foreach ($in_files as $file) {
			if (file_exists($this->widget_base_path.$this->model->name.'/'.$file.'.'.$type))
				$files[] = $this->widget_base_path.$this->model->name.'/'.$file.'.'.$type;
			else if (file_exists($file.'.'.$type))
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
	 * Set correct paths considering
	 * the path to current theme.
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
		# assume rel_path is relative from current theme
		$path = 'application/views/'.$this->theme_path.$rel_path;
		# make sure we didn't mix up start/end slashes
		$path = str_replace('//', '/', $path);
		return $path;
	}
}
