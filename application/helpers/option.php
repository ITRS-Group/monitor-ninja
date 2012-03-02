<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * A helper for widgets to render their custom configuration options
 */
class option_Core
{
	private $should_render_js = true; /**< True to auto-render javascript to handle changes */
	public $ns; /**< A unique namespace, usually the widget name */
	public $name; /**< A field name, should be "code-friendly" */
	public $label; /**< The label text, should be translated */
	private $type; /**< The widget type, ie "input", "checkbox", "textarea", etc - must exist in kohana's form helper */
	private $args; /**< Arguments to send to the form helper to render it properly */
	private $default; /**< A default value to fall back on */

	/**
	 * Constructor
	 *
	 * @param $ns A unique namespace for the options, usually the widget name
	 * @param $name A field name, should be "code-friendly" so lower-case ascii and no spaces
	 * @param $label A (translated) label to print
	 * @param $type The widget type, ie "input", "checkbox", "textarea", etc - must exist in kohana's form helper
	 * @param $args An attribute-value map that will be added to the widget
	 * @param $default Default value for the widget
	 */
	public function __construct($ns, $name, $label, $type, $args=array(), $default=0) {
		$this->ns = $ns;
		$this->name = $name;
		$this->label = $label;
		$this->type = $type;
		$this->args = $args;
		$this->default = $default;
	}

	/**
	 * True to auto-render javascript to handle changes, false otherwise
	 */
	public function should_render_js($render_js) {
		$this->should_render_js = $render_js;
	}

	/**
	 * Get the value for the setting
	 *
	 * @param $settings Settings from a widget model object
	 * @return The setting if set, otherwise the default value
	 */
	public function value($settings) {
		if (isset($settings[$this->name]))
			return $settings[$this->name];
		return $this->default;
	}

	/**
	 * Print the label tag
	 * @param $id Instance id for this widget
	 */
	public function render_label($id) {
		return "<label for=\"$this->ns-$this->name-$id\">"._($this->label)."</label>";
	}

	/**
	 * Print the widget itself
	 * @param $id Instance id for this widget
	 * @param $settings Settings from a widget model object
	 */
	public function render_widget($id, $settings) {
		$type = $this->type;

		$args = $this->args;
		if (!isset($args['id']))
			$args['id'] = $this->ns.'-'.$this->name.'-'.$id;
		if (!isset($args['class']))
			$args['class'] = $this->name;
		if (!isset($args['name']))
			$args['name'] = $this->name;

		$value = $this->value($settings);
		switch ($type) {
		 case 'checkbox':
		 case 'radio':
			if (!isset($args['value']))
				$args['value'] = 1;
			$value_arg = 'checked';
			break;
		 case 'dropdown':
			$value_arg = 'selected';
			break;
		 default:
			$value_arg = 'value';
			break;
		}
		if (!isset($args[$value_arg]))
			$args[$value_arg] = $value;

		$f = new form();
		return $f->$type($args);
	}

	/**
	 * Returns the javascript to handle option changes
	 * Will return the empty string if should_render_js is false
	 */
	public function render_js() {
		if (!$this->should_render_js)
			return '';
		return <<<EOSCRIPT
function(widget) {
	\$('#'+ widget.widget_id + ' *[name=$this->name]').live('change', function() {
	widget.save_custom_val(this.type == 'checkbox' ? this.checked + 0 : $(this).val(), '$this->name');
	widget.update_display();
});}
EOSCRIPT;
	}
}
