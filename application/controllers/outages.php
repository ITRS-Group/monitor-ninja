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
	const SERVICE_SEVERITY_DIVISOR = 4; /**< Magical constant that tells us how many times less interesting a service is compared to a host */

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
		$ls = Livestatus::instance();
		$outages = $ls->getHosts(array('filter' => array( 	'state'  => 1,
															'childs' => array( '!=' => '' )
													)));


		if(count($outages) > 0) {
			$all_hosts = array();
			$hosts = $ls->getHosts();
			foreach($hosts as &$h) {
				$all_hosts[$h['name']] =& $h;
			}

			foreach($outages as &$host) {
				# count number of affected hosts / services
				list($affected_hosts,$affected_services) = _count_affected_hosts_and_services($host['name'], $all_hosts);
				$host['affected_hosts']    = $affected_hosts;
				$host['affected_services'] = $affected_services;

				$host['severity'] = round($affected_hosts + $affected_services/self::SERVICE_SEVERITY_DIVISOR);
			}
		}


		$this->template->content = $this->add_view('outages/network_outages');
		$this->template->js_header = $this->add_view('js_header');
		$content = $this->template->content;
		$content->title = _('Blocking Outages');

		$content->outage_data = $outages;
		$this->template->title = _('Monitoring » Network outages');
	}
}

function _count_affected_hosts_and_services($host, $all_hosts) {
	$affected_hosts    = 0;
	$affected_services = 0;

	if(!isset($all_hosts[$host]))
		return(array(0,0));

	if(isset($all_hosts[$host]['childs']) && $all_hosts[$host]['childs'] != '') {
		foreach($all_hosts[$host]['childs'] as $child) {
			list($child_affected_hosts,$child_affected_services) = _count_affected_hosts_and_services($child, $all_hosts);
			$affected_hosts    += $child_affected_hosts;
			$affected_services += $child_affected_services;
		}
	}

	# add number of directly affected hosts
	$affected_hosts++;

	# add number of directly affected services
	$affected_services += $all_hosts[$host]['num_services'];

	return(array($affected_hosts, $affected_services));
}
