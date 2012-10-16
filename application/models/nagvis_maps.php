<?php defined('SYSPATH') OR die('No direct access allowed.');

class Nagvis_Maps_Model extends Model
{
	public $acl = null;
	protected $_path = "/nagvis/userfiles/images/maps/";

	public function __construct() {
		$this->acl = Nagvis_acl_Model::getInstance();
	}

	public function get_list($key_as_mapname = false)
	{
		$maps = array();
		$path = Kohana::config("nagvis.nagvis_real_path") . 'etc/maps';
		$dir = opendir($path);
		while ($file = readdir($dir)) {
			if (!is_dir($path.'/'.$file)
				&& $file != '__automap.cfg'
				&& substr($file, -4) == '.cfg')
			{
				$map = substr($file, 0, -4);
				if($this->acl->isPermitted('Map', 'view', $map)) {
					if($key_as_mapname) {
						$maps[$map] = $map;
					} else {
						$maps[] = $map;
					}
				}
			}
		}
		closedir($dir);
		return $maps;
	}

	public function getImage($name) {
		$path = Kohana::config("nagvis.nagvis_real_path") . "etc/maps/$name.cfg";
		if(!file_exists($path)) {
			return FALSE;
		}
		$content = file_get_contents($path);
		$matches = array();
		preg_match("|map_image=(.*)\n|", $content, $matches);
		if (!$matches)
			return false;
		list(,$img_name) = $matches;
		$file_name = "{$this->_path}$img_name";
		/*if(!file_exists($file_name)) {
			return FALSE;
		}*/
		return $file_name;
	}

	public function get_thumbnail($name) {
		return "/nagvis/var/$name-thumb.png";
	}

	public function create($map)
	{
        if(!preg_match('/^[0-9A-Za-z_\-]+$/', $map)) {
            return false;
        }
		if(!$this->acl->isPermitted('Map', 'add')) {
			throw new Kohana_Exception("You have not permissions for add map!");
		}
		$filename = Kohana::config("nagvis.nagvis_real_path") . 'etc/maps/' . $map . '.cfg';
		$contents = <<<EOD
define global {
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
		if(!$this->acl->isPermitted('Map', 'delete')) {
			throw new Kohana_Exception("You have not permissions for delete map!");
		}
		unlink(Kohana::config("nagvis.nagvis_real_path") . 'etc/maps/' . $map . '.cfg');
	}
}


