<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controller to fetch data via Ajax calls
 * Requires authentication
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.

 */
class Ajax_Controller extends Authenticated_Controller {

	const FILTER_CHAR = ':';
	const DELIM_CHAR = ':';

	public function __construct()
	{
		parent::__construct();
		if ($this->profiler)
			$this->profiler->disable();
		$this->auto_render=false;
	}

	/**
	*	Handle search queries from front page search field
	*/
	public function global_search($q=false)
	{
		if(!request::is_ajax()) {
			$msg = $this->translate->_('Only Ajax calls are supported here');
			die($msg);
		} else {
			# we handle queries by trying to locate wanted filtering options separated by colon (:)
			$q = $this->input->get('query', $q);
			$q = urldecode($q);
			$divider_str = '========================================';
			if (strstr($q, self::FILTER_CHAR)) {
				# some extra filtering option detected
				$options = explode(self::FILTER_CHAR, $q);
				$obj_type = false;
				$obj_class_name = false;
				$obj_class = false;
				$obj_name = false;
				$obj_data = false;
				$obj_info = false;
				if (is_array($options) && !empty($options[0])) {
					$obj_type = trim($options[0]);
					if (isset($options[1])) {
						$obj_name = $options[1];
					} else {
						return false;
					}
					switch ($obj_type) {
						case 'host': case 'h':
							$settings = array(
								'class' => 'Host_Model',
								'name_field' => 'host_name',
								'data' => 'host_name',
								'path' => '/status/service/%s'
								);
							break;
						case 'service': case 's':
							$obj_type = 'service';
							$settings = array(
								'class' => 'Service_Model',
								'name_field' => 'service_description',
								'data' => 'host_name',
								'path' => '/extinfo/details/service/%s/?service=%s'
							);
							break;
						case 'hostgroup': case 'hg':
							$settings = array(
								'class' => 'Hostgroup_Model',
								'name_field' => 'hostgroup_name',
								'data' => 'hostgroup_name',
								'path' => '/status/hostgroup/%s'
							);
							break;
						case 'servicegroup': case 'sg':
							$settings = array(
								'class' => 'Servicegroup_Model',
								'name_field' => 'servicegroup_name',
								'data' => 'servicegroup_name',
								'path' => '/status/servicegroup/%s'
							);
							break;
						default:
							return false;
					}
					$obj_class_name = $settings['class'];
					$obj_class = new $obj_class_name();
					# find requested object
					$limit = 0;
					$data = $obj_class->get_where($settings['name_field'], $obj_name, $limit);
					$obj_info = false;
					$max_rows = Kohana::config('config.autocomplete_limit');
					$cnt = 0;
					$found_rows = 0;
					$found_str = '';
					if ($data!==false) {
						$found_rows = count($data);
						if ($found_rows > $max_rows) {
							$found_str = sprintf($this->translate->_('Search returned %s rows total'), $found_rows);
						}
						foreach ($data as $row) {
							if ($cnt++ > $max_rows) {
								break;
							}
							$obj_info[] = $obj_type == 'service' ? $row->{$settings['data']} . ';' . $row->{$settings['name_field']} : $row->{$settings['name_field']};
							$obj_data[] = array($settings['path'], $row->{$settings['data']});
						}
						if (!empty($obj_data) && !empty($found_str)) {
							$obj_info[] = $divider_str;
							$obj_data[] = array('', $divider_str);
							$obj_info[] = $found_str;
							$obj_data[] = array('', $found_str);
						}

					} else {
						$host_info = $this->translate->_('Nothing found');
					}
					$var = array('query' => $q, 'suggestions' => $obj_info, 'data' => $obj_data);
					$json_str = json::encode($var);
					echo $json_str;

				} else {
					return false;
				}
			} else {
				# assuming we want host data
				$host_model = new Host_Model();
				$limit = 0;
				$data = $host_model->get_where('host_name', $q, $limit);
				$host_info = false;
				$host_data = false;
				$max_rows = Kohana::config('config.autocomplete_limit');
				$cnt = 0;
				$found_rows = 0;
				$found_str = '';
				if ($data!==false) {
					$found_rows = count($data);
					if ($found_rows > $max_rows) {
						$found_str = sprintf($this->translate->_('Search returned %s rows total'),$found_rows);
					}
					foreach ($data as $row) {
						if ($cnt++ > $max_rows) {
							break;
						}
						$host_info[] = $row->host_name;
						$host_data[] = array('/status/service/%s', $row->host_name);
					}
					if (!empty($host_data) && !empty($found_str)) {
						$host_info[] = $divider_str;
						$host_data[] = array('', $divider_str);
						$host_info[] = $found_str;
						$host_data[] = array('', $found_str);
					}
				} else {
					$host_info = array($this->translate->_('Nothing found'));
				}
				$var = array('query' => $q, 'suggestions' => $host_info, 'data' => $host_data);
				$json_str = json::encode($var);
				echo $json_str;
			}
		}
	}

