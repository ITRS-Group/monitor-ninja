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
	private $options = array();

	public function __construct()
	{
		parent::__construct();

		$this->get_options();
	}

	protected function get_options()
	{
		$this->options = $this->input->get();
		if (empty($this->options))
			$this->options = $this->input->post();


		if (!empty($this->options['cal_start']) && isset($this->options['time_start'])) {
			$this->options['first'] = strtotime($this->options['cal_start'] . ' ' . $this->options['time_start']);
		} else if (isset($this->options['first']) && !empty($this->options['first'])) {
			$this->options['first'] = strtotime($this->options['first']);
		}


		if (!empty($this->options['cal_end']) && isset($this->options['time_end'])) {
			$this->options['last'] = strtotime($this->options['cal_end'] . ' ' . $this->options['time_end']);
		} else if (isset($this->options['last']) && !empty($this->options['last'])) {
			$this->options['last'] = strtotime($this->options['last']);
		}
		if ($this->options) {
			if (!isset($this->options['host_state_options'])) {
				$this->options['host_state_options'] = array();
			}
			if (!isset($this->options['service_state_options'])) {
				$this->options['service_state_options'] = array();
			}
			return;
		}

		# set default if no options are found
		$defaults = array(
			 'state_type' => array('soft' => true, 'hard' => true),
			 'host_state_options' => array('r' => true, 'd' => true, 'u' => true),
			 'service_state_options' => array('r' => true, 'w' => true, 'c' => true, 'u' => true),
			 'hide_initial' => true
		);
		foreach($defaults as $key => $value) {
			if(!isset($this->options[$key])) {
				$this->options[$key] = $value;
			}
		}

		if (!Auth::instance()->authorized_for('system_information')) {
			$this->options['hide_process'] = 1;
			$this->options['hide_commands'] = 1;
		}
	}

	public function _show_log_entries()
	{
		showlog::show_log_entries($this->options);
	}

	public function basic_setup()
	{
		$this->xtra_js[] = 'application/media/js/jquery.datePicker.js';
		$this->xtra_js[] = 'application/media/js/jquery.timePicker.js';
		$this->xtra_js[] = $this->add_path('reports/js/common.js');
		$this->xtra_js[] = $this->add_path('showlog/js/showlog.js');


		$this->xtra_css[] = $this->add_path('reports/css/datePicker.css');
		$this->xtra_css[] = $this->add_path('showlog/css/showlog.css');
		$this->js_strings .= reports::js_strings();
		$this->template->inline_js = $this->inline_js;
		$this->template->js_strings = $this->js_strings;

		$host_state_options = array
			(_('Host down') => 'd',
			 _('Host unreachable') => 'u',
			 _('Host recovery') => 'r');
		$service_state_options = array
			(_('Service warning') => 'w',
			 _('Service unknown') => 'u',
			 _('Service critical') => 'c',
			 _('Service recovery') => 'r');

		$this->template->content->host_state_options = $host_state_options;
		$this->template->content->service_state_options = $service_state_options;
	}

	public function showlog()
	{
		$this->template->content = $this->add_view('showlog/showlog');
		$this->basic_setup();
		$this->template->title = _("Reporting » Event Log");

		$is_authorized = false;
		if (Auth::instance()->authorized_for('system_information')) {
			$is_authorized = true;
		}

		$this->template->toolbar = new Toolbar_Controller( _( "Event Log" ) );

		$this->template->content->is_authorized = $is_authorized;
		$this->template->content->options = $this->options;
	}
}
