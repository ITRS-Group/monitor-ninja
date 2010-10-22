<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana loader class for xajax
 */
class get_xajax_Core {
	/**
	*
	*
	*/
	public function instance()
	{
		$path = self::path();
		if ($path !== false)
		{
			ini_set('include_path',
			ini_get('include_path').PATH_SEPARATOR.dirname(dirname($path)));
			require_once(dirname($path).'/xajax_core/xajax.inc.php');
			$classname = 'xajax';
			$obj = @new $classname();
			return $obj;
		}
		return false;
	}

	/**
	* Fetch xajax absolute path
	*/
	public function path()
	{
		$path = Kohana::find_file('vendor', 'xajax/copyright.inc');
		return $path;
	}

	/**
	*	Fetch and return xajax web path
	*/
	public function web_path()
	{
		return Kohana::config('config.site_domain').'application/vendor/xajax/xajax_core';
	}

	/**
	*	Fetch requested items for a user depending on type (host, service or groups)
	* 	Found data is returned by xajax to javascript function populate_options()
	*/
	public function group_member($input=false, $type=false, $erase=true, &$xajax=null)
	{
		$auth = new Nagios_auth_Model();
		if (empty($type)) {
			return false;
		}

		$return = false;
		$items = false;
		switch ($type) {
			case 'hostgroup': case 'servicegroup':
				$field_name = $type."_tmp";
				$empty_field = $type;
				#$res = get_host_servicegroups($type);
				$res = $auth->{'get_authorized_'.$type.'s'}();
				if (!$res) {
					$objResponse = new xajaxResponse();
					$objResponse->call("delayed_hide_progress");
					return $objResponse;
				}
				foreach ($res as $name) {
					$items[] = $name;
				}
				break;
			case 'host':
				$field_name = "host_tmp";
				$empty_field = 'host_name';
				$items = $auth->get_authorized_hosts();
				break;
			case 'service':
				$field_name = "service_tmp";
				$empty_field = 'service_description';
				$items = $auth->get_authorized_services();
				break;
		}

		if (is_null($xajax))
			return false;
		$objResponse = new xajaxResponse();

		$objResponse->call("show_progress", "progress", $this->translate->_('Please wait'));

		# empty both select lists before populating if it's not a saved report
		if (empty($erase)) {
			$objResponse->call("empty_list", $field_name);
			$objResponse->call("empty_list", $empty_field);
		}

		sort($items);
		$return_data = false;
		foreach ($items as $k => $item) {
			$return_data[] = array('optionValue' => $item, 'optionText' => $item);
		}
		$json_val = json::encode($return_data);

		# pass the JSON data to javascript to build the HTML select options
		$objResponse->call("populate_options", $field_name, $empty_field, $json_val);
		#$objResponse->call("setup_hide_content", "progress");

		//return the  xajaxResponse object
		return $objResponse;
	}
}
