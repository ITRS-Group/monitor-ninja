<?php

call_user_func(function () {

	$root = dirname(__FILE__);
	$include_paths = array();

	/**
	 * Set include paths for all modules that we have
	 */
	foreach (glob($root . '/modules/*', GLOB_ONLYDIR) as $path) {
		$include_paths[] = $path;
	}

	$include_paths[] = $root . '/application';
	$include_paths[] = $root . '/system';

	/**
	 * This is a "copy" of the Kohana::find_file that does not depend on
	 * any global constants being set
	 */
	$file_cache = array();
	$locate = function ($directory, $filename) use (&$file_cache, &$include_paths) {

		$search = $directory.'/'.$filename.'.php';
		if (isset($file_cache[$search])) return $file_cache[$search];

		$paths = $include_paths;
		$found = NULL;

		if ($directory === 'config' OR $directory === 'i18n' OR $directory === 'config/custom') {
			$paths = array_reverse($paths);
			foreach ($paths as $path) {
				if (is_file($path.'/'.$search)) {
					$found[] = $path.'/'.$search;
				}
			}
		} else {
			foreach ($paths as $path) {
				if (is_file($path.'/'.$search)) {
					$found = $path.'/'.$search;
					break;
				}
			}
		}

		if ($found === NULL) $found = false;
		return $file_cache[$search] = $found;

	};

	/**
	 * This replicates the behaviour that was previously in the Kohana::auto_load
	 * but does not utilize global constants.
	 */
	$autoloader = function ($class) use ($locate) {

		$suffix = (($suffix = strrpos($class, '_')) > 0) ? substr($class, $suffix + 1) : false;

		if ($suffix === 'Controller') {
			$type = 'controllers';
			$file = strtolower(substr($class, 0, -11));
		} elseif ($suffix === 'Model') {
			$type = 'models';
			$file = strtolower(substr($class, 0, -6));
		} elseif ($suffix === 'Driver') {
			$type = 'libraries/drivers';
			$file = str_replace('_', '/', substr($class, 0, -7));
		} elseif ($suffix === 'Widget') {
			$type = 'widgets';
			$classname = substr($class, 0, -7);
			$file = $classname . '/' . $classname;
		} else {
			$type = ($class[0] < 'a') ? 'libraries' : 'helpers';
			$file = $class;
		}

		/**
		 * If we find the file we require it and "hopefully" the class should
		 * now be availables.
		 */
		if ($filename = $locate($type, $file)) {
			require $filename;
			return true;
		} else return false;

	};

	spl_autoload_register($autoloader);

});
