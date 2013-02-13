<?php defined('SYSPATH') OR die('No direct access allowed.');

class Module_Manifest_Model {
	private static $manifests = array();
	
	private static function load_manifest( $name ) {
		$manifest = array();
		$files = glob(MODPATH . '*/manifest/'.$name.'.php' );
		foreach( $files as $file ) {
			require( $file );
		}
		return $manifest;
	}
	
	public static function get( $name ) {
		if( !isset( self::$manifests[$name] ) ) {
			self::$manifests[$name] = self::load_manifest( $name ); 
		}
		return self::$manifests[$name];
	}
}