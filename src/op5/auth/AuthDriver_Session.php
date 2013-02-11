<?php

require_once('op5/auth/AuthDriver.php' );
require_once('op5/auth/User.php' );
require_once('op5/config.php' );

/**
 * User authentication and authorization library.
 *
 * @package    Auth
 * @author     
 * @copyright  
 * @license    
 */
class op5AuthDriver_Session extends op5AuthDriver {
	
	/**
	 * Attempt to log in a user by static configuration, or external infromation.
	 *
	 * Useful for example for HTTP-auth.
	 *
	 * @return  op5User  User object, or false
	 */
	public function auto_login() {
		$params = array();
		
		if( isset( $this->config['username_session_key'] ) ) {
			$params['username'] = $_SESSION[$this->config['username_session_key']];
		} else {
			return false;
		}
		
		if( isset( $this->config['groups_session_key'] ) ) {
			$params['groups'] = $_SESSION[$this->config['groups_session_key']];
		}
		
		if( isset( $this->config['single_shot'] ) && $this->config['single_shot'] ) {
			unset( $_SESSION[$this->config['username_session_key']] );
			unset( $_SESSION[$this->config['groups_session_key']] );
		}
		
		return new op5User( $params );
	}
} // End Auth
