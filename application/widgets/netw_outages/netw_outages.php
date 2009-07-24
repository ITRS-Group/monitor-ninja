<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Network outages widget
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Netw_outages_Widget extends widget_Core {

	public function __construct()
	{
		parent::__construct();

		# needed to figure out path to widget
		$this->set_widget_name(__CLASS__, basename(__FILE__));
	}

	public function index($arguments=false, $master=false)
	{
		# required to enable us to assign the correct
		# variables to the calling controller
		$this->master_obj = $master;

		# fetch widget view path
		$view_path = $this->view_path('view');

		$auth = new Nagios_auth_Model();

		# fetch info on outages
		$outages = new Outages_Model();
		$outage_data = $outages->fetch_outage_data();

		# assign variables for our view

		$widget_id = $this->widgetname;
		$refresh_rate = 60;
		if (isset($arguments['refresh_interval'])) {
			$refresh_rate = $arguments['refresh_interval'];
		}

		$title = $this->translate->_('Network Outages');
		if (isset($arguments['widget_title'])) {
			$title = $arguments['widget_title'];
		}

		# let view template know if wrapping div should be hidden or not
		$ajax_call = request::is_ajax() ? true : false;

		$label = $this->translate->_('Blocking Outages');
		$no_access_msg = $this->translate->_('N/A');

		$total_blocking_outages = !empty($outage_data) ? count($outage_data) : 0;

		$user_has_access = $auth->view_hosts_root ? true : false;

		# fetch widget content
		require_once($view_path);

		if(request::is_ajax()) {
			# output widget content
			echo json::encode( $this->output());
		} else {
			# set required extra resources
			$this->js = array('/js/netw_outages');

			# call parent helper to assign all
			# variables to master controller
			return $this->fetch();
		}
	}
}
