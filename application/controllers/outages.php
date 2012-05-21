<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Status controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
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
		$auth = Nagios_auth_Model::instance();
		if(!$auth->view_hosts_root) {
			$this->template->content = $this->add_view('unauthorized');
			$this->template->content->error_message = _('It appears as though you do not have permission to view information you requested...');
			$this->template->content->error_description = _('If you believe this is an error, check the HTTP server authentication requirements for accessing this page
			and check the authorization options in your CGI configuration file.');
			return;
		}
		$this->template->content = $this->add_view('outages/network_outages');
		$this->template->js_header = $this->add_view('js_header');
		$content = $this->template->content;
		$outages = new Outages_Model();
		$outage_data = $outages->fetch_outage_data();
		$content->title = _('Blocking Outages');

		$content->outage_data = $outage_data;
		$this->template->title = _('Monitoring » Network outages');
		# date::timespan(time(), $result->last_update, 'days,hours,minutes,seconds');

	}

}
