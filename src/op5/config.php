<?php

require_once(__DIR__.'/spyc.php');
require_once(__DIR__.'/objstore.php');

/**
 * Configuration Exceptions
 *
 * @package default
 **/
class ConfigException extends Exception
{
} // END class ConfigException extends Exception

class op5config {
	private $basepath     = '/etc/op5/';
	private $apc_enabled = false; /* Sets to true if apc_fetch exists */
	private $apc_ttl     = 10;
	const RESERVED_PREFIX = '__'; // Prefix for reserved keys

	/**
	 * Create an instance of op5Config.
	 *
	 * @param options an arrary of options
	 * @return  object
	 */
	public static function factory($options=false)
	{
		return new self($options);
	}

	/**
	 * Return a static instance of op5Config.
	 *
	 * @return  op5Config instance of config object
	 */
	public static function instance($options=false)
	{
		return op5objstore::instance()->obj_instance(__CLASS__, $options);
	}

	/**
	 * __contruct
	 *
	 * @param $options array
	 * @return void
	 **/
	public function __construct($options=false)
	{
		$basepath = false;
		if (isset($options['basepath']))
			$basepath = $options['basepath'];
		else if (getenv('OP5LIBCFG')) {
			$basepath = getenv('OP5LIBCFG');
		}
		if ($basepath) {
			if ($basepath[strlen($basepath)-1] != '/')
				$basepath .= '/';
			$this->basepath = $basepath;
		}

		$this->apc_enabled = function_exists('apc_fetch');
	}

	/**
	 * Get config for supplied namespace
	 * If $reserved is set additional info about the configuration
	 * will be returned, i.e. version
	 *
	 * @param $parameter mixed
	 * @param $reserved boolean
	 * @return mixed
	 **/
	public function getConfig($parameter, $reserved = false)
	{
		$config = $this->getConfigVar(explode('.',$parameter), $this->basepath);
		if (!$reserved && is_array($config)) {
			$config = $this->cleanConfigArray($config);
		}
		return $config;
	}

	/**
	 * Set config for supplied namespace
	 *
	 * @param $parameter string
	 * @param $array array
	 * @param $set_reserved boolean
	 * @return void
	 * @throws ConfigException if file is unwritable
	 */
	public function setConfig($parameter, $array, $set_reserved = false)
	{
		$path = $this->getPathForNamespace($parameter);
		if (false === $this->setConfigFile($path, $array, $set_reserved)) {
			throw new ConfigException("Could not write to $path");
		}
	}

	/**
	 * Get individual config parameters from yml-file
	 *
	 * @param $parameter array
	 * @param $path string
	 * @return mixed
	 **/
	protected function getConfigVar($parameter, $path)
	{
		/* Parameter array is empty; fetch tree */
		if (count($parameter) == 0) {
			return $this->getConfigFile($path.'.yml');
		/* Parameter tree isn't empty, step into */
		} else {
			$head = array_shift($parameter);
			/* head is a yml file, just fetch the parameter without recursion and exit */
			$value = $this->getConfigFile($path . '/' . $head . '.yml');
			while(count($parameter)) {
				$head = array_shift($parameter);
				if (isset($value[$head])) {
					$value = $value[$head];
				} else {
					return null;
				}
			}
			return $value;
		}
	}

	/**
	 * Returns the path to supplied namespace
	 *
	 * @param $namespace string
	 * @return string
	 **/
	public function getPathForNamespace($namespace)
	{
		return $this->basepath . $namespace . '.yml';
	}

	/**
	 * Returns content of yaml config file as array
	 *
	 * @param $path string
	 * @return array
	 **/
	protected function getConfigFile($path)
	{
		if ($this->apc_enabled) {
			$array = apc_fetch($this->apc_tag_for_path($path), $success);
			if ($success) {
				return $array;
			}
		}

		$array = null;
		if (is_readable($path)) {
			$array = Spyc::YAMLLoad($path);
		}

		if ($this->apc_enabled) {
			apc_store($this->apc_tag_for_path($path), $array, (int) $this->apc_ttl);
		}
		return $array;
	}

