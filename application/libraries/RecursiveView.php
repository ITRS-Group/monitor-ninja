<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * A view whose variables will be carried over into it's sub-views
 */
class RecursiveView_Core extends View {
	public function __construct($name = NULL, $inner = null) {
		parent::__construct($name);
		$this->regular = $inner;
	}

	public function render($print = false, $renderer = false)
	{
		foreach ($this->kohana_local_data as $var) {
			if (is_a($var, 'View')) {
				$inner = $this->kohana_local_data;
				foreach ($inner as $key => $val) {
					if ($key != 'regular')
						$var->set($key, $val);
				}
			}
		}
		return parent::render($print, $renderer);
	}
}
