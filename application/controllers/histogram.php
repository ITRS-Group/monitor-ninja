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
	public $data = false;
	private $labels = array();
	public $type = 'histogram';

	/**
	*	Setup options for histogram report
	*/
	public function index($input = false)
	{
		$this->setup_options_obj($input);

		$this->template->disable_refresh = true;
		$this->template->content = $this->add_view('histogram/setup');
		$template = $this->template->content;

		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('histogram/js/histogram.js');

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = $this->add_path('histogram/css/histogram.css');
		#$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->template->css_header->css = $this->xtra_css;

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
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_js[] = 'application/media/js/jquery.flot.min.js';
		$this->xtra_js[] = 'application/media/js/date.js';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min.js';

		$this->xtra_css[] = 'application/media/css/jquery.fancybox.css';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('histogram/js/histogram.js');
		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = $this->add_path('histogram/css/histogram.css');
		$this->template->css_header->css = $this->xtra_css;
		$rpt = new Reports_Model($this->options);

		$hostgroup			= false;
		$hostname			= false;
		$servicegroup		= false;
		$service			= false;

		$group_name = false;
		$title = _('Event history for ');
		$objects = false;
		switch ($this->options['report_type']) {
			case 'hostgroups':
				$sub_type = "host";
				$hostgroup = $this->options['hostgroup'];
				$group_name = $hostgroup;
				$title .= _('Hostgroup(s): ');
				$this->object_varname = 'host_name';
				$objects = $this->options['hostgroup'];
				break;
			case 'servicegroups':
				$sub_type = "service";
				$servicegroup = $this->options['servicegroup'];
				$group_name = $servicegroup;
				$title .= _('Servicegroup(s): ');
				$this->object_varname = 'service_description';
				$objects = $this->options['servicegroup'];
				break;
			case 'hosts':
				$sub_type = "host";
				$hostname = $this->options['host_name'];
				$title .= _('Host(s): ');
				$this->object_varname = 'host_name';
				if (is_array($this->options['host_name'])) {
					$objects = $this->options['host_name'];
				} else {
					$objects[] = $this->options['host_name'];
				}
				break;
			case 'services':
				$sub_type = "service";
				$service = $this->options['service_description'];
				$title .= _('Service(s): ');
				$tmp_obj = false;
				if (is_array($service)) {
					foreach ($service as $s) {
						if (strstr($s, ';')) {
							$tmp = explode(';', $s);
							$tmp_obj[] = "'".$tmp[1]."' "._('On Host')." '".$tmp[0]."' ";
						} else {
							$tmp_obj[] = "'".$s."' "._('On Host')." '".$this->options['host_name']."' ";
						}
					}
					if (!empty($tmp_obj)) {
						$objects = $tmp_obj;
					}
				} else {
					if (strstr($service, ';')) {
						$tmp = explode(';', $service);
						$objects[] = "'".$tmp[1]."' "._('On Host')." '".$tmp[0]."' ";
					} else {
						$objects[] = "'".$service."' "._('On Host')." '".$this->options['host_name']."' ";
					}
				}
				$this->object_varname = 'service_description';
				break;
			default:
				return url::redirect(Router::$controller.'/index');
		}

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

		$min = false;
		$max = false;
		$avg = false;
		$sum = false;

		if (!empty($histogram_data)) {
			# pull the data from the returned array
			$this->data = isset($histogram_data['data']) ? $histogram_data['data'] : false;
			$min = isset($histogram_data['min']) ? $histogram_data['min'] : false;
			$max = isset($histogram_data['max']) ? $histogram_data['max'] : false;
			$avg = isset($histogram_data['avg']) ? $histogram_data['avg'] : false;
			$sum = isset($histogram_data['sum']) ? $histogram_data['sum'] : false;
		}

		$sub_type = false;
		switch ($this->options['report_type']) {
			case 'hosts': case 'hostgroups':
				$state_names = array(
					Reports_Model::HOST_UP => _('UP'),
					Reports_Model::HOST_DOWN => _('DOWN'),
					Reports_Model::HOST_UNREACHABLE => _('UNREACHABLE')
				);
				$sub_type = 'host';
				break;
			case 'services': case 'servicegroups':
				$state_names = array(
					Reports_Model::SERVICE_OK => _('OK'),
					Reports_Model::SERVICE_WARNING => _('WARNING'),
					Reports_Model::SERVICE_CRITICAL => _('CRITICAL'),
					Reports_Model::SERVICE_UNKNOWN => _('UNKNOWN')
				);
				$sub_type = 'service';
				break;
		}

		$this->inline_js .= "var graph_options = {legend: {show: true,container: $('#overviewLegend')},xaxis:{ticks:".$this->_get_xaxis_ticks()."},bars:{align:'center'}, grid: { hoverable: true, clickable: true }, yaxis:{min:0}};";
		$this->js_strings .= "var graph_xlables = new Array(".implode(',', $this->labels).");";

		$this->js_strings .= reports::js_strings();

		$data = $this->_prepare_graph_data();
		$datasets = array();
		$this->inline_js .= "var datasets = {";

		$states = array_keys($state_names);
		foreach ($data as $key => $val) {
			$datasets[] = "'".ucfirst(strtolower($state_names[$key]))."': {label: '".ucfirst(strtolower($state_names[$key]))."', data: [".implode(',', $val)."], color:'".Reports_Controller::_state_colors($sub_type, $states[$key])."', bars: { show: true}}";
		}

		$this->inline_js .= implode(',', $datasets).'};';

		$this->inline_js .= "var choiceContainer = $('#choices');
		$.each(datasets, function(key, val) {
			choiceContainer.append('<br/><input type=\"checkbox\" name=\"' + key +
			'\" checked=\"checked\" id=\"id' + key + '\">' +
			'<label for=\"id' + key + '\">'
			+ val.label + '</label>');
		});
		choiceContainer.find(\"input\").click(plotAccordingToChoices);

		function plotAccordingToChoices() {
			var data = [];

			choiceContainer.find(\"input:checked\").each(function () {
				var key = $(this).attr(\"name\");
				if (key && datasets[key])
					data.push(datasets[key]);
			});

			if (data.length > 0)
				$.plot($('#histogram_graph'), data, graph_options);
		}

	    plotAccordingToChoices();";

		$this->template->content = $this->add_view('histogram/index');

		$base = $this->template->content;
		$base->content = $this->add_view("histogram/histogram");
		$content = $base->content;
		$content->state_names = $state_names;

		$content->min = $min;
		$content->max = $max;
		$content->avg = $avg;
		$content->sum = $sum;
		$content->states = $state_names;
		$content->available_states = array_keys($min);
		$content->title = $title;
		$content->objects = $objects;
		$timeformat_str = nagstat::date_format();
		$content->report_time = date($timeformat_str, $this->options['start_time']).' '._('to').' '.date($timeformat_str, $this->options['end_time']);

		$this->template->content->report_options = $this->add_view('histogram/options');
		$tpl_options = $this->template->content->report_options;

		$tpl_options->sub_type = $sub_type;

		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;
		$this->template->title = _('Reporting » Histogram » Report');
	}

	/**
	*	Replace all integer indicies with proper
	* 	translated strings
	*/
	public function _get_xaxis_ticks()
	{
		if (empty($this->data)) {
			return false;
		}

		$return = false;
		$i = 0;
		foreach ($this->data as $key => $data) {
			switch ($this->options['breakdown']) {
				case 'dayofmonth':
					$return[] = '['.$i.', '.$key.']';
					$this->labels[] = "'".$key."'";
					break;
				case 'monthly':
					$return[] = '['.$i.', "'.$key.'"]';
					$this->labels[] = "'".$key."'";
					break;
				case 'dayofweek':
					$return[] = '['.$i.', "'.$key.'"]';
					$this->labels[] = "'".$key."'";
					break;
				case 'hourly':
					$return[] = '['.$i.', "'.$key.':00'.'"]';
					$this->labels[] = "'".$key.':00'."'";
					break;
			}
			$i++;
		}
		return '['.implode(',', $return).']';
	}

	/**
	*	Prepare data structore for use in histogram
	*/
	public function _prepare_graph_data($data=false)
	{
		if (empty($this->data)) {
			return false;
		}

		$return = false;
		$i = 0; # graph data needs to have 0 indicies
		foreach ($this->data as $key => $data) {
			foreach ($data as $k => $v) {
				$return[$k][] = '['.$i.','.$v.']';
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