	/**
	 *	wrapper for widget ajax calls
	 */
	public function widget($widget, $method, $arguments=false)
	{
		// Disable auto-rendering
		$this->auto_render = FALSE;

		# path to widget helper is somehow lost when doing ajax calls
		# so let kohana find it for us
		$widget_core_path = Kohana::find_file('helpers', 'widget', true);
		require_once($widget_core_path);

		# first try custom path
		$path = Kohana::find_file(Kohana::config('widget.custom_dirname').$widget, $widget, false);
		if ($path === false) {
			# try core path if not found in custom
			$path = Kohana::find_file(Kohana::config('widget.dirname').$widget, $widget, true);
		}

		$page = false;
		if (empty($arguments)) {
			$page = request::referrer();
			$page_parts = explode(Kohana::config('config.site_domain').Kohana::config('config.index_page').'/', $page);
			$page = isset($page_parts[1]) ? $page_parts[1] : false;
			$page = (!empty($page) && $page == 'tac') ? $page.'/index' : $page;
			if (!empty($page)) {
				$data = Ninja_widget_Model::get_widget($page, $widget, true);
				$arguments = $data!==false ? unserialize($data->setting) : false;
				$arguments[0] = false;
			}
		}

		require_once($path);
		$classname = $widget.'_Widget';
		$obj = new $classname;
		# if we have a requested widget method - let's call it
		if (!empty($method)) {
			if (method_exists($obj, $method)) {
				if (empty($arguments)) {
					$arguments[] = false;
				}
				$arguments['is_ajax'] = true;
				return $obj->$method($arguments);
			}
		}

		# return false if no method defined
		return false;

	}

	/**
	*	Save location and order of widgets on a page
	*	@param  str $widget_str Serialized data for widget locations
	* 	@param 	str $page The page to save the data for
	*/
	public function save_widgets_order($widget_str=false, $page=false)
	{
		$widget_str = urldecode($this->input->post('widget_str', $widget_str));
		$page = urldecode($this->input->post('page', $page));
		$widget_str = trim($widget_str);
		$page = trim($page);
		if (empty($widget_str) || empty($page))
			return false;

		# save data to database
		Ninja_setting_Model::save_page_setting('widget_order', $page, $widget_str);
	}

	/**
	*	Fetch current widget orde from database
	*/
	public function fetch_widgets_order($page=false)
	{
		$page = urldecode($this->input->get('page', $page));
		$default = urldecode($this->input->get('default', false));
		$default = (int)$default == 1 ? true : $default;
		if (empty($page))
			return false;
		$data = Ninja_setting_Model::fetch_page_setting('widget_order', $page, $default);
		if (empty($data)) {
			echo json::encode(array('widget_order' => false));
			return false;
		}
		$settings = $data->setting;
		echo json::encode(array('widget_order' => $settings));
	}

	/**
	*	Save current state of single widget
	*
	*/
	public function save_widget_state()
	{
		$page = urldecode($this->input->post('page', false));
		$method = urldecode($this->input->post('method', false));
		$name = urldecode($this->input->post('name', false));
		if (empty($page))
			return false;
		# save data to database
		Ninja_widget_Model::save_widget_state($page, $method, $name);
	}

	/**
	*	Accepts call from a widget to save settings for a user
	*/
	public function save_widget_setting()
	{
		$page = urldecode($this->input->post('page', false));
		$widget = urldecode($this->input->post('widget', false));
		$data = false;
		foreach ($_POST as $key => $val) {
			if ($key == 'page' || $key == 'widget')
				continue;
			$data[$key] = $val;
		}
		if (empty($widget) || empty($data) || empty($page))
			return false;
		Ninja_widget_Model::save_widget_setting($page, $widget, $data);
	}

