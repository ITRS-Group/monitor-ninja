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
class Status_Controller extends Authenticated_Controller {

	/**
	 * Equivalent to style=hostdetail
	 *
	 * @param $host
	 */
	public function host($host='all', $hoststatustypes=false, $sort_order='ASC', $sort_field='name', $show_services=false, $group_type=false, $serviceprops=false, $hostprops=false)
	{
		$host = $this->input->get('host', $host);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$sort_order = $this->input->get('sort_order', $sort_order);
		$sort_field = $this->input->get('sort_field', $sort_field);
		#$show_services = $this->input->get('show_services', $show_services);
		$group_type = $this->input->get('group_type', $group_type);
		$group = $this->input->get('group', false);
		#$serviceprops = $this->input->get('serviceprops', $serviceprops);
		$hostprops = $this->input->get('hostprops', $hostprops);
		$group_type = strtolower($group_type);

		$status = new Old_Status_Model();
		if( !empty($group_type) && $group_type=='hostgroup' ) {
			list($hostfilter, $servicefilter) = $status->classic_filter('host', $host, $group, false, $hoststatustypes, $hostprops, false, $serviceprops);
		}
		else if( !empty($group_type) && $group_type=='servicegroup' ) {
			list($hostfilter, $servicefilter) = $status->classic_filter('host', $host, false, $group, $hoststatustypes, $hostprops, false, $serviceprops);
		}
		else {
			list($hostfilter, $servicefilter) = $status->classic_filter('host', $host, false, false, $hoststatustypes, $hostprops, false, $serviceprops);
		}

		return $this->_redirect_to_query($hostfilter);
	}

	/**
	 * List status details for hosts and services
	 *
	 */
	public function service($name='all', $hoststatustypes=false, $servicestatustypes=false, $service_props=false, $sort_order='ASC', $sort_field='host_name', $group_type=false, $hostprops=false)
	{
		$name = $this->input->get('name', $name);
		$hoststatustypes = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$service_props = $this->input->get('serviceprops', $service_props);
		$service_props = $this->input->get('service_props', $service_props);
		$hostprops = $this->input->get('hostprops', $hostprops);
		$sort_order = $this->input->get('sort_order', $sort_order);
		$sort_field = $this->input->get('sort_field', $sort_field);
		$group_type = $this->input->get('group_type', $group_type);
		$noheader = $this->input->get('noheader', false);
		$group_type = strtolower($group_type);

		$status = new Old_Status_Model();
		if(empty($group_type)) {
			list($hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter) = $status->classic_filter('service', $name, false, false, $hoststatustypes, $hostprops, $servicestatustypes, $service_props);
		} else {
			if($group_type=='servicegroup') {
				list($hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter) = $status->classic_filter('service', false, false, $name, $hoststatustypes, $hostprops, $servicestatustypes, $service_props);
			} else {
				list($hostfilter, $servicefilter, $hostgroupfilter, $servicegroupfilter) = $status->classic_filter('service', false, $name, false, $hoststatustypes, $hostprops, $servicestatustypes, $service_props);
			}
		}


		return $this->_redirect_to_query($servicefilter);
	}

	/**
	*	Wrapper for Service problems link in menu
	*/
	public function service_problems()
	{
		return $this->_redirect_to_query('[services] has_been_checked!=0 and state!=0');
	}

	/**
	*	Wrapper for Host problems link in menu
	*/
	public function host_problems()
	{
		return $this->_redirect_to_query('[hosts] has_been_checked!=0 and state!=0');
	}

	/**
	 * Show servicegroup status, wrapper for group('service', ...)
	 * @param $group
	 *
	 */
	public function servicegroup($group='all', $hoststatustypes=false, $servicestatustypes=false, $style='overview', $serviceprops=false, $hostprops=false)
	{
		return $this->group('service', $group, $hoststatustypes, $servicestatustypes, $style, $serviceprops, $hostprops);
	}

	/**
	 * Show hostgroup status, wrapper for group('host', ...)
	 * @param $group
	 *
	 */
	public function hostgroup($group='all', $hoststatustypes=false, $servicestatustypes=false, $style='overview', $serviceprops=false, $hostprops=false)
	{
		return $this->group('host', $group, $hoststatustypes, $servicestatustypes, $style, $serviceprops, $hostprops);
	}

	/**
	 * Show group status
	 */
	public function group($grouptype='service', $group='all', $hoststatustypes=false, $servicestatustypes=false, $style='overview', $serviceprops=false, $hostprops=false)
	{
		$grouptype          = $this->input->get('grouptype', $grouptype);
		$group              = $this->input->get('group', $group);
		$hoststatustypes    = $this->input->get('hoststatustypes', $hoststatustypes);
		$servicestatustypes = $this->input->get('servicestatustypes', $servicestatustypes);
		$serviceprops       = $this->input->get('serviceprops', $serviceprops);
		$hostprops          = $this->input->get('hostprops', $hostprops);
		$status = new Old_Status_Model();
		list($hostfilter, $servicefilter) = $status->classic_filter($grouptype, false, ($grouptype=='host' ? $group : false), ($grouptype=='service' ? $group : false), $hoststatustypes, $hostprops, $servicestatustypes, $serviceprops);
		return $this->_redirect_to_query(${$grouptype.'filter'});
	}

	/**
	 * Display servicegroup summary
	 */
	public function servicegroup_summary()
	{
		return $this->_redirect_to_query('[servicegroups] all');
	}

	/**
	 * Display hostgroups summary
	 */
	public function hostgroup_summary()
	{
		return $this->_redirect_to_query('[hostgroups] all');
	}
	
	private function _redirect_to_query( $query ) {
		return url::redirect('listview?'.http_build_query(array('q'=>$query)));
	}
}
