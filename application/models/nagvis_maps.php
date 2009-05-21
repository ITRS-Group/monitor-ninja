<?php defined('SYSPATH') OR die('No direct access allowed.');

class Nagvis_Maps_Model extends Model
{
	public function get_list()
	{
		if (Kohana::config('config.nagvis_path') !== false)
		{
			$maps = array();
			$path = Kohana::config('config.nagvis_real_path') . '/etc/maps';
			$dir = opendir($path);
			while ($file = readdir($dir)) {
				if (!is_dir($path.'/'.$file)
					&& $file != '__automap.cfg'
					&& substr($file, -4) == '.cfg')
				{
					$maps[] = substr($file, 0, -4);
				}
			}
			closedir($dir);
			return $maps;
		}
		else
			return array();
	}
}
