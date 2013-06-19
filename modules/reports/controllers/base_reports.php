<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Base_reports_Controller extends Authenticated_Controller
{
	protected static $sla_field_names = array(
		'hosts' => 'PERCENT_TOTAL_TIME_UP',
		'hostgroups' => 'PERCENT_TOTAL_TIME_UP',
		'services' => 'PERCENT_TOTAL_TIME_OK',
		'servicegroups' => 'PERCENT_TOTAL_TIME_OK'
	);

	protected $err_msg = '';

	protected $state_values = false;

	protected $histogram_link = "histogram/generate";

	public $type = false;

	protected $options = false;

	public function __construct() {
		if ($this->type === false)
			die("You must set \$type in ".get_class($this));

		parent::__construct();

		$this->state_values = array(
			'OK' => _('OK'),
			'WARNING' => _('WARNING'),
			'UNKNOWN' => _('UNKNOWN'),
			'CRITICAL' => _('CRITICAL'),
			'PENDING' => _('PENDING'),
			'UP' => _('UP'),
			'DOWN' => _('DOWN'),
			'UNREACHABLE' => _('UNREACHABLE')
		);

		# When run from cron-job, or mailing out reports from gui, we need access
		if(Router::$method == 'generate' && !Auth::instance()->get_user()->logged_in() && PHP_SAPI == 'cli') {
			$op5_auth = Op5Auth::factory(array('session_key' => false));
			$op5_auth->force_user(new Op5User_AlwaysAuth());
		}

		$this->template->disable_refresh = true;
	}

	abstract public function index($input = false);
	abstract public function generate($input = false);

	/**
	 * Generate PDF instead of normal rendering. Uses shell
	 *
	 * Assumes that $this->template is set up correctly
	 */
	protected function generate_pdf()
	{
		$this->template->base_href = 'https://localhost'.url::base();

		# not using exec, so STDERR (used for status info) will be loggable
		$pipe_desc = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'));
		$pipes = false;

		$command = Kohana::config('reports.pdf_command');
		$this->log->log('debug', "Running pdf generation command '$command'");
		$process = proc_open($command, $pipe_desc, $pipes, DOCROOT);

		if (is_resource($process)) {
			// Render and store output
			$content = $this->template->render();
			$this->auto_render = false;

			$filename = $this->type;
			if ($this->options['schedule_id']) {
				$schedule_info = Scheduled_reports_Model::get_scheduled_data($this->options['schedule_id']);
				if ($schedule_info)
					$filename = $schedule_info['filename'];
			}
			$months = date::abbr_month_names();
			$month = $months[date('m')-1]; // January is [0]
			$filename = preg_replace("~\.pdf$~", null, $filename)."_".date("Y_").$month.date("_d").'.pdf';

			fwrite($pipes[0], $content);
			fclose($pipes[0]);

			$out = stream_get_contents($pipes[1]);
			$err = stream_get_contents($pipes[2]);
			if (trim($out)) {
				header("Content-disposition: attachment; filename=$filename");
				header('Content-Type: application/pdf');
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private", false);
				header("Content-Transfer-Encoding: binary");
				echo $out;
			} else {
				$this->log->log('error', $err);
			}
			fclose($pipes[1]);
			fclose($pipes[2]);
			proc_close($process);
		} else {
			$this->log->log('error', "Tried running the following command but was unsuccessful:");
			$this->log->log('error', $command);
		}
	}

	/**
	*	Save a report via ajax call
	* 	Called from reports.js
	* 	@return JSON string
	*/
	public function save($input = false)
	{
		if(!request::is_ajax()) {
			$msg = _('Only Ajax calls are supported here');
			die($msg);
		}

		$this->setup_options_obj($input);

		$this->auto_render=false;

		$return = false;
		if (!$this->options['report_name']) {
			return json::fail(_('Unable to save this report, report name missing.'));
		}
		$report_id = Saved_reports_Model::edit_report_info($this->type, $this->options['report_id'], $this->options);
		if ($report_id) {
			return json::ok(array('status_msg' => _("Report was successfully saved"), 'report_id' => $report_id));
		}
		return json::fail(_('Unable to save this report.'));
	}

	public function delete() {
		if(!request::is_ajax()) {
			$msg = _('Only Ajax calls are supported here');
			die($msg);
		}

		$id = $this->input->post('id');
		if (!$id)
			return json::fail(_('No id supplied'));

		if (Saved_reports_Model::delete_report($this->type, $id))
			return json::ok(_('Report deleted'));
		return json::fail(_("Couldn't delete report: unknown error"));
	}

	protected function setup_options_obj($input = false, $type = false)
	{
		if ($this->options) // If a child class has already set this, leave it alone
			return;
		$this->options = Report_options::setup_options_obj($type ? $type : $this->type, $input);
		$this->template->set_global('options', $this->options);
		$this->template->set_global('type', $this->type);
	}

	/**
	 * @param $options Report_options
	 */
	function set_options(Report_options $options)
	{
		$this->options = $options;
	}

	/**
	 * Expands a series of groupnames (host or service) into its member objects, and calculate uptime for each
	 *
	 * @uses Status_Reports_Model::get_uptime()
	 * @param array $arr List of groups
	 * @param string $type The type of objects in $arr. Valid values are "hostgroup" or "servicegroup".
	 * @return array Calculated uptimes.
	 */
	protected function _expand_group_request(array $arr, $type, $options = false)
	{
		if (!$options) {
			$optclass = get_class($this->options);
			$options = new $optclass($this->options);
		}

		$data_arr = false;
		foreach ($arr as $data) {
			$options[$options->get_value('report_type')] = array($data);
			$model = new Status_Reports_model($options);
			$data_arr[] = $model->get_uptime();
		}
		return $data_arr;
	}


	/**
	*	Determine the name of the state
	*/
	protected function _state_string_name($type='host', $state=false) {

		$type = strtolower($type);

		if ($type === "host") {
			return Reports_Model::$host_states[$state];
		} elseif ($type === "service") {
			return Reports_Model::$service_states[$state];
		}

		return "N/A";

	}

	/**
	*	Convert between yes/no and 1/0
	* 	@param 	mixed val, value to be converted
	* 	@param 	bool use_int, to indicate if we should use
	* 			1/0 instead of yes/no
	* 	@return mixed str/int
	*/
	protected function _convert_yesno_int($val, $use_int=true)
	{
		$return = false;
		if ($use_int) {
			// This is the way that we normally do things
			switch (strtolower($val)) {
				case 'yes':
					$return = 1;
					break;
				case 'no':
					$return = 0;
					break;
				default:
					$return = $val;
			}
		} else {
			// This is the old way, using yes/no values
			switch ($val) {
				case 1:
					$return = 'yes';
					break;
				case 0:
					$return = 'no';
					break;
				default:
					$return = $val;
			}
		}
		return $return;
	}

	/**
	* 	@param array  $data_arr report source data, generated by status_reports_model:get_uptime()
	* 	@param string $sub_type The report subtype. Can be 'host' or 'service'.
	* 	@param string $get_vars query string containing values of options for the report<br>
	* 	@param int $start_time Start timestamp for the report.
	* 	@param int $end_time End timestamp for the report.
	*
	* 	@return	array report info divided by states
	*/
	protected function _get_multiple_state_info(&$data_arr, $sub_type, $get_vars, $start_time, $end_time, $type)
	{
		$prev_host = '';
		$php_self = url::site().$this->type.'/generate';
		if (array_key_exists('states', $data_arr) && !empty($data_arr['states']))
			$group_averages = $data_arr['states'];
		else {
			$this->log->log('error', 'Stuff went belly-up: '.var_export($data_arr, true));
			return;
		}

		$return = array();
		$cnt = 0;
		if ($sub_type=='service') {
			$sum_ok = $sum_warning = $sum_unknown = $sum_critical = $sum_undetermined = 0;
			foreach ($data_arr as $k => $data) {
				if (!reports::is_proper_report_item($k, $data))
					continue;

				$host_name = $data['states']['HOST_NAME'];
				$service_description = $data['states']['SERVICE_DESCRIPTION'];

				$return['host_link'][] = $php_self . "?host_name[]=". urlencode($host_name). "&report_type=hosts&".$get_vars;
				$return['service_link'][] = $php_self . '?service_description[]=' . urlencode("$host_name;$service_description") . '&report_type=services&start_time=' . $start_time . '&end_time=' . $end_time . '&'.$get_vars;

				$return['HOST_NAME'][] 				= $host_name;
				$return['SERVICE_DESCRIPTION'][] 	= $service_description;
				$return['ok'][] 			= $data['states']['PERCENT_KNOWN_TIME_OK'];
				$return['warning'][] 		= $data['states']['PERCENT_KNOWN_TIME_WARNING'];
				$return['unknown'][] 		= $data['states']['PERCENT_KNOWN_TIME_UNKNOWN'];
				$return['critical'][] 		= $data['states']['PERCENT_KNOWN_TIME_CRITICAL'];
				$return['undetermined'][] 	= $data['states']['PERCENT_TOTAL_TIME_UNDETERMINED'];
				if ($this->options['scheduleddowntimeasuptime'] == 2)
					$return['counted_as_ok'][]  = $data['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP'];

				$prev_host = $host_name;
				$sum_ok += $data['states']['PERCENT_KNOWN_TIME_OK'];
				$sum_warning += $data['states']['PERCENT_KNOWN_TIME_WARNING'];
				$sum_unknown += $data['states']['PERCENT_KNOWN_TIME_UNKNOWN'];
				$sum_critical += $data['states']['PERCENT_KNOWN_TIME_CRITICAL'];
				$sum_undetermined += $data['states']['PERCENT_TOTAL_TIME_UNDETERMINED'];
				$cnt++;
			}
			$return['nr_of_items'] = $cnt;
			$return['average_ok'] = $sum_ok!=0 ? reports::format_report_value($sum_ok/$cnt) : '0';
			$return['average_warning'] = $sum_warning!=0 ? reports::format_report_value($sum_warning/$cnt) : '0';
			$return['average_unknown'] = $sum_unknown!=0 ? reports::format_report_value($sum_unknown/$cnt) : '0';
			$return['average_critical'] = $sum_critical!=0 ? reports::format_report_value($sum_critical/$cnt) : '0';
			$return['average_undetermined'] = $sum_undetermined!=0 ? reports::format_report_value($sum_undetermined/$cnt) : '0';
			$return['group_ok'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_OK']);
			$return['group_warning'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_WARNING']);
			$return['group_unknown'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_UNKNOWN']);
			$return['group_critical'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_CRITICAL']);
			$return['group_undetermined'] = reports::format_report_value($group_averages['PERCENT_TOTAL_TIME_UNDETERMINED']);
			$return['groupname'] = $data_arr['groupname']?:false;
		} else {
			// host
			$sum_up = $sum_down = $sum_unreachable = $sum_undetermined = 0;
			foreach ($data_arr as $k => $data) {
			if (!reports::is_proper_report_item($k, $data))
					continue;
				$host_name = $data['states']['HOST_NAME'];
				$return['host_link'][] = $php_self . "?host_name[]=". urlencode($host_name). "&report_type=hosts" .
				'&start_time=' . $start_time . '&end_time=' . $end_time . '&' . $get_vars;
				$return['HOST_NAME'][] 		= $host_name;
				$return['up'][] 			= $data['states']['PERCENT_KNOWN_TIME_UP'];
				$return['down'][] 			= $data['states']['PERCENT_KNOWN_TIME_DOWN'];
				$return['unreachable'][]	= $data['states']['PERCENT_KNOWN_TIME_UNREACHABLE'];
				$return['undetermined'][]	= $data['states']['PERCENT_TOTAL_TIME_UNDETERMINED'];
				if ($this->options['scheduleddowntimeasuptime'] == 2)
					$return['counted_as_up'][]  = $data['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP'];

				$sum_up += $data['states']['PERCENT_KNOWN_TIME_UP'];
				$sum_down += $data['states']['PERCENT_KNOWN_TIME_DOWN'];
				$sum_unreachable += $data['states']['PERCENT_KNOWN_TIME_UNREACHABLE'];
				$sum_undetermined += $data['states']['PERCENT_TOTAL_TIME_UNDETERMINED'];
				$cnt++;
			}
			$return['nr_of_items'] = $cnt;
			$return['average_up'] = $sum_up!=0 ? reports::format_report_value($sum_up/$cnt) : '0';
			$return['average_down'] =  $sum_down!=0 ? reports::format_report_value($sum_down/$cnt) : '0';
			$return['average_unreachable'] = $sum_unreachable!=0 ? reports::format_report_value($sum_unreachable/$cnt) : '0';
			$return['average_undetermined'] = $sum_undetermined!=0 ? reports::format_report_value($sum_undetermined/$cnt) : '0';

			$return['group_up'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_UP']);
			$return['group_down'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_DOWN']);
			$return['group_unreachable'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_UNREACHABLE']);
			$return['group_undetermined'] = reports::format_report_value($group_averages['PERCENT_TOTAL_TIME_UNDETERMINED']);
			$return['groupname'] = $data_arr['groupname']?:false;
		}
		return $return;
	}

	/**
	 * @desc Re-order alphabetically a group to
	 * 1) sort by host name
	 * 2) sort by service description
	 * A group here refers to the return value given by a call to get_multiple_state_info().
	 * @param array &$group Return parameter.
	 */
	protected function _reorder_by_host_and_service(&$group, $report_type=false)
	{
		$num_hosts = count($group['HOST_NAME']);

		# Set up structure ('host1' => array(1,5,8), 'host2' =>array(2,3,4,7), ...)
		# where the numbers are indices of services in original array.
		$host_idxs = array();
		for($i=0 ; $i<$num_hosts ; $i++) {
			$h = $group['HOST_NAME'][$i];
			if(array_key_exists($h, $host_idxs)) {
				$host_idxs[$h][] = $i;
			} else {
				$host_idxs[$h] = array($i);
			}
		}

		$new_order = array(); # The new sorting order. used to re-order every array in $group
		ksort($host_idxs);

		if(!array_key_exists('SERVICE_DESCRIPTION', $group)) {
			$new_order = array_values($host_idxs);
			for($i=0,$n=count($new_order) ; $i<$n ; $i++) {
				$new_order[$i] = $new_order[$i][0];
			}
		} else { #services or servicegroups
			# For every host: re-order service names by alphabet
			foreach($host_idxs as $h => $serv_indices) {
				$tmp_servs = array();
				foreach($serv_indices as $i) {
					$tmp_servs[$i] = $group['SERVICE_DESCRIPTION'][$i];
				}
				asort($tmp_servs);
				$new_order = array_merge($new_order, array_keys($tmp_servs));
			}
		}
		# $new_order now contains the indices to move elements of
		# arrays as for them to become correctly ordered.

		# use new order to reorder all arrays
		$a_names = array_keys($group);
		foreach($a_names as $a_name) {
			$arr =& $group[$a_name];
			if(!is_array($arr)) # only re-order arrays
				continue;

			$tmp_arr = array();
			foreach($new_order as $new_index => $old_index) {
				# print "moving ".$arr[$old_index]." from $old_index to $new_index\n";
				$tmp_arr[$new_index] = $arr[$old_index];
			}

			ksort($tmp_arr);
			$group[$a_name] = $tmp_arr;
		}
	}

	public function _print_state_breakdowns($source=false, $values=false, $type="hosts")
	{
		if (empty($values)) {
			return false;
		}
		$tot_time = 0;
		$tot_time_perc = 0;
		$tot_time_known_perc = 0;
		if ($type=='hosts' || $type=='hostgroups') {
			$tot_time = $values['KNOWN_TIME_UP'] +
				$values['KNOWN_TIME_DOWN'] +
				$values['KNOWN_TIME_UNREACHABLE'] +
				$values['TOTAL_TIME_UNDETERMINED'];
			$tot_time_perc = $values['PERCENT_KNOWN_TIME_UP'] +
				$values['PERCENT_KNOWN_TIME_DOWN'] +
				$values['PERCENT_KNOWN_TIME_UNREACHABLE'] +
				$values['PERCENT_TIME_UNDETERMINED_NOT_RUNNING'] +
				$values['PERCENT_TIME_UNDETERMINED_NO_DATA'];
			$var_types = array('UP', 'DOWN', 'UNREACHABLE');
		} else {
			$tot_time = $values['KNOWN_TIME_OK'] +
				$values['KNOWN_TIME_WARNING'] +
				$values['KNOWN_TIME_UNKNOWN'] +
				$values['KNOWN_TIME_CRITICAL'] +
				$values['TOTAL_TIME_UNDETERMINED'];
			$tot_time_perc = $values['PERCENT_KNOWN_TIME_OK'] +
				$values['PERCENT_KNOWN_TIME_WARNING'] +
				$values['PERCENT_KNOWN_TIME_UNKNOWN'] +
				$values['PERCENT_KNOWN_TIME_CRITICAL'] +
				$values['PERCENT_TIME_UNDETERMINED_NOT_RUNNING'] +
				$values['PERCENT_TIME_UNDETERMINED_NO_DATA'];
			$var_types = array('OK', 'WARNING', 'UNKNOWN', 'CRITICAL');
		}

		return array('tot_time' => $tot_time, 'tot_time_perc' => $tot_time_perc, 'var_types' => $var_types, 'values' => $values);
	}

	public static function _helptexts($id)
	{
		$helptexts = array(
			'report-type' => _("Select the preferred report type. Hostgroup, Host, Servicegroup or Service. ".
				"To include objects of the given type in the report, select the objects from the left list and click on ".
				"the right pointing arrow. To exclude objects from the report, select the objects from the right list ".
				"and click on the left pointing arrow."),
			'reporting_period' => _("Choose from a set of predefined report periods or choose &quot;CUSTOM REPORT PERIOD&quot; ".
				"to manually specify Start and End date."),
			'report_time_period' => _("What time should the report be created for. Tip: This can be used for SLA reporting."),
			'description' => _("Optionally add a description to this report, such as an explanation of what the report conveys. Plain text only."),
			"skin" => _("Choose a skin for your summary report."),

		);

		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else {
			echo sprintf(_("This helptext ('%s') is not translated yet"), $id);
		}
	}
}
