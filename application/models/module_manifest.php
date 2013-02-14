<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle manifest files from modules
 */
class Module_Manifest_Model {
	private static $manifests = array();
	
	/**
	 * Load manifest files from modules manifest directories
	 */
	private static function load_manifest( $name ) {
		$manifest = array();
		if(!preg_match('/^[a-z_]+$/',$name)) {
			return array();
		}
		$files = glob(MODPATH . '*/manifest/'.$name.'.php' );
		foreach( $files as $file ) {
			require( $file );
		}
		return $manifest;
	}
	
	/**
	 * Load manifest parameters from modules
	 */
	public static function get( $name ) {
		if( !isset( self::$manifests[$name] ) ) {
			self::$manifests[$name] = self::load_manifest( $name ); 
		}
		return self::$manifests[$name];
	}
}