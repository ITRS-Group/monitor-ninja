<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Showlog controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Showlog_Controller extends Authenticated_Controller
{
	private $show;
	private $logos_path = '';
	private $options = array();

	private $abbr_month_names = false;
	private $month_names = false;
	private $day_names = false;
	private $abbr_day_names = false;
	private $first_day_of_week = 1;

	public function __construct()
	{
		parent::__construct();

		$this->logos_path = Kohana::config('config.logos_path');
		$this->get_options();

		$this->abbr_month_names = array(
			$this->translate->_('Jan'),
			$this->translate->_('Feb'),
			$this->translate->_('Mar'),
			$this->translate->_('Apr'),
			$this->translate->_('May'),
			$this->translate->_('Jun'),
			$this->translate->_('Jul'),
			$this->translate->_('Aug'),
			$this->translate->_('Sep'),
			$this->translate->_('Oct'),
			$this->translate->_('Nov'),
			$this->translate->_('Dec')
		);

		$this->month_names = array(
			$this->translate->_('January'),
			$this->translate->_('February'),
			$this->translate->_('March'),
			$this->translate->_('April'),
			$this->translate->_('May'),
			$this->translate->_('June'),
			$this->translate->_('July'),
			$this->translate->_('August'),
			$this->translate->_('September'),
			$this->translate->_('October'),
			$this->translate->_('November'),
			$this->translate->_('December')
		);

		$this->abbr_day_names = array(
			$this->translate->_('Sun'),
			$this->translate->_('Mon'),
			$this->translate->_('Tue'),
			$this->translate->_('Wed'),
			$this->translate->_('Thu'),
			$this->translate->_('Fri'),
			$this->translate->_('Sat')
		);

		$this->day_names = array(
			$this->translate->_('Sunday'),
			$this->translate->_('Monday'),
			$this->translate->_('Tuesday'),
			$this->translate->_('Wednesday'),
			$this->translate->_('Thursday'),
			$this->translate->_('Friday'),
			$this->translate->_('Saturday')
		);
	}

	protected function get_options()
	{
		$x = $this->translate;
		$this->options = $this->input->get();
		if (empty($this->options))
			$this->options = $this->input->post();

		if (isset($this->options['first']) && !empty($this->options['first'])) {
			$this->options['first'] = strtotime($this->options['first']);
		}
		if (isset($this->options['last']) && !empty($this->options['last'])) {
			$this->options['last'] = strtotime($this->options['last']);
		}
		if (!empty($this->options) && !empty($this->options['have_options'])) {
			if (!isset($this->options['host_state_options'])) {
				$this->options['host_state_options'] = array();
			}
			if (!isset($this->options['service_state_options'])) {
				$this->options['service_state_options'] = array();
			}
			return;
		}

		# set default if no options are found
		$this->options = array
			('detail' => array('service' => '15', 'host' => '7'),
			 'state_type' => array('soft' => true, 'hard' => true),
			 'host_state_options' =>
			 array('r' => true, 'd' => true, 'u' => true),
			 'service_state_options' =>
			 array('r' => true, 'w' => true, 'c' => true, 'u' => true),
			 );

		$auth = new Nagios_auth_Model();
		if (!$auth->authorized_for_system_information) {
			$this->options['hide_process'] = 1;
			$this->options['hide_commands'] = 1;
		}
	}

	public function _show_log_entries()
	{
		$user = user::session('username');
		if (!empty($user))
			$this->options['user'] = $user;
		showlog::show_log_entries($this->options);
	}

	public function basic_setup()
	{
		$x = $this->translate;

		$this->template->content = $this->add_view('showlog/showlog');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_js[] = 'application/media/js/date';
		$this->xtra_js[] = 'application/media/js/jquery.datePicker';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker';
		$this->xtra_js[] = 'application/media/js/jquery.fancybox.min';
		$this->xtra_js[] = $this->add_path('reports/js/common');
		$this->xtra_js[] = $this->add_path('showlog/js/showlog');

		$this->template->js_header->js = $this->xtra_js;

		$this->xtra_css[] = $this->add_path('reports/css/datePicker');
		$this->xtra_css[] = $this->add_path('showlog/css/showlog');
		$this->xtra_css[] = 'application/media/css/jquery.fancybox';
		$this->template->css_header->css = $this->xtra_css;
		# fetch users date format in PHP style so we can use it
		# in date() below
		$date_format = cal::get_calendar_format(true);

		$js_month_names = "Date.monthNames = ".json::encode($this->month_names).";";
		$js_abbr_month_names = 'Date.abbrMonthNames = '.json::encode($this->abbr_month_names).';';
		$js_day_names = 'Date.dayNames = '.json::encode($this->day_names).';';
		$js_abbr_day_names = 'Date.abbrDayNames = '.json::encode($this->abbr_day_names).';';
		$js_day_of_week = 'Date.firstDayOfWeek = '.$this->first_day_of_week.';';
		$js_date_format = "Date.format = '".cal::get_calendar_format()."';";
		$js_start_date = "_start_date = '".date($date_format, mktime(0,0,0,1, 1, 1996))."';";

		# inline js should be the
		# var host =
		# var service =
		# 	etc...
		$this->inline_js .= "\n".$js_month_names."\n";
		$this->inline_js .= $js_abbr_month_names."\n";
		$this->inline_js .= $js_day_names."\n";
		$this->inline_js .= $js_abbr_day_names."\n";
		$this->inline_js .= $js_day_of_week."\n";
		$this->js_strings .= $js_date_format."\n";
		$this->inline_js .= $js_start_date."\n";
		$this->js_strings .= reports::js_strings();
		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;

		$host_state_options = array
			($x->_('Host down') => 'd',
			 $x->_('Host unreachable') => 'u',
			 $x->_('Host recovery') => 'r');
		$service_state_options = array
			($x->_('Service warning') => 'w',
			 $x->_('Service unknown') => 'u',
			 $x->_('Service critical') => 'c',
			 $x->_('Service recovery') => 'r');

		$this->template->content->host_state_options = $host_state_options;
		$this->template->content->service_state_options = $service_state_options;
	}

	public function alert_history($obj_name = false)
	{
		$this->basic_setup();
		$this->template->title = $this->translate->_("Reporting » Alert history");
		$this->template->disable_refresh = true;
		if ($obj_name) {
			$obj_type = 'host';
			$service = urldecode( # check for service param passed in GET or POST
					$this->input->get('service',
						$this->input->post('service', false)
					)
				);
			if (!is_array($obj_name)) {
				if (strstr($obj_name, ';') !== false || !empty($service)) {
					$obj_type = 'service';
					$this->options['host_state_options'] = array();
					$this->options['hide_downtime'] = true;
					$this->options['hide_logrotation'] = true;
				}
				$obj_name = !empty($service) ? $obj_name.';'.$service : $obj_name;
				$obj_name = array($obj_name);
			}
			$this->options[$obj_type] = $obj_name;
		}

		if (!isset($this->options['have_options'])) {
			$this->options['hide_process'] = true;
			$this->options['hide_initial'] = true;
			$this->options['hide_commands'] = true;
			$this->options['hide_notifications'] = true;
		}

		$auth = new Nagios_auth_Model();
		$is_authorized = false;
		if ($auth->authorized_for_system_information) {
			$is_authorized = true;
		}

		$this->template->content->is_authorized = $is_authorized;
		$this->template->content->options = $this->options;
	}

	public function showlog()
	{
		$this->basic_setup();
		$this->template->title = $this->translate->_("Reporting » Event Log");
		$this->options['hide_initial'] = true;

		$auth = new Nagios_auth_Model();
		$is_authorized = false;
		if ($auth->authorized_for_system_information) {
			$is_authorized = true;
		}

		$this->template->content->is_authorized = $is_authorized;
		$this->template->content->options = $this->options;
	}
}
