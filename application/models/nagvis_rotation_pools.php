<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Model for old nagvis rotation pools. Not used in monitor.
 */
class Nagvis_Rotation_Pools_Model extends Model
{
	/**
	 * Return list of nagvis rotation pools
	 */
	public function get_list()
	{
		$pools = array();
		$matches = array();

		$current_pool = false;

		$lines = @file(Kohana::config('config.nagvis_real_path') . '/etc/nagvis.ini.php');

		foreach ($lines as $line)
		{
			if (preg_match('/^\[rotation_(([a-zA-Z0-9_-])+)\]$/', $line, $matches))
			{
				$current_pool = $matches[1];
				continue;
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
