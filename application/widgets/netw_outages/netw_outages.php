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

		$current_status = new Current_status_Model();
		$auth = new Nagios_auth_Model();

		# fetch info on outages
		$current_status->find_hosts_causing_outages();

		# assign variables for our view
		$title = $this->translate->_('Network Outages');
		$label = $this->translate->_('Outages');
		$no_access_msg = $this->translate->_('N/A');

		$total_blocking_outages = $current_status->total_blocking_outages;

		$user_has_access = $auth->view_hosts_root ? true : false;

		# fetch widget content
		require_once($view_path);

		# call parent helper to assign all
		# variables to master controller
		return $this->fetch();
	}
}