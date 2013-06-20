<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Base-class that report controllers build on.
 *
 * Might have been called Report_controller, had that name not been busy.
 */
abstract class Base_reports_Controller extends Authenticated_Controller
{
	/** All states, translated. Only used where actually translating them would break stuff. */
	protected $state_values = false;

	/** Useless base variable jay */
	protected $histogram_link = "histogram/generate";

	/** The type of this report. Usually based on controller name, but not always. */
	public $type = false;

	/** A report_option object */
	protected $options = false;

	/** Sanity-checks */
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

	/** Controller method that should render a form for creating a report */
	abstract public function index($input = false);
	/** Controller method that should render a report */
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

	/**
	 * Delete a saved report
	 */
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

	/**
	 * Helper that makes sure a Report_options object is setup and available
	 */
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
	 * uses Status_Reports_Model::get_uptime()
	 * @param $arr (array) List of groups
	 * @param $type (string) The type of objects in $arr. Valid values are "hostgroup" or "servicegroup".
	 * @param $options (Report_option) Can be sent to override the options from $this->options
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
	 * Convert between yes/no and 1/0
	 * @param $val (mixed) value to be converted
	 * @param $use_int (bool) to indicate if we should use 1/0 instead of yes/no
	 * @return mixed str/int
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
	 * Re-order alphabetically a group to
	 * 1) sort by host name
	 * 2) sort by service description
	 * A group here refers to the return value given by a call to get_multiple_state_info().
	 * @param &$group Return parameter.
	 */
	protected function _reorder_by_host_and_service(&$group)
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

	/**
	 * So this static method that random code everywhere assumes exist doesn't even have a fallback defined?
	 * Yeah, that's good code...
	 */
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
