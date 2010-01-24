<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Showlog controller
 * *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 * @copyright 2009 op5 AB
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

	public function __construct()
	{
		parent::__construct();

		$this->logos_path = Kohana::config('config.logos_path');
		$this->get_options();
	}

	protected function get_options()
	{
		$x = $this->translate;
		$this->options = $this->input->get();
		if (empty($this->options))
			$this->options = $this->input->post();

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
	}

	public function _show_log_entries()
	{
		showlog::show_log_entries($this->options);
	}

	public function showlog($host = false)
	{
		$x = $this->translate;
		$this->template->title = $this->translate->_("View log");
		$this->template->disable_refresh = true;
		$this->template->content = $this->add_view('showlog/showlog');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$host_state_options = array
			($x->_('Host down') => 'd',
			 $x->_('Host unreachable') => 'u',
			 $x->_('Host recovery') => 'r');
		$service_state_options = array
			($x->_('Service warning') => 'w',
			 $x->_('Service unknown') => 'u',
			 $x->_('Service critical') => 'c',
			 $x->_('Service recovery') => 'r');

		if ($host) {
			if (!is_array($host)) {
				$host = array($host);
			}
			$this->options['host'] = $host;
		}
		$this->template->content->options = $this->options;
		$this->template->content->host_state_options = $host_state_options;
		$this->template->content->service_state_options = $service_state_options;
	}
}
