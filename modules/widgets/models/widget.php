<?php

/**
 * Data model class for widgets.
 */
class Widget_Model extends Object_Model {
	/**
	 * Cache for widget path
	 */
	protected $widget_path_cache = NULL;

	/**
	 * Build a ninja widget model
	 */
	public function __construct($initial = false) {
		if($initial !== false) {
			if (array_key_exists ( 'page', $initial ))
				$this->set_page ( $initial ['page'] );

			if (array_key_exists ( 'name', $initial ))
				$this->set_name ( $initial ['name'] );

			if (array_key_exists ( 'username', $initial ))
				$this->set_username ( $initial ['username'] );

			if (array_key_exists ( 'setting', $initial ))
				$this->set_setting ( $initial ['setting'] );
		}
	}

	/**
	 * Generate a widget given the current configuration
	 * May return a Dead_Widget, in case the generated widget failed to instantiate
	 *
	 * @return widget_Base
	 */
	public function build() {
		/* We should make this autoload nicer, but still keep dropdir support for widgets (as that is expected) */
		require_once(Kohana::find_file('widgets', 'widget_Base'));
		try {
			if(!is_file($this->widget_path() . $this->get_name() . '.php'))
				throw new Exception("Widget type '" . $this->get_name() ."' does not seem to be installed.");
			require_once($this->widget_path() . $this->get_name() . '.php');
			$classname = $this->get_name().'_Widget';
			return new $classname($this);
		}
		catch (Exception $e) {
			require_once(Kohana::find_file('widgets/dead', 'dead'));
			return new Dead_Widget($this, $e);
		}
	}

	/**
	 * Widget path
	 *
	 * @return string
	 * @throws Exception When widget with the current name does not seem to be installed.
	 */
	public function widget_path() {
		if($this->widget_path_cache !== NULL) {
			return $this->widget_path_cache;
		}

		$dirs = array(Kohana::config('widget.custom_dirname'), Kohana::config('widget.dirname'));
		foreach($dirs as $dir) {
			$path = Kohana::find_file($dir . $this->get_name(), $this->get_name(), false);
			if($path !== false)
				break;
		}
		if($path === false || substr($path,0,strlen(DOCROOT)) != DOCROOT) {
			throw new Exception("Widget type '" . $this->get_name() . "' does not seem to be installed.");
		}
		// Remove DOCROOT and widget source file name from the path, to identify the URL Prefix for files in widget
		$this->widget_path_cache = dirname(substr($path, strlen(DOCROOT))) . "/";
		return $this->widget_path_cache;
	}

	/**
	 * Access parameters in old style
	 *
	 * This is deprecated
	 */
	public function __get($var) {
		flag::deprecated(__METHOD__);
		$func_name = 'get_'.$var;
		return $this->$func_name();
	}
}
