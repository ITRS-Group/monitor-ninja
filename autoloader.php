<?php

call_user_func(function () {

	$root = dirname(__FILE__);

	/**
	 * Set include paths for all modules that we have
	 */
	$include_paths = glob($root . '/modules/*', GLOB_ONLYDIR);
	$include_paths[] = $root . '/application';
	$include_paths[] = $root . '/system';

	/**
	 * This function locates classes as Kohanas find_file does it
	 * but can ignore caching since a class will never be located
	 * twice once included, and ignore config files and more since
	 * this can only load classes.
	 */
	$locate = function ($directory, $filename) use ($include_paths) {

		$file = false;
		$classpath = $directory.'/'.$filename.'.php';

		foreach ($include_paths as $path) {
			if (is_file($path.'/'.$classpath)) {
				$file = $path.'/'.$classpath;
				break;
			}
		}

		return $file;

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
