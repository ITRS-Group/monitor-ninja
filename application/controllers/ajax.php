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
		if(!request::is_ajax()) {
			url::redirect(Kohana::config('routes.logged_in_default'));
		}

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
	*	Accepts call from a widget to save custom settings for a user
	* 	The POST data should contain fieldname and fieldvalue
	*/
	public function save_dynamic_widget_setting()
	{
		$page = urldecode($this->input->post('page', false));
		$widget = urldecode($this->input->post('widget', false));
		$fieldname = $this->input->post('fieldname', false);
		$fieldvalue = $this->input->post('fieldvalue', false);
		$data = false;
		$data[$fieldname] = $fieldvalue;
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
	*	Return current time to be used to update in GUI
	*/
	public function current_time()
	{
		$time = date(nagstat::date_format());
		echo $time;
	}

	/**
	*	Fetch PNP image from supplied params
	*/
	public function pnp_image()
	{
		$param = urldecode($this->input->post('param', false));
		$param = pnp::clean($param);
		$pnp_path = Kohana::config('config.pnp4nagios_path');

		if ($pnp_path != '') {
			$pnp_path .= '/image?'.$param.'&source=0&view=1&display=image';
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

	/**
	*	Fetch requested items for a user depending on type (host, service or groups)
	* 	Found data is returned as json data
	*/
	public function group_member($input=false, $type=false)
	{
		$input = urldecode($this->input->post('input', false));
		$type = urldecode($this->input->post('type', false));

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
					return false;
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

		sort($items);
		$return_data = false;
		foreach ($items as $k => $item) {
			$return_data[] = array('optionValue' => $item, 'optionText' => $item);
		}
		$json_val = json::encode($return_data);

		echo $json_val;
	}

	/**
	*	Fetch available report periods for selected report type
	*/
	public function get_report_periods()
	{
		$type = urldecode($this->input->post('type', 'avail'));
		if (empty($type))
			return false;

		$report_periods = Reports_Controller::_report_period_strings($type);
		$periods = false;
		if (!empty($report_periods)) {
			foreach ($report_periods['report_period_strings'] as $periodval => $periodtext) {
				$periods[] = array('optionValue' => $periodval, 'optionText' => $periodtext);
			}
		} else {
			return false;
		}

		# add custom period
		$periods[] = array('optionValue' => 'custom', 'optionText' => "* " . $this->translate->_('CUSTOM REPORT PERIOD') . " *");

		echo json::encode($periods);
	}

	/**
	*	Fetch saved reports when switching report type
	*/
	public function get_saved_reports()
	{
		$type = urldecode($this->input->post('type', 'avail'));
		if (empty($type))
			return false;

		$saved_reports = Saved_reports_Model::get_saved_reports($type);
		if (count($saved_reports) == 0) {
			echo '';
			return false;
		}

		$scheduled_label = $this->translate->_('Scheduled');
		$scheduled_ids = array();
		$scheduled_periods = null;
		$scheduled_res = Scheduled_reports_Model::get_scheduled_reports($type);
		if ($scheduled_res && count($scheduled_res)!=0) {
			foreach ($scheduled_res as $sched_row) {
				$scheduled_ids[] = $sched_row->report_id;
				$scheduled_periods[$sched_row->report_id] = $sched_row->periodname;
			}
		}

		$return = false;
		$return[] = array('optionValue' => '', 'optionText' => ' - '.$this->translate->_('Select saved report') . ' - ');
		switch ($type) {
			case 'avail':
			case 'summary':
				$field_name = 'report_name';
				break;
			case 'sla':
				$field_name = 'sla_name';
				break;
		}

		if (!isset($field_name)) {
			return false;
		}

		foreach ($saved_reports as $info) {
			$sched_str = in_array($info->id, $scheduled_ids) ? " ( *".$scheduled_label."* )" : "";
			if (in_array($info->id, $scheduled_ids)) {
				$sched_str = " ( *".$scheduled_label."* )";
			} else {
				$sched_str = "";
			}
			$return[] = array('optionValue' => $info->id, 'optionText' =>$info->{$field_name}.$sched_str);
		}

		echo json::encode($return);
		return true;
	}

	public function get_sla_from_saved_reports()
	{

		$sla_id = urldecode($this->input->post('sla_id', false));
		if (empty($sla_id))
			return false;

		$saved_sla = Saved_reports_Model::get_sla_from_saved_reports($sla_id);
		if (count($saved_sla) == 0) {
			echo '';
			return false;
		}

		$return = false;
		foreach ($saved_sla as $info) {
			$return[] = array('name' => $info->name, 'value' => $info->value);
		}

		echo json::encode($return);
		return true;
	}

	/**
	*	Fetch date ranges for reports
	*/
	public function get_date_ranges()
	{
		$the_year = urldecode($this->input->post('the_year', false));
		$type = urldecode($this->input->post('type', 'start'));
		$item = urldecode($this->input->post('item', 'year'));
		$date_ranges = Reports_Model::get_date_ranges();

		if (empty($date_ranges)) return false;

		$start_date 	= $date_ranges[0]; 	// first date in db
		$end_date 		= $date_ranges[1];	// last date in db
		$type 			= trim($type);
		$item 			= trim($item);
		$the_year 		= (int)$the_year;
		$start_year 	= date('Y', $start_date);
		$current_year	= date('Y');
		$current_month	= date('m');
		$end_year 		= date('Y', $end_date);
		$start_month 	= date('m', $start_date);
		$end_month 		= date('m', $end_date);

		$arr_end = false;
		$arr_start = false;
		$type_item = false;
		$end_num = 0;
		$start_num = 0;
		if (empty($type)) {
			// Print all years
			for ($i=$start_year;$i<=$end_year;$i++) {
				#$objResponse->call("addSelectOption", "start_year", $i, $i);
				#$objResponse->call("addSelectOption", "end_year", $i, $i);
				$arr_start[] = $i;
				$arr_end[] = $i;
			}
		} else {
			// empty month list
			#$objResponse->call("empty_list", $type."_month");
			if (!empty($the_year)) {
				// end month should always be 12 unless we only
				// have data for a single year
				if ($start_year == $end_year) {
					$end_num = ($end_month == 1) ? 11 : $end_month-1;
					$start_num = $start_month;
				} else {
					$end_num = $the_year == $current_year ? $current_month: 12;
					$start_num = $the_year == $start_year ? $start_month : 1;
					#$objResponse->call("log","end_num: $end_num, current_year: $current_year, current_month: $current_month");
				}
			} else {
				return false;
			}

			for ($i=$start_num;$i<=$end_num;$i++) {
				#$objResponse->call("addSelectOption", $type."_".$item, str_pad($i, 2, '0', STR_PAD_LEFT), $i);
				$type_item[] = array($type."_".$item, str_pad($i, 2, '0', STR_PAD_LEFT), $i);
				echo $i."\n";
			}
		}

		$return = array('start_year' => $arr_start, 'end_year' => $arr_end, 'type_item' => $type_item);
		echo json::encode($return);
		return true;
	}
}

