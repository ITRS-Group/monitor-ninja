<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Histogram controller
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
class Histogram_Controller extends Base_reports_Controller
{
	private $labels = array();
	public $type = 'histogram';

	/**
	 * Setup options for histogram report
	 */
	public function index($input = false)
	{
		$this->setup_options_obj($input);

		$this->template->disable_refresh = true;
		$this->template->content = $this->add_view('reports/setup');
		$template = $this->template->content;
		if(isset($_SESSION['report_err_msg'])) {
			$template->error_msg = $_SESSION['report_err_msg'];
			unset($_SESSION['report_err_msg']);
		}

		$template->saved_reports = $this->options->get_all_saved();
		$scheduled_info = false;
		if ($this->options['report_id']) {
			$scheduled_info = Scheduled_reports_Model::report_is_scheduled($this->type, $this->options['report_id']);
		}
		$template->scheduled_info = $scheduled_info;
		$template->report_options = $this->add_view('histogram/options');

		$this->template->css[] = $this->add_path('reports/css/datePicker.css');

		$this->js_strings .= reports::js_strings();
		$this->js_strings .= "var _reports_error = '"._('Error')."';\n";

		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
		$this->template->title = _('Reporting » Histogram » Setup');
	}

	/**
	 * Generate the event history report
	 */
	public function generate($input = false)
	{
		$this->setup_options_obj($input);
		$this->template->disable_refresh = true;
		$this->template->css[] = $this->add_path('reports/css/datePicker.css');
		$rpt = new Summary_Reports_Model($this->options);

		$title = _('Alert histogram');

		$breakdown_keys = false;
		switch ($this->options['breakdown']) {
			case 'monthly':
				for ($i = 1;$i<=12;$i++) $breakdown_keys[] = $i;
				break;
			case 'dayofmonth':
				# build day numbers 1-31 (always 31 slots for each month as in histogram.c)
				for ($i = 1;$i<=31;$i++) $breakdown_keys[] = $i;
				break;
			case 'dayofweek':
				# using integer equivalent to date('N')
				$breakdown_keys = array(1, 2, 3, 4, 5, 6, 7);
				break;
			case 'hourly':
				# build hour strings like '00', '01' etc
				for ($i=0;$i<=24;$i++) $breakdown_keys[] = substr('00'.$i, -2);
				break;
		}
		$histogram_data = $rpt->histogram($breakdown_keys);

		# pull the data from the returned array
		$data = isset($histogram_data['data']) ? $histogram_data['data'] : array();
		$min = isset($histogram_data['min']) ? $histogram_data['min'] : array();
		$max = isset($histogram_data['max']) ? $histogram_data['max'] : array();
		$avg = isset($histogram_data['avg']) ? $histogram_data['avg'] : array();
		$sum = isset($histogram_data['sum']) ? $histogram_data['sum'] : array();

		$sub_type = false;
		$is_group = false;
		switch ($this->options['report_type']) {
			case 'hostgroups':
				$is_group = true;
			case 'hosts':
				$state_names = array(
					Reports_Model::HOST_UP => _('UP'),
					Reports_Model::HOST_DOWN => _('DOWN'),
					Reports_Model::HOST_UNREACHABLE => _('UNREACHABLE')
				);
				$sub_type = 'host';
				break;
			case 'servicegroups':
				$is_group = true;
			case 'services':
				$state_names = array(
					Reports_Model::SERVICE_OK => _('OK'),
					Reports_Model::SERVICE_WARNING => _('WARNING'),
					Reports_Model::SERVICE_CRITICAL => _('CRITICAL'),
					Reports_Model::SERVICE_UNKNOWN => _('UNKNOWN')
				);
				$sub_type = 'service';
				break;
		}

		$report_members = $this->options->get_report_members();
		if (empty($report_members)) {
			if (!$is_group)
				$_SESSION['report_err_msg'] = _("You didn't select any objects to include in the report");
			else
				$_SESSION['report_err_msg'] = sprintf(_("The groups you selected (%s) had no members, so cannot create a report from them"), implode(', ', $this->options['objects']));
			return url::redirect(Router::$controller.'/index?' . http_build_query($this->options->options));
		}

		$this->js_strings .= "var graph_options = {legend: {show: true,container: $('#overviewLegend')},xaxis:{ticks:".json_encode($this->_get_xaxis_ticks($data))."},bars:{align:'center'}, grid: { hoverable: true, clickable: true }, yaxis:{min:0}};\n";
		$this->js_strings .= "var graph_xlables = ".json_encode($this->labels).";\n";

		$this->js_strings .= reports::js_strings();

		$data = $this->_prepare_graph_data($data);
		$datasets = array();

		$states = array_keys($state_names);
		foreach ($data as $key => $val) {
			$datasets[ucfirst(strtolower($state_names[$key]))] = array('label' => ucfirst(strtolower($state_names[$key])), 'data' => $val, 'color' => reports::_state_colors($sub_type, $states[$key]), 'bars' => array('show' => true));
		}

		$this->js_strings .= 'var datasets = '.json_encode($datasets).";\n";

		$this->template->content = $this->add_view('reports/index');
		$base = $this->template->content;

		$base->header = $this->add_view('reports/header');
		$base->header->title = $title;
		$base->header->report_time_formatted = $this->format_report_time(date::date_format());

		$base->content = $this->add_view("histogram/histogram");
		$content = $base->content;

		$content->min = $min;
		$content->max = $max;
		$content->avg = $avg;
		$content->sum = $sum;
		$content->states = $state_names;
		$content->available_states = array_keys($min);
		$content->objects = $this->options['objects'];
		$timeformat_str = date::date_format();
		$content->report_time = date($timeformat_str, $this->options['start_time']).' '._('to').' '.date($timeformat_str, $this->options['end_time']);

		$this->template->content->report_options = $this->add_view('histogram/options');
		$tpl_options = $this->template->content->report_options;

		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
		$this->template->title = _('Reporting » Histogram » Report');
		$this->generate_toolbar();
	}

	public function edit_settings($input = false){
		$this->setup_options_obj($input);
		$this->template->content = $this->add_view('reports/edit_settings');
		$template = $this->template->content;
		$template->report_options = $this->add_view('histogram/options');
	}

	/**
	 * Replace all integer indicies with proper translated strings
	 */
	private function _get_xaxis_ticks($data)
	{
		$return = false;
		$i = 0;
		foreach ($data as $key => $values) {
			switch ($this->options['breakdown']) {
				case 'dayofmonth':
					$return[] = array($i, $key);
					$this->labels[] = $key;
					break;
				case 'monthly':
					$return[] = array($i, $key);
					$this->labels[] = "'".$key."'";
					break;
				case 'dayofweek':
					$return[] = array($i, $key);
					$this->labels[] = $key;
					break;
				case 'hourly':
					$return[] = array($i, $key.':00');
					$this->labels[] = $key.':00';
					break;
			}
			$i++;
		}
		return $return;
	}

	/**
	 * Prepare data structore for use in histogram
	 */
	private function _prepare_graph_data($data)
	{
		$return = array();
		$i = 0; # graph data needs to have 0 indicies
		foreach ($data as $key => $value) {
			foreach ($value as $k => $v) {
				$return[$k][] = array($i, $v);
			}
			$i++;
		}
		return $return;
	}

	/**
	* Translated helptexts for this controller
	*/
	public static function _helptexts($id)
	{
		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array();
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else
			return parent::_helptexts($id);
	}
}