	/**
	 * Writes array to yaml config file
	 *
	 * @param $path string
	 * @param $array array
	 * @param $set_reserved boolean
	 * @return boolean
	 **/
	protected function setConfigFile($path, $array, $set_reserved = false)
	{
		if ($this->apc_enabled) {
			/* TODO: Use store instead... but I want to verify that it's stored correctly */
			apc_delete($this->apc_tag_for_path($path));
		}

		if (!$set_reserved) {
			$array = $this->cleanConfigArray($array);
		}

		// Preserve special keys
		$presentYaml = $this->getConfigFile($path);
		if (is_array($presentYaml)) {
			foreach (array_keys($presentYaml) as $key) {
				if (substr($key, 0, strlen(static::RESERVED_PREFIX)) === static::RESERVED_PREFIX) {
					// Store this value with the new array
					$array[$key] = $presentYaml[$key];
				}
			}
		}

		$spyc = new Spyc();
		$spyc->setting_dump_force_quotes = true;
		$yaml = $spyc->dump($array);
		return (bool) file_put_contents( $path, $yaml );
	}

	/**
	 * Rename keys or values across all configuration files based on a replace map
	 *
	 * @param $path string, the path, with wildcards, to replace
	 * @param $type string, "key" or "value"
	 * @param $old_value string
	 * @param $new_value string
	 * @return boolean
	 **/
	public function cascadeEditConfig($path, $type, $old_value, $new_value)
	{
		$path = explode('.', $path);
		$namespace = array_shift($path);
		$config = $this->getConfig($namespace);
		$modifiedConfig = $this->recursiveReplace($config, $path, $type, $old_value, $new_value);
		$this->setConfig($namespace, $modifiedConfig);
	}

	/**
	 * Recursively replace config parameters in configuration files
	 * based on a given path
	 *
	 * @param $config array
	 * @param $path array
	 * @param $type string
	 * @param $old string
	 * @param $new string
	 * @throws ConfigException
	 * @return array
	 **/
	private function recursiveReplace($config, $path, $type, $old, $new)
	{
		$new_config = array();
		foreach ($config as $key => $value) {
			if (count($path) === 1 && $path[0] === '*') {
				// We have reached the end of the path, check for matching keys/values
				switch ($type) {
					case 'key':
						if ($key === $old) {
							$new_config[$new] = $value;
						} else {
							$new_config[$key] = $value;
						}
						break;
					case 'value':
						if ($value === $old) {
							$new_config[$key] = $new;
						} else {
							$new_config[$key] = $value;
						}
						break;
					default:
						// TODO: Bail earlier?
						throw new ConfigException("Unexpected type: $type is not valid for config parameter replacement");
						break;
				}
			} else if (count($path) > 1 && $path[0] !== '*') {
				// Recurse down a named path
				$named_path = $path[0];
				if (is_array($value) && $key === $named_path) {
					// Path was found
					$new_config[$named_path] = $this->recursiveReplace($value, array_slice($path, 1), $type, $old, $new);
				} else {
					// Not the path we were looking for
					$new_config[$key] = $value;
				}
			} else if (count($path) > 1 && $path[0] === '*') {
				// Recurse down all paths
				if (is_array($value)) {
					$new_config[$key] = $this->recursiveReplace($value, array_slice($path, 1), $type, $old, $new);
				} else {
					$new_config[$key] = $value;
				}
			}
		}
		return $new_config;
	}

	/**
	 * Removes array keys that is not valid for storage
	 *
	 * @param $array arrray
	 * @return array
	 **/
	private function cleanConfigArray($array)
	{
		foreach (array_keys($array) as $key) {
			if (substr($key, 0, strlen(static::RESERVED_PREFIX)) === static::RESERVED_PREFIX) {
				unset($array[$key]);
			}
		}
		return $array;
	}

	/**
	 * Hashes path tag for apc
	 *
	 * @param $path string
	 * @return string
	 **/
	protected function apc_tag_for_path($path) {
		/* Fix path with realpath before hashing. Fixes double slashes and relative paths */
		$tag = 'op5_config_' . md5(realpath($path));
		return $tag;
	}
}

