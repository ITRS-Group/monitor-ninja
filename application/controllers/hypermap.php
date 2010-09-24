<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Configuration controller used to connect to Hypergraph
 * http://hypergraph.sf.net
 * Requires authentication
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Hypermap_Controller extends Authenticated_Controller {

	/**
	 * Enable links from Ninja to hypermap
	 * @see http://hypergraph.sf.net
	 *
	 * Checks are made that hypermap is configured in config file
	 * and that user is authenticated for all hosts and services
	 */
	public function index()
	{
		$this->template->disable_refresh = true;
		$auth = new Nagios_auth_Model();
		if (!$auth->view_hosts_root || !$auth->view_services_root) {
			$this->template->content = $this->add_view('unauthorized');
			$this->template->content->error_message = $this->translate->_("It appears as though you aren't authorized to access the hypermap.");
			$this->template->content->error_description = $this->translate->_('Read the section of the documentation that deals with authentication and authorization in the CGIs for more information.');
			return false;
		}
		$hypermap_path = Kohana::config('config.hypermap_path');
		$service = urldecode($this->input->get('service', false));
		if ($hypermap_path === false) {
			return false;
		}

		$this->template->content = '<iframe src="'.$hypermap_path.'" style="width: 100%; height: 768px" frameborder="0" id="iframe"></iframe>';
		$this->template->title = $this->translate->_('Hypermap');
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->xtra_js = array($this->add_path('hypermap/hypermap.js'));
		$this->template->js_header->js = $this->xtra_js;
	}
}