	/**
	*	fetch specific setting
	*/
	public function get_setting()
	{
		$type = urldecode($this->input->post('type', false));
		$page = urldecode($this->input->post('page', false));
		if (empty($type))
			return false;
		$type = trim($type);
		$page = trim($page);
		$data = Ninja_setting_Model::fetch_page_setting($type, $page);
		$setting = $data!==false ? $data->setting : false;
		echo json::encode(array($type => $setting));
	}

	/**
	* Fetch widget setting through ajax call
	*/
	public function get_widget_setting()
	{
		$page = urldecode($this->input->post('page', false));
		$widget = urldecode($this->input->post('widget', false));
		$page = trim($page);
		$widget = trim($widget);
		$data = Ninja_widget_Model::get_widget($page, $widget, true);
		$setting = $data!==false ? $data->setting : serialize(array(false));
		echo json::encode(unserialize($setting));
	}

	/**
	*	Save a specific setting
	*/
	public function save_page_setting()
	{
		$type = urldecode($this->input->post('type', false));
		$page = urldecode($this->input->post('page', false));
		$setting = urldecode($this->input->post('setting', false));

		if (empty($type) || empty($page) || empty($setting))
			return false;
		Ninja_setting_Model::save_page_setting($type, $page, $setting);
	}

	/**
	*	Set a refresh rate for all widgets on a page.
	*/
	public function set_widget_refresh()
	{
		$page = urldecode($this->input->post('page', false));
		$value = urldecode($this->input->post('value', false));
		$type = urldecode($this->input->post('type', false));
		$success = Ninja_widget_Model::update_all_widgets($page, $value, $type);
		echo json::encode(array('success' => $success));
	}

	/**
	*	Fetch translated help text
	* 	Two parameters arre supposed to be passed through POST
	* 		* controller - where is the translation?
	* 		* key - what key should be fetched
	*/
	public function get_translation()
	{
		$controller = urldecode($this->input->post('controller', false));
		$key = urldecode($this->input->post('key', false));

		if (empty($controller) || empty($key)) {
			return false;
		}
		$controller = ucfirst($controller).'_Controller';
		$result = call_user_func(array($controller,'_helptexts'), $key);
		return $result;
	}

	/**
	*	Check that we are still getting data from merlin.
	*	If not, user should be alerted
	*/
	public function is_alive()
	{
		$last_alive = Program_status_Model::last_alive();
		$stale_data_limit = Kohana::config('config.stale_data_limit');
		$diff = time() - $last_alive;
		$return = 0;
		if ($diff  > $stale_data_limit) {
			$return = $diff;
		}
		echo $return;
	}

	/**
	*	Fetch PNP image from supplied params
	*/
	public function pnp_image()
	{
		$param = urldecode($this->input->post('param', false));
		$pnp_path = Kohana::config('config.pnp4nagios_path');

		if ($pnp_path != '') {
			$pnp_path .= '?'.$param.'&source=1&view=1&display=image';
		}

		echo '<img src="'.$pnp_path.'" />';
		#echo $pnp_path;
	}

	/**
	*	Fetch comment for object
	*/
	public function fetch_comments()
	{
		#$obj_type = urldecode($this->input->post('obj_type', false));
		$host = urldecode($this->input->post('host', false));
		$service = false;
		$data = false;
		$model = new Comment_Model();
		if (strstr($host, '?service=')) {
			# we have a service - needs special handling
			$parts = explode('?service=', $host);
			if (sizeof($parts) == 2) {
				$host = $parts[0];
				$service = $parts[1];
			}
		}

		$res = $model->fetch_comments($host, $service);
		if ($res !== false) {
			$data = "<table><tr><td><strong>".$this->translate->_('Author')."</strong></td><td><strong>".$this->translate->_('Comment')."</strong></td></tr>";
			foreach ($res as $row) {
				$data .= '<tr><td valign="top">'.$row->author_name.'</td><td width="400px">'.wordwrap($row->comment_data, '50', '<br />').'</td></tr>';
			}
		}

		if (!empty($data)) {
			echo $data.'</table>';
		} else {
			echo $this->translate->_('Found no data');
		}
	}
}

