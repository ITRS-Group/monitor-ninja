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
	public function __construct()
	{
		parent::__construct();
		if(!request::is_ajax()) {
			url::redirect(Kohana::config('routes.logged_in_default'));
		}
	}

	/**
	*	Handle search queries from front page search field
	*/
	public function global_search($q=false)
	{
		$q = $this->input->get('query', $q);
		
		if(!request::is_ajax()) {
			$msg = _('Only Ajax calls are supported here');
			die($msg);
		} else {
			
			$parser = new ExpParser_SearchFilter();
			
			try {
				$parser->parse($q);
			} catch( ExpParserException $e ) {
				return false;
			} catch( Exception $e ) {
			}
			
			$obj_type = $parser->getLastObject();
			$obj_name = $parser->getLastString();
			$obj_data = array();
			$obj_info = array();
			
			if ($obj_type !== false) {
				switch ($obj_type) {
					case 'hosts':         $settings = array( 'name_field' => 'name',         'data' => 'name',        'path' => '/status/service/?name=%s'                          ); break;
					case 'services':      $settings = array( 'name_field' => 'description',  'data' => 'host_name',   'path' => '/extinfo/details/?type=service&host=%s&service=%s' ); break;
					case 'hostgroups':    $settings = array( 'name_field' => 'name',         'data' => 'name',        'path' => '/status/hostgroup/?group=%s'                       ); break;
					case 'servicegroups': $settings = array( 'name_field' => 'name',         'data' => 'name',        'path' => '/status/servicegroup/?group=%s'                    ); break;
					case 'comments':      $settings = array( 'name_field' => 'comment_data', 'data' => 'host_name',   'path' => '/extinfo/details/?type=host&host=%s'               ); break;
					default: return false;
				}
				
				$ls = Livestatus::instance();
				$lsb = $ls->getBackend();
				
				$max_rows = Kohana::config('config.autocomplete_limit');
				
				$data = $lsb->getTable($obj_type, array(
						'columns' => array($settings['name_field'], $settings['data']),
						'filter' => array($settings['name_field'] => array( '~~' => $obj_name )),
						'limit' => $max_rows
						));
				
				
				if ($data!==false) {
					foreach ($data as $row) {
						$row = (object)$row;
						$obj_info[] = $obj_type == 'services' ? $row->{$settings['data']} . ';' . $row->{$settings['name_field']} : $row->{$settings['name_field']};
						$obj_data[] = array($settings['path'], $row->{$settings['data']});
					}
					if (!empty($obj_data) && !empty($found_str)) {
						$obj_info[] = $divider_str;
						$obj_data[] = array('', $divider_str);
						$obj_info[] = $found_str;
						$obj_data[] = array('', $found_str);
					}
				}
				$var = array('query' => $q, 'suggestions' => $obj_info, 'data' => $obj_data);

			} else {
				$var = array('query' => $q, 'suggestions' => array(), 'data' => array());
			}
			$json_str = json::encode($var);
			echo $json_str;
		}
	}

	/**
	*	fetch specific setting
	*/
	public function get_setting()
	{
		$type = $this->input->post('type', false);
		$page = $this->input->post('page', false);
		if (empty($type))
			return false;
		$type = trim($type);
		$page = trim($page);
		$data = Ninja_setting_Model::fetch_page_setting($type, $page);
		$setting = $data!==false ? $data->setting : false;
		echo json::encode(array($type => $setting));
	}

	/**
	*	Save a specific setting
	*/
	public function save_page_setting()
	{
		$type = $this->input->post('type', false);
		$page = $this->input->post('page', false);
		$setting = $this->input->post('setting', false);

		if (empty($type) || empty($page) || empty($setting))
			return false;
		Ninja_setting_Model::save_page_setting($type, $page, $setting);
	}

	/**
	*	Fetch translated help text
	* 	Two parameters arre supposed to be passed through POST
	* 		* controller - where is the translation?
	* 		* key - what key should be fetched
	*/
	public function get_translation()
	{
		$controller = $this->input->post('controller', false);
		$key = $this->input->post('key', false);

		if (empty($controller) || empty($key)) {
			return false;
		}
		$controller = ucfirst($controller).'_Controller';
		$result = call_user_func(array($controller,'_helptexts'), $key);
		return $result;
	}

	/**
	*	Fetch PNP image from supplied params
	*/
	public function pnp_image()
	{
		$param = $this->input->post('param', false);
		$param = pnp::clean($param);
		$pnp_path = Kohana::config('config.pnp4nagios_path');

		if ($pnp_path != '') {
			$pnp_path .= '/image?'.$param;

			if (strpos($param, 'source') === false) {
				$source = Ninja_setting_Model::fetch_page_setting('source', $pnp_path);
				if ($source)
					$pnp_path .= '&source='.$source->setting;
				else
					$pnp_path .= '&source=0';
			}

			$view = Ninja_setting_Model::fetch_page_setting('view', $pnp_path);
			if ($view)
				$pnp_path .= '&view='.$view->setting;
			else
				$pnp_path .= '&view=1';

			$pnp_path .= '&display=image';
		}

		echo '<img src="'.$pnp_path.'" />';
	}

	/**
	 *	Save prefered graph for a specific param
	 */
	public function pnp_default()
	{
		$param = $this->input->post('param', false);
		$param = pnp::clean($param);
		$pnp_path = Kohana::config('config.pnp4nagios_path');

		if ($pnp_path != '') {
			$source = intval($this->input->post('source', false));
			$view = intval($this->input->post('view', false));

			Ninja_setting_Model::save_page_setting('source', $pnp_path.'/image?'.$param, $source);
			Ninja_setting_Model::save_page_setting('view', $pnp_path.'/image?'.$param, $view);
		}
	}

	/**
	*	Fetch comment for object
	*/
	public function fetch_comments()
	{
		$host = $this->input->post('host', false);
		$service = false;
		$data = false;
		$model = new Comment_Model();
		if (strstr($host, ';')) {
			# we have a service - needs special handling
			$parts = explode(';', $host);
			if (sizeof($parts) == 2) {
				$host = $parts[0];
				$service = $parts[1];
			}
		}

		$res = $model->fetch_comments_by_object($host, $service);
		if ($res !== false) {
			$data = "<table><tr><td><strong>"._('Author')."</strong></td><td><strong>"._('Comment')."</strong></td></tr>";
			foreach ($res as $row) {
				$data .= '<tr><td valign="top">'.$row->author_name.'</td><td width="400px">'.wordwrap($row->comment_data, '50', '<br />').'</td></tr>';
			}
		}

		if (!empty($data)) {
			echo $data.'</table>';
		} else {
			echo _('Found no data');
		}
	}

	/**
	 * @param $type string = false
	 */
	public function group_member($type=false)
	{
		$type = $this->input->get('type', false);

		$result = array();
		switch ($type) {
			case 'hostgroup':
			case 'servicegroup':
			case 'host':
				foreach(Livestatus::instance()->{'get'.$type.'s'}(array(
					'columns' => array('name')
				)) as $row) {
					$result[] = $row['name'];
				}
				break;
			case 'service':
				foreach(Livestatus::instance()->getServices(array(
					'columns' => array('host_name', 'service_description')
				)) as $row) {
					$result[] = $row['host_name'].";".$row['service_description'];
				}
				break;
			default:
				json::fail("No object type given");
		}

		json::ok($result);
	}

	/**
	*	Fetch available report periods for selected report type
	*/
	public function get_report_periods()
	{
		$type = $this->input->post('type', 'avail');
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
		$periods[] = array('optionValue' => 'custom', 'optionText' => "* " . _('CUSTOM REPORT PERIOD') . " *");

		echo json::encode($periods);
	}

	/**
	*	Fetch saved reports when switching report type
	*/
	public function get_saved_reports()
	{
		$type = $this->input->get('type');
		if (empty($type))
			return false;
		switch ($type) {
			case 'avail':
			case 'summary':
			case 'sla':
				break;
			default:
				return false;
		}

		$saved_reports = Saved_reports_Model::get_saved_reports($type);
		if (count($saved_reports) == 0) {
			echo '';
			return false;
		}

		$scheduled_label = _('Scheduled');
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
		$return[] = array('optionValue' => '', 'optionText' => ' - '._('Select saved report') . ' - ');

		foreach ($saved_reports as $info) {
			$sched_str = in_array($info->id, $scheduled_ids) ? " ( *".$scheduled_label."* )" : "";
			if (in_array($info->id, $scheduled_ids)) {
				$sched_str = " ( *".$scheduled_label."* )";
			} else {
				$sched_str = "";
			}
			$return[] = array('optionValue' => $info->id, 'optionText' =>$info->report_name.$sched_str);
		}

		echo json::encode($return);
		return true;
	}

	public function get_sla_from_saved_reports()
	{

		$sla_id = $this->input->post('sla_id', false);
		if (empty($sla_id))
			return false;

		$saved_sla = Saved_reports_Model::get_period_info($sla_id);
		if (count($saved_sla) == 0) {
			echo '';
			return false;
		}

		$return = false;
		foreach ($saved_sla as $info) {
			$return[] = array('name' => $info->name, 'value' => $info->value);
		}

		echo json::ok($return);
		return true;
	}

	/**
	*	Fetch date ranges for reports
	*/
	public function get_date_ranges()
	{
		$the_year = $this->input->post('the_year', false);
		$type = $this->input->post('type', 'start');
		$item = $this->input->post('item', 'year');
		$date_ranges = reports::get_date_ranges();

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
				$arr_start[] = $i;
				$arr_end[] = $i;
			}
		} else {
			// empty month list
			if (!empty($the_year)) {
				// end month should always be 12 unless we only
				// have data for a single year
				if ($start_year == $end_year) {
					$end_num = ($end_month == 1) ? 11 : $end_month-1;
					$start_num = $start_month;
				} else {
					$end_num = $the_year == $current_year ? $current_month: 12;
					$start_num = $the_year == $start_year ? $start_month : 1;
				}
			} else {
				return false;
			}

			for ($i=$start_num;$i<=$end_num;$i++) {
				$arr_start = $type == 'start' ? $the_year : false;
				$arr_end = $type == 'end' ? $the_year : false;
				$type_item[] = array($type."_".$item, str_pad($i, 2, '0', STR_PAD_LEFT), $i);
			}
		}

		$return = array('start_year' => $arr_start, 'end_year' => $arr_end, 'type_item' => $type_item);
		echo json::encode($return);
		return true;
	}

	/**
	*	Save a search for later use
	*/
	public function save_search()
	{
		$search_name = $this->input->post('name', false);
		$search_query = $this->input->post('query', false);
		$search_description = $this->input->post('description', false);
		$search_id = $this->input->post('search_id', false);

		$model = new Saved_searches_Model();
		$res = $model->save_search($search_query, $search_name, $search_description, $search_id);

		echo ((int)$res != 0) ? (int)$res : 'Error';
	}

	/**
	*	Remove a saved search
	*/
	public function remove_search()
	{
		$search_id = $this->input->post('search_id', false);
		$res = Saved_searches_Model::remove_search($search_id);

		echo $res != false ? 'OK' : 'Error';
	}

	/**
	*	Fetch a saved search by ID
	*/
	public function fetch_saved_search()
	{
		$search_id = (int)$this->input->post('search_id', false);
		if (empty($search_id)) {
			echo "Error";
			return false;
		}

		$res = Saved_searches_Model::get_search_by_id($search_id);
		if ($res != false) {
			$result = $res->current();
			echo json::encode(array('search_name' => $result->search_name, 'search_query' => $result->search_query, 'search_description' => $result->search_description, 'search_id' => $result->id));
			return true;
		}
		echo "Error";
		return false;
	}

	public function fetch_saved_search_by_query() {
		$query = $this->input->get('query', false);
		$model = new Saved_searches_Model();
		$result = $model->get_search_by_query($query)->as_array();
		if(!$result) {
			json::fail("'$query' has not yet been saved");
		}
		json::ok(current($result));
	}
}
