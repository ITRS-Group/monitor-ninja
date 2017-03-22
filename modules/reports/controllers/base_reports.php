<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Base-class that report controllers build on.
 *
 * Might have been called Report_controller, had that name not been busy.
 */
abstract class Base_reports_Controller extends Ninja_Controller
{
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
		// FIXME: not everything is hosts...
		$resource = ObjectPool_Model::pool('hosts')->all()->mayi_resource();
		$this->_verify_access($resource.':read.report.'.$this->type.'.html');

		# When run from cron-job, or mailing out reports from gui, we need access
		if(Router::$method == 'generate' && !Auth::instance()->get_user()->logged_in() && PHP_SAPI == 'cli') {
			$op5_auth = Op5Auth::factory(array('session_key' => false));
			$op5_auth->force_user(new User_AlwaysAuth_Model());
		}

		$this->template->disable_refresh = true;
		$this->template->css[] = 'application/media/css/jquery.filterable.css';

	}

	/** Controller method that should render a form for creating a report */
	abstract public function index($input = false);
	/** Controller method that should render a report */
	abstract public function generate($input = false);
	/** Controller method that should render a form for editing a report in LightBox */
	abstract public function edit_settings($input = false);

	/**
	 * Fill the toolbar with appropriate things for the current report
	 * type.
	 */
	protected function generate_toolbar() {
		$this->template->toolbar = new Toolbar_Controller('Report');

		if($this->type != 'histogram') {
			$pdf_button = form::open($this->type.'/generate');
			$pdf_button .= $this->options->as_form();
			$pdf_button .= '<input type="hidden" name="output_format" value="pdf" />';
			$pdf_button .= sprintf('<input type="submit" value="%s" />', _('As PDF'));
			$pdf_button .= '</form>';
			$this->template->toolbar->html_as_button($pdf_button);

			$csv_button = form::open($this->type.'/generate');
			$csv_button .= $this->options->as_form();
			$csv_button .= '<input type="hidden" name="output_format" value="csv" />';
			$csv_button .= sprintf('<input type="submit" value="%s" />', _('As CSV'));
			$csv_button .= "</form>\n";
			$this->template->toolbar->html_as_button($csv_button);
		}

		if($this->type !== 'alert_history') {
			$this->template->toolbar->button(_('Save report'), array('href' => '#', 'id' => 'save_report'));
		}

		$lp = LinkProvider::factory();
		$edit_settings_args = $this->options->as_keyval();
		if($this->options['report_id']) {
			$this->template->toolbar->button(_('View schedule'), array('href' => $lp->get_url('schedule', 'show'), 'id' => 'show_schedule'));
			$edit_settings_args = array('report_id' => $this->options['report_id']);
		}

		$this->template->toolbar->button(_('Edit settings'), array('href' => $lp->get_url($this->type, 'edit_settings', $edit_settings_args), 'class' => 'edit_settings'));
		$this->template->toolbar->button(_('Permalink'), array('href' => $lp->get_url($this->type, 'generate', $this->options->as_keyval())));
	}


	/**
	 * Generate PDF instead of normal rendering. Uses shell
	 *
	 * Assumes that $this->template is set up correctly
	 */
	protected function generate_pdf()
	{
		$resource = ObjectPool_Model::pool('hosts')->all()->mayi_resource();
		$this->_clear_print_notification();
		$this->_verify_access($resource.':read.report.'.$this->type.'.pdf');
		$this->template->base_href = 'https://localhost'.url::base();

		# not using exec, so STDERR (used for status info) will be loggable
		$pipe_desc = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'));
		$pipes = false;

		$command = Kohana::config('reports.pdf_command');
		$brand = brand::get('http://localhost', false);
		$command .= ' --replace brand "' . $brand . '"';
		$command .= ' - -';

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
				$this->log->log('error', "Pdf command " . $command . "resulted in no output. stderr:");
				$this->log->log('error', $err);
			}
			fclose($pipes[1]);
			fclose($pipes[2]);
			$return_value = proc_close($process);
			if ($return_value != 0) {
				$this->log->log('error', "Pdf command " . $command . " returned $return_value:");
				$this->log->log('error', "stderr: $err");
				$this->log->log('error', "stdout: $out");
			}
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

		$saved = $this->options->save($message);
		if ($saved) {
			return json::ok(array('status_msg' => _("Report was successfully saved"), 'report_id' => $this->options['report_id']));
		}
		return json::fail(array('error' => sprintf(_('Unable to save this report: %s'), $message)));
	}

	/**
	 * Delete a saved report
	 */
	public function delete() {
		if(!request::is_ajax()) {
			$msg = _('Only Ajax calls are supported here');
			die($msg);
		}

		$id = $this->input->post('report_id',$this->input->get('report_id'));
		if (!$id)
			return json::fail(array('error' => _('No id supplied')));

		$this->setup_options_obj(array('report_id' => $id));
		if (!$this->options['report_name'])
			return json::fail(array('error' => _("Couldn't find saved report")));
		if ($this->options->delete())
			return json::ok(array('success' =>_('Report deleted')));
		return json::fail(array('error' => _("Couldn't delete report: unknown error")));
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
	 * So this static method that random code everywhere assumes exist doesn't even have a fallback defined?
	 * Yeah, that's good code...
	 */
	public static function _helptexts($id)
	{
		$helptexts = array(
			'report-type' => _("Select the preferred report type. Hostgroup, Host, Servicegroup or Service. ".
				"To include objects of the given type in the report, click the object in the left list and it will move".
				"into the selection. To exclude objects from the report, click the object in the right list and ".
				"it will move out of selection, you may also click and drag over a group of objects in any list to select/deselect"),
			'reporting_period' => _("Choose from a set of predefined report periods or choose &quot;CUSTOM REPORT PERIOD&quot; ".
				"to manually specify Start and End date."),
			'report_time_period' => _("What time should the report be created for. Tip: This can be used for SLA reporting."),
			'description' => _("Optionally add a description to this report, such as an explanation of what the report conveys. Plain text only."),
			"skin" => _("Choose a skin for your summary report."),
			"standardreport" => _("Choose the type of report you want from the list of predefined summary reports."),
			"summary_type" => _('The format of the summary. &quot;Most recent alerts&quot; simply lists alerts, &quot;Top alert producers&quot; orders host and/or services by the one that has notified the most recently, and &quot;Alert totals&quot; sums up the number of alerts per selected object'),
			"summary_items" => _("Enter the number of items you wish the report to contain."),
			"state_types" => _("Whether to include only hard alerts, soft alerts, or both"),
			"host_states" => _("Uncheck the host states you want to remove from the report."),
			"service_states" => _("Uncheck the host states you want to remove from the report."),
			'map_states' => _("This lets you choose what to do about removed states. For instance, you could map all states except critical to hidden to get a report where critical really sticks out, or you could map warning to OK, because warning isn't serious enough to bring up to the recipient."),
			"include_long_output" => _("In views that displays individual alerts, include the full check output, instead of only the first line"),
			'filter' => _("Free text search, matching the objects in the left list below"),
			'saved_reports' => _("A list of all your saved reports. To load them, select the report you wish to generate and click select."),
		);

		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else {
			echo sprintf(_("This helptext ('%s') is not translated yet"), $id);
		}
	}

	/**
	 * All reports must display the time range they cover, and this helper
	 * helps them do so with some amount of consistency.
	 *
	 * @param $date_format string Format string for date(), probably date::date_format()
	 * @returns string HTML including a formatted date for this report
	 */
	protected function format_report_time($date_format)
	{
		if ($this->options['start_time'] == 0) {
			$start_time = _('Dawn of Time');
		} else {
			$start_time = date($date_format, $this->options['start_time']);
		}
		if($this->options['report_period'] && $this->options['report_period'] != 'custom')
			$report_time_formatted  = sprintf(
				_('%s (%s to %s)'),
				html::specialchars($this->options->get_value('report_period')),
				"<strong>".html::specialchars($start_time)."</strong>",
				"<strong>".html::specialchars(date($date_format, $this->options['end_time']))."</strong>"
			);
		else {
			$report_time_formatted  = sprintf(_("%s to %s"),
				html::specialchars($start_time),
				html::specialchars(date($date_format, $this->options['end_time'])));
		}
		if($this->options['rpttimeperiod'] != '')
			$report_time_formatted .= " - ".html::specialchars($this->options['rpttimeperiod']);
		return $report_time_formatted;
	}
}
