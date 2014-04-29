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
 *
 */
class Ajax_Controller extends Authenticated_Controller {

	public function __construct()
	{
		parent::__construct();

		/* Ajax calls shouldn't be rendered. This doesn't, because some unknown
		 * magic doesn't render templates in ajax requests, but for debugging
		 */
		$this->auto_render = false;
	}

	public function command ( $method = false, $command = false ) {

		$method = $this->input->post( 'method', $method );
		$command = $this->input->post( 'command', $command );
		$naming = preg_replace( "/^(STOP|START|ENABLE|DISABLE)\_/", "", $command );

		$cmd_name_mapping = array(
			"NOTIFICATIONS" => "enable_notifications",
			'EXECUTING_SVC_CHECKS' => "execute_service_checks",
			'ACCEPTING_PASSIVE_SVC_CHECKS' => "accept_passive_service_checks",
			'EXECUTING_HOST_CHECKS' => "execute_host_checks",
			'ACCEPTING_PASSIVE_HOST_CHECKS' => "accept_passive_host_checks",
			'EVENT_HANDLERS' => "enable_event_handlers",
			'OBSESSING_OVER_SVC_CHECKS' => "obsess_over_services",
			'OBSESSING_OVER_HOST_CHECKS' => "obsess_over_hosts",
			'FLAP_DETECTION' => "enable_flap_detection",
			'PERFORMANCE_DATA' => "process_performance_data"
		);

		if ( isset( $cmd_name_mapping[ $naming ] ) )
			$naming = $cmd_name_mapping[ $naming ];

		if ( $method ) {

			$cmd = new Command_Controller();

			if ( $method === "submit" ) {

				$info = $cmd->submit( $command );
				return json::ok( $info );

			} elseif ( $method === "commit" ) {

				$status = Current_status_Model::instance()->program_status();

				if ( isset( $status->$naming ) )
					$state = $status->$naming;
				$cmd->commit( $command );

				if ( isset( $state ) )
					return json::ok( array( "state" => $state ) );
				else
					return json::ok( array( ) );

			}

		}

	}

	/**
	*	Handle search queries from front page search field
	*/
	public function global_search($q=false)
	{
		$q = $this->input->get('query', $q);

		$result = $this->global_search_build_filter($q);

		if( $result !== false ) {
			$obj_type = $result[0];
			$obj_name = $result[1];
			$settings = $result[2];
			$livestatus_options = $result[3];

			$ls = Livestatus::instance();
			$lsb = $ls->getBackend();

			$livestatus_options['limit'] = Kohana::config('config.autocomplete_limit');

			$data = $lsb->getTable($obj_type, $livestatus_options);
			$obj_info = array();
			$obj_data = array();

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
		$json_str = json_encode($var);
		echo $json_str;
	}

	/**
	 * This is actually a local method for global_search to build the search query for live search.
	 *
	 * This method is public to make it testable. It doesn't interact with anything external, or take time, so it's no security issue...
	 *
	 * @param $q Search query
	 */
	public function global_search_build_filter($q)
	{
		$parser = new ExpParser_SearchFilter();

		try {
			$parser->parse($q);
			$obj_type = $parser->getLastObject();
			$obj_name = $parser->getLastString();
		} catch( ExpParserException $e ) {
			$obj_type = 'hosts';
			$obj_name = $q;
		} catch( Exception $e ) {
			return false;
		}

		$obj_data = array();
		$obj_info = array();

		if ($obj_type !== false) {
			switch ($obj_type) {
				case 'hosts':         $settings = array( 'name_field' => 'name',         'data' => 'name',        'path' => '/listview/?q=[services] host.name="%s"'            ); break;
				case 'services':      $settings = array( 'name_field' => 'description',  'data' => 'host_name',   'path' => '/extinfo/details/?type=service&host=%s&service=%s' ); break;
				case 'hostgroups':    $settings = array( 'name_field' => 'name',         'data' => 'name',        'path' => '/listview/?q=[hosts] in "%s"'                      ); break;
				case 'servicegroups': $settings = array( 'name_field' => 'name',         'data' => 'name',        'path' => '/listview/?q=[services] in "%s"'                   ); break;
				case 'comments':      $settings = array( 'name_field' => 'comment_data', 'data' => 'host_name',   'path' => '/extinfo/details/?type=host&host=%s'               ); break;
				default: return false;
			}

			return array( $obj_type, $obj_name, $settings, array(
					'columns' => array_unique( array($settings['name_field'], $settings['data']) ),
					'filter' => array($settings['name_field'] => array( '~~' => str_replace('%','.*',$obj_name) ))
			) );
		}
		return false;
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
		return json::ok(array($type => json_decode($setting)));
	}

	/**
	*	Save a specific setting
	*/
	public function save_page_setting()
	{
		$type = $this->input->post('type', false);
		$page = $this->input->post('page', false);
		$setting = $this->input->post('setting', false);

		if (empty($type) || empty($page) || (empty($setting) && $setting !== "0"))
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

			$setting_key = $pnp_path;

			if (strpos($param, 'source') === false) {
				$source = Ninja_setting_Model::fetch_page_setting('source', $setting_key);
				if ($source)
					$pnp_path .= '&source='.$source->setting;
				else
					$pnp_path .= '&source=0';
			}

			$view = Ninja_setting_Model::fetch_page_setting('view', $setting_key, 1);
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
		$host = $this->input->get('host', false);
		$service = false;

		if (strstr($host, ';')) {
			# we have a service - needs special handling
			$parts = explode(';', $host);
			if (sizeof($parts) == 2) {
				$host = $parts[0];
				$service = $parts[1];
			}
		}

		$data = _('Found no data');
		$set = CommentPool_Model::all();
		/* @var $set ObjectSet_Model */
		$set = $set->reduce_by('host.name', $host, '=');
		if($service !== false)
			$set = $set->reduce_by('service.description', $service, '=');

		if (count($set) > 0) {
			$data = "<table><tr><th>"._("Timestamp")."</th><th>"._('Author')."</th><th>"._('Comment')."</th></tr>";
			foreach ($set->it(array('entry_time', 'author', 'comment'),array()) as $row) {
				$data .= '<tr><td>'.date(nagstat::date_format(), $row->get_entry_time()).'</td><td valign="top">'.$row->get_author().'</td><td width="400px">'.wordwrap($row->get_comment(), '50', '<br />').'</td></tr>';
			}
			$data .= '</table>';
		}

		echo $data;
	}

	/**
	 * Worst methodname evah.
	 *
	 * Returns all the objects of the specified type that your user has
	 * permissions to view.
	 *
	 * @param $type string = false
	 */
	public function group_member($type=false)
	{
		$type = $this->input->get('type', false);

		$result = array();
		switch ($type) {
			case 'hostgroups':
			case 'servicegroups':
			case 'hosts':
				foreach(Livestatus::instance()->{'get'.$type}(array(
					'columns' => array('name')
				)) as $row) {
					$result[] = $row['name'];
				}
				break;
			case 'services':
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

		echo json_encode($periods);
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
			echo json_encode(array('search_name' => $result->search_name, 'search_query' => $result->search_query, 'search_description' => $result->search_description, 'search_id' => $result->id));
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
