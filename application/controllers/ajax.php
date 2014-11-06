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
}
