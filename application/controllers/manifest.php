<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controller to fetch manifest variables for usage in client side scripts
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.

 */
class Manifest_Controller extends Ninja_Controller {
	/**
	 * Load a manifest as an ajax request
	 */
	public function ajax( $name ) {
		$this->auto_render = false;
		$manifest = Module_Manifest_Model::get( $name );
		return json::ok( $manifest );
	}
	
	/**
	 * Return a manifest variable as a javascript file, for loading through a script tag
	 */
	public function js( $name ) {
		if( substr( $name, -3 ) == '.js' ) {
			$name = substr( $name, 0, -3 );
		}
		
		$this->auto_render = false;
		$manifest = Module_Manifest_Model::get( $name );
		
		header('Content-Type: text/javascript');
		
		print "var $name = " . json_encode( $manifest ) . ";";
	}
}
