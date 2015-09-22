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
class Showlog_Controller extends Ninja_Controller
{
	private $options = array();

	/**
	 * Create a showlog controller
	 */
	public function __construct()
	{
		parent::__construct();

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

		if (!$this->mayi->run('monitoring.status:read.showlog')) {
			$this->options['hide_process'] = 1;
			$this->options['hide_commands'] = 1;
		}
	}

	/**
	 * Basic setup
	 */
	public function basic_setup()
	{
		$this->_verify_access('ninja.showlog:read.showlog');
		$this->template->js[] = 'application/media/js/jquery.datePicker.js';
		$this->template->js[] = 'application/media/js/jquery.timePicker.js';
		$this->template->js[] = 'modules/reports/views/reports/js/common.js';
		$this->template->js[] = 'modules/monitoring/views/showlog/js/showlog.js';


		$this->template->css[] = $this->add_path('reports/css/datePicker.css');
		$this->template->css[] = $this->add_path('showlog/css/showlog.css');
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

	/**
	 * Show log
	 */
	public function showlog()
	{
		$this->_verify_access('ninja.showlog:read.showlog');
		$this->template->content = $this->add_view('showlog/showlog');
		$this->basic_setup();
		$this->template->title = _("Reporting » Event Log");

		$this->template->toolbar = new Toolbar_Controller( _( "Event Log" ) );

		$resource = ObjectPool_Model::pool('status')->all()->mayi_resource();
		$this->template->content->is_authorized = $this->mayi->run($resource.':read.showlog');
		$this->template->content->options = $this->options;
	}
}
