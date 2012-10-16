<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vstukanov
 * Date: 9/7/11
 * Time: 12:55 PM
 * To change this template use File | Settings | File Templates.
 */
 
class Nagvis_acl_Model {
	static private $__instance = NULL;
	/**
	 * Get singleton instance
	 * @static
	 * @return Nagvis_acl_Model
	 */
	static public function getInstance() {
		if(self::$__instance === NULL) {
			self::$__instance = new self();
		}
		return self::$__instance;
	}

	
	private $op5auth = null;
	private $config = null;
	private $aPermissions = null;
	
	public function __construct() {
		$this->op5auth      = Op5Auth::factory(); // Using multiple auth instances is bad for your health, but this is just for reading...
		$this->op5conf     = Op5Config::instance();
		$this->config      = $this->op5conf->getConfig('nagvis'); // Using multiple auth instances is bad for your health, but this is just for reading...
		$this->custom_conf = $this->op5conf->getConfig('nagvis_custom');
		$this->aPermissions = $this->parsePermissions();
	}
	/**
	 * Creates a permission table with full permissions.
	 * 
	 * @param number $level Levels left to fill
	 * @return array Nagvis permission table that gives full permission of a
	 *               subtree
	 */
	private function fullPerm( $level=3 ) {
		if( $level <= 0 ) return array('*'); /* Just dont make it empty, array_filter should not remove this */
		return array( '*' => $this->fullPerm($level-1) );
	}

	/**
	 * Prepare the permissions array, taken form the nagvis_custom yml file and
	 * convert it so it can be interpretable from nagvis.
	 * 
	 * This means, expand allow_all tag to array( '*' => ... ) with a depth of 3
	 * and remove empty permission fields
	 * 
	 * @param array $permtbl table from yml
	 * @param number $level Levels left to recurse, used internally
	 * @return array output table
	 */
	private function preparePerm( $permtbl, $level=3 ) {
		$outp = array();
		if( isset( $permtbl['allow_all'] ) && $permtbl['allow_all'] ) {
			return $this->fullPerm( $level );
		}
		foreach( $permtbl as $perm => $subobj ) {
			if( is_array( $subobj ) ) {
				$outp[$perm] = $this->preparePerm( $subobj, $level-1 );
			}
		}
		/* If no permissions given under the element, remove it not to confuse nagvis */
		return array_filter( $outp );
	}

	/**
	 * Parses permissions from configuration and current auth status.
	 * 
	 * @return array Nagvis permission table
	 */
	public function parsePermissions() {
		$map_perm = array();
		if( $this->op5auth->authorized_for( 'nagvis_view' ) ) {
			$map_perm['view']   = array('*'=>array());
		}
		if( $this->op5auth->authorized_for( 'nagvis_edit' ) ) {
			$map_perm['edit']   = array('*'=>array());
		}
		if( $this->op5auth->authorized_for( 'nagvis_add_del' ) ) {
			$map_perm['add']    = array('*'=>array());
			$map_perm['delete'] = array('*'=>array());
		}
		
		$groups_per_type = array(
				'auth_groups'    => $this->op5auth->get_user()->groups,
				'contact_groups' => $this->op5auth->get_contact_groups()
				);
		
		/* Iterate through auth groups and contact groups (grouptypes) */
		foreach( $groups_per_type as $grouptype => $groups ) {
			if( isset( $this->config[$grouptype])) {
				/* Iterate trhough the groups */
				foreach( $groups as $group ) {
					if( isset( $this->config[$grouptype][$group] ) ) {
						/* Iterate through the maps related to the groups */
						foreach( $this->config[$grouptype][$group] as $map => $perms ) {
							/* Iterate through the permissions given to the map */
							foreach( $perms as $perm ) {
								$map_perm[$perm][$map] = array();
							}
						}
					}
				}
			}
		}
		
		$perm = array(
			'Overview' => array( 'view' => array( '*' => array() ) ),
			'General'  => array( '*'    => array( '*' => array() ) ),
			'AutoMap'  => $map_perm,
			'Map'      => $map_perm,
			'Rotation' => $map_perm
			);
		
		
		/* Fetch custom configuration overlay */
		$overlay = array();
		
		/* Fill with custom permissions */
		if( !empty( $this->custom_conf ) ) {
			/* Iterate through auth groups and contact groups (grouptypes) */
			foreach( $groups_per_type as $grouptype => $groups ) {
				if( isset( $this->custom_conf[$grouptype])) {
					foreach( $groups as $group ) {
						if( isset( $this->custom_conf[$grouptype][$group] ) ) {
							$overlay = array_merge_recursive( $overlay,
									$this->custom_conf[$grouptype][$group] );
						}
					}
				}
			}
		}
		$overlay = $this->preparePerm( $overlay );

		/* Merge custom configuration overlay and gui configuration */
		return array_merge_recursive( $perm, $overlay );
	}

	/**
	 * test if a module is permitted to do an action
	 * 
	 * @param unknown $sModule
	 * @param unknown $sAction
	 * @param string $sObj
	 * @return boolean
	 */
	public function isPermitted($sModule, $sAction, $sObj = null) {
		// Module access?
		$access = Array();
		if(isset($this->aPermissions[$sModule]))
			$access[$sModule] = Array();
		if(isset($this->aPermissions['*']))
			$access['*'] = Array();

		if(count($access) > 0) {
			// Action access?
			foreach($access AS $mod => $acts) {
				if(isset($this->aPermissions[$mod][$sAction]))
					$access[$mod][$sAction] = Array();
				if(isset($this->aPermissions[$mod]['*']))
					$access[$mod]['*'] = Array();
			}

			if(count($access[$mod]) > 0) {
				// Don't check object permissions
				if($sObj === null)
					return true;

				// Object access?
				foreach($access AS $mod => $acts) {
					foreach($acts AS $act => $objs) {
						if(isset($this->aPermissions[$mod][$act][$sObj]))
							return true;
						elseif(isset($this->aPermissions[$mod][$act]['*']))
							return true;
					}
				}
			}
		}
		return false;
	}

}

