<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Model for nagvis rotation pools
 * 
 */
class Nagvis_Rotation_Pools_Model extends Model
{
	/**
	 * get a list of rotation pools
	 * 
	 * @return array
	 */
	public function get_list()
	{
		$acl = Nagvis_acl_Model::getInstance();
		$pools = array();
		$matches = array();

		$current_pool = false;

		$lines = @file(Kohana::config('nagvis.nagvis_real_path') . '/etc/nagvis.ini.php');

		foreach ($lines as $line)
		{
			if (preg_match('/^\[rotation_(([a-zA-Z0-9_-])+)\]$/', $line, $matches))
			{
				if($acl->isPermitted('Rotation', 'view', $matches[1])) {
					$current_pool = $matches[1];
					continue;
				}
			}
			elseif ($current_pool !== false
				&& preg_match('/^maps="(([a-zA-Z0-9_:-])+)(,.*)*"$/', $line, $matches))
			{
				$submatches = explode(':', $matches[1]);
				$pools[$current_pool] = array_pop($submatches);
				$current_pool = false;
			}
		}

		return $pools;
	}
}
