<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle manifest files from modules
 */
class Module_Manifest_Model {
	private $manifests = array();
	
	/**
	 * Make this internally a singleton, so we can clear the cache in tests.
	 */
	public static function instance() {
		return op5objstore::instance()->obj_instance(__CLASS__);
	}

	/**
	 * Load manifest files from modules manifest directories
	 */
	private function load_manifest( $name ) {
		if( isset( $this->manifests[$name] ) ) {
			return $this->manifests[$name];
		}

		$manifest = array();
		if(!preg_match('/^[a-z_]+$/',$name)) {
			return array();
		}

		$module_dirs = Kohana::include_paths();
		$suffixname = "manifest/$name.php";

		foreach( $module_dirs as $moddir ) {
			if(is_readable($moddir . $suffixname)) {
				require( $moddir . $suffixname );
			}
		}

		$this->manifests[$name] = $manifest;
		return $manifest;
	}
	
	/**
	 * Load manifest parameters from modules
	 */
	public static function get( $name ) {
		return self::instance()->load_manifest($name);
	}
}
