<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * A helper for widgets to render their custom configuration options
 */
class option_Core
{
	private $should_render_js = true;
	public $ns;
	public $name;
	public $label;
	private $type;
	private $args;
	private $default;

	public function __construct($ns, $name, $label, $type, $args=array(), $default=0) {
		$this->ns = $ns;
		$this->name = $name;
		$this->label = $label;
		$this->type = $type;
		$this->args = $args;
		$this->default = $default;
	}

	public function should_render_js($render_js) {
		$this->should_render_js = $render_js;
	}

	public function value($settings) {
		if (isset($settings[$this->name]))
			return $settings[$this->name];
		return $this->default;
	}

	public function render_label($id) {
		echo "<label for=\"$this->ns-$this->name-$id\">"._($this->label)."</label>";
	}

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
		echo $f->$type($args);
	}

	public function render_js() {
		if (!$this->should_render_js)
			return '';
		return <<<EOSCRIPT
function(widget) {
	\$('#'+ widget.widget_id + ' *[name=$this->name]').live('change', function() {
	widget.save_custom_val(\$(this).val(), '$this->name');
	widget.update_display();
});}
EOSCRIPT;
	}
}
