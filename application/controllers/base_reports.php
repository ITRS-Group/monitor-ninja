<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Base_reports_Controller extends Authenticated_Controller
{
	public static $colors = array(
		'green' => '#aade53',
		'yellow' => '#ffd92f',
		'orange' => '#ff9d08',
		'red' 	=> '#f7261b',
		'grey' 	=> '#a19e95',
		'lightblue' => '#EAF0F2', # actual color is #ddeceb, but it is hardly visible
		'white' => '#ffffff'
	);

	protected static $sla_field_names = array(
		'hosts' => 'PERCENT_TOTAL_TIME_UP',
		'hostgroups' => 'PERCENT_TOTAL_TIME_UP',
		'services' => 'PERCENT_TOTAL_TIME_OK',
		'servicegroups' => 'PERCENT_TOTAL_TIME_OK'
	);

	protected $err_msg = '';

	protected $state_values = false;

	protected $abbr_month_names = false;
	protected $month_names = false;
	protected $day_names = false;
	protected $abbr_day_names = false;
	protected $first_day_of_week = 1;

	protected $histogram_link = "histogram/generate";

	public $type = false;

	protected $options = false;

	public function __construct() {
		if ($this->type === false)
			die("You must set $type in ".get_class($this));

		parent::__construct();

		$this->abbr_month_names = array(
			_('Jan'),
			_('Feb'),
			_('Mar'),
			_('Apr'),
			_('May'),
			_('Jun'),
			_('Jul'),
			_('Aug'),
			_('Sep'),
			_('Oct'),
			_('Nov'),
			_('Dec')
		);

		$this->month_names = array(
			_('January'),
			_('February'),
			_('March'),
			_('April'),
			_('May'),
			_('June'),
			_('July'),
			_('August'),
			_('September'),
			_('October'),
			_('November'),
			_('December')
		);

		$this->abbr_day_names = array(
			_('Sun'),
			_('Mon'),
			_('Tue'),
			_('Wed'),
			_('Thu'),
			_('Fri'),
			_('Sat')
		);

		$this->day_names = array(
			_('Sunday'),
			_('Monday'),
			_('Tuesday'),
			_('Wednesday'),
			_('Thursday'),
			_('Friday'),
			_('Saturday')
		);

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
	}

	abstract public function index($input = false);
	abstract public function generate($input = false);

	protected function discover_options($input = false)
	{
		# not using $_REQUEST, because that includes weird, scary session vars
		if (!empty($input)) {
			$report_info = $input;
		} else if (!empty($_POST)) {
			$report_info = $_POST;
		} else {
			$report_info = $_GET;
		}

		if (isset($report_info['report_id'])) {
			$saved_report_info = Saved_reports_Model::get_report_info($this->type, $report_info['report_id']);
			if ($saved_report_info) {
				$report_info = array_merge($saved_report_info, $report_info);
			}
		}
		return $report_info;
	}

	protected function create_options_obj($report_info = false, $type = false)
	{
		$class = ucfirst($type ? $type : $this->type) . '_options';
		if (!class_exists($class))
			$class = 'Report_options';
		$options = new $class($report_info);
		if (isset($report_info['report_id'])) {
			# now that report_type is set, ship off objects to the correct var
			$options[$options->get_value('report_type')] = $report_info['objects'];
		}
		return $options;
	}

	protected function setup_options_obj($input = false, $type = false)
	{
		if ($this->options) // If a child class has already set this, leave it alone
			return;

		$report_info = $this->discover_options($input);
		$this->options = $this->create_options_obj($report_info, $type);
		$this->template->set_global('options', $this->options);
	}

	/**
	 * Expands a series of groupnames (host or service) into its member objects, and calculate uptime for each
	 *
	 * @uses Reports_Model::get_uptime()
	 * @param array $arr List of groups
	 * @param string $type The type of objects in $arr. Valid values are "hostgroup" or "servicegroup".
	 * @param mixed $start_date datetime or unix timestamp
	 * @param mixed $end_date datetime or unix timestamp
	 * @global array Report options
	 * @global array Dependent report options
	 * @global array
	 * @global string Error log.
	 * @return array Calculated uptimes.
	 */
	protected function _expand_group_request($arr=false, $type='hostgroup')
	{
		$err_msg = $this->err_msg;

		if (empty($arr))
			return false;
		if ($type!='hostgroup' && $type!='servicegroup')
			return false;
		$hostgroup = false;
		$servicegroup = false;
		$data_arr = false;
		$real_group = $this->options[$type];
		foreach ($arr as $data) {
			$this->options[$type] = array($data);
			$model = new Reports_model($this->options);
			$data_arr[] = $model->get_uptime();
		}
		$this->options[$type] = $real_group;
		return $data_arr;
	}

	/**
	*	Determine what color to assign to an event
	*/
	protected function _state_colors($type='host', $state=false)
	{
		$colors['host'] = array(
			Reports_Model::HOST_UP => static::$colors['green'],
			Reports_Model::HOST_DOWN => static::$colors['red'],
			Reports_Model::HOST_UNREACHABLE => static::$colors['orange'],
			Reports_Model::HOST_PENDING => static::$colors['grey']
		);
		$colors['service'] = array(
			Reports_Model::SERVICE_OK => static::$colors['green'],
			Reports_Model::SERVICE_WARNING => static::$colors['orange'],
			Reports_Model::SERVICE_CRITICAL => static::$colors['red'],
			Reports_Model::SERVICE_UNKNOWN => static::$colors['grey'],
			Reports_Model::SERVICE_PENDING => static::$colors['grey']
		);
		return $colors[$type][$state];
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
	* 	@param array  $data_arr report source data, generated by report_class:get_uptime()
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
			Kohana::log('error', 'Stuff went belly-up: '.var_export($data_arr, true));
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

				$return['host_link'][] = $php_self . "?host_name[]=". $host_name . "&report_type=hosts&new_avail_report_setup=1".$get_vars;
				$return['service_link'][] = $php_self . "?host_name[]=". $host_name . '&service_description[]=' . "$host_name;$service_description" . '&report_type=services&start_time=' . $start_time . '&end_time=' . $end_time . '&new_avail_report_setup=1'.$get_vars;

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
			$return['group_average_ok'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_OK']);
			$return['group_average_warning'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_WARNING']);
			$return['group_average_unknown'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_UNKNOWN']);
			$return['group_average_critical'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_CRITICAL']);
			$return['group_average_undetermined'] = reports::format_report_value($group_averages['PERCENT_TOTAL_TIME_UNDETERMINED']);
			$return['groupname'] = !empty($data_arr['groupname']) ? 'Servicegroup: '.(is_array($data_arr['groupname'])?implode(', ', $data_arr['groupname']):$data_arr['groupname']) : false;
			$return[';testcase;'] = $data_arr[';testcase;'];
		} else {
			// host
			$sum_up = $sum_down = $sum_unreachable = $sum_undetermined = 0;
			foreach ($data_arr as $k => $data) {
			if (!reports::is_proper_report_item($k, $data))
					continue;
				$host_name = $data['states']['HOST_NAME'];
				$return['host_link'][] = $php_self . "?host_name[]=". $host_name. "&report_type=hosts" .
				'&start_time=' . $start_time . '&end_time=' . $end_time .$get_vars;
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

			$return['group_average_up'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_UP']);
			$return['group_average_down'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_DOWN']);
			$return['group_average_unreachable'] = reports::format_report_value($group_averages['PERCENT_KNOWN_TIME_UNREACHABLE']);
			$return['group_average_undetermined'] = reports::format_report_value($group_averages['PERCENT_TOTAL_TIME_UNDETERMINED']);
			$return['groupname'] = !empty($data_arr['groupname']) ? 'Hostgroup: '.(is_array($data_arr['groupname'])?implode(', ', $data_arr['groupname']):$data_arr['groupname']) : false;
			$return[';testcase;'] = $data_arr[';testcase;'];
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
		$testcase = isset($group[';testcase;']) ? $group[';testcase;'] : false;
		unset($group[';testcase;']);

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

		$group[';testcase;'] = $testcase;
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

	/**
	*	Fetch and print information on saved timperiods
	*/
	protected function _get_reporting_periods()
	{
		$res = Timeperiod_Model::get_all();
		if (!$res)
			return false;
		$return = false;
		foreach ($res as $row) {
			$return .= '<option value="'.$row->timeperiod_name.'">'.$row->timeperiod_name.'</option>';
		}
		return $return;
	}

	/**
	 * Print one alert totals table. Since they all look more or
	 * less the same, we can re-use the same function for all of
	 * them, provided we get the statenames (OK, UP etc) from the
	 * caller, along with the array of state totals.
	 */
	protected function _print_alert_totals_table($topic, $ary, $state_names, $totals, $name)
	{
		echo "<br /><table class=\"host_alerts\"><tr>\n";
		echo "<caption style=\"margin-top: 15px\">".$topic.' '._('for').' '.$name."</caption>".$spacer;
		echo "<th class=\"headerNone\">" . _('State') . "</th>\n";
		echo "<th class=\"headerNone\">" . _('Soft Alerts') . "</th>\n";
		echo "<th class=\"headerNone\">" . _('Hard Alerts') . "</th>\n";
		echo "<th class=\"headerNone\">" . _('Total Alerts') . "</th>\n";
		echo "</tr>\n";

		$total = array(0, 0); # soft and hard
		$i = 0;
		foreach ($ary as $state_id => $sh) {
			if (!isset($state_names[$state_id]))
				continue;
			$i++;
			echo "<tr class=\"".($i%2 == 0 ? 'odd' : 'even')."\">\n";
			echo "<td>" . $state_names[$state_id] . "</td>\n"; # topic
			echo "<td>" . $sh[0] . "</td>\n"; # soft
			echo "<td>" . $sh[1] . "</td>\n"; # hard
			$tot = $sh[0] + $sh[1];
			echo "<td>" . $tot . "</td>\n"; # soft + hard
			echo "</tr>\n";
		}
		$i++;
		echo "<tr class=\"".($i%2 == 0 ? 'odd' : 'even')."\"><td>Total</td>\n";
		echo "<td>" . $totals['soft'] . "</td>\n";
		echo "<td>" . $totals['hard'] . "</td>\n";
		$tot = $totals['soft'] + $totals['hard'];
		echo "<td>" . $tot . "</td>\n";
		echo "</tr></table><br />\n";
	}
}
