<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Nagvis config reader class
 */

class nagvisconfig {
	/**
	 * Get a list of maps and automaps the current user is authorized for
	 */
	public static function get_map_list() {
		$maps = array();
		$auth = Auth::instance();
		if ($auth->authorized_for('nagvis_view')) {
			$maps_dir = Kohana::config('nagvis.nagvis_real_path').'/etc/maps';
			if (!is_dir($maps_dir) || !is_readable($maps_dir)) {
				op5log::instance('ninja')->log('error', 'No Nagvis maps can be found, check configuration for nagvis.nagvis_real_path (it is currently not a valid directory, it needs to contain etc/maps)');
				return $maps;
			}
			$files = scandir($maps_dir);
			foreach ($files as $file) {
				if (strpos($file, '.cfg') !== false)
					$maps[] = substr($file, 0, -4);
			}
			return $maps;
		}
		$cfg = Op5Config::instance();
		$nagvis_config = $cfg->getConfig('nagvis');

		$contactgroups = Livestatus::instance()->getContactGroups(array(
			'columns'=>array('name'),
			'filter'=>array(
				'members'=>array('>='=>$auth->get_user()->get_username())
				)
			));
		foreach ($contactgroups as $idx) {
			$contactgroups[$idx] = $contactgroups[$idx]['name'];
		}

		$groups_per_type = array(
			'auth_groups'    => $auth->get_user()->get_groups(),
			'contact_groups' => $contactgroups
		);

		foreach($groups_per_type as $grouptype => $groups) {
			if(!isset($nagvis_config[$grouptype]))
				continue;
			foreach( $groups as $group ) {
				if (!isset( $nagvis_config[$grouptype][$group]))
					continue;
				foreach( $nagvis_config[$grouptype][$group] as $map => $perms ) {
					if (in_array('view', $perms))
						$maps[] = $map;
				}
			}
		}
		return $maps;
	}
}
