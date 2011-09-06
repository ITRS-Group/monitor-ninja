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

		$this->template->content = $this->add_view('hypermap/hypermap');
		$content = $this->template->content;

		$content->hyperapplet_path = Kohana::config('config.site_domain')
			.'application/'. Kohana::config('hypergraph.hyperapplet_path');
		$content->nagios_prop = Kohana::config('config.site_domain')
			.'application/'. Kohana::config('hypergraph.nagios_props');
		$content->xml_path = url::site().'hypermap/createxml';
	}

	/**
	*	Create the xml data needed for hyperapplet
	*/
	public function createxml()
	{
		$this->template->content = $this->add_view('hypermap/xml');
		$content = $this->template->content;
		$content->dtd = Kohana::config('config.site_domain')
			.'application/'.Kohana::config('hypergraph.hyper_dtd');

		$host_model = new Host_Model();
		$host_model->show_services = false;
		$host_parents = false;
		$no_parents = false;

		$result = $host_model->get_host_status();
		$content->result = $result;

		foreach ($result as $host) {
			$parent = $host_model->get_parents($host->host_name);
			if ($parent != false) {
				$parent = $parent[0];
				$host_parents[$host->host_name] = $parent->host_name;
			} else {
				$no_parents[] = $host->host_name;
			}
		}

		$content->data = $host_parents;
		$content->no_parents = $no_parents;
		echo $content->render();

		# prevent ninja from displaying master template etc
		die();
	}
}
