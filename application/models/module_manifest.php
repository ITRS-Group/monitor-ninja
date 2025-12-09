<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle manifest files from modules
 */
class Module_Manifest_Model {
	private $manifests = [];
	
	/**
	 * Make this internally a singleton, so we can clear the cache in tests.
	 */
	public static function instance() {
		return op5objstore::instance()->obj_instance(__CLASS__);
	}

	/**
	 * Load manifest files from modules manifest directories
	 */
	private function load_manifest(string $name ) {
		if( isset( $this->manifests[$name] ) ) {
			return $this->manifests[$name];
		}

		$manifest = [];
		if(!preg_match('/^[a-z_]+$/',$name)) {
			return [];
		}

		$module_dirs = Kohana::include_paths();
		$suffixname = "manifest/$name.php";

		foreach( $module_dirs as $moddir ) {
			$filepath = $moddir . $suffixname;
			if (is_readable($filepath)) {
				require $filepath;
            }
		}

		$this->manifests[$name] = $manifest;
		return $manifest;
	}
	
	/**
	 * Load manifest parameters from modules
	 */
	public static function get( string $name ) {
		$instance = new self();
		return $instance->load_manifest($name);
	}
}
