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
		$outages = $ls->getHosts( array(
			'filter' => array(
				'state'  => 1,
				'childs' => array(
					'!=' => ''
				)
			)
		));


		if(count($outages) > 0) {
			foreach($outages as &$host) {

				# count number of affected hosts / services

				list($affected_hosts,$affected_services,$severity) = $this->count_affected_hosts_and_services($host['name']);
				$host['affected_hosts']    = $affected_hosts;
				$host['affected_services'] = $affected_services;
				$host['severity']          = $severity;

			}
		}


		$this->template->toolbar = new Toolbar_Controller( _("Network outages"), _("Blocking outages") );

		$this->template->content = $this->add_view('outages/network_outages');
		$this->template->js_header = $this->add_view('js_header');
		$content = $this->template->content;
		$content->title = _('Blocking Outages');

		$content->outage_data = $outages;
		$this->template->title = _('Monitoring » Network outages');
	}


	private function count_affected_hosts_and_services($host) {
		/* FIXME: This method needs to be partly implemented in livestatus.
		 * That can be done by allowing the recursion in childs/parents-column
		 * to be handled within livestatus, and do a simple Stats-request instead
		 */
		$affected_hosts    = 0;
		$affected_services = 0;

		$hosts_to_test = array($host);
		$hosts_services = array();

		$ls = Livestatus::instance();
		$lsb = $ls->getBackend();

		$severity_value = 0;

		/* Iterate through all hosts with children. */
		while( !empty( $hosts_to_test ) ) {
			$host_name = array_shift($hosts_to_test);

			/* Skip if already tested... */
			if(isset($hosts_services[$host_name]))
				continue;

			$host = $ls->getHosts(array(
					'filter'=>array('name'=>$host_name),
					'columns' => array('name', 'childs', 'hourly_value', 'num_services')
			));
			$service_value = $lsb->getStats('services', array('value' => 'sum hourly_value'), array(
					'filter' => array('host_name'=>$host[0]['name'])
					));

			$hosts_services[$host_name] = $host[0]['num_services'];
			$hosts_to_test += $host[0]['childs'];

			$severity_value += $host[0]['hourly_value'] + $service_value[0]['value'];
		}

		return array(count($hosts_services), array_sum($hosts_services), $severity_value);
	}

}
