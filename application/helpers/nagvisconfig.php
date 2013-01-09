<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Nagvis config reader class
 */

class nagvisconfig_Core {
	/**
	 * Get a list of maps and automaps the current user is authorized for
	 */
	public static function get_map_list() {
		$maps = array();
		$auth = Auth::instance();
		if ($auth->authorized_for('nagvis_view')) {
			$files = scandir(Kohana::config('nagvis.nagvis_real_path').'/etc/maps');
			foreach ($files as $file) {
				if (strpos($file, '.cfg') !== false)
					$maps[] = substr($file, 0, -4);
			}

			return $maps;
		}
		$cfg = Op5Config::instance();
		$nagvis_config = $cfg->getConfig('nagvis');

		$contactgroups = Livestatus::instance()->getContactGroups(array(
			'columns'=>'name',
			'filter'=>array(
				'members'=>array('>='=>$auth->get_user()->username)
				)
			));
		
		$groups_per_type = array(
			'auth_groups'    => $auth->get_user()->groups,
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

	/**
	 * Function to read Nagvis config file and return array with key->values
	*/
	public static function get($ConfigFile) {
		$raw = file_get_contents($ConfigFile);
		$lines = explode("\n", $raw);

		$data = array();
		$cat = '';
		foreach ($lines as $line) {
			// Comments and empty lines, don't care
			if (preg_match("/^;/", $line) || $line == "") {
				continue;
			}

			// A category tagged [name]
			if (preg_match("/^\[(.*)\]/", $line, $category)) {
				$cat = $category[1];
			}

			// A value under a category, key=value
			if (preg_match('/^(.*)="(.*)"/', $line, $values)) {
				$data[$cat][$values[1]] = str_replace('"', '', $values[2]);
			}
		}

		return $data;
	}
}
?>
