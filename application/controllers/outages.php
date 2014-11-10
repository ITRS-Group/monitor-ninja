<?php
defined( 'SYSPATH' ) or die( 'No direct access allowed.' );
/**
 * Outages controller
 *
 * Calculate the value of network outages
 *
 * op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 * or registered trademarks of op5 AB.
 * All other trademarks, servicemarks, registered trademarks, and registered
 * servicemarks mentioned herein may be the property of their respective
 * owner(s).
 * The information contained herein is provided AS IS with NO WARRANTY OF ANY
 * KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 * PARTICULAR PURPOSE.
 */
class Outages_Controller extends Ninja_Controller {
	/**
	 * default method
	 *
	 * @name index
	 */
	public function index() {
		$outages_objs = HostPool_Model::all()->reduce_by( 'state', 1, '=' )->reduce_by( 'childs', '', '!=' );
		/* @var $outages_objs HostSet_Model */

		$this->_verify_access($outages_objs->mayi_resource().':view.list.outages');

		$outages = array ();
		foreach ( $outages_objs as $host_obj ) {
			$host = $host_obj->export();

			// count number of affected hosts / services

			list ( $affected_hosts, $affected_services, $severity ) = $this->count_affected_hosts_and_services( $host_obj );

			$host['affected_hosts'] = $affected_hosts;
			$host['affected_services'] = $affected_services;
			$host['severity'] = $severity;

			$outages[] = $host;
		}

		$this->template->toolbar = new Toolbar_Controller( _( "Network outages" ), _( "Blocking outages" ) );

		$this->template->content = $this->add_view( 'outages/network_outages' );
		$content = $this->template->content;
		$content->title = _( 'Blocking Outages' );

		$content->outage_data = $outages;
		$this->template->title = _( 'Monitoring » Network outages' );
	}
	private function count_affected_hosts_and_services($host) {
		/*
		 * TODO: This method needs to be partly implemented in livestatus.
		 * That can be done by allowing the recursion in childs/parents-column
		 * to be handled within livestatus, and do a simple Stats-request
		 * instead
		 *
		 * But until then, this is needed...
		 */
		$affected_hosts = 0;
		$affected_services = 0;

		$hosts_to_test = array (
				$host->get_name()
		);
		$hosts_services = array ();

		$severity_value = 0;

		/* Iterate through all hosts with children. */
		while ( ! empty( $hosts_to_test ) ) {
			$host_name = array_shift( $hosts_to_test );
			$host = HostPool_Model::fetch_by_key( $host_name );
			/* @var $host Host_Model */

			/* Skip if already tested... */
			if (isset( $hosts_services[$host_name] ))
				continue;

				/* Fetch sum of hourly_values */
			$service_value = 0;
			foreach ( $host->get_services_set()->it( array (
					'hourly_value'
			), array () ) as $service ) {
				$service_value += $service->get_hourly_value();
			}

			$hosts_services[$host_name] = $host->get_num_services();
			$hosts_to_test += $host->get_childs();

			$severity_value += $host->get_hourly_value() + $service_value;
		}

		return array (
				count( $hosts_services ),
				array_sum( $hosts_services ),
				$severity_value
		);
	}
}
