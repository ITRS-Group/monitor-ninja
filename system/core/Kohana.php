<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once('op5/log.php');

/**
 * Provides Kohana-specific helper functions. This is where the magic happens!
 *
 * $Id: Kohana.php 3917 2009-01-21 03:06:22Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
final class Kohana {

	// The singleton instance of the controller
	public static $instance;

	// Will be set to TRUE when an exception is caught
	public static $has_error = FALSE;

	// The final output that will displayed by Kohana
	public static $output = '';

	// The current user agent
	public static $user_agent;

	// The current locale
	public static $locale;

	// Configuration
	private static $configuration;

	// Include paths
	private static $include_paths;

	// Logged messages
	private static $log;

	// Internal caches and write status
	private static $internal_cache = array();

	/**
	 * Sets up the PHP environment. Adds error/exception handling, output
	 * buffering, and adds an auto-loading method for loading classes.
	 *
	 * This method is run immediately when this file is loaded, and is
	 * benchmarked as environment_setup.
	 *
	 * For security, this function also destroys the $_REQUEST global variable.
	 * Using the proper global (GET, POST, COOKIE, etc) is inherently more secure.
	 * The recommended way to fetch a global variable is using the Input library.
	 * @see http://www.php.net/globals
	 *
	 * @return  void
	 */
	public static function setup()
	{
		static $run;

		// This function can only be run once
		if ($run === TRUE)
			return;

		// Start the environment setup benchmark
		Benchmark::start(SYSTEM_BENCHMARK.'_environment_setup');

		// Define Kohana error constant
		define('E_KOHANA', 42);

		// Define 404 error constant
		define('E_PAGE_NOT_FOUND', 43);

		// Define database error constant
		define('E_DATABASE_ERROR', 44);

		// Add APPPATH as the first path
		self::$include_paths = array(APPPATH);
		foreach (glob(MODPATH.'*', GLOB_ONLYDIR) as $path)
		{
			self::$include_paths[] = $path.'/';
		}
		// Add SYSPATH as the last path
		self::$include_paths[] = SYSPATH;

		// Disable notices and "strict" errors
		$ER = error_reporting(~E_NOTICE & ~E_STRICT);

		// Set the user agent
		self::$user_agent = trim($_SERVER['HTTP_USER_AGENT']);

		if (function_exists('date_default_timezone_set'))
		{
			$timezone = self::config('locale.timezone');

			// Set default timezone, due to increased validation of date settings
			// which cause massive amounts of E_NOTICEs to be generated in PHP 5.2+
			date_default_timezone_set(empty($timezone) ? date_default_timezone_get() : $timezone);
		}

		// Restore error reporting
		error_reporting($ER);

		// Set autoloader
		spl_autoload_register(array('Kohana', 'auto_load'));

		// Set error handler
		if (PHP_SAPI !== 'cli' && !defined('SKIP_KOHANA')) {
			set_error_handler(array('Kohana', 'error_handler'));

			// Send default text/html UTF-8 header
			header('Content-Type: text/html; charset=UTF-8');
		}

		// Load locales
		$locales = self::config('locale.language');

		// Make first locale UTF-8
		$locales[0] .= '.UTF-8';

		// Set locale information
		self::$locale = setlocale(LC_ALL, $locales);

		// Enable Kohana routing
		Event::add('system.routing', array('Router', 'find_uri'));
		Event::add('system.routing', array('Router', 'setup'));

		// Enable Kohana controller initialization
		Event::add('system.execute', array('Kohana', 'instance'));

		// Enable Kohana 404 pages
		Event::add('system.404', array('Kohana', 'show_404'));

		// Find all the hook files and load them
		$hooks = self::list_files('hooks', TRUE);
		foreach ($hooks as $file) {
			include $file;
		}

		// Setup is complete, prevent it from being run again
		$run = TRUE;

		// Stop the environment setup routine
		Benchmark::stop(SYSTEM_BENCHMARK.'_environment_setup');
	}

	/**
	 * Loads the controller and initializes it. Runs the pre_controller,
	 * post_controller_constructor, and post_controller events. Triggers
	 * a system.404 event when the route cannot be mapped to a controller.
	 *
	 * This method is benchmarked as controller_setup and controller_execution.
	 *
	 * @return  object  instance of controller
	 */
	public static function & instance()
	{
		if (self::$instance === NULL)
		{
			Benchmark::start(SYSTEM_BENCHMARK.'_controller_setup');

			if (Router::$method[0] === '_')
			{
				// Do not allow access to hidden methods
				Event::run('system.404');
			}

			// Include the Controller file
			require Router::$controller_path;

			$classname = ucfirst(Router::$controller).'_Controller';

			if (!class_exists($classname)) {
				Event::run('system.404');
			}

			if (IN_PRODUCTION && !$classname::ALLOW_PRODUCTION) {
				// Controller is not allowed to run in production
				Event::run('system.404');
			}

			try {
				// Run system.pre_controller
				Event::run('system.pre_controller');

				// Create a new controller instance
				$controller = new $classname();

				// Controller constructor has been executed
				Event::run('system.post_controller_constructor', $controller);

				$method = Router::$method;

				// Stop the controller setup benchmark
				Benchmark::stop(SYSTEM_BENCHMARK.'_controller_setup');

				// Start the controller execution benchmark
				Benchmark::start(SYSTEM_BENCHMARK.'_controller_execution');

				// Execute the controller method
				// $method does always exist in a controller, since Controller
				// implements the fucntion __call()
				call_user_func_array(array($controller, $method), Router::$arguments);

				// Controller method has been executed
				Event::run('system.post_controller', $controller);

				// Stop the controller execution benchmark
				Benchmark::stop(SYSTEM_BENCHMARK.'_controller_execution');
			} catch( Exception $e ) {
				self::exception_handler($e);
			}
		}

		return self::$instance;
	}

	/**
	 * Get all include paths.
	 * APPPATH is the first path, followed by module
	 * paths in the order they are configured, follow by the SYSPATH.
	 *
	 * @param
	 *        	boolean re-process the include paths, we don't do that...
	 *        	ignore
	 * @return array
	 */
	public static function include_paths($process = FALSE) {
		return self::$include_paths;
	}

	/**
	 * Remove include paths given a certain pattern.
	 * Useful for replacing modules
	 * for testing
	 */
	public static function remove_include_paths($pattern) {
		self::$include_paths = array_filter(self::$include_paths,
			function ($path) use($pattern) {
				return !preg_match($pattern, $path);
			});
	}

	/**
	 * Add include path, useful for unit testing of external libraries
	 */
	public static function add_include_path($path) {
		self::$include_paths[] = $path;
	}

	/**
	 * Get a config item or group.
	 *
	 * @param   string   item name
	 * @param   boolean  force a forward slash (/) at the end of the item
	 * @param   boolean  is the item required?
	 * @return  mixed
	 */
	public static function config($key, $slash = FALSE, $required = TRUE)
	{
		if (self::$configuration === NULL)
		{
			// Load core configuration
			self::$configuration['core'] = self::config_load('core');

			// Re-parse the include paths
			self::include_paths(TRUE);
		}

		// Get the group name from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		if ( ! isset(self::$configuration[$group]))
		{
			// Load the configuration group
			self::$configuration[$group] = self::config_load($group, $required);
		}

		// Get the value of the key string
		$value = self::key_string(self::$configuration, $key);

		if ($slash === TRUE AND is_string($value) AND $value !== '')
		{
			// Force the value to end with "/"
			$value = rtrim($value, '/').'/';
		}

		return $value;
	}

	/**
	 * Sets a configuration item, if allowed.
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function config_set($key, $value)
	{
		// Do this to make sure that the config array is already loaded
		self::config($key);

		if (substr($key, 0, 7) === 'routes.')
		{
			// Routes cannot contain sub keys due to possible dots in regex
			$keys = explode('.', $key, 2);
		}
		else
		{
			// Convert dot-noted key string to an array
			$keys = explode('.', $key);
		}

		// Used for recursion
		$conf =& self::$configuration;
		$last = count($keys) - 1;

		foreach ($keys as $i => $k)
		{
			if ($i === $last)
			{
				$conf[$k] = $value;
			}
			else
			{
				$conf =& $conf[$k];
			}
		}

		if ($key === 'core.modules')
		{
			// Reprocess the include paths
			self::include_paths(TRUE);
		}

		return TRUE;
	}

	/**
	 * Load a config file.
	 *
	 * @param   string   config filename, without extension
	 * @param   boolean  is the file required?
	 * @return  array
	 */
	public static function config_load($name, $required = TRUE)
	{
		/* By some reason, the config file "config" is called "core" in Kohana */
		if ($name === 'core')
			$name = 'config';

		if (isset(self::$internal_cache['configuration'][$name]))
			return self::$internal_cache['configuration'][$name];

		// Load matching configs
		$configuration = array();

		if ($files = self::find_file('config', $name, $required))
		{

			foreach ($files as $file)
			{
				require $file;

				if (isset($config) AND is_array($config))
				{
					// Merge in configuration
					$configuration = array_merge($configuration, $config);
				}
			}
		}

		if ($files = self::find_file('config/custom', $name, false))
		{
			foreach ($files as $file)
			{
				require $file;
				if (isset($config) and is_array($config))
				{
					$configuration = array_merge($configuration, $config);
				}
			}
		}

		return self::$internal_cache['configuration'][$name] = $configuration;
	}

	/**
	 * Clears a config group from the cached configuration.
	 *
	 * @param   string  config group
	 * @return  void
	 */
	public static function config_clear($group)
	{
		// Remove the group from config
		unset(self::$configuration[$group], self::$internal_cache['configuration'][$group]);
	}

	/**
	 * Add a new message to the log.
	 *
	 * @param   string  type of message
	 * @param   string  message text
	 * @return  void
	 */
	public static function log($type, $message)
	{
		op5log::instance()->log('ninja', $type, $message);
	}

	/**
	 * Load data from a simple cache file. This should only be used internally,
	 * and is NOT a replacement for the Cache library.
	 *
	 * @param   string   unique name of cache
	 * @param   integer  expiration in seconds
	 * @return  mixed
	 */
	public static function cache($name, $lifetime)
	{
		if ($lifetime > 0)
		{
			$path = APPPATH.'cache/kohana_'.$name;

			if (is_file($path))
			{
				// Check the file modification time
				if ((time() - filemtime($path)) < $lifetime)
				{
					// Cache is valid
					return unserialize(file_get_contents($path));
				}
				else
				{
					// Cache is invalid, delete it
					unlink($path);
				}
			}
		}

		// No cache found
		return NULL;
	}

	/**
	 * Save data to a simple cache file. This should only be used internally, and
	 * is NOT a replacement for the Cache library.
	 *
	 * @param   string   cache name
	 * @param   mixed    data to cache
	 * @param   integer  expiration in seconds
	 * @return  boolean
	 */
	public static function cache_save($name, $data, $lifetime)
	{
		if ($lifetime < 1)
			return FALSE;

		$path = APPPATH.'cache/kohana_'.$name;

		if ($data === NULL)
		{
			// Delete cache
			return (is_file($path) and unlink($path));
		}
		else
		{
			// Write data to cache file
			return (bool) file_put_contents($path, serialize($data));
		}
	}

	/**
	 * Displays a 404 page.
	 *
	 * @throws  Kohana_404_Exception
	 * @param   string  URI of page
	 * @param   string  custom template
	 * @return  void
	 */
	public static function show_404($page = FALSE, $template = FALSE)
	{
		throw new Kohana_404_Exception($page, $template);
	}

	/**
	 * Error handler. Send an exception upon PHP errors to enter the normal
	 * error/exception handling scheme.
	 *
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param string $errline
	 * @param string $errcontext
	 * @throws Kohana_PHPError_Exception
	 */
	public static function error_handler($errno, $errstr,  $errfile = NULL, $errline = NULL, $errcontext = NULL) {
		// Test to see if errors should be displayed
		if ((error_reporting() & $errno) === 0)
			return;
		throw new Kohana_PHPError_Exception($errno, $errstr, $errfile, $errline, $errcontext);
	}

	/**
	 * Exception handler. Uses the kohana_error_page view to display the message,
	 * if exception isn't a Kohana_Exception, in that case, obey the exception.
	 *
	 * @param   object  exception object
	 * @return  void
	 */
	public static function exception_handler($exception)
	{
		try {
			// This is useful for hooks to determine if a page has an error
			self::$has_error = TRUE;

			$code     = $exception->getCode();
			$type     = get_class($exception);
			$message  = $exception->getMessage();
			$file     = $exception->getFile();
			$line     = $exception->getLine();
			$template = ($exception instanceof Kohana_Exception) ? $exception->getTemplate() : 'kohana_error_page';

			if (is_numeric($code))
			{
				$codes = self::lang('errors');

				if ( ! empty($codes[$code]))
				{
					list($level, $error, $description) = $codes[$code];
				}
				else
				{
					$level = 1;
					$error = get_class($exception);
					$description = '';
				}
			}
			else
			{
				// Custom error message, this will never be logged
				$level = 5;
				$error = $code;
				$description = '';
			}

			// Remove the DOCROOT from the path, as a security precaution
			$file = str_replace('\\', '/', realpath($file));
			$file = preg_replace('|^'.preg_quote(DOCROOT).'|', '', $file);

			self::log('error', self::lang('core.uncaught_exception', $type, $message, $file, $line));

			if(!headers_sent()) {
				if (method_exists($exception, 'sendHeaders'))
				{
					$exception->sendHeaders();
				} else {
					header('HTTP/1.1 500 Internal Server Error');
				}
			}

			// Test if display_errors is on
			if (self::$configuration['core']['display_errors'] === TRUE)
			{
				if ($line != FALSE)
				{
					// Remove the first entry of debug_backtrace(), it is the exception_handler call
					$trace = $exception->getTrace();

					// Beautify backtrace
					$trace = new View('kohana/backtrace', array('trace'=>$trace));
				}

				// Load the error
				require self::find_file('views', empty($template) ? 'kohana_error_page' : $template);
			}
			else
			{
				// Get the i18n messages
				$error   = self::lang('core.generic_error');
				$message = self::lang('core.errors_disabled', url::site(), url::site(Router::$current_uri));

				// Load the errors_disabled view
				require self::find_file('views', 'kohana_error_disabled');
			}

			if ( ! Event::has_run('system.shutdown'))
			{
				// Run the shutdown even to ensure a clean exit
				Event::run('system.shutdown');
			}

			// Turn off error reporting
			error_reporting(0);
		} catch( Exception $e ) {
			/* Exceptions in an exceptionhandler results in "Exception thrown without a stack trace in "Unkonwn"
			 * Better to just print the exception ugly, so we get some kind of useful information instaead
			 */
			while( @ob_end_clean() ) {}
			print "Exception during error handler: ".$e->getMessage()."\n";
			print $e->getTraceAsString();

		}
		exit;
	}

	/**
	 * Provides class auto-loading.
	 *
	 * @throws  Kohana_Exception
	 * @param   string  name of class
	 * @return  bool
	 */
	public static function auto_load($class)
	{
		if (class_exists($class, FALSE))
			return TRUE;

		if (($suffix = strrpos($class, '_')) > 0)
		{
			// Find the class suffix
			$suffix = substr($class, $suffix + 1);
		}
		else
		{
			// No suffix
			$suffix = FALSE;
		}

		if ($suffix === 'Core')
		{
			$type = 'libraries';
			$file = substr($class, 0, -5);
		}
		elseif ($suffix === 'Controller')
		{
			$type = 'controllers';
			// Lowercase filename
			$file = strtolower(substr($class, 0, -11));
		}
		elseif ($suffix === 'Model')
		{
			$type = 'models';
			// Lowercase filename
			$file = strtolower(substr($class, 0, -6));
		}
		elseif ($suffix === 'Driver')
		{
			$type = 'libraries/drivers';
			$file = str_replace('_', '/', substr($class, 0, -7));
		}
		elseif ($suffix === 'Widget')
		{
			$type = 'widgets';
			$classname = substr($class, 0, -7);
			$file = $classname . '/' . $classname;
		}
		else
		{
			// This could be either a library or a helper, but libraries must
			// always be capitalized, so we check if the first character is
			// uppercase. If it is, we are loading a library, not a helper.
			$type = ($class[0] < 'a') ? 'libraries' : 'helpers';
			$file = $class;
		}

		if ($filename = self::find_file($type, $file))
		{
			// Load the class
			require $filename;
		}
		else
		{
			// The class could not be found
			return FALSE;
		}

		if ($filename = self::find_file($type, self::$configuration['core']['extension_prefix'].$class))
		{
			// Load the class extension
			require $filename;
		}
		elseif ($suffix !== 'Core' AND class_exists($class.'_Core', FALSE))
		{
			// Class extension to be evaluated
			$extension = 'class '.$class.' extends '.$class.'_Core { }';

			// Start class analysis
			$core = new ReflectionClass($class.'_Core');

			if ($core->isAbstract())
			{
				// Make the extension abstract
				$extension = 'abstract '.$extension;
			}

			// Transparent class extensions are handled using eval. This is
			// a disgusting hack, but it gets the job done.
			eval($extension);
		}

		return TRUE;
	}

	/**
	 * Find a resource file in a given directory. Files will be located according
	 * to the order of the include paths. Configuration and i18n files will be
	 * returned in reverse order.
	 *
	 * @throws  Kohana_Exception  if file is required and not found
	 * @param   string   directory to search in
	 * @param   string   filename to look for (including extension only if 4th parameter is TRUE)
	 * @param   boolean  file required
	 * @param   string   file extension
	 * @return  array    if the type is config, i18n or l10n
	 * @return  string   if the file is found
	 * @return  FALSE    if the file is not found
	 */
	public static function find_file($directory, $filename, $required = FALSE, $ext = FALSE)
	{
		// If $ext === false, the caller want the default extension
		if ($ext === false)
			$ext = EXT;

		// Make sure the extension starts with an ., if not empty
		if($ext !== '')
			$ext = '.'.ltrim($ext,'.');

		// Search path
		$search = $directory.'/'.$filename.$ext;

		if (isset(self::$internal_cache['find_file_paths'][$search]))
			return self::$internal_cache['find_file_paths'][$search];

		// Load include paths
		$paths = self::$include_paths;

		// Nothing found, yet
		$found = NULL;

		if ($directory === 'config' OR $directory === 'i18n' OR $directory === 'config/custom')
		{
			// Search in reverse, for merging
			$paths = array_reverse($paths);

			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					// A matching file has been found
					$found[] = $path.$search;
				}
			}
		}
		else
		{
			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					// A matching file has been found
					$found = $path.$search;

					// Stop searching
					break;
				}
			}
		}

		if ($found === NULL)
		{
			if ($required === TRUE)
			{
				// Directory i18n key
				$directory = 'core.'.inflector::singular($directory);

				// If the file is required, throw an exception
				throw new Kohana_Exception('core.resource_not_found', self::lang($directory), $filename);
			}
			else
			{
				// Nothing was found, return FALSE
				$found = FALSE;
			}
		}

		return self::$internal_cache['find_file_paths'][$search] = $found;
	}

	/**
	 * Lists all files and directories in a resource path.
	 *
	 * @param   string   directory to search
	 * @param   boolean  list all files to the maximum depth?
	 * @param   string   full path to search (used for recursion, *never* set this manually)
	 * @return  array    filenames and directories
	 */
	public static function list_files($directory, $recursive = FALSE, $path = FALSE)
	{
		$files = array();

		if ($path === FALSE)
		{
			$paths = array_reverse(self::include_paths());

			foreach ($paths as $path)
			{
				// Recursively get and merge all files
				$files = array_merge($files, self::list_files($directory, $recursive, $path.$directory));
			}
		}
		else
		{
			$path = rtrim($path, '/').'/';

			if (is_readable($path))
			{
				$items = (array) glob($path.'*');

				foreach ($items as $index => $item)
				{
					$files[] = $item = str_replace('\\', '/', $item);

					// Handle recursion
					if (is_dir($item) AND $recursive == TRUE)
					{
						// Filename should only be the basename
						$item = pathinfo($item, PATHINFO_BASENAME);

						// Append sub-directory search
						$files = array_merge($files, self::list_files($directory, TRUE, $path.$item));
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Fetch an i18n language item.
	 *
	 * @param   string  language key to fetch
	 * @param   array   additional information to insert into the line
	 * @return  string  i18n language string, or the requested key if the i18n item is not found
	 */
	public static function lang($key, $args = array())
	{
		// Extract the main group from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		// Get locale name
		$locale = self::config('locale.language.0');

		if ( ! isset(self::$internal_cache['language'][$locale][$group]))
		{
			// Messages for this group
			$messages = array();

			if ($files = self::find_file('i18n', $locale.'/'.$group))
			{
				foreach ($files as $file)
				{
					include $file;

					// Merge in configuration
					if ( ! empty($lang) AND is_array($lang))
					{
						foreach ($lang as $k => $v)
						{
							$messages[$k] = $v;
						}
					}
				}
			}

			self::$internal_cache['language'][$locale][$group] = $messages;
		}

		// Get the line from cache
		$line = self::key_string(self::$internal_cache['language'][$locale], $key);

		if ($line === NULL)
		{
			self::log('error', 'Missing i18n entry '.$key.' for language '.$locale);

			// Return the key string as fallback
			return $key;
		}

		if (is_string($line) AND func_num_args() > 1)
		{
			$args = array_slice(func_get_args(), 1);

			// Add the arguments into the line
			$line = vsprintf($line, is_array($args[0]) ? $args[0] : $args);
		}

		return $line;
	}

	/**
	 * Returns the value of a key, defined by a 'dot-noted' string, from an array.
	 *
	 * @param   array   array to search
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  string  if the key is found
	 * @return  void    if the key is not found
	 */
	public static function key_string($array, $keys)
	{
		if (empty($array))
			return NULL;

		// Prepare for loop
		$keys = explode('.', $keys);

		do
		{
			// Get the next key
			$key = array_shift($keys);

			if (isset($array[$key]))
			{
				if (is_array($array[$key]) AND ! empty($keys))
				{
					// Dig down to prepare the next loop
					$array = $array[$key];
				}
				else
				{
					// Requested key was found
					return $array[$key];
				}
			}
			else
			{
				// Requested key is not set
				break;
			}
		}
		while ( ! empty($keys));

		return NULL;
	}

	/**
	 * Sets values in an array by using a 'dot-noted' string.
	 *
	 * @param   array   array to set keys in (reference)
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  mixed   fill value for the key
	 * @return  void
	 */
	public static function key_string_set( & $array, $keys, $fill = NULL)
	{
		if (is_object($array) AND ($array instanceof ArrayObject))
		{
			// Copy the array
			$array_copy = $array->getArrayCopy();

			// Is an object
			$array_object = TRUE;
		}
		else
		{
			if ( ! is_array($array))
			{
				// Must always be an array
				$array = (array) $array;
			}

			// Copy is a reference to the array
			$array_copy =& $array;
		}

		if (empty($keys))
			return $array;

		// Create keys
		$keys = explode('.', $keys);

		// Create reference to the array
		$row =& $array_copy;

		for ($i = 0, $end = count($keys) - 1; $i <= $end; $i++)
		{
			// Get the current key
			$key = $keys[$i];

			if ( ! isset($row[$key]))
			{
				if (isset($keys[$i + 1]))
				{
					// Make the value an array
					$row[$key] = array();
				}
				else
				{
					// Add the fill key
					$row[$key] = $fill;
				}
			}
			elseif (isset($keys[$i + 1]))
			{
				// Make the value an array
				$row[$key] = (array) $row[$key];
			}

			// Go down a level, creating a new row reference
			$row =& $row[$key];
		}

		if (isset($array_object))
		{
			// Swap the array back in
			$array->exchangeArray($array_copy);
		}
	}

	/**
	 * Retrieves current user agent information:
	 * keys:  browser, version, platform, mobile, robot, referrer, languages, charsets
	 * tests: is_browser, is_mobile, is_robot, accept_lang, accept_charset
	 *
	 * @param   string   key or test name
	 * @param   string   used with "accept" tests: user_agent(accept_lang, en)
	 * @return  array    languages and charsets
	 * @return  string   all other keys
	 * @return  boolean  all tests
	 */
	public static function user_agent($key = 'agent', $compare = NULL)
	{
		static $info;

		// Return the raw string
		if ($key === 'agent')
			return self::$user_agent;

		if ($info === NULL)
		{
			// Parse the user agent and extract basic information
			$agents = self::config('user_agents');

			foreach ($agents as $type => $data)
			{
				foreach ($data as $agent => $name)
				{
					if (stripos(self::$user_agent, $agent) !== FALSE)
					{
						if ($type === 'browser' AND preg_match('|'.preg_quote($agent).'[^0-9.]*+([0-9.][0-9.a-z]*)|i', self::$user_agent, $match))
						{
							// Set the browser version
							$info['version'] = $match[1];
						}

						// Set the agent name
						$info[$type] = $name;
						break;
					}
				}
			}
		}

		if (empty($info[$key]))
		{
			switch ($key)
			{
				case 'is_robot':
				case 'is_browser':
				case 'is_mobile':
					// A boolean result
					$return = ! empty($info[substr($key, 3)]);
				break;
				case 'languages':
					$return = array();
					if ( ! empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
					{
						if (preg_match_all('/[-a-z]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])), $matches))
						{
							// Found a result
							$return = $matches[0];
						}
					}
				break;
				case 'charsets':
					$return = array();
					if ( ! empty($_SERVER['HTTP_ACCEPT_CHARSET']))
					{
						if (preg_match_all('/[-a-z0-9]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET'])), $matches))
						{
							// Found a result
							$return = $matches[0];
						}
					}
				break;
				case 'referrer':
					if ( ! empty($_SERVER['HTTP_REFERER']))
					{
						// Found a result
						$return = trim($_SERVER['HTTP_REFERER']);
					}
				break;
			}

			// Cache the return value
			isset($return) and $info[$key] = $return;
		}

		if ( ! empty($compare))
		{
			// The comparison must always be lowercase
			$compare = strtolower($compare);

			switch ($key)
			{
				case 'accept_lang':
					// Check if the lange is accepted
					return in_array($compare, self::user_agent('languages'));
				break;
				case 'accept_charset':
					// Check if the charset is accepted
					return in_array($compare, self::user_agent('charsets'));
				break;
				default:
					// Invalid comparison
					return FALSE;
				break;
			}
		}

		// Return the key, if set
		return isset($info[$key]) ? $info[$key] : NULL;
	}

	/**
	 * Quick debugging of any variable. Any number of parameters can be set.
	 *
	 * @return  string
	 */
	public static function debug()
	{
		if (func_num_args() === 0)
			return;

		// Get params
		$params = func_get_args();
		$output = array();

		foreach ($params as $var)
		{
			$output[] = '<pre>('.gettype($var).') '.html::specialchars(print_r($var, TRUE)).'</pre>';
		}

		return implode("\n", $output);
	}

} // End Kohana

/**
 * Creates a generic i18n exception.
 */
class Kohana_Exception extends Exception {

	// Template file
	protected $template = 'kohana_error_page';

	// Header
	protected $header = FALSE;

	// Error code
	protected $code = E_KOHANA;

	/**
	 * Set exception message.
	 *
	 * @param  string  i18n language key for the message
	 * @param  array   addition line parameters
	 */
	public function __construct($error)
	{
		$args = array_slice(func_get_args(), 1);

		// Fetch the error message
		$message = Kohana::lang($error, $args);

		if ($message === $error OR empty($message))
		{
			// Unable to locate the message for the error
			$message = 'Unknown Exception: '.$error;
		}

		// Sets $this->message the proper way
		parent::__construct($message);
	}

	/**
	 * Magic method for converting an object to a string.
	 *
	 * @return  string  i18n message
	 */
	public function __toString()
	{
		return (string) $this->message;
	}

	/**
	 * Fetch the template name.
	 *
	 * @return  string
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * Sends an Internal Server Error header.
	 *
	 * @return  void
	 */
	public function sendHeaders()
	{
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
	}

} // End Kohana Exception

/**
 * Creates a custom exception.
 */
class Kohana_User_Exception extends Kohana_Exception {

	/**
	 * Set exception title and message.
	 *
	 * @param   string  exception title string
	 * @param   string  exception message string
	 * @param   string  custom error template
	 */
	public function __construct($title, $message, $template = FALSE)
	{
		Exception::__construct($message);

		$this->code = $title;

		if ($template !== FALSE)
		{
			$this->template = $template;
		}
	}

} // End Kohana PHP Exception

/**
 * Creates a Page Not Found exception.
 */
class Kohana_404_Exception extends Kohana_Exception {

	protected $code = E_PAGE_NOT_FOUND;

	/**
	 * Set internal properties.
	 *
	 * @param  string  URL of page
	 * @param  string  custom error template
	 */
	public function __construct($page = FALSE, $template = FALSE)
	{
		if ($page === FALSE)
		{
			// Construct the page URI using Router properties
			$page = Router::$current_uri.Router::$url_suffix.Router::$query_string;
		}

		Exception::__construct(Kohana::lang('core.page_not_found', $page));

		$this->template = $template;
	}

	/**
	 * Sends "File Not Found" headers, to emulate server behavior.
	 *
	 * @return void
	 */
	public function sendHeaders()
	{
		// Send the 404 header
		header('HTTP/1.1 404 File Not Found');
	}

} // End Kohana 404 Exception

/**
 * Exception used to track PHP Errors
 */
class Kohana_PHPError_Exception extends Kohana_Exception {
	protected $errcontext;

	public function __construct($errno, $errstr,  $errfile = NULL, $errline = NULL, $errcontext = NULL) {
		Exception::__construct($errstr, $errno);
		$this->file = $errfile;
		$this->line = $errline;
		$this->errcontext = $errcontext;
	}
}
