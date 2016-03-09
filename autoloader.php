<?php

call_user_func(function () {

	$root = dirname(__FILE__);
	$include_paths = array();

	$mod_path = $root . DIRECTORY_SEPARATOR . 'modules';
	$app_path = $root . DIRECTORY_SEPARATOR . 'application';
	$sys_path = $root . DIRECTORY_SEPARATOR . 'system';

	foreach (glob($mod_path . '/*', GLOB_ONLYDIR) as $path) {
		$include_paths[] = $path.'/';
	}

	$include_paths[] = $app_path;
	$include_paths[] = $sys_path;

	$file_cache = array();
	$find_file = function ($directory, $filename) use (&$file_cache, &$include_paths) {

		$search = $directory.'/'.$filename.'.php';
		if (isset($file_cache[$search])) return $file_cache[$search];

		$paths = $include_paths;
		$found = NULL;

		if ($directory === 'config' OR $directory === 'i18n' OR $directory === 'config/custom') {
			$paths = array_reverse($paths);
			foreach ($paths as $path) {
				if (is_file($path.$search)) {
					$found[] = $path.$search;
				}
			}
		} else {
			foreach ($paths as $path) {
				if (is_file($path.$search)) {
					$found = $path.$search;
					break;
				}
			}
		}

		if ($found === NULL) $found = FALSE;
		return $file_cache[$search] = $found;

	};

	$autoloader = function ($class) use ($find_file) {

		if (class_exists($class, FALSE)) return TRUE;
		$suffix = (($suffix = strrpos($class, '_')) > 0) ? substr($class, $suffix + 1) : false;

		if ($suffix === 'Core') {
			$type = 'libraries';
			$file = substr($class, 0, -5);
		} elseif ($suffix === 'Controller') {
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

		if ($filename = $find_file($type, $file)) require $filename;
		else return FALSE;

		if ($suffix !== 'Core' AND class_exists($class.'_Core', FALSE)) {

			$extension = 'class '.$class.' extends '.$class.'_Core { }';
			$core = new ReflectionClass($class.'_Core');

			if ($core->isAbstract()) {
				// Make the extension abstract
				$extension = 'abstract '.$extension;
			}

			// Transparent class extensions are handled using eval. This is
			// a disgusting hack, but it gets the job done.
			eval($extension);
		}

		return TRUE;

	};

	spl_autoload_register($autoloader);
});

