<?php defined('SYSPATH') OR die('No direct access allowed.');
class Tac_buildbot_Widget extends widget_Base {

	protected $duplicatable = true;
	public function url_exists($url=NULL)
	{
		if($url == NULL) return false;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpcode>=200 && $httpcode<300){
			return true;
		} else {
			return false;
		}
	}
	public function index()
	{
		$builders = array();
		$view_path = $this->view_path('view');
		$arguments = $this->get_arguments();
		$url = $arguments["buildbot_url"];
		if ($this->url_exists($url)){
			$builders = json_decode(file_get_contents("$url/json/builders/?as_text=1"));
			foreach ($builders as $name => $attributes) {
				$attributes->latest_build = json_decode(
					file_get_contents("$url/json/builders/".urlencode($name)."/builds/-1?as_text=1")
				);
			}
		}
		require($view_path);
	}

	public function options()
	{
		$options = parent::options();
		$options[] = new option($this->model->name, 'buildbot_url', _('Buildbot URL'), 'input', array('size=>5', 'type'=>'text'), 'http://172.27.86.229:8010');
		return $options;
	}


}
