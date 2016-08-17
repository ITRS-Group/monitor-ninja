<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once('op5/log.php');
/**
 * Loads and displays Kohana view files. Can also handle output of some binary
 * files, such as Javascript and CSS files.
 *
 * $Id: View.php 3917 2009-01-21 03:06:22Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class View {

	// The view file name and type
	protected $kohana_filename = FALSE;
	protected $kohana_filetype = FALSE;

	// View variable storage
	protected $kohana_local_data = array();
	protected static $kohana_global_data = array();

	/**
	 * Creates a new View using the given parameters.
	 *
	 * @param   string  view name
	 * @param   array   pre-load data
	 * @param   string  type of file: html, css, js, etc.
	 * @return  object
	 */
	public static function factory($name = NULL, $data = NULL, $type = NULL)
	{
		return new View($name, $data, $type);
	}

	/**
	 * Attempts to load a view and pre-load view data.
	 *
	 * @throws  Kohana_Exception  if the requested view cannot be found
	 * @param   string  view name
	 * @param   array   pre-load data
	 * @param   string  type of file: html, css, js, etc.
	 * @return  void
	 */
	public function __construct($name, $data = NULL)
	{
		$this->set_filename($name);

		if (is_array($data) AND ! empty($data))
		{
			// Preload data using array_merge, to allow user extensions
			$this->kohana_local_data = array_merge($this->kohana_local_data, $data);
		}
	}

	/**
	 * Magic method access to test for view property
	 *
	 * @param   string   View property to test for
	 * @return  boolean
	 */
	public function __isset($key = NULL)
	{
		return $this->is_set($key);
	}

	/**
	 * @param $filename string
	 * @throws Exception if the filename doesn't
	 */
	public function set_filename($filename)
	{
		$this->kohana_filename = Kohana::get_view($filename);
		$this->kohana_filetype = 'php';
	}

	/**
	 * Sets a view variable.
	 *
	 * @param   string|array  name of variable or an array of variables
	 * @param   mixed         value when using a named variable
	 * @return  object
	 */
	public function set($name, $value = NULL)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $value)
			{
				$this->__set($key, $value);
			}
		}
		else
		{
			$this->__set($name, $value);
		}

		return $this;
	}

	/**
	 * Checks for a property existence in the view locally or globally. Unlike the built in __isset(),
	 * this method can take an array of properties to test simultaneously.
	 *
	 * @param string $key property name to test for
	 * @param array $key array of property names to test for
	 * @return boolean property test result
	 * @return array associative array of keys and boolean test result
	 */
	public function is_set( $key = FALSE )
	{
		// Setup result;
		$result = FALSE;

		// If key is an array
		if (is_array($key))
		{
			// Set the result to an array
			$result = array();

			// Foreach key
			foreach ($key as $property)
			{
				// Set the result to an associative array
				$result[$property] = (array_key_exists($property, $this->kohana_local_data) OR array_key_exists($property, self::$kohana_global_data)) ? TRUE : FALSE;
			}
		}
		else
		{
			// Otherwise just check one property
			$result = (array_key_exists($key, $this->kohana_local_data) OR array_key_exists($key, self::$kohana_global_data)) ? TRUE : FALSE;
		}

		// Return the result
		return $result;
	}

	/**
	 * Sets a bound variable by reference.
	 *
	 * @param   string   name of variable
	 * @param   mixed    variable to assign by reference
	 * @return  object
	 */
	public function bind($name, & $var)
	{
		$this->kohana_local_data[$name] =& $var;

		return $this;
	}

	/**
	 * Sets a view global variable.
	 *
	 * @param   string|array  name of variable or an array of variables
	 * @param   mixed         value when using a named variable
	 * @return  object
	 */
	public function set_global($name, $value = NULL)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $value)
			{
				self::$kohana_global_data[$key] = $value;
			}
		}
		else
		{
			self::$kohana_global_data[$name] = $value;
		}

		return $this;
	}

	/**
	 * Magically sets a view variable.
	 *
	 * @param   string   variable key
	 * @param   string   variable value
	 * @return  void
	 */
	public function __set($key, $value)
	{
		$this->kohana_local_data[$key] = $value;
	}

	/**
	 * Magically gets a view variable.
	 *
	 * @param  string  variable key
	 * @return mixed   variable value if the key is found
	 * @return void    if the key is not found
	 */
	public function &__get($key)
	{
		if (isset($this->kohana_local_data[$key]))
			return $this->kohana_local_data[$key];

		if (isset(self::$kohana_global_data[$key]))
			return self::$kohana_global_data[$key];

		if (isset($this->$key))
			return $this->$key;

		$this->kohana_local_data[$key] = null;
		return $this->kohana_local_data[$key];
	}

	/**
	 * Magically converts view object to string.
	 *
	 * This is deprecated in ninja. Since rendering is heavy, and might contain
	 * lot of data, we should use ->render() instead explicitly, and prefferably
	 * use the streamed version of it.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		$stack = debug_backtrace();
		$file = $stack[0]['file'];
		$line = $stack[0]['line'];
		$msg = "Don't typecast view ".$this->kohana_filename." to string\n"
			."    at ".$file."(".$line.")";
		// We actually would like to use flag::deprecated() here,
		// but we cannot, since it throws an exception (and PHP
		// doesn't allow __toString() to throw exceptions:
		// https://bugs.php.net/bug.php?id=53648 )
		op5log::instance('ninja')->log('debug', $msg);

		ob_start();
		$this->render(TRUE);
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Renders a view.
	 */
	public function render($print = FALSE, $renderer = FALSE)
	{
		if ($print == false) {
			$stack = debug_backtrace();
			$file = $stack[0]['file'];
			$line = $stack[0]['line'];
			$msg = "Don't render a view to a variable!\n"
				."    View: ".$this->kohana_filename."\n"
				."    at ".$file."(".$line.")";
			flag::deprecated(__METHOD__.' with $print == false', $msg);

			ob_start();
		}

		if (is_string($this->kohana_filetype))
		{
			// Merge global and local data, local overrides global with the same name
			$data = array_merge(self::$kohana_global_data, $this->kohana_local_data);

			// Load the view in the controller for access to $this
			if(Kohana::$instance instanceof System_Controller) {
				// We're in PNP land, and there you assign $this->foo in the
				// controller, instead of Ninja's $this->content->foo
				Kohana::$instance->_load_view($this->kohana_filename, $data);
			} else {
				$this->load_view($this->kohana_filename, $data);
			}
		}
		else
		{
			// Set the content type and size
			header('Content-Type: '.$this->kohana_filetype[0]);

			if ($file = fopen($this->kohana_filename, 'rb'))
			{
				// Display the output
				fpassthru($file);
				fclose($file);
			}
		}

		if ($print == false) {
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
	}

	/**
	 * Includes a View
	 *
	 * @param   string  view filename
	 * @param   array   array of view variables
	 * @return  string
	 */
	protected function load_view($kohana_view_filename, $kohana_input_data)
	{
		if ($kohana_view_filename == '')
			return;

		// Import the view variables to local namespace
		extract($kohana_input_data, EXTR_SKIP);

		try
		{
			// Views are straight HTML pages with embedded PHP, so importing them
			// this way insures that $this can be accessed as if the user was in
			// the controller, which gives the easiest access to libraries in views
			include $kohana_view_filename;
		}
		catch (Exception $e)
		{
			// Display the exception using its internal __toString method
			echo $e;
		}
	}
} // End View
