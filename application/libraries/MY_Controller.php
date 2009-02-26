<?php defined('SYSPATH') or die('No direct script access.');
class Controller extends Controller_Core
{
	function __construct()
	{
		parent::__construct();
	}

	public function _kohana_load_view($template, $vars)
	{
		if ($template == '') return;

		$tbs = new Tbs;

		if (substr(strrchr($template, '.'), 1) === 'html')
		{
			$tbs->LoadTemplate($template);
			if (is_array($vars) && count($vars)> 0) {
				foreach ($vars as $key => $val) {
					if(is_array($val)) {
			  			$tbs->MergeBlock($val['name'], $val['data']);
					} else {
			  			$tbs->MergeField($key, $val);
					}
				}
			}
			$output = $tbs->Show(TBS_NOTHING);
		} else {
			$output = parent::_kohana_load_view($template, $vars);
		}

		return $output;
	}
}

?>
