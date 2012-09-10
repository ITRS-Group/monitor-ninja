<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Network outages widget
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Netw_outages_Widget extends widget_Base {
	protected $duplicatable = true;
	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		$auth = Nagios_auth_Model::instance();

		# fetch info on outages
		$outages = new Outages_Model();
		$outage_data = $outages->fetch_outage_data();

		$label = _('Blocking Outages');
		$no_access_msg = _('N/A');

		$total_blocking_outages = !empty($outage_data) ? count($outage_data) : 0;

		$user_has_access = $auth->view_hosts_root ? true : false;

		require($view_path);
	}
}
