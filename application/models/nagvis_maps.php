<?php defined('SYSPATH') OR die('No direct access allowed.');

class Nagvis_Maps_Model extends Model
{
	public function get_list()
	{
		if (empty($this->auth->id))
			return array();
		if (Kohana::config('config.nagvis_path') !== false)
		{
			$maps = array();
			$path = Kohana::config('config.nagvis_real_path') . 'etc/maps';
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

	public function create($map)
	{
		if (Kohana::config('config.nagvis_path') === false)
			return false;

		$filename = Kohana::config('config.nagvis_real_path') . 'etc/maps/' . $map . '.cfg';
		$contents = <<<EOD
define global {
allowed_user=EVERYONE
allowed_for_config=EVERYONE
iconset=std_medium
map_image=demo_background.png
}
EOD;
		if (file_put_contents($filename, $contents) !== false)
			return true;
		else
			return false;
	}

	public function delete($map)
	{
		if (Kohana::config('config.nagvis_path') !== false)
			unlink(Kohana::config('config.nagvis_real_path') . 'etc/maps/' . $map . '.cfg');
	}
}
