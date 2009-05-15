<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Status controller
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Outages_Controller extends Authenticated_Controller
{
	/**
	*	@name	index
	*	@desc	default method
	*
	*/
	public function index()
	{
		return $this->display_network_outages();
	}

	/**
	*	shows all hosts that are causing network outages
	*/
	public function display_network_outages()
	{
		$auth = new Nagios_auth_Model();
		if(!$auth->view_hosts_root) {
			$this->template->content = $this->add_view('unauthorized');
			$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to view information you requested...');
			$this->template->content->error_description = $this->translate->_('If you believe this is an error, check the HTTP server authentication requirements for accessing this page
			and check the authorization options in your CGI configuration file.');
			return;
		}
		$this->template->content = $this->add_view('outages/network_outages');
		$content = $this->template->content;
		$outages = new Outages_Model();
		$outage_data = $outages->fetch_outage_data();
		$t = $this->translate;
		$content->title = $t->_('Blocking Outages');
		$content->label_severity = $t->_('Severity');
		$content->label_host = $t->_('Host');
		$content->label_state = $t->_('State');
		$content->label_notes = $t->_('Notes');
		$content->label_duration = $t->_('State Duration');
		$content->label_hosts_affected = $t->_('# Hosts Affected');
		$content->label_services_affected = $t->_('# Services Affected');
		$content->label_actions = $t->_('Actions');

		$content->outage_data = $outage_data;
		# date::timespan(time(), $result->last_update, 'days,hours,minutes,seconds');

	}

}
